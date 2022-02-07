<?php
namespace backend\modules\worker\controllers;


use backend\models\BackendController;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class WidgetController extends BackendController
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
        return $this->render("index");
    }
}