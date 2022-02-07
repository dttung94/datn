<?php
namespace frontend\controllers;

use backend\forms\UserChangePasswordForm;
use backend\modules\calendar\forms\booking\CalendarForm;
use common\components\WebSocketClient;
use common\entities\calendar\BookingInfo;
use common\entities\service\TemplateMail;
use common\entities\user\UserConfig;
use common\entities\user\UserInfo;
use common\entities\worker\WorkerInfo;
use common\helper\AmazonHelper;
use common\mail\forms\Mail;
use frontend\forms\profile\MemberBookingHistory;
use frontend\models\FrontendController;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\Response;

class ProfileController extends FrontendController
{
    public $layout = "layout_profile";
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [
                            'index',
                            'load-booking-history',
                            'cancel-booking',
                            'change-password'
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => [
                            'verify-email',
                        ],
                        'allow' => true,
                    ]
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [],
            ],
        ];
    }

    public function actionLoadBookingHistory()
    {
        if (!$this->request->isAjax) {
            return $this->redirect([
                "/shop"
            ]);
        }
        $this->response->format = Response::FORMAT_JSON;
        return [
            "success" => true,
            "data" => MemberBookingHistory::getBookingHistory($this->userInfo),
        ];
    }

    public function actionCancelBooking($id, $shop_id = null)
    {
        if (!$this->request->isAjax) {
            return $this->redirect([
                "/shop"
            ]);
        }
        $this->response->format = Response::FORMAT_JSON;

            $model = MemberBookingHistory::toCancelBooking($this->userInfo->user_id, $id);
            if ($model != false &&
                $this->toSendMail($model, $model->slotInfo->shopInfo->shop_email, BookingInfo::BOOKING_CANCEL_SLOT)
            ) {
                \App::$app->webServiceClient->send(
                    WebSocketClient::EVENT_BOOKING_CANCELED,
                    \App::t("common.notice.message", "Đã hủy bỏ đặt lịch."),
                    [
                        "booking_id" => $id,
                        "shop_id" => $model->slotInfo->shop_id,
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
                    "message" => \App::t("frontend.booking-history.message", "Đã hủy đặt lịch"),
                ];
            }

        return [
            "success" => false,
            "message" => \App::t(
                "frontend.booking-history.message",
                "Have error when cancel your booking, please try again."
            ),
        ];
    }

    private function checkEmailExists($email)
    {
        return UserInfo::find()->where(['email' => $email])->exists();
    }

    private function checkTimeValid($userId)
    {
        $users = UserInfo::find()
            ->select('time_sent_mail')
            ->where(['user_id' => $userId])
            ->one();
        if (empty($users->time_sent_mail)) {
            return false;
        }
        $sub = strtotime(date('Y-m-d H:i:s')) - strtotime($users->time_sent_mail);
        return $sub/60 < 5;
    }

    public function actionIndex()
    {
        $form = UserInfo::findOne(\App::$app->user->id);
        return $this->render("info", [
            "model" => $form,
        ]);
    }

    public function actionChangePassword()
    {
        $form = UserChangePasswordForm::findOne(\App::$app->user->id);
        if ($form->load($this->request->post()) && $form->toChangePassword()) {
            \App::$app->session->setFlash("ALERT_MESSAGE", \App::t("backend.profile.message", "Cập nhật mật khẩu thành công"));
            return $this->redirect('index');
        }
        return $this->render("password", [
            "model" => $form,
        ]);
    }
}
