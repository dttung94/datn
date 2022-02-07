<?php

namespace backend\modules\rating\controllers;

use backend\filters\BackendAccessRule;
use backend\models\BackendController;
use backend\modules\rating\forms\RatingForm;
use backend\modules\rating\RatingModule;
use common\entities\user\UserInfo;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class ManageController extends BackendController
{
    public $layout = 'layout_admin';

    public function behaviors()
    {
        return [
            "access" => [
                "class" => AccessControl::className(),
                "ruleConfig" => [
                    'class' => BackendAccessRule::className(),
                    "module" => RatingModule::MODULE_ID,
                ],
                "rules" => [
                    [
                        'actions' => [
                            'index',
                        ],
                        'allow' => true,
                        'roles' => [UserInfo::ROLE_ADMIN],
                    ]
                ]
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
        $form = new RatingForm();
        $form->load($this->request->get());
        $form->load($this->request->post());
        return $this->render('index', [
            'model' => $form,
        ]);
    }
}
