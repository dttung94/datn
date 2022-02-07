<?php
namespace frontend\forms\shop;

use common\entities\shop\ShopCalendar;
use common\entities\shop\ShopCalendarSlot;
use common\entities\shop\ShopInfo;
use common\entities\user\UserConfig;
use common\entities\user\UserInfo;
use common\entities\worker\WorkerInfo;
use common\entities\worker\WorkerMappingShop;
use common\helper\ArrayHelper;
use yii\helpers\Json;

/**
 * Class ShopForm
 * @package frontend\forms\shop
 *
 * @property string $date
 *
 * @property string $dateTomorrow
 * @property boolean $isAllowFreeBooking
 * @property WorkerInfo[] $workers
 */
class ShopForm extends ShopInfo
{
    public $date;

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [["date"], 'safe'],
        ]);
    }

    public function getWorkingWorkerIds($date, $userInfo = null) {
        if(empty($userInfo)) {
            $userInfo = UserInfo::findOne(\App::$app->user->id);
        }
        $workerBlacklistIds = [];
        $workerQuery = ShopCalendar::find()
            ->innerJoin(
                ShopCalendarSlot::tableName(),
                ShopCalendarSlot::tableName().".shop_id = ".ShopCalendar::tableName().".shop_id AND ".
                ShopCalendarSlot::tableName().".worker_id = ".ShopCalendar::tableName().".worker_id"
            )
            ->where([
                ShopCalendar::tableName().".shop_id" => $this->shop_id,
                ShopCalendar::tableName().".type" => ShopCalendar::TYPE_WORKING_DAY
            ])
            ->andWhere([ShopCalendarSlot::tableName().".date" => $date])
            ->select(ShopCalendar::tableName().".worker_id")
            ->distinct(ShopCalendar::tableName().".worker_id")
            ->all();
        $workerIds = ArrayHelper::getColumn($workerQuery, "worker_id");
        return array_diff($workerIds, $workerBlacklistIds);
    }

    public function getWorkers()
    {
        $userInfo = UserInfo::findOne(\App::$app->user->id);
        if ($userInfo) {
            /**
             * @var $userInfo UserInfo
             */
            $workers = $this->getListWorker($this->shop_id);
            $workerBlacklistIds = [];
            return WorkerInfo::find()
                ->innerJoin(ShopCalendarSlot::tableName(), ShopCalendarSlot::tableName() . ".worker_id = " . ShopCalendarSlot::tableName() . ".worker_id AND " . ShopCalendarSlot::tableName() . ".date = :date", [
                    ":date" => $this->date,
                ])
                ->where([
                    WorkerInfo::tableName() . ".status" => WorkerInfo::STATUS_ACTIVE,
                    ShopCalendarSlot::tableName() . ".shop_id" => $this->shop_id,
                ])
                ->andWhere(['not in', WorkerInfo::tableName() . '.worker_id', $workerBlacklistIds])
                ->andWhere(['in', WorkerInfo::tableName() . '.worker_id', $workers])
                ->all();
        }
        return [];
    }

    protected function getListWorker($shopId)
    {
        $now = date('Y-m-d');
        $tomorow = date('Y-m-d', strtotime('+1 day', strtotime($now)));
        $workers = [];
        $datas = ShopCalendar::find()
            ->select('worker_id')
            ->where(['between', "date", $now, $tomorow])
            ->andWhere("status = :status", [
                ":status" => ShopCalendar::STATUS_ACTIVE
            ])
            ->andWhere("shop_id = :shop_id", [
                ":shop_id" => $shopId
            ])
            ->andWhere("type = :type", [
                ":type" => ShopCalendar::TYPE_WORKING_DAY
            ])->all();
        foreach ($datas as $data) {
            $workers[] = $data->worker_id;
        }
        return $workers;
    }

    public function getDateTomorrow()
    {
        return date("Y-m-d", strtotime($this->date) + 1 * 24 * 60 * 60);
    }
}