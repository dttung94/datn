<?php
namespace frontend\controllers;


use backend\modules\calendar\controllers\SlotBookingController;
use backend\modules\calendar\forms\booking\CalendarForm;
use common\components\WebSocketClient;
use common\entities\calendar\BookingData;
use common\entities\calendar\BookingInfo;
use common\entities\calendar\CourseInfo;
use common\entities\calendar\Rating;
use common\entities\shop\ShopCalendarSlot;
use common\entities\shop\ShopConfig;
use common\entities\system\SystemConfig;
use common\entities\worker\WorkerInfo;
use common\entities\worker\WorkerMappingShop;
use common\helper\DatetimeHelper;
use common\helper\StringHelper;
use frontend\forms\booking\BookingConfirmForm;
use frontend\forms\booking\BookingEditForm;
use frontend\forms\booking\BookingOnlineForm;
use frontend\forms\shop\ShopForm;
use frontend\forms\worker\WorkerSlotForm;
use frontend\models\FrontendController;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\Response;
use common\entities\user\UserToken;
use common\models\UserIdentity;

class BookingController extends FrontendController
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [
                            'load-online-booking',
                            'save-online-booking',
                            'update-online-booking',
                            'load-booked-booking',

                            'load-shop-slot',

                            'history',
                            'get-rating',
                            'save-rating',
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => [
                            'booking-accept',
                            'booking-reject',
                            'booking-confirm'
                        ],
                        'allow' => true,
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'save-online-booking' => ['POST'],
                    'save-rating' => ['POST'],
                ],
            ],
        ];
    }

    public function actionLoadBookedBooking($booking_id)
    {
        if (!$this->request->isAjax) {
            return $this->redirect([
                "/shop"
            ]);
        }
        $this->response->format = Response::FORMAT_JSON;
        $form = BookingEditForm::findOne($booking_id);
        if ($form->toPrepare('load')) {
            $datas = $form->toArray();
            $datas['isBookingSortTime'] = (int)SystemConfig::getValue(SystemConfig::CATEGORY_BOOKING,
                SystemConfig::BOOKING_IS_BOOKING_SORT_TIME);
            return [
                "success" => true,
                "data" => $datas,
            ];
        }
            return [
                "success" => false,
                "message" => StringHelper::errorToString($form->getErrors()),
                "error" => $form->getErrors(),
            ];
    }

    public function actionLoadOnlineBooking($slot_id)
    {
        if (!$this->request->isAjax) {
            return $this->redirect([
                "/shop"
            ]);
        }
        $this->response->format = Response::FORMAT_JSON;
        $form = new BookingOnlineForm();
        $form->member_id = $this->userInfo->user_id;
        $form->slot_id = $slot_id;
        if ($form->toPrepare('load')) {
            $datas = $form->toArray();
//            $datas['courseTypes'] = $this->checkShopIdExists($form->slotInfo->shopInfo->shop_id, $datas['courseTypes']);
//            $datas['coupons'] = $this->optimizeCouponCode($datas['coupons']);
            $datas['isBookingSortTime'] = (int)SystemConfig::getValue(SystemConfig::CATEGORY_BOOKING, SystemConfig::BOOKING_IS_BOOKING_SORT_TIME);
            return [
                "success" => true,
                "data" => $datas,
            ];
        }
        return [
            "success" => false,
            "message" => StringHelper::errorToString($form->getErrors()),
            "error" => $form->getErrors(),
        ];
    }

    protected function optimizeCouponCode($coupons)
    {
        $couponMinValue = (int)SystemConfig::getValue(SystemConfig::CATEGORY_COUPON, SystemConfig::BOOKING_COUPON_BLOCK_VALUE);
        $response = [];
        foreach ($coupons as $coupon) {
            if (($coupon->yield % $couponMinValue) == 0) {
                $response[] = $coupon;
            }
        }
        return $response;
    }

    public function actionUpdateOnlineBooking($booking_id)
    {
        if (!$this->request->isAjax) {
            return $this->redirect([
                "/shop"
            ]);
        }
        $this->response->format = Response::FORMAT_JSON;
        $model = BookingEditForm::findOne($booking_id);
        if ($model->toPrepare()) {
            $shopInfo = $model->slotInfo->shopInfo;
            if ($model->load($this->request->post(), "")) {
                $post = $this->request->post();
                $model->course_id = $post['course_id'];
                $model->comment = $post['comment'];
            }
            if ($model->load($this->request->post(), "") && $model->toEdit() && $this->toSendMail($model, $shopInfo->shop_email, BookingInfo::BOOKING_ONLINE_UPDATE)) {
                \App::$app->webServiceClient->send(
                    WebSocketClient::EVENT_BOOKING_ONLINE_UPDATE_REQUEST,
                    \App::t("common.notice.message", $shopInfo->shop_name." đã cập nhật"), [
                        "booking_id" => $model->booking_id,
                        "shop_id" => $shopInfo->shop_id,
                    ]
                );//todo send notification
                \App::$app->webServiceClient->send(
                    WebSocketClient::EVENT_SHOP_CALENDAR_SLOT_CHANGED,
                    \App::t("common.notice.message", "Slot updated."), [
                        "shop_id" => $model->slotInfo->shop_id,
                        "worker_id" => $model->slotInfo->worker_id,
                        "date" => $model->slotInfo->date,
                        "oldCalendarData" => [],
                        "newCalendarData" => CalendarForm::getSlotData($model->slotInfo),
                    ]
                );
                return [
                    "success" => true,
                    "message" => \App::t("frontend.online-booking.message", "Chúng tôi sẽ liên hệ lại với bạn trong vòng 3 phút, xin cảm ơn!"),
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
            "message" => StringHelper::errorToString($model->getErrors()),
            "error" => $model->getErrors(),
        ];
    }

    public function actionSaveOnlineBooking($slot_id)
    {
        if (!$this->request->isAjax) {
            return $this->redirect([
                "/shop"
            ]);
        }
        $this->response->format = Response::FORMAT_JSON;
        $form = new BookingOnlineForm();
        $form->member_id = $this->userInfo->user_id;
        $form->slot_id = $slot_id;
        if ($form->toPrepare()) {
            $shopInfo = $form->slotInfo->shopInfo;
            if ($form->load($this->request->post(), "")) {
                $post = $this->request->post();
                $form->slotInfo->end_time = $post['end_time'];
            }
            if ($form->load($this->request->post(), "") && $form->toSave() && $this->toSendMail($form, $shopInfo->shop_email, BookingInfo::BOOKING_ONLINE)) {
                \App::$app->webServiceClient->send(
                    WebSocketClient::EVENT_BOOKING_ONLINE_CREATED,
                    \App::t("common.notice.message", $shopInfo->shop_name." có 1 lượt đặt lịch mới"), [
                        "booking_id" => $form->booking_id,
                        "shop_id" => $shopInfo->shop_id,
                    ]
                );//todo send notification
                return [
                    "success" => true,
                    "message" => \App::t("frontend.online-booking.message", "Chúng tôi sẽ liên hệ với bạn trong vòng 3 phút, xin vui lòng để ý điện thoại."),
                    "data" => $form,
                ];
            } else {
                return [
                    "success" => false,
                    "message" => StringHelper::errorToString($form->getErrors()),
                    "data" => $form,
                    "error" => $form->getErrors(),
                ];
            }
        }
        return [
            "success" => false,
            "message" => StringHelper::errorToString($form->getErrors()),
            "error" => $form->getErrors(),
        ];
    }


    protected function checkShopIdExists($shopId, $courseTypes, $isChange = false)
    {
        $result = [];
        foreach ($courseTypes as $key => $courseType) {
            $courseTypes[$key]['courseTypeText'] = $isChange ?
                $courseTypes[$key]['courseTypeText'].'('.$courseTypes[$key]['duration_minute'].'分)' : $courseType['courseTypeText'];
            $strShopIds = CourseInfo::findOne($courseType['course_id'])->shop_ids;
            $shopIds = (array)explode('-', $strShopIds);
            if (in_array($shopId, $shopIds)) {
                $result[] = $courseTypes[$key];
            }
        }
        return $result;
    }


    public function actionLoadShopSlot($shop_id, $date)
    {
        if (!$this->request->isAjax) {
            return $this->redirect([
                "/shop"
            ]);
        }
        $this->response->format = Response::FORMAT_JSON;
        $model = ShopForm::findOne($shop_id);
        if ($model) {
            if ($date == null) {
                $date = date("Y-m-d");
            }
            $model->date = $date;
            $tomorrowDate = date("Y-m-d", strtotime($date) + 1 * 24 * 60 * 60);
            $isShowTomorrowSlot = ShopConfig::isExist(ShopConfig::KEY_SHOP_BOOKING_TOMORROW_AT, $shop_id);
            if ($isShowTomorrowSlot) {
                $timeNow = date("H:i");
                $allowTomorrowBookingAt = ShopConfig::getValue(ShopConfig::KEY_SHOP_BOOKING_TOMORROW_AT, $shop_id);
                $isShowTomorrowSlot = DatetimeHelper::timeFormat2Seconds($timeNow) >= DatetimeHelper::timeFormat2Seconds($allowTomorrowBookingAt);
            }
            $data = [
                $date => [],
                $tomorrowDate => [],
            ];

            $listWorkerToday = $model->getWorkingWorkerIds($date);
            $data[$date] = $this->getWorkersSlots($listWorkerToday, $date, $model->shop_id);

            $data[$tomorrowDate]["workers"] = [];
            if ($isShowTomorrowSlot) {
                $listWorkerTomorow = $model->getWorkingWorkerIds($tomorrowDate);
                $data[$tomorrowDate] = $this->getWorkersSlots($listWorkerTomorow, $tomorrowDate, $model->shop_id);
            }
            return [
                "success" => true,
                "data" => $data,
            ];
        }
        return [
            "success" => false,
            "message" => \App::t("frontend.shop.message", "Request is invalid"),
        ];
    }

    private function getWorkersSlots($worker_ids, $date, $shop_id) {
        $data = [];
        $workers = WorkerSlotForm::find()
            ->where(['in', 'worker_id', $worker_ids])
            ->andWhere(['status' => WorkerSlotForm::STATUS_ACTIVE])
            ->all();
        $data['workers'] = $workers;
        foreach ($workers as $worker) {
            $data['slots'][$worker->worker_id] = ShopCalendarSlot::find()
                ->where([
                    "shop_id" => $shop_id,
                    "worker_id" => $worker->worker_id,
                    "date" => $date
                ])
                ->andWhere(['!=', 'status', ShopCalendarSlot::STATUS_DELETE])
                ->orderBy("end_time ASC")
                ->all();
        }
        return $data;
    }

    public function actionBookingAccept($id, $accessToken)
    {
        //\App::$app->user->logout();
        if (($model = BookingConfirmForm::toAccept($id, $accessToken)) != false) {
            \App::$app->webServiceClient->send(
                WebSocketClient::EVENT_BOOKING_FREE_CONFIRM_ACCEPT,
                \App::t("common.notice.message", "新しいフリー予約を承認しました"), [
                    "booking_id" => $id,
                ]
            );//todo send notification
            \App::$app->webServiceClient->send(
                WebSocketClient::EVENT_SHOP_CALENDAR_SLOT_CHANGED,
                \App::t("common.notice.message", "Slot updated."), [
                    "shop_id" => $model->slotInfo->shop_id,
                    "worker_id" => $model->slotInfo->worker_id,
                    "date" => $model->slotInfo->date,
                    "oldCalendarData" => [],
                    "newCalendarData" => CalendarForm::getSlotData($model->slotInfo),
                ]
            );//todo send notification
            \Yii::$app->session->setFlash("ALERT_MESSAGE", \App::t("frontend.booking-confirm.message", "予約成功"));
            return $this->redirect([
                "/shop"
            ]);
        } else {
            \Yii::$app->session->setFlash("ERROR_MESSAGE", \App::t("frontend.booking-confirm.message", "予約承認時にエラーがでました。"));
            return $this->redirect([
                "/site/login"
            ]);
        }
    }

    public function actionBookingReject($id, $accessToken)
    {
        //\App::$app->user->logout();
        $model = BookingConfirmForm::toReject($id, $accessToken);
        if ($model != false && $this->toSendMail($model, $model->slotInfo->shopInfo->shop_email, BookingInfo::BOOKING_REJECT)) {
            \App::$app->webServiceClient->send(
                WebSocketClient::EVENT_BOOKING_FREE_CONFIRM_REJECT,
                \App::t("common.notice.message", "新規フリー予約拒否。"), [
                    "booking_id" => $id,
                    "shop_id" => $model->slotInfo->shop_id
                ]
            );//todo send notification
            \App::$app->webServiceClient->send(
                WebSocketClient::EVENT_SHOP_CALENDAR_SLOT_CHANGED,
                \App::t("common.notice.message", "Slot updated."), [
                    "shop_id" => $model->slotInfo->shop_id,
                    "worker_id" => $model->slotInfo->worker_id,
                    "date" => $model->slotInfo->date,
                    "oldCalendarData" => [],
                    // task 334: booking free when member click reject after received mail, not remove slot
                    "newCalendarData" => empty($_GET['showSlot']) ? CalendarForm::getSlotDataRemove($model->slotInfo) : [],
                ]
            );//todo send notification
            \Yii::$app->session->setFlash("ALERT_MESSAGE", \App::t("frontend.booking-confirm.message", "予約を拒否しました"));
            return $this->redirect([
                "/shop"
            ]);
        } else {
            \Yii::$app->session->setFlash("ERROR_MESSAGE", \App::t("frontend.booking-confirm.message", "Have error when reject booking."));
            return $this->redirect([
                "/site/login"
            ]);
        }
    }

    public function actionBookingConfirm($id, $accessToken)
    {
        \App::$app->user->logout();

        $userToken = UserToken::findOne([
            'token' => $accessToken,
            'status' => UserToken::STATUS_ACTIVE
        ]);

        if ($userToken && $userToken->isValid()) {
            //todo login
            $userIdentity = UserIdentity::findIdentity($userToken->user_id);

            if ($userIdentity) {
                \App::$app->user->login($userIdentity, 3600 * 24 * 30);
            }

            //todo find booking model
            $book = BookingConfirmForm::findOne([
                "booking_id" => $id,
                "status" => BookingConfirmForm::STATUS_CONFIRMING,
                "booking_type" => BookingConfirmForm::BOOKING_TYPE_FREE,
                "member_id" => $userToken->user_id,
            ]);

            if ($book) {
                return $this->render('confirm', [
                    'id' => $id,
                    'accessToken' => $accessToken
                ]);
            } else {
                return $this->redirect([
                    "/shop"
                ]);
            }
        }

        return $this->redirect([
            "/site/login"
        ]);
    }

    public function actionHistory()
    {
        $id = \Yii::$app->user->identity->getId();
        $query = BookingInfo::find()->with('slotInfo.shopInfo', 'courseInfo')->innerJoin(ShopCalendarSlot::tableName(), ShopCalendarSlot::tableName() . '.slot_id = ' . BookingInfo::tableName() . '.slot_id')
            ->innerJoin(WorkerInfo::tableName(), ShopCalendarSlot::tableName() . '.worker_id = ' . WorkerInfo::tableName() . '.worker_id')
            ->where(['member_id' => $id, BookingInfo::tableName() . '.status' => BookingInfo::STATUS_ACCEPTED])->orderBy(['created_at' => SORT_DESC]);

        $bookings = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => 10,
            ],
        ]);

        return $this->render('history', [
            'model' => $bookings,
        ]);
    }

    public function actionSaveRating()
    {
        $userId = \Yii::$app->user->identity->getId();
        if ($this->request->post('rating_id') != '') {
            $rating = Rating::findOne($this->request->post('rating_id'));
            $rating->memo = $this->request->post('memo');
        } else {
            $rating = new Rating();
            $rating->user_id = $userId;
            $rating->booking_id = $this->request->post('booking_id');
            $rating->worker_id = $this->request->post('worker_id');
            $rating->behavior = $this->request->post('behavior');
            $rating->technique = $this->request->post('technique');
            $rating->service = $this->request->post('service');
            $rating->price = $this->request->post('price');
            $rating->satisfaction = $this->request->post('satisfaction');
            if ($this->request->post('memo') !== '') {
                $rating->memo = $this->request->post('memo');
            }
        }
        if ($rating->save()) {
            return Json::encode([
                'success' => true,
                'message' => 'Đánh giá hoàn tất',
            ]);
        }
        return Json::encode([
            'success' => false,
            'message' => 'Có lỗi xảy ra, vui lòng thử lại',
        ]);
    }

    public function actionGetRating()
    {
        $bookingId = $this->request->post('bookingId');
        $rating = Rating::find()->where(['booking_id'=> $bookingId])->one();
        return Json::encode($rating);
    }
}
