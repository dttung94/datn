<?php
namespace frontend\controllers;

use backend\modules\calendar\forms\shop\ShopConfigForm;
use common\entities\shop\ShopCalendar;
use common\entities\shop\ShopCalendarSlot;
use common\entities\shop\ShopInfo;
use common\entities\user\UserInfo;
use common\entities\worker\WorkerInfo;
use common\entities\worker\WorkerConfig;
use common\helper\DatetimeHelper;
use frontend\forms\shop\ShopForm;
use frontend\models\FrontendController;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ShopController extends FrontendController
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
                            'view',
                            'load-info',
                            'your-account'
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

    public function actionView($shop_id = null, $worker_id = null)
    {
        if ($shop_id == null) {
            $model = ShopForm::find()
                ->innerJoin(ShopCalendar::tableName(),
                    ShopCalendar::tableName() . ".shop_id = " . ShopInfo::tableName() . ".shop_id AND " . ShopCalendar::tableName() . ".date = :date AND " . ShopCalendar::tableName() . ".type = :type AND " . ShopCalendar::tableName() . ".status = :status", [
                        ":type" => ShopCalendar::TYPE_WORKING_DAY,
                        ":date" => \App::$app->request->get("date", DatetimeHelper::now(DatetimeHelper::FULL_DATE)),
                        ":status" => ShopCalendar::STATUS_ACTIVE,
                    ])
                ->where([
                    ShopInfo::tableName() . ".status" => ShopInfo::STATUS_ACTIVE,
                ])
                ->one();
            if ($model) {
                /**
                 * @var $model ShopInfo
                 */
                return $this->redirect([
                    "/shop/$model->shop_id",
                ]);
            }
        } else {
            if (ShopCalendar::isLockUserBooking($shop_id)) {
                return $this->redirect([
                    "/site/index",
                ]);
            }
            $model = ShopForm::find()
                ->innerJoin(ShopCalendar::tableName(),
                    ShopCalendar::tableName() . ".shop_id = " . ShopInfo::tableName() . ".shop_id AND " . ShopCalendar::tableName() . ".date = :date AND " . ShopCalendar::tableName() . ".type = :type AND " . ShopCalendar::tableName() . ".status = :status", [
                        ":type" => ShopCalendar::TYPE_WORKING_DAY,
                        ":date" => \App::$app->request->get("date", DatetimeHelper::now(DatetimeHelper::FULL_DATE)),
                        ":status" => ShopCalendar::STATUS_ACTIVE,
                    ])
                ->where([
                    ShopInfo::tableName() . ".status" => ShopInfo::STATUS_ACTIVE,
                    ShopInfo::tableName() . ".shop_id" => $shop_id,
                ])
                ->one();
        }
        if ($model) {
            /**
             * @var $model ShopForm
             */
            $today = date('Y-m-d');
            $tomorrow = date('Y-m-d', strtotime('+1 day', strtotime($today)));
            $model->date = $today;
            $userInfo = UserInfo::findOne(\App::$app->user->id);
            $listWorkerToday = $model->getWorkingWorkerIds($today, $userInfo);
            $listWorkerTomorrow = $model->getWorkingWorkerIds($tomorrow, $userInfo);
            $slotToday = ShopCalendarSlot::find()
                ->where(['in', 'worker_id', $listWorkerToday])
                ->andWhere(['status' => ShopCalendarSlot::STATUS_ACTIVE])
                ->count();
            $slotTomorrow = ShopCalendarSlot::find()
                ->where(['in', 'worker_id', $listWorkerTomorrow])
                ->andWhere(['status' => ShopCalendarSlot::STATUS_ACTIVE])
                ->count();

            $workersToday = WorkerInfo::getWorkersInfo($shop_id, $listWorkerToday, $today);
            $workersTomorrow = WorkerInfo::getWorkersInfo($shop_id, $listWorkerTomorrow, $tomorrow);

            return $this->render("index", [
                "shop_id" => $shop_id,
//                "shop_url" => ShopInfo::getShopUrl($shop_id),
                "model" => $model,
                "worker_id" => $worker_id,
                "workers_today" => $workersToday,
                "workers_tomorow" => $workersTomorrow,
                "slot_today" => $slotToday,
                "slot_tomorow" => $slotTomorrow,
//                "list_worker_rank" => WorkerInfo::getListWorkerRank(),
            ]);
        } else {
            throw new NotFoundHttpException(\Yii::t('common.message', 'The requested page does not exist.'));
        }
    }

    public function actionLoadInfo($shop_id)
    {
        if (!$this->request->isAjax) {
            return $this->redirect("/shop/$shop_id");
        }
        $this->response->format = Response::FORMAT_JSON;
        $model = ShopConfigForm::findOne($shop_id);
        if ($model) {
            return [
                "success" => true,
                "data" => $model
            ];
        } else {
            return [
                "success" => false,
            ];
        }
    }
    
}