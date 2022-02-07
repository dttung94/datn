<?php
namespace backend\modules\calendar\forms\booking;


use common\entities\calendar\CoursePrice;
use common\entities\calendar\OptionFee;
use common\entities\shop\ShopCalendarSlot;
use common\entities\shop\ShopInfo;
use common\entities\system\SystemConfig;
use common\entities\worker\WorkerInfo;
use common\helper\ArrayHelper;
use common\helper\DatetimeHelper;

/**
 * Class ShopCalendarSlotForm
 * @package backend\modules\calendar\forms\booking
 *
 * @property string $start_hour
 * @property string $start_minute
 *
 * @property array $workers
 * @property array $shops
 */
class WorkerCalendarSlotForm extends ShopCalendarSlot
{

    public static function getInstance($worker_id, $shop_id, $date, $start_time)
    {
        $model = self::findOne([
            "shop_id" => $shop_id,
            "worker_id" => $worker_id,
            "date" => $date,
            "start_time" => $start_time
        ]);
        if (!$model) {
            $model = new self();
            $model->shop_id = $shop_id;
            $model->worker_id = $worker_id;
            $model->date = $date;
            $model->start_time = $start_time;
            $model->status = self::STATUS_ACTIVE;
        }
        $model->duration_minute = "45";
        $model->start_hour = DatetimeHelper::getHourFromTimeFormat($start_time);
        $model->start_minute = DatetimeHelper::getMinuteFromTimeFormat($start_time);
        return $model;
    }

    public $start_hour, $start_minute;

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['start_hour', 'start_minute'], 'required'],
            [['start_hour', 'start_minute'], 'safe'],
