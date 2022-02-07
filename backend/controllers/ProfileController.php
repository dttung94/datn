<?php
namespace backend\controllers;

use backend\forms\UserChangePasswordForm;
use backend\forms\UserProfileForm;
use backend\models\BackendController;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * Class ProfileController
 * @package backend\controllers
 */
class ProfileController extends BackendController
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
                'rules' => [
                    [
                        'actions' => [
                            'index',
                            'password',
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

    public function actionIndex()
    {
        $form = UserProfileForm::findOne(\App::$app->user->id);
        if ($form->load($this->request->post()) && $form->save()) {
            \App::$app->session->setFlash("ALERT_MESSAGE", \App::t("backend.profile.message", "プロフィールをアップデートする"));
        }
        return $this->render("info", [
            "model" => $form,
        ]);
    }

    public function actionPassword()
    {
        $form = UserChangePasswordForm::findOne(\App::$app->user->id);
        if ($form->load($this->request->post()) && $form->toChangePassword()) {
            \App::$app->session->setFlash("ALERT_MESSAGE", \App::t("backend.profile.message", "Cập nhật mật khẩu thành công"));
        }
        return $this->render("password", [
            "model" => $form,
        ]);
    }
}