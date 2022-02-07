<?php
namespace frontend\controllers;

use frontend\forms\file\FileForm;
use common\entities\resource\FileInfo;
use frontend\models\FrontendController;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class FileController extends FrontendController
{
    public $layout = "layout_member";

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [
                            'down',
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => [
                            'view',
                        ],
                        'allow' => true,
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                ],
            ],
            'httpCache' => [
                'class' => 'yii\filters\HttpCache',
                'only' => ['view'],
                'lastModified' => function ($action, $params) {

                },
            ],
        ];
    }

    public function actionView($id = null, $type = 'avatar')
    {
        FileForm::getPreview($id, $type);
    }
}