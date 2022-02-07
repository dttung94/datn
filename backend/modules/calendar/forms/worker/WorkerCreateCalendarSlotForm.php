<?php
namespace backend\modules\calendar\forms\worker;


use backend\modules\calendar\forms\booking\WorkerCalendarSlotForm;
use common\entities\calendar\CoursePrice;
use common\entities\shop\ShopCalendar;
use common\entities\shop\ShopCalendarSlot;
use common\entities\shop\ShopInfo;
use common\entities\system\SystemConfig;
use common\entities\worker\WorkerInfo;
use common\helper\ArrayHelper;
use common\helper\DatetimeHelper;
use common\helper\HtmlHelper;

/**
 * Class WorkerCreateCalendarSlotForm
 * @package backend\modules\calendar\forms\worker
 *
 * @property string $date
 * @property string $type
 * @property integer $start_hour
 * @property integer $start_minute
 * @property integer $duration_minute
 * @property integer $break_duration_minute
 *
 * @property array $listDurationMinute
 * @property array $listHour
 * @property array $listMinute
 */
class WorkerCreateCalendarSlotForm extends WorkerInfo
{
    public $date, $start_hour, $start_minute, $duration_minute, $break_duration_minute, $type;

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [["date", "start_hour", "start_minute", "duration_minute", "break_duration_minute"], 'required'],
            [['date'], 'date', 'format' => 'yyyy-M-d'],
            [['date'], 'validateDate'],
            [["start_hour", "start_minute", "duration_minute", "break_duration_minute"], "integer"],
            [["date", "start_hour", "start_minute", "duration_minute", "break_duration_minute", 'type'], 'safe'],
        ]);
    }

    public function validateDate($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if (!$this->isWorkingDay($this->date)) {
                $this->addError($attribute, \App::t("backend.schedule.message", "Worker is offline on this date [{date}]", [
                    "date" => $this->date,
                ]));
            }
        }
    }

    public function extraFields()
    {
        return ArrayHelper::merge(parent::extraFields(), [
            "date",
            "start_hour",
            "start_minute",
            "duration_minute",
            "break_duration_minute",

            "listHour",
            "listMinute",
        ]);
    }

    public function getListHour()
    {
        $hours = [];
        $startHour = DatetimeHelper::getHourFromTimeFormat($this->getStartTime($this->date));
        $endHour = DatetimeHelper::getHourFromTimeFormat($this->getEndTime($this->date));
        for ($hour = intval($startHour); $hour <= intval($endHour); $hour++) {
            $hours["$hour"] = $hour . " giờ";
        }
        return $hours;
    }

    public function getListMinute()
    {
        $minutes = [];
        for ($minute = 0; $minute < 60; $minute += 5) {
            $minutes[$minute] = "$minute phút";
        }
        return $minutes;
    }

    public function getStartTime($date)
    {
        $time = parent::getStartTime($date);
        return (strtotime("$date $time") > time()) ? $time : date("H:i");
    }

    public function loadForm()
    {
        if ($this->validate(["date"])) {
            $startHour = DatetimeHelper::getHourFromTimeFormat($this->getStartTime($this->date), ":", 0);
            $startMinute = DatetimeHelper::getMinuteFromTimeFormat($this->getStartTime($this->date), ":", 0);
            $startMinute = ((intval((intval($startMinute) / 5)) + 1) * 5) . "";
            if ($startMinute >= 60) {
                $startMinute = $startMinute - 60;
                $startHour += 1;
            }
            $this->start_hour = $startHour;
            $this->start_minute = $startMinute;
            $this->duration_minute = SystemConfig::getValue(SystemConfig::CONFIG_DURATION_TIME_COURSE, SystemConfig::DURATION_TIME);
            $this->break_duration_minute = "10";
            $data = $this->toArray([], [
                "date",
                "start_hour",
                "start_minute",
                "duration_minute",
                "break_duration_minute",

                "listHour",
                "listMinute"
            ], true);
            $data["start_time"] = $this->getStartTime($this->date);
            $data["end_time"] = $this->getEndTime($this->date);
            return $data;
        }
        return false;
    }

    public function toCreateSlot()
    {
        if ($this->validate(["date", "start_hour", "start_minute", "duration_minute", "break_duration_minute"])) {
            $startMinute = DatetimeHelper::timeFormat2Seconds("$this->start_hour:$this->start_minute") / 60;
            $endMinute = DatetimeHelper::timeFormat2Seconds($this->getEndTime($this->date)) / 60;
            $result = [];
            for ($time = $startMinute; $time <= $endMinute; $time += $this->duration_minute + $this->break_duration_minute) {
                if ($time + $this->duration_minute <= $endMinute) {
                    $startTime = intval($time / 60) . ":" . ($time % 60);
                    //todo check time is valid
                    $query = ShopCalendar::find()
                        ->innerJoin(ShopInfo::tableName(), ShopInfo::tableName() . ".shop_id = " . ShopCalendar::tableName() . ".shop_id")
                        ->where([
                            ShopCalendar::tableName() . ".type" => ShopCalendar::TYPE_WORKING_DAY,
                            ShopCalendar::tableName() . ".date" => $this->date,
                            ShopCalendar::tableName() . ".worker_id" => $this->worker_id,
                            ShopInfo::tableName() . ".status" => ShopInfo::STATUS_ACTIVE,
                        ])
                        ->andWhere("TIME(work_start_time) <= TIME(:time)  AND TIME(work_end_time) > TIME(:time)", [
                            ":time" => $startTime
                        ]);
                    if (($shopCalendar = $query->one())) {
                        /**
                         * @var $shopCalendar ShopCalendar
                         */
                        $model = WorkerCalendarSlotForm::getInstance($this->worker_id, $shopCalendar->shop_id, $this->date, $startTime);
                        if (!$model->toSave(intval($time / 60), $time % 60, $this->duration_minute)) {
                            $result["$startTime"] = $model->getErrors();
                        } else {
                            $result["$startTime"] = "Add success";
                        }
                    } else {
                        $result["$startTime"] = "No Shop Calendar for this time: " . $query->createCommand()->rawSql;
                    }
                }
            }
            return $result;
        }
        return false;
    }
}