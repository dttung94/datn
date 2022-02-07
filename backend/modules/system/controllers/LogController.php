<?php
namespace backend\modules\system\controllers;


use backend\filters\BackendAccessRule;
use backend\models\BackendController;
use backend\modules\system\forms\log\SystemLogSearchForm;
use backend\modules\system\SystemModule;
use common\entities\system\SystemLog;
use common\entities\user\UserInfo;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class LogController extends BackendController
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
                            'clean',
                        ],
                        'allow' => true,
                        'roles' => [UserInfo::ROLE_ADMIN],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'save' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex($category = "All", $level = 0)
    {
        $form = new SystemLogSearchForm();
        $form->load($this->request->get(), "");
        $form->category = $category;
        $form->level = $level;
        return $this->render("index", [
            "model" => $form,
        ]);
    }

    public function actionClean()
    {
        SystemLog::deleteAll([]);
        return $this->redirect("index");
    }
}