<?php
namespace backend\modules\calendar\controllers;


use backend\filters\BackendAccessRule;
use backend\models\BackendController;
use backend\modules\calendar\CalendarModule;
use backend\modules\calendar\forms\booking\BookingForm;
use backend\modules\calendar\forms\booking\BookingOnlineForm;
use backend\modules\calendar\forms\booking\BookingOnlineUpdateForm;
use backend\modules\calendar\forms\booking\CalendarForm;
use common\components\WebSocketClient;
use common\entities\calendar\BookingInfo;
use common\entities\calendar\BookingMissAccept;
use common\entities\shop\ShopCalendarSlot;
use common\entities\user\UserInfo;
use common\entities\user\UserLog;
use common\forms\service\SendSMSForm;
use common\helper\StringHelper;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;

class OnlineBookingController extends BackendController
{
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
                            "booking-form",
                            "save-booking",
                            "check-slot-booking-confirm",
                            "accept-booking",
                            "reject-booking",
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                ],
            ],
        ];
    }

    public function actionCheckSlotBookingConfirm($id)
    {
        $trans = \App::$app->db->beginTransaction();
        try {
            if (!$this->request->isAjax) {
                return $this->redirect("index");
            }
            $this->response->format = Response::FORMAT_JSON;
            $model = BookingForm::find()
                ->where(['booking_id' => $id])
                ->one();
            if ($model->status == BookingForm::STATUS_PENDING) {
                $datas = BookingOnlineForm::findOne($id);
                $this->actionRejectBooking($id, "Yêu cầu đặt lịch đã hết hạn");
                $this->toSendMail($datas, $datas->slotInfo->shopInfo->shop_email, BookingInfo::BOOKING_TIME_EXPIRED_REJECT);
            }
            if ($model->status == BookingForm::STATUS_CONFIRMING) {
                $datas = BookingForm::findOne([
                    "booking_id" => $id,
                ]);
                $datas->toCancel(SendSMSForm::TYPE_BOOKING_ONLINE_AUTO_REJECT);
                $this->toSendMail($datas, $datas->slotInfo->shopInfo->shop_email, BookingInfo::BOOKING_TIME_EXPIRED_REJECT);
            }
            $trans->commit();

            return true;
        } catch (\Throwable $throwable) {
            $trans->rollBack();

            return false;
        }
    }

    public function actionBookingForm($id)
    {
        $this->response->format = Response::FORMAT_JSON;
        $model = BookingOnlineUpdateForm::findOne($id);
        if ($model && $model->toPrepare()) {
            $data = $model->toArray();
            $phoneNumber = $data['memberInfo']['phone_number'];
            $users = UserInfo::findOne(['phone_number' => $phoneNumber]);
            $data['customerInfo']['customer_name'] = isset($users->full_name) ? $users->full_name : $users->phone_number;
            return [
                "success" => true,
                "data" => $data,
            ];
        }
        return [
            "success" => false,
            "message" => \App::t("backend.booking.message", "Have error when load booking."),
        ];
    }

    public function actionSaveBooking($id)
    {
        $this->response->format = Response::FORMAT_JSON;
        $model = BookingOnlineUpdateForm::findOne($id);
        if ($model && $model->toPrepare()) {
            if ($model->load($this->request->post(), "") && $model->toSave()) {
                \App::$app->webServiceClient->send(
                    WebSocketClient::EVENT_BOOKING_ONLINE_UPDATED,
                    \App::t("common.notice.message", "Online booking updated."),
                    [
                        "shop_id" => $model->slotInfo->shop_id,
                        "date" => $model->slotInfo->date,
                        "worker_id" => $model->slotInfo->worker_id,
                        "course_name" => $model->courseInfo->course_name,
                    ]
                );//todo send notification
                return [
                    "shop_id" => $model->slotInfo->shop_id,
                    "date" => $model->slotInfo->date,
                    "worker_id" => $model->slotInfo->worker_id,
                    "course_name" => $model->courseInfo->course_name,
                    "modal" => $model,
                    "success" => true,
                    "message" => \App::t("backend.booking.message", "Đã cập nhật thành công"),
                ];
            } else {
                return [
                    "success" => false,
                    "error" => $model->getErrors(),
                    "message" => StringHelper::errorToString($model->getErrors()),
                ];
            }
        }
        return [
            "success" => false,
            "message" => \App::t("backend.booking.message", "Have error when load booking."),
        ];
    }

    public function actionAcceptBooking($id)
    {
        if (!$this->request->isAjax) {
            return $this->redirect("index");
        }
        $this->response->format = Response::FORMAT_JSON;
        $model = BookingOnlineForm::findOne($id);
        if ($model->status == BookingInfo::STATUS_PENDING) {
            if ($model->toAccept(
                $this->request->post("sms_content", ""),
                $this->request->post('title_email', '')
            )) {
                \App::$app->webServiceClient->send(
                    WebSocketClient::EVENT_BOOKING_ONLINE_ACCEPTED,
                    \App::t("common.notice.message", "Online booking accepted."),
                    [
                        "shop_id" => $model->slotInfo->shop_id,
                        "date" => $model->slotInfo->date,
                        "worker_id" => $model->slotInfo->worker_id,
                    ]
                );//todo send notification
                \App::$app->webServiceClient->send(
                    WebSocketClient::EVENT_SHOP_CALENDAR_SLOT_CHANGED,
                    \App::t("common.notice.message", "Slot updated."),
                    [
                        "shop_id" => $model->slotInfo->shop_id,
                        "worker_id" => $model->slotInfo->worker_id,
                        "date" => $model->slotInfo->date,
                        "oldCalendarData" => [],
                        "newCalendarData" => CalendarForm::getSlotData($model->slotInfo),
                    ]
                );//todo send notification

                return [
                    "success" => true,
                    "message" => \App::t("backend.booking.message", "Phê duyệt đặt lịch thành công"),
                    "data" => $model,
                ];
            } else {
                return [
                    "success" => false,
                    "message" => StringHelper::errorToString($model->getErrors()),
                    "data" => $model,
                    "error" => $model->getErrors(),
                ];
            }
        } elseif ($model->status == BookingInfo::STATUS_UPDATING) {
            if ($model->toAccept(
                $this->request->post("sms_content", ""),
                $this->request->post("title_email")
            )) {
                \App::$app->webServiceClient->send(
                    WebSocketClient::EVENT_BOOKING_ONLINE_UPDATED,
                    \App::t("common.notice.message", "Online booking update accepted."),
                    [
                        "shop_id" => $model->slotInfo->shop_id,
                        "date" => $model->slotInfo->date,
                        "worker_id" => $model->slotInfo->worker_id,
                    ]
                );//todo send notification
                \App::$app->webServiceClient->send(
                    WebSocketClient::EVENT_SHOP_CALENDAR_SLOT_CHANGED,
                    \App::t("common.notice.message", "Slot updated."),
                    [
                        "shop_id" => $model->slotInfo->shop_id,
                        "worker_id" => $model->slotInfo->worker_id,
                        "date" => $model->slotInfo->date,
                        "oldCalendarData" => [],
                        "newCalendarData" => CalendarForm::getSlotData($model->slotInfo),
                    ]
                );
                return [
                    "success" => true,
                    "message" => \App::t("backend.booking.message", "Phê duyệt yêu cầu cập nhật"),
                    "data" => $model,
                ];
            } else {
                return [
                    "success" => false,
                    "message" => StringHelper::errorToString($model->getErrors()),
                    "data" => $model,
                    "error" => $model->getErrors(),
                ];
            }
        }
        return [
            "success" => false,
        ];
    }

    public function actionRejectBooking($id, $text = "Từ chối book lịch")
    {
        if (!$this->request->isAjax) {
            return $this->redirect("index");
        }
        $this->response->format = Response::FORMAT_JSON;
        $model = BookingOnlineForm::findOne($id);
        $slotInfo = $model->slotInfo;
        if ($model) {
            if ($model->status == BookingInfo::STATUS_PENDING) {
                if ($model->toReject(
                    $this->request->post("sms_content", ""),
                    $text,
                    $this->request->post("title_email", "")
                )) {
                    \App::$app->webServiceClient->send(
                        WebSocketClient::EVENT_BOOKING_ONLINE_ACCEPTED,
                        \App::t("common.notice.message", "Online booking rejected."),
                        [
                            "shop_id" => $slotInfo->shop_id,
                            "date" => $slotInfo->date,
                            "worker_id" => $slotInfo->worker_id,
                        ]
                    );//todo send notification
                    return [
                        "success" => true,
                        "message" => \App::t("backend.booking.message", "Đã từ chối lượt đặt chỗ này"),
                        "data" => $model,
                    ];
                } else {
                    return [
                        "success" => false,
                        "message" => StringHelper::errorToString($model->getErrors()),
                        "data" => $model,
                        "error" => $model->getErrors(),
                    ];
                }
            }
        }
        return [
            "success" => false,
        ];
    }
}
