<?php


namespace backend\modules\calendar\controllers;


use backend\filters\BackendAccessRule;
use backend\models\BackendController;
use backend\modules\calendar\CalendarModule;
use backend\modules\calendar\forms\rating\RatingRankForm;
use common\entities\worker\WorkerInfo;
use common\entities\worker\WorkerMappingShop;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Json;

class RatingController extends BackendController
{
    public $layout = "layout_admin";

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => BackendAccessRule::className(),
                    'module' => CalendarModule::MODULE_ID
                ],
                'rules' => [
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [],
            ],
        ];
    }

    public function actionRanking()
    {
        $model = new RatingRankForm();
        $model->load($this->request->post());
        $model->load($this->request->get());
        return $this->render('ranking/index', [
            'model' => $model,
        ]);
    }

    public function actionGetListShopOfWorker()
    {
        $workerId = $this->request->post('workerId');
        $shops = WorkerMappingShop::find()->where(['worker_id' => $workerId])->all();
        $listUrl = [];
        foreach ($shops as $shop) {
            if ($shop->status == WorkerMappingShop::STATUS_ACTIVE) {
                $listUrl[] = [
                    'shop_name' => $shop->shopInfo->shop_name,
                    'url' =>rtrim($shop->shopInfo->shop_address, '/') .'/sp/profile.php?id=' . $shop->ref_id,
                ];
            }
        }
        return Json::encode($listUrl);
    }
}