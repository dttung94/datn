<?php
namespace backend\controllers;

use backend\forms\ForgotPasswordForm;
use backend\forms\LoginForm;
use backend\forms\ResetPasswordForm;
use backend\models\BackendController;
use common\components\WebSocketClient;
use common\entities\system\SystemConfig;
use common\entities\user\UserConfig;
use common\entities\user\UserInfo;
use common\forms\service\URLShortener;
use common\helper\DatetimeHelper;
use WebSocket\Client;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * Site controller
 */
class SiteController extends BackendController
{
    public $layout = "layout_base";

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [
                            'login',
                            'error',
                            'forgot-password',
                            'reset-password',
                            'now',
//                            'test',
//                            'check-is-online',
//                            'change-volume',
//                            'get-volume'
                        ],
                        'allow' => true,
                    ],
                    [
                        'actions' => [
                            'logout',
                            'index',
//                            "change-language",
                            'statistics-sms',
                            'statistics-usage-rate',
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
                "view" => "error/index",
            ],
        ];
    }

    public function actionIndex()
    {
        $this->layout = "layout_admin";
        return $this->render('dashboard/index');
    }
    public function actionStatisticsSms()
    {
        $this->layout = "layout_admin";
        return $this->render('statistics/sms/index');
    }
    public function actionStatisticsUsageRate()
    {
        $this->layout = "layout_admin";
        return $this->render('statistics/usage-rate/index');
    }

    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $this->layout = "layout_guest";
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            $userInfo = UserInfo::findOne(['username' => Yii::$app->request->post('LoginForm')['username']]);
//            $userInfo->is_online = UserInfo::IS_ONLINE;
            $userInfo->update();
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    public function actionForgotPassword()
    {
        $model = new ForgotPasswordForm();
        $this->layout = "layout_guest";
        return $this->render("forgot-password/index", [
            "model" => $model,
            "is_done" => ($model->load($this->request->post()) && $model->toSendMail()),
        ]);
    }

    public function actionResetPassword($reset_token)
    {
        $this->layout = "layout_guest";
        $model = new ResetPasswordForm();
        $model->reset_token = $reset_token;
        if ($model->tokenIsValid()) {
            return $this->render("forgot-password/reset-password", [
                "model" => $model,
                "is_done" => ($model->load($this->request->post()) && $model->toResetPassword()),
            ]);
        }
        return $this->redirect(["site/login"]);
    }

//    public function actionCheckIsOnline()
//    {
//        $users = Yii::$app->user;
//        $check = UserInfo::find()
//            ->where([
//                'user_id' => $users->id,
//                'is_online' => UserInfo::IS_ONLINE
//            ])->exists();
//        if ($check == false) {
//            return 'is_offline';
//        }
//        return 'is_online';
//    }

    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

//    public function actionChangeLanguage($id)
//    {
//        UserConfig::setValue(UserConfig::KEY_LANGUAGE, Yii::$app->user->id, $id);
//        if (Yii::$app->request->referrer) {
//            return $this->redirect(Yii::$app->request->referrer);
//        } else {
//            return $this->goHome();
//        }
//    }

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
    }

//    public function actionChangeVolume($volume)
//    {
//        $_SESSION['volume'] = $volume;
//    }
//
//    public function actionGetVolume()
//    {
//        $volume = isset($_SESSION['volume']) ? $_SESSION['volume'] : SystemConfig::DEFAULT_VOLUME;
//        return round($volume/100, 2);
//    }
//
//    public function actionTest()
//    {
//        //todo send notification
//        \App::$app->webServiceClient->ping(true);
//
////        //todo test shorten url
////        $url = URLShortener::shortenLongUrl("http://dantri.com/");
////        var_dump($url);
//    }
}
