<?php
namespace backend\modules\calendar\controllers;

use App;
use backend\filters\BackendAccessRule;
use backend\models\BackendController;
use backend\modules\calendar\CalendarModule;
use backend\modules\calendar\forms\booking\BookingForm;
use backend\modules\calendar\forms\booking\BookingHistorySearchForm;
use backend\modules\calendar\forms\booking\BookingOnlineForm;
use backend\modules\calendar\forms\booking\CalendarForm;
use backend\modules\calendar\forms\booking\WorkerCalendarSlotForm;
use backend\modules\calendar\forms\shop\ShopConfigForm;
use backend\modules\calendar\forms\shop\ShopConfigsForm;
use backend\modules\system\forms\SystemConfigExportForm;
use common\components\WebSocketClient;
use common\entities\calendar\BookingInfo;
use common\entities\shop\ShopCalendarSlot;
use common\entities\shop\ShopConfig;
use common\entities\system\EventSound;
use common\entities\system\SystemConfig;
use common\entities\user\UserInfo;
use common\entities\worker\WorkerInfo;
use common\helper\ArrayHelper;
use common\helper\DatetimeHelper;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use Yii;

class BookingController extends BackendController
{
    public $layout = "layout_admin";

    private $bookingStatusMapper = [
        BookingForm::STATUS_PENDING => 'pending',
        BookingForm::STATUS_ACCEPTED => 'accepted',
        BookingForm::STATUS_REJECTED => 'rejected',
        BookingForm::STATUS_UPDATING => 'updating',
    ];

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => BackendAccessRule::className(),
                    "module" => CalendarModule::MODULE_ID
                ],
                'rules' => [
                    [
                        'actions' => [
                            'index',
                            'load-list-worker',
                            'load-calendar-worker',
                            'get-session',
                            'get-booking-count',
                            "load-booking-info",
                            'update-booking-note',
                            'cancel-booking-info',
                            'delete-booking-info',
                            "history",
                            'remove-slot-expired',
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'index' => ['post', 'get'],
                    'remove-slot-expired' => ['get','post'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $model = new CalendarForm();
        $model->load($this->request->post());
        $model->load($this->request->get());

        $model->date = $this->request->get("date", DatetimeHelper::now(DatetimeHelper::FULL_DATE));
        $shops = $model->shops;
        $shopIds = ArrayHelper::getColumn($shops, "shop_id");
        $requestShopIds = $this->request->get("shop_ids");

        if (count($shopIds) > 0 &&
            ($requestShopIds == null || (
                    count(array_intersect($requestShopIds, $shopIds)) == 0)
            )
        ) {
            return $this->redirect([
                Yii::$app->urlManager->createUrl([
                    "calendar/booking",
                    "shop_ids" => array_slice($shopIds, 0, 1),
                    "date" => $this->request->get("date")
                ]),
            ]);
        }
        $model->shop_ids = $this->request->get("shop_ids", $shopIds);
        $model->type = $this->request->get('type');
        $_SESSION['shop_ids'] = $model->shop_ids;
        $listCourses = BookingInfo::getListCourseType();
        return $this->render('index', [
            "model" => $model,
            "listCourses" => $listCourses,
            "dataShops" => $shops
        ]);
    }

    public function actionGetSession()
    {
        $session = [];
        if (!empty($_SESSION['shop_ids'])){
            foreach ($_SESSION['shop_ids'] as $value) {
                $session[] = (int)$value;
            }
        }
        $data = [
            'session' => $session,
        ];

        return Json::encode($data);
    }

    public function actionGetBookingCount($date = null) {
        if ($date == null) {
            $date = DatetimeHelper::now(DatetimeHelper::FULL_DATE);
        }
        $result = \Yii::$app->getDb()
            ->createCommand("
                SELECT b.shop_id, a.status, count(a.booking_id) as cnt
                FROM (
                    SELECT bi.booking_id, bi.slot_id, bi.status
                    FROM booking_info bi
                    WHERE bi.status = :bookingStatus OR bi.status = :bookingStatus2
                ) a
                JOIN (
                    SELECT slot_id, shop_id
                    FROM shop_calendar_slot scs
                    WHERE scs.date = :date
                ) b
                ON a.slot_id = b.slot_id
                GROUP BY b.shop_id, a.status
            ", [
                ':bookingStatus' => BookingForm::STATUS_PENDING,
                ':bookingStatus2' => BookingForm::STATUS_UPDATING,
                ':date' => $date
            ])
            ->queryAll();
        $empty = [
            'pending' => 0,
        ];

        $model = new CalendarForm();
        $model->date = $date;
        $shopIds = ArrayHelper::getColumn($model->shops, "shop_id");

        $responses = [];
        foreach ($shopIds as $shopId) {
            $responses[$shopId] = $empty;
        }

        foreach ($result as $row) {
            $responses[
            $row['shop_id']
            ][
            $this->bookingStatusMapper[$row['status']]
            ] = (int) $row['cnt'];
        }
        if (count($responses) == 0) {
            return Json::encode((object) null);
        }
        return Json::encode($responses);
    }

    public function actionLoadListWorker()
    {
        $this->response->format = Response::FORMAT_JSON;
        $model = new CalendarForm();
        $model->date = $this->request->get("date", DatetimeHelper::now(DatetimeHelper::FULL_DATE));
        $model->shop_ids = $this->request->get("shop_ids", ArrayHelper::getColumn($model->shops, "shop_id"));
        $workers = $model->workers;
        foreach ($workers as $key => $worker) {
            $workers[$key]["bookingRepeatPercent"] = \App::$app->formatter->asPercent(WorkerInfo::getBookingRepeatPercent($worker['worker_id']));
        }
        return [
            "success" => true,
            "params" => [
                $model->date,
                $model->shop_ids
            ],
            "data" => $workers,
        ];
    }

    public function actionLoadCalendarWorker($worker_id = null)
    {
        $this->response->format = Response::FORMAT_JSON;
        $model = new CalendarForm();
        $model->date = $this->request->get("date", DatetimeHelper::now(DatetimeHelper::FULL_DATE));
        $model->shop_ids = $this->request->get("shop_ids", ArrayHelper::getColumn($model->shops, "shop_id"));
        $model->type = $this->request->get("type");
        $datas = $model->getCalendarDataSecond($worker_id);
        $datas['background'] = SystemConfig::getColorToHtml(SystemConfig::BACKGROUND);
        $colorShops = ShopConfig::find()->select(['shop_id', 'value'])->where([
            'in', 'shop_id', $model->shop_ids
        ])->andWhere(['key' => ShopConfig::KEY_SHOP_COLOR])->all();
        $datas['colorShop'] = null;
        foreach ($colorShops as $colorShop) {
            $datas['colorShop'] = [
                $colorShop->shop_id => $colorShop->value,
            ];
        }
        return [
            "success" => true,
            "params" => [
                $model->date,
                $model->shop_ids,
                $model->type
            ],
            "data" => $datas['data'],
            "background" => $datas['background'],
            'startTime' => $datas['startTime'],
            'colorShop' => $datas['colorShop'] ? $datas['colorShop'] : null,
            'timeConfirmExpired' => intval(SystemConfig::getValue(SystemConfig::CATEGORY_BOOKING, SystemConfig::BOOKING_TIME_CONFIRM_EXPIRED)),
        ];
    }

    public function actionLoadBookingInfo($id)
    {
        if (!$this->request->isAjax) {
            return $this->redirect("index");
        }
        $this->response->format = Response::FORMAT_JSON;
        $model = BookingOnlineForm::findOne($id);
        if ($model) {
            $data = $model->toArray();
            $phoneNumber = $data['memberInfo']['phone_number'];
            $users = UserInfo::findOne(['phone_number' => $phoneNumber]);
            $data['memberInfo']['full_name'] = isset($users->full_name) ? $users->full_name : $users->phone_number;
            return [
                "success" => true,
                "data" => [
                    "booking-info" => $data,
                    "booking-histories" => $model->bookingHistories,
                ],
            ];
        }
        return [
            "success" => false,
        ];
    }

    public function actionUpdateBookingNote($id)
    {
        if (!$this->request->isAjax) {
            return $this->redirect("index");
        }
        $this->response->format = Response::FORMAT_JSON;
        $model = BookingForm::findOne($id);
        if ($model) {
            if ($model->toUpdateNote($this->request->post("note"))) {
                return [
                    "success" => true,
                    "message" => \App::t("backend.booking.message", "Đã cập nhật ghi chú"),
                ];
            } else {
                return [
                    "success" => false,
                    "message" => \App::t("backend.booking.message", "Have erorr when update booking note"),
                    "error" => $model->getErrors(),
                ];
            }
        }
        return [
            "success" => false,
            "message" => \App::t("backend.booking.message", "Booking [{id}] is not found", [
                "id" => $id,
            ]),
        ];
    }

    public function actionCancelBookingInfo($id, $smsContent = "")
    {
        if (!$this->request->isAjax) {
            return $this->redirect("index");
        }
        $this->response->format = Response::FORMAT_JSON;
        $model = BookingForm::findOne([
            "booking_id" => $id,
        ]);
        if ($model) {
            if ($model->toCancel($smsContent)) {
                \App::$app->webServiceClient->send(
                    WebSocketClient::EVENT_BOOKING_CANCELED_BY_MANAGER,
                    \App::t("common.notice.message", "Booking was canceled.."), [
                        "shop_id" => $model->slotInfo->shop_id,
                        "date" => $model->slotInfo->date,
                        "worker_id" => $model->slotInfo->worker_id,
                    ]
                );//todo send notification
                return [
                    "success" => true,
                    "message" => \App::t("backend.booking.message", "Đã hủy slot đặt lịch"),
                ];
            } else {
                return [
                    "success" => false,
                    "error" => $model->getErrors(),
                    "message" => \App::t("backend.booking.message", "Have error when cancel booking."),
                ];
            }
        }
        return [
            "success" => false,
            "message" => \App::t("backend.booking.message", "Không tìm thấy kết quả"),
        ];
    }

    public function actionDeleteBookingInfo($id)
    {
        if (!$this->request->isAjax) {
            return $this->redirect("index");
        }
        $this->response->format = Response::FORMAT_JSON;
        $model = BookingForm::findOne([
            "booking_id" => $id,
        ]);
        if ($model) {
            if ($model->toDelete()) {
                \App::$app->webServiceClient->send(
                    WebSocketClient::EVENT_BOOKING_DELETED,
                    \App::t("common.notice.message", "Booking was deleted."), [
                        "shop_id" => $model->slotInfo->shop_id,
                        "date" => $model->slotInfo->date,
                        "worker_id" => $model->slotInfo->worker_id,
                    ]
                );//todo send notification
                return [
                    "success" => true,
                    "message" => \App::t("backend.booking.message", "Đã xóa thành công"),
                ];
            } else {
                return [
                    "success" => false,
                    "error" => $model->getErrors(),
                    "message" => \App::t("backend.booking.message", "Have error when delete booking."),
                ];
            }
        }
        return [
            "success" => false,
            "message" => \App::t("backend.booking.message", "Không tìm thấy kết quả"),
        ];
    }

    public function actionHistory()
    {
        $model = new BookingHistorySearchForm();
        $model->load($this->request->post());
        $model->load($this->request->get());
        return $this->render("history/index", [
            "model" => $model,
        ]);
    }

    public function actionRemoveSlotExpired()
    {
        $now = strtotime(date('H:i'));
        $slots = ShopCalendarSlot::find()
            ->where(['!=',"status", ShopCalendarSlot::STATUS_BOOKED])
            ->andWhere(['!=',"status", ShopCalendarSlot::STATUS_DELETED])
            ->andWhere(['date' => date('Y-m-d')])
            ->all();
        foreach ($slots as $slot) {
            if (strtotime($slot->start_time) <= $now) {
                $slot->delete();
            }
        }
        return true;
    }
}