//            [['duration_minute'], 'validateTime'],
        ]);
    }

    public function validateTime($attribute, $params)
    {
        if (!$this->hasErrors()) {//todo validate DATE with SHOP calendar
            $dfw = date("N", strtotime($this->date));
            if (ArrayHelper::getValue($this->shopInfo->workingDayOnWeek, $dfw, 1) != 1) {
                $this->addError($attribute, \App::t("backend.booking.message", "Shop [{shop}] is off-day on {date}", [
                    "shop" => $this->shopInfo->shop_name,
                    "date" => \App::$app->formatter->asDate($this->date),
                ]));
            }
        }
        if (!$this->hasErrors()) {//todo validate TIME with SHOP calendar
            if (
                (DatetimeHelper::timeFormat2Seconds($this->shopInfo->openDoorAt) > DatetimeHelper::timeFormat2Seconds($this->start_time)) ||
                ($this->shopInfo->closeDoorAt != "24:0" && (DatetimeHelper::timeFormat2Seconds($this->shopInfo->closeDoorAt) < DatetimeHelper::timeFormat2Seconds($this->start_time))) ||
                ($this->shopInfo->closeDoorAt != "24:0" && (DatetimeHelper::timeFormat2Seconds($this->shopInfo->closeDoorAt) < DatetimeHelper::timeFormat2Seconds($this->end_time)))
            ) {
                $this->addError($attribute, \App::t("backend.booking.message", "[{start-time} - {end-time}] is invalid with [{shop}]", [
                    "shop" => $this->shopInfo->shop_name,
                    "start-time" => $this->start_time,
                    "end-time" => $this->end_time,
                ]));
            }
        }
        if (!$this->hasErrors()) {//todo validate DATE TIME with WORKER calendar
            $shopCalendar = $this->workerInfo->getScheduleOnShop($this->date, $this->shop_id);
            if ($shopCalendar == null) {//todo validate the Date is WORKING_DATE of WORKER calendar
                $this->addError($attribute, \App::t("backend.booking.message", "[{date}] is Holiday of [{worker}]", [
                    "date" => \App::$app->formatter->asDate($this->date),
                    "worker" => $this->workerInfo->worker_name,
                ]));
            } else if (//todo validate TIME with WORKER calendar
                (DatetimeHelper::timeFormat2Seconds($shopCalendar->work_start_time) > DatetimeHelper::timeFormat2Seconds($this->start_time)) ||
                ($shopCalendar->work_end_time != "24:0" && (DatetimeHelper::timeFormat2Seconds($shopCalendar->work_end_time) < DatetimeHelper::timeFormat2Seconds($this->start_time))) ||
                ($shopCalendar->work_end_time != "24:0" && (DatetimeHelper::timeFormat2Seconds($shopCalendar->work_end_time) < DatetimeHelper::timeFormat2Seconds($this->end_time)))
            ) {
                $this->addError($attribute, \App::t("backend.booking.message", "[{start-time} - {end-time}] is invalid with [{worker}]", [
                    "worker" => $this->workerInfo->worker_name,
                    "start-time" => $this->start_time,
                    "end-time" => $this->end_time,
                ]));
            } else if (//todo validate TIME with now
                strtotime("$this->date $this->start_time") < strtotime(\App::$app->formatter->asDatetime(time(), "yyyy-M-d H:i"))
            ) {
                $this->addError($attribute, \App::t("backend.booking.message", "Start time much greater now [{now}]", [
                    "now" => \App::$app->formatter->asDatetime(time()),
                ]));
            } else { //todo validate TIME is CONFLICT with another time of WORKER
                $todaySlots = $this->workerInfo->getSlots($this->date);
                foreach ($todaySlots as $slot) {
                    if (
                        (
                            $this->isNewRecord ||
                            $this->slot_id != $slot->slot_id
                        )
                        &&
                        (
                            (
                                (DatetimeHelper::timeFormat2Seconds($slot->start_time) < DatetimeHelper::timeFormat2Seconds($this->start_time)) &&
                                (DatetimeHelper::timeFormat2Seconds($slot->end_time) > DatetimeHelper::timeFormat2Seconds($this->start_time))
                            ) ||
                            (
                                (DatetimeHelper::timeFormat2Seconds($slot->start_time) < DatetimeHelper::timeFormat2Seconds($this->end_time)) &&
                                (DatetimeHelper::timeFormat2Seconds($slot->end_time) > DatetimeHelper::timeFormat2Seconds($this->end_time))
                            ) ||
                            (
                                (DatetimeHelper::timeFormat2Seconds($this->start_time) < DatetimeHelper::timeFormat2Seconds($slot->start_time)) &&
                                (DatetimeHelper::timeFormat2Seconds($this->end_time) > DatetimeHelper::timeFormat2Seconds($slot->start_time))
                            ) ||
                            (
                                (DatetimeHelper::timeFormat2Seconds($this->start_time) < DatetimeHelper::timeFormat2Seconds($slot->end_time)) &&
                                (DatetimeHelper::timeFormat2Seconds($this->end_time) > DatetimeHelper::timeFormat2Seconds($slot->end_time))
                            )
                        )
                    ) {
                        $this->addError($attribute, \App::t("backend.booking.message", "[{start-time} - {end-time}]bị trùng với khung của [{worker}]", [
                            "worker" => $this->workerInfo->worker_name,
                            "start-time" => $this->start_time,
                            "end-time" => $this->end_time,
                        ]));
                    }
                }
            }
        }
    }

    public function toSave($start_hour, $start_minute, $duration)
    {
        $this->start_hour = $start_hour;
        $this->start_minute = $start_minute;
        $this->start_time = "$start_hour:$start_minute";
        $this->duration_minute = $duration;
        $this->end_time = date("H:i", strtotime("+$duration minutes", strtotime($this->start_time)));
        if (strtotime($this->start_time) > strtotime($this->end_time)) {
            $this->end_time = $this->getTimeTomorrow($this->end_time);
        }
        return $this->save();
    }

    private function getTimeTomorrow($time)
    {
        $times = explode(':', $time);
        return (24+(int)$times[0]).':'.$times[1];
    }


    public function getDurationMinute()
    {
        return SystemConfig::getValue(SystemConfig::CONFIG_DURATION_TIME_COURSE, SystemConfig::DURATION_TIME);
    }

    public function getWorkers()
    {
        return ArrayHelper::map(WorkerInfo::findAll([
            "status" => WorkerInfo::STATUS_ACTIVE,
        ]), "worker_id", "worker_name");
    }

    public function getShops()
    {
        return ArrayHelper::map(ShopInfo::findAll([
            "status" => ShopInfo::STATUS_ACTIVE,
        ]), "shop_id", "shop_name");
    }

    public function extraFields()
    {
        return ArrayHelper::merge(parent::extraFields(), [
            "shopInfo",
            "workerInfo",
            "isNewRecord",
        ]);
    }

    public function toArray(array $fields = [], array $expand = ["shopInfo", "workerInfo", "isNewRecord", "isCanBooking", "bookingInfo"], $recursive = true)
    {
        $data = parent::toArray($fields, $expand, $recursive);
        $data["start_hour"] = DatetimeHelper::getHourFromTimeFormat($this->start_time);
        $data["start_minute"] = DatetimeHelper::getMinuteFromTimeFormat($this->start_time);
        $data["end_hour"] = DatetimeHelper::getHourFromTimeFormat($this->end_time);
        $data["end_minute"] = DatetimeHelper::getMinuteFromTimeFormat($this->end_time);
        return $data;
    }
}