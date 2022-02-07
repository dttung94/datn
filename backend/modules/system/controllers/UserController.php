<?php
namespace backend\modules\system\controllers;

use backend\models\BackendController;
use backend\modules\system\forms\user\UserForm;
use backend\modules\system\SystemModule;
use common\entities\user\UserInfo;
use backend\filters\BackendAccessRule;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

class UserController extends BackendController
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
        $form = new UserForm();
        $form->load($this->request->get());
        $form->load($this->request->post());
        return $this->render('index', [
            'model' => $form
        ]);
    }

    /**
     * @param $id
     * @return null|UserForm
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = UserForm::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(\App::t("backend.system_user.message", 'The requested page does not exist.'));
        }
    }
}