<?php
namespace backend\modules\system\controllers;

use backend\filters\BackendAccessRule;
use backend\models\BackendController;
use backend\modules\system\forms\manager\ManagerForm;
use backend\modules\system\SystemModule;
use common\entities\user\UserInfo;
use common\entities\user\UserToken;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use Yii;

class ManagerController extends BackendController
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
                    "module" => SystemModule::MODULE_ID
                ],
                'rules' => [
                    [
                        'actions' => [
                            'index',

                            'permission',
                            'create',
                            'update',
                            'logout',

                            'switch-status',
                            "switch-module-permission",
                            "switch-shop",
                        ],
                        'allow' => true,
                        'roles' => [UserInfo::ROLE_ADMIN],
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
        $form = new ManagerForm();
        $form->load($this->request->get());
        $form->load($this->request->post());
        return $this->render('list/index', [
            'model' => $form
        ]);
    }

    public function actionPermission()
    {
        $form = new ManagerForm();
        $form->load($this->request->get());
        $form->load($this->request->post());
        return $this->render('list/permission', [
            'model' => $form
        ]);
    }

    public function actionCreate()
    {
        $form = new ManagerForm();
//        $form->role = ManagerForm::ROLE_MANAGER;
        $form->status = ManagerForm::STATUS_ACTIVE;
        if ($form->load($this->request->post()) && $form->toSave()) {
            return $this->redirect("index");
        }
        return $this->render("create", [
            "model" => $form,
        ]);
    }

    public function actionUpdate($id)
    {
        $form = $this->findModel($id);
        if ($form->load($this->request->post()) && $form->toSave()) {
            return $this->redirect("index");
        }
        return $this->render("update", [
            "model" => $form,
        ]);
    }

    public function actionLogout()
    {
        if (!$this->request->isAjax) {
            return $this->redirect("index");
        }
        $id = $this->request->post('id');
        $model = $this->findModel($id);
        $model->toToggleIsOnline();
        return true;
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
                "message" => \App::t("backend.system_manager.message", "Đã thay đổi trạng thái hoạt động"),
            ];
        }
        return [
            "success" => false,
            "message" => \App::t("backend.system_manager.message", "Có lỗi xảy ra khi thay đổi trạng thái"),
        ];
    }

    public function actionSwitchModulePermission($id, $module, $permission)
    {
        if (!$this->request->isAjax) {
            return $this->redirect("index");
        }
        $this->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        if ($model->toToggleModulePermission($module, $permission)) {
            return [
                "success" => true,
                "message" => \App::t("backend.system_manager.message", "Đã chuyển trạng thái hoạt động"),
            ];
        }
        return [
            "success" => false,
            "message" => \App::t("backend.system_manager.message", "Đã chuyển trạng thái hoạt động"),
        ];
    }

    public function actionSwitchShop($id, $shop_id)
    {
        if (!$this->request->isAjax) {
            return $this->redirect("index");
        }
        $this->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        if ($model->toToggleShop($shop_id)) {
            return [
                "success" => true,
                "message" => \App::t("backend.system_manager.message", "Đã chuyển trạng thái hoạt động"),
            ];
        }
        return [
            "success" => false,
            "message" => \App::t("backend.system_manager.message", "Lỗi xảy ra khi thay đổi trạng thái "),
        ];
    }

    /**
     * @param $id
     * @return null|ManagerForm
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = ManagerForm::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(\App::t("backend.system_manager.message", 'The requested page does not exist.'));
        }
    }
}