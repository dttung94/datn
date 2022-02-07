<?php
namespace backend\modules\service\controllers;


use backend\filters\BackendAccessRule;
use backend\models\BackendController;
use backend\modules\service\forms\file\FileSearchForm;
use backend\modules\service\ServiceModule;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class FileController extends BackendController
{
    public $layout = "layout_admin";

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => BackendAccessRule::className(),
                    "module" => ServiceModule::MODULE_ID
                ],
                'rules' => [
                    [
                        'actions' => [
                            'preview',
                            "download",
                            'email-image'
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

    public function actionPreview($id = null)
    {
        FileSearchForm::getPreview($id);
    }

    public function actionDownload($id)
    {
        FileSearchForm::toDownload($id);
    }

    public function actionEmailImage($id)
    {
        FileSearchForm::getEmailImage(json_decode(base64_decode($id)));
    }
}