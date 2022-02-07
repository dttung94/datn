<?php
namespace frontend\controllers;

use backend\modules\coupon\forms\CouponForm;
use common\components\WebSocketClient;
use common\entities\calendar\CouponInfo;
use common\entities\calendar\CouponLog;
use common\entities\referrer\ReferInfo;
use common\entities\service\TemplateMail;
use common\entities\shop\ShopCalendar;
use common\entities\shop\ShopInfo;
use common\entities\system\SystemConfig;
use common\entities\user\UserInfo;
use common\helper\ArrayHelper;
use common\helper\DatetimeHelper;
use common\helper\StringHelper;
use common\mail\forms\Mail;
use common\models\UserIdentity;
use frontend\forms\auth\LoginForm;
use frontend\forms\auth\PasswordResetRequestForm;
use frontend\forms\auth\ResendVerifyForm;
use frontend\forms\auth\ResetPasswordForm;
use frontend\forms\auth\SignupForm;
use frontend\forms\auth\SignUpVerifyEmailForm;
use frontend\forms\auth\SignUpVerifyPhoneNumberForm;
use frontend\forms\shop\ShopForm;
use frontend\models\FrontendController;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\HttpException;
use yii\web\Response;

class SiteController extends FrontendController
{
    public $enableCsrfValidation = false;
    public $layout = "layout_guest";

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [
                            'index',
                            'login',
                            'error',
                            'sign-up',
                            'sign-up-verify-phone-number',
                            'sign-up-verify-email',

                            'forgot-password',
                            'reset-password',
                            'resend-verify',
                            'booking',

                            "now",
                            "invite"
                        ],
                        'allow' => true,
                    ],
                    [
                        'actions' => [
                            'logout',
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

    public function actionError()
    {
        $exception = \App::$app->errorHandler->exception;
        if ($exception !== null) {
            if (\App::$app->user->isGuest) {
                $this->layout = 'layout_guest';
            } else {
                $this->layout = 'layout_member';
            }
            return $this->render('error/index', [
                "name" => \App::t("frontend.error.message", "Error"),
                "message" => $exception->getMessage(),
                "exception" => $exception,
            ]);
        }
        return $this->redirect("index");
    }

    public function actionIndex()
    {
        $model = ShopInfo::find()->innerJoin(
            ShopCalendar::tableName(),
            ShopCalendar::tableName() . ".shop_id = " . ShopInfo::tableName() . ".shop_id AND " .
            ShopCalendar::tableName() . ".date = :date AND " . ShopCalendar::tableName() . ".type = :type AND " .
            ShopCalendar::tableName() . ".status = :status",
            [
            ":type" => ShopCalendar::TYPE_WORKING_DAY,
            ":date" => \App::$app->request->get("date", DatetimeHelper::now(DatetimeHelper::FULL_DATE)),
            ":status" => ShopCalendar::STATUS_ACTIVE,
            ]
        )->where([ShopInfo::tableName() . ".status" => ShopInfo::STATUS_ACTIVE])->one();

        if ($model && !ShopCalendar::isLockUserBooking($model->shop_id)) {
            /**
             * @var $model ShopForm
             */
            $data = [
                "/shop/$model->shop_id",
            ];
            if (!empty($_GET['token'])) {
                $data['token'] = $_GET['token'];
            }
            return $this->redirect($data);
        }
        if (!\App::$app->user->isGuest) {
            $this->layout = "layout_member";
        }else{
            $this->layout = "layout_guest";
        }

        return $this->render("index", []);
    }

    public function actionBooking($worker_id)
    {
        $date = DatetimeHelper::now(DatetimeHelper::FULL_DATE);
        $time = DatetimeHelper::now(DatetimeHelper::FULL_TIME);
        $model = ShopCalendar::find()
            ->where([
                "type" => ShopCalendar::TYPE_WORKING_DAY,
                "date" => $date,
                "worker_id" => $worker_id
            ])
            ->andWhere("work_start_time < :time AND work_end_time >= :time", [
                ":time" => "$time",
            ])
            ->one();
        if ($model) {
            /**
             * @var $model ShopCalendar
             */
            return $this->redirect([
                "shop/$model->shop_id/$worker_id",
            ]);
        }
        return $this->redirect([
            "shop/view"
        ]);
    }

    public function actionLogin()
    {
        if (!\App::$app->user->isGuest) {
            return $this->redirect("index");
        }
        $form = new LoginForm();
        $form->rememberMe = 1;
        if ($this->request->isAjax) {
            $form->load($this->request->post(), '');
            if ($form->toLogin()) {
                return $this->redirect("index");
            } else {
                $this->response->format = Response::FORMAT_JSON;
                return [
                    "success" => false,
                    "error" => $form->getErrors(),
                ];
            }
        }
        return $this->render("login", [
            "model" => $form,
        ]);
    }

    /**
     * when index and submit form sign up in client
     *
     * @return array|string|Response
     */
    public function actionSignUp()
    {
        if (!\App::$app->user->isGuest) {
            return $this->redirect("index");
        }
        $form = new SignupForm();
        if ($this->request->isAjax) {
            $form->load($this->request->post(), '');
            if (($user = $form->toSignUp()) !== null) {
                \App::$app->webServiceClient->send(
                    WebSocketClient::EVENT_NEW_MEMBER_SIGN_UP,
                    \App::t("common.notice.message", "Đã đăng ký thành viên"),
                    [
                        "member_id" => $user->user_id,
                    ]
                );//todo send notification
                \App::$app->session->setFlash("ALERT_MESSAGE", \App::t("frontend.sign-up.message", "
    Đăng ký vẫn chưa hoàn thành<br/>
    Vui lòng chờ tin nhắn xác thực gửi tới số điện thoại của bạn </br>
    Xin cảm ơn đã lựa chọn hệ thống của chúng tôi<br/>"));
                return $this->redirect("login");
            } else {
                $this->response->format = Response::FORMAT_JSON;
                return [
                    "success" => false,
                    "error" => $form->getErrors(),
                ];
            }
        }
        return $this->render("sign-up", [
            "model" => $form,
//            'countUserInvited' => $countUserInvited,
        ]);
    }

    public function actionSignUpVerifyPhoneNumber($verifyToken)
    {
        \App::$app->user->logout();
        $form = new SignUpVerifyPhoneNumberForm();
        $form->token = $verifyToken;
        if (($userIdentity = $form->toVerify())) {
            if ($userIdentity->status == UserIdentity::STATUS_ACTIVE) {
                \Yii::$app->session->setFlash(
                    "ALERT_MESSAGE",
                    \App::t("frontend.sign-up.message", "Xác thực số điện thoại thành công")
                );
                \App::$app->user->login($userIdentity, 30 * 24 * 60 * 60);
                return $this->redirect([
                    "/shop"
                ]);
            }
        } else {
            \Yii::$app->session->setFlash("ERROR_MESSAGE", StringHelper::errorToString($form->getErrors()));
        }
        return $this->redirect("/site/login");
    }

    public function actionResendVerify()
    {
        \App::$app->user->logout();
        $form = new ResendVerifyForm();
        if ($this->request->post()) {
            $this->response->format = Response::FORMAT_JSON;
            if ($form->load($this->request->post(), "") && $form->sendVerifyLink()) {
                return [
                    "success" => true,
                    "message" => \App::t("frontend.sign-up.message", "Hệ thống đã gửi tin nhắn xác thực, quý khách vui lòng kiểm tra"),
                    "login_url" => '/site/login',
                ];
            } else {
                return [
                    "success" => false,
                    "message" => StringHelper::errorToString($form->getErrors()),
                    "error" => $form->getErrors(),
                ];
            }
        }
//        var_dump($form->errors);die;
        return $this->render("resend-verify", [
            "model" => $form,
        ]);
    }

    public function actionForgotPassword()
    {
        \App::$app->user->logout();
        $form = new PasswordResetRequestForm();
        if ($this->request->post()) {
            $this->response->format = Response::FORMAT_JSON;
            if ($form->load($this->request->post(), "") && $form->sendSMS($onlySendSms = true)) {
                return [
                    "success" => true,
                    "message" => \App::t("frontend.sign-up.message", "Hệ thống đã gửi tin nhắn cấp lại mật khẩu, quý khách vui lòng kiểm tra"),
                    "login_url" => \App::$app->urlManager->createAbsoluteUrl([
                        "site/login"
                    ]),
                ];
            } else {
                return [
                    "success" => false,
                    "message" => StringHelper::errorToString($form->getErrors()),
                    "error" => $form->getErrors(),
                ];
            }
        }
        return $this->render("forgot-password", [
            "model" => $form,
        ]);
    }


    public function actionResetPassword($resetToken = "")
    {
        \App::$app->user->logout();
        $form = new ResetPasswordForm();
        $form->token = $resetToken;
        if ($form->isTokenValid()) {
            if ($form->load($this->request->post())) {
                if ($form->resetPassword()) {
                    \Yii::$app->session->setFlash("ALERT_MESSAGE", \App::t("frontend.forgot_password.message", "Đặt lại mật khẩu thành công"));
                    return $this->redirect("login");
                } else {
                    \Yii::$app->session->setFlash("ERROR_MESSAGE", StringHelper::errorToString($form->getErrors()));
                }
            }
        } else {
            \Yii::$app->session->setFlash("ERROR_MESSAGE", \App::t("frontend.forgot_password.message", "Không hợp lệ"));
        }
        return $this->render("reset-password", [
            "model" => $form,
        ]);
    }

    public function actionLogout()
    {
        \App::$app->user->logout();
        return $this->redirect("login");
    }

    public function actionNow()
    {
        var_dump(date_default_timezone_get());
        var_dump(DatetimeHelper::now());
        echo "<hr/>";
        var_dump(\App::$app->language);
        var_dump(\App::$app->formatter->timeZone);
        var_dump(\App::$app->formatter->asDatetime(time()));
        echo "<hr/>";
        var_dump($_SERVER["HTTP_HOST"]);
        echo "<hr/>";
        var_dump(\App::$app->formatter->asDatetime("2018-04-09 08:04:22"));
        var_dump(\App::$app->formatter->asTime("2018-04-02 8:4:22"));
    }
}
