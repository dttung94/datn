<?php
namespace backend\modules\system\controllers;


use backend\filters\BackendAccessRule;
use backend\models\BackendController;
use backend\modules\system\forms\user\UserLogForm;
use backend\modules\system\SystemModule;
use common\entities\user\UserInfo;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class UserLogController extends BackendController
{
    public $layout = "layout_admin";

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
                        ],
                        'allow' => true,
                        'roles' => [UserInfo::ROLE_ADMIN, UserInfo::ROLE_MANAGER],
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

    public function actionIndex()
    {
        $form = new UserLogForm();
        $form->load($this->request->get());
        return $this->render("index", [
            "model" => $form,
        ]);
    }
}