<?php
namespace backend\modules\calendar\controllers;


use backend\filters\BackendAccessRule;
use backend\models\BackendController;
use backend\modules\calendar\CalendarModule;
use backend\modules\calendar\forms\shop\ShopConfigForm;
use common\components\WebSocketClient;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * Class SmsController
 * @package backend\modules\calendar\controllers
 */
class ShopController extends BackendController
{
    public $enableCsrfValidation = false;

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
                            'load-shop-config',
                            'save-shop-config',
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

    public function actionLoadShopConfig()
    {
        if (!$this->request->isAjax) {
            return $this->redirect([
                "calendar/booking/index"
            ]);
        }
        $this->response->format = Response::FORMAT_JSON;
        $form = new ShopConfigForm();
        return [
            "success" => true,
            "data" => $form->search(),
        ];
    }

    public function actionSaveShopConfig()
    {
        if (!$this->request->isAjax) {
            return $this->redirect([
                "calendar/booking/index"
            ]);
        }
        $this->response->format = Response::FORMAT_JSON;
        if (ShopConfigForm::setShopConfigs($this->request->post("shopConfigs"))) {
            \App::$app->webServiceClient->send(
                WebSocketClient::EVENT_SHOP_CONFIG_CHANGED,
                \App::t("common.notice.message", "Shop config updated."), []
            );//todo send notification
            return [
                "success" => true,
                "message" => \App::t("backend.shop_config.message", "店舗情報をセーブしました"),
            ];
        } else {
            return [
                "success" => false,
                "message" => \App::t("backend.shop_config.message", "Have error when save shop configs"),
            ];
        }
    }
}