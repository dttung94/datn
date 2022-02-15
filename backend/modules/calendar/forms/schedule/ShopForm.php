<?php
namespace backend\modules\calendar\forms\schedule;


use App;
use common\entities\shop\ShopCalendar;
use common\entities\shop\ShopCalendarSlot;
use common\entities\worker\WorkerInfo;
use common\entities\worker\WorkerMappingShop;
use common\helper\ArrayHelper;
use common\helper\DatetimeHelper;
use yii\web\NotFoundHttpException;

/**
 * Class ShopForm
 * @package backend\modules\calendar\forms\schedule
 *
 * @property string $date
 * @property array $scheduleWorkers
 */
class ShopForm extends \backend\modules\shop\forms\ShopForm
{
    public $date;
    public $scheduleWorkers;
    public $totalPage;

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['scheduleWorkers'], "safe"],
        ]);
    }

    public function toPrepare($page = 1, $name = null, $per = 20)
    {
        parent::toPrepare();
        $this->scheduleWorkers = [];
        $configs = ShopCalendar::find()
            ->where("shop_id = :shop_id", [
                ":shop_id" => $this->shop_id
            ])
            ->andWhere("date = :date", [
                ":date" => $this->date,
            ])
            ->andWhere("status = :status_active", [
                ":status_active" => ShopCalendar::STATUS_ACTIVE,
            ])
            ->all();
        $workerIdMaps = WorkerMappingShop::find()
            ->where("shop_id = :shop_id", [
                ":shop_id" => $this->shop_id
            ])
            ->andWhere("status = :status_active", [
                ":status_active" => ShopCalendar::STATUS_ACTIVE,
            ])->all();
        $workerIds = [];
        foreach ($workerIdMaps as $workerIdMap) {
            $workerIds[] = $workerIdMap->worker_id;
        }
        $query = WorkerInfo::find()
            ->where(['in', "worker_id", $workerIds])
            ->andWhere("status = :status_active", [
                ":status_active" => ShopCalendar::STATUS_ACTIVE,
            ]);
        if ($name != null) {
            $query->andWhere(['LIKE', 'worker_name', $name]);
        }
        $pages = ceil(count($query->all())/$per);
        if ($pages < 1) {
            $pages = 1;
        }
        if (!is_numeric($page) || $page < 0 || $page > $pages) {
            throw new NotFoundHttpException(\Yii::t('common.message', 'The requested page does not exist.'));
        }
        $offset = ($page - 1)*$per;
        $workers = $query->offset($offset)->limit($per)->all();
        foreach ($workers as $worker) {
            $config = new ShopCalendar();
            $config->shop_id = $this->shop_id;
            $config->worker_id = $worker->worker_id;
            $config->date = $this->date;
            $config->type = ShopCalendar::TYPE_HOLIDAY;
            $config->work_start_time = $this->openDoorAt;
            $config->work_end_time = $this->closeDoorAt;
            $config->status = ShopCalendar::STATUS_ACTIVE;
            foreach ($configs as $value) {
                if ($value->worker_id == $worker->worker_id) {
                    $config = $value;
                }
            }

            $workerShopCalendar = $config->toArray([], ['workerInfo']);
            $workerShopCalendar["work_start_hour"] = DatetimeHelper::getHourFromTimeFormat($config->work_start_time, ":", $this->open_door_hour);
            $workerShopCalendar["work_start_minute"] = DatetimeHelper::getMinuteFromTimeFormat($config->work_start_time, ":", $this->open_door_minute);
            $workerShopCalendar["work_end_hour"] = DatetimeHelper::getHourFromTimeFormat($config->work_end_time, ":", $this->close_door_hour);
            $workerShopCalendar["work_end_minute"] = DatetimeHelper::getMinuteFromTimeFormat($config->work_end_time, ":", $this->close_door_minute);
            $workerShopCalendar["is_work_day"] = $config->type == ShopCalendar::TYPE_WORKING_DAY ? 1 : 0;
            if ($config->type == ShopCalendar::TYPE_WORKING_DAY) {
                //todo check worker off able
                $totalSlotAvailable = ShopCalendarSlot::find()
                    ->where([
                        "worker_id" => $worker->worker_id,
                        "shop_id" => $this->shop_id,
                        "date" => $this->date,
                    ])
                    ->andWhere(["IN", "status", [
                        ShopCalendarSlot::STATUS_ACTIVE,
                        ShopCalendarSlot::STATUS_BOOKED,
                    ]])
                    ->count();
                $workerShopCalendar["isSwitchable"] = $totalSlotAvailable > 0 ? false : true;
            } else {
                $workerShopCalendar["isSwitchable"] = true;
            }
            $this->scheduleWorkers[$config->worker_id] = $workerShopCalendar;
            $this->totalPage = $pages;
        }
    }

    public function checkDateIsWorkingDay($date)
    {
        $dayOfWeek = DatetimeHelper::getDayOfWeekFromDate($date);
        return ArrayHelper::getValue($this->workingDayOnWeek, $dayOfWeek, 0) == "1";
    }

    public function extraFields()
    {
        return ArrayHelper::merge(parent::extraFields(), [
            "date",
            "scheduleWorkers",
        ]);
    }

    public function toSave()
    {
        $trans = App::$app->db->beginTransaction();
        //todo save worker calendar
        foreach ($this->scheduleWorkers as $worker_id => $dataConfig) {
            $config = ShopCalendar::findOne([
                "shop_id" => $this->shop_id,
                "worker_id" => $worker_id,
                "date" => $this->date,
            ]);
            if ($config == null) {
                $config = new ShopCalendar();
                $config->shop_id = $this->shop_id;
                $config->worker_id = $worker_id;
                $config->date = $this->date;
                $config->type = ShopCalendar::TYPE_HOLIDAY;
            }
            $config->status = ShopCalendar::STATUS_ACTIVE;
            if (isset($dataConfig["is_work_day"]) && $dataConfig["is_work_day"]) {
                $work_start_hour = ArrayHelper::getValue($dataConfig, "work_start_hour", 0);
                $work_start_minute = ArrayHelper::getValue($dataConfig, "work_start_minute", 0);
                $work_end_hour = ArrayHelper::getValue($dataConfig, "work_end_hour", 0);
                if ($work_end_hour != 24) {
                    $work_end_minute = ArrayHelper::getValue($dataConfig, "work_end_minute", 0);
                } else {
                    $work_end_minute = 0;
                }
                $config->type = ShopCalendar::TYPE_WORKING_DAY;
                $config->work_start_time = "$work_start_hour:$work_start_minute";
                $config->work_end_time = "$work_end_hour:$work_end_minute";
                if (!$config->isNewRecord) {//todo validate if schedule changed
                    $slots = ShopCalendarSlot::find()
                        ->where([
                            "shop_id" => $config->shop_id,
                            "worker_id" => $config->worker_id,
                            "date" => $config->date,
                        ])
                        ->andWhere(["IN", "status", [
                            ShopCalendarSlot::STATUS_ACTIVE,
                            ShopCalendarSlot::STATUS_BOOKED,
                        ]])
                        ->all();
                    $scheduleStartTime = DatetimeHelper::timeFormat2Seconds($config->work_start_time);
                    $scheduleEndTime = DatetimeHelper::timeFormat2Seconds($config->work_end_time);
                    foreach ($slots as $slot) {
                        /**
                         * @var $slot ShopCalendarSlot
                         */
                        $slotStartTime = DatetimeHelper::timeFormat2Seconds($slot->start_time);
                        $slotEndTime = DatetimeHelper::timeFormat2Seconds($slot->end_time);
                        if (
                            $slotStartTime < $scheduleStartTime ||
                            $slotStartTime > $scheduleEndTime ||
                            $slotEndTime < $scheduleStartTime ||
                            $slotEndTime > $scheduleEndTime
                        ) {
                            $this->addError("$this->date-$config->worker_id", App::t("backend.schedule.message", "{worker} đang có khung làm việc [{start-time} - {end-time}]", [
                                "worker" => $slot->workerInfo->worker_name,
                                "start-time" => $slot->start_time,
                                "end-time" => $slot->end_time,
                            ]));
                            break;
                        }
                    }
                }
            } else {
                if ($config->type == ShopCalendar::TYPE_WORKING_DAY) {
                    //todo check worker off able
                    $totalSlotAvailable = ShopCalendarSlot::find()
                        ->where([
                            "worker_id" => $config->worker_id,
                            "shop_id" => $this->shop_id,
                            "date" => $this->date,
                        ])
                        ->andWhere(["IN", "status", [
                            ShopCalendarSlot::STATUS_ACTIVE,
                            ShopCalendarSlot::STATUS_BOOKED,
                        ]])
                        ->count();
                    if ($totalSlotAvailable > 0) {
                        $config->addError("$this->date-$config->worker_id", App::t("backend.schedule.message", "{worker} have available slot or booking, so can not off.", [
                            "worker" => $config->workerInfo->worker_name,
                        ]));
                    }
                }
                $config->type = ShopCalendar::TYPE_HOLIDAY;
                $config->work_start_time = null;
                $config->work_end_time = null;
            }
            if (!$config->hasErrors()) {
                if (!$config->save()) {
                    $this->addErrors($config->getErrors());
                }
            } else {
                $this->addErrors($config->getErrors());
            }
        }
        if (!$this->hasErrors()) {
            $trans->commit();
            return true;
        }
        $trans->rollBack();
        return false;
    }

    public function saveCalendarConfigs($scheduleConfigs)
    {
        $trans = \App::$app->db->beginTransaction();
        foreach ($scheduleConfigs as $date => $configs) {
            foreach ($configs as $worker_id => $config) {
                $model = ShopCalendar::getInstance($this->shop_id, $worker_id, $date);
                if ($model == null) {
                    $model = new ShopCalendar();
                    $model->shop_id = $this->shop_id;
                    $model->worker_id = $worker_id;
                    $model->date = $date;
                }
                $model->status = ShopCalendar::STATUS_ACTIVE;
                if (isset($config["is_work_day"]) && $config["is_work_day"] == '1') {
                    $model->type = ShopCalendar::TYPE_WORKING_DAY;
                    $model->work_start_time = $config["work_start_time"];
                    $model->work_end_time = $config["work_end_time"];
                } else {
                    $model->type = ShopCalendar::TYPE_HOLIDAY;
                }
                if (!$model->save()) {
                    $this->addErrors($model->getErrors());
                    $trans->rollBack();
                    return false;
                }
            }
        }
        if (!$this->hasErrors()) {
            $trans->commit();
            return true;
        }
        $trans->rollBack();
        return false;
    }

    /**
     * @param $worker_id
     * @param $date
     * @param $start_time
     * @param $end_time
     * @return ShopCalendar
     */
    public function toCheckWorkerTimeIsValid($worker_id, $date, $work_start_hour, $work_start_minute, $work_end_hour, $work_end_minute)
    {
        if ($work_end_hour == 24) {
            $work_end_minute = 0;
        }
        $config = ShopCalendar::findOne([
            "shop_id" => $this->shop_id,
            "worker_id" => $worker_id,
            "date" => $date,
        ]);
        if ($config == null) {
            $config = new ShopCalendar();
            $config->shop_id = $this->shop_id;
            $config->worker_id = $worker_id;
            $config->date = $date;
        }
        $config->type = ShopCalendar::TYPE_WORKING_DAY;
        $config->work_start_time = "$work_start_hour:$work_start_minute";
        $config->work_end_time = "$work_end_hour:$work_end_minute";
        $config->validate();
        return $config;
    }

    public function getListWorkers()
    {
        return ArrayHelper::map($this->workers, "worker_id", "worker_name");
    }
}