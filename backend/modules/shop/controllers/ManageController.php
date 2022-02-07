<?php
namespace backend\modules\shop\controllers;

use backend\filters\BackendAccessRule;
use backend\models\BackendController;
use backend\modules\shop\forms\ShopForm;
use backend\modules\shop\ShopModule;
use common\entities\shop\ShopCalendar;
use common\entities\shop\ShopConfig;
use common\entities\shop\ShopInfo;
use common\entities\user\UserInfo;
use common\helper\StringHelper;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ManageController extends BackendController
{
    public $layout = "layout_admin";

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
                    "module" => ShopModule::MODULE_ID
                ],
                'rules' => [
                    [
                        'actions' => [
                            'index',
                            'update',
                            'view',
                            'config'
                        ],
                        'allow' => true,
                        'roles' => [UserInfo::ROLE_ADMIN, UserInfo::ROLE_MANAGER],
                    ],
                    [
                        'actions' => [
                            'create',
                            'delete',
                            'switch-status',
                        ],
                        'allow' => true,
                        'roles' => [UserInfo::ROLE_ADMIN, UserInfo::ROLE_MANAGER],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'index' => ['post', 'get'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $form = new ShopForm();
        $form->load($this->request->get());
        $form->load($this->request->post());
        return $this->render('list/index', [
            'model' => $form,
        ]);
    }

    public function actionView($id)
    {
        $form = $this->findModel($id);
        return $this->render("view/index", [
            "model" => $form,
        ]);
    }

    public function actionCreate()
    {
        $form = new ShopForm();
        $form->status = ShopForm::STATUS_ACTIVE;
//        $form->is_auto_create = ShopInfo::AUTO_CREATE_ACTIVE;
        $form->toPrepare();
        if ($form->load(\Yii::$app->request->post())) {
            if ($form->toSave()) {
                return $this->redirect([
                    'index',
                ]);
            }
        }
        return $this->render('create', [
            'model' => $form,
        ]);
    }

    public function actionUpdate($id)
    {
        $form = $this->findModel($id);
        $form->toPrepare();
        if ($form->load(\Yii::$app->request->post())) {
            $checks = $this->checkUpdateShop($form);
            if (empty($checks) && $form->toSave()) {
                if (\App::$app->user->identity->role == UserInfo::ROLE_MANAGER) {
                    return $this->redirect([
                        "/calendar/schedule/config",
                        "shop_id" => $form->shop_id,
                    ]);
                }
                return $this->redirect([
                    'index',
                ]);
            } else {
                $message = "";
                foreach ($checks as $check) {
                    $message .= "【".$check->date."】";
                }
                \App::$app->session->setFlash("ERROR_MESSAGE", \App::t("backend.shop.message", $message."に枠がありますので、営業時間が変更できません。"));
            }
        }
        return $this->render('update', [
            'model' => $form,
        ]);
    }

    protected function checkUpdateShop($form)
    {
        $response = [];
        $date = date('Y-m-d');
        $start = $form->open_door_hour.":".$form->open_door_minute;
        $end = $form->close_door_hour.":".$form->close_door_minute;
        $shopConfig = ShopConfig::findAll(['shop_id' => $form->shop_id]);
        foreach ($shopConfig as $value) {
            if ($value->key == ShopConfig::KEY_SHOP_OPEN_DOOR_AT) {
                $startOld = $value->value;
            } elseif ($value->key == ShopConfig::KEY_SHOP_CLOSE_DOOR_AT) {
                $endOld = $value->value;
            }
        }

        if ($start != $startOld || $end != $endOld) {
            $response = ShopCalendar::find()
                ->distinct('date')
                ->select('date')
                ->where("shop_id = :shop_id", [
                    ":shop_id" => $form->shop_id
                ])
                ->andWhere(['>=', 'date', $date])
                ->andWhere("type = :type", [
                    ":type" => ShopCalendar::TYPE_WORKING_DAY
                ])->all();
        }

        return $response;
    }

    public function actionDelete($id)
    {
        $form = $this->findModel($id);
        if ($form->status == $form::STATUS_INACTIVE) {
            $form->status = ShopForm::STATUS_DELETED;
            if ($form->save(false)) {
                \App::$app->session->setFlash("ALERT_MESSAGE", \App::t("backend.shop.message", "店舗削除成功しました"));
            } else {
                \App::$app->session->setFlash("ERROR_MESSAGE", StringHelper::errorToString($form->getErrors()));
            }
        } else {
            \App::$app->session->setFlash("ERROR_MESSAGE", \App::t("backend.shop.message", "Please inactive shop before delete."));
        }
        return $this->redirect(['index']);
    }

    public function actionSwitchStatus($id)
    {
        if (!$this->request->isAjax) {
            return $this->redirect("index");
        }
        $this->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        if ($model->toToggleStatus()) {
            return [
                "success" => true,
                "message" => \App::t("backend.shop.message", "Đã thay đổi trạng thái hoạt động"),
            ];
        }
        return [
            "success" => false,
            "message" => \App::t("backend.shop.message", "Có lỗi xảy ra khi thay đổi trạng trái"),
        ];
    }

    public function actionConfig()
    {
        if (!$this->request->isAjax) {
            return $this->redirect("index");
        }
        $this->response->format = Response::FORMAT_JSON;
        $data = $this->request->post();
        $shopId = $data['shop_id'];
        $value = $data['value'];
        ShopConfig::setValue(ShopConfig::KEY_SHOP_ALLOW_BLOCK_BOOKING, $shopId, $value);
        return true;
    }

    /**
     * @param $id
     * @return ShopForm
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = ShopForm::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(\Yii::t('common.message', 'The requested page does not exist.'));
        }
    }
}