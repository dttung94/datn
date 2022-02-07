<?php
namespace frontend\controllers;


use common\entities\system\SystemSortURL;
use frontend\models\FrontendController;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

class UrlController extends FrontendController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
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
        ];
    }

    public function actionView($url_id)
    {
        \App::error("Access Sort URL: $url_id", "SortURL");
        $model = SystemSortURL::findOne([
            "id" => $url_id
        ]);
        if ($model && $model->isValid()) {
            $model->total_access += 1;
            $model->save(false);
            return $this->redirect($model->url);
        }
        throw new NotFoundHttpException();
    }
}