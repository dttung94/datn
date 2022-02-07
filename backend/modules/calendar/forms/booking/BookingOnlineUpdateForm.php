<?php
namespace backend\modules\calendar\forms\booking;

use common\entities\calendar\BookingInfo;
use common\entities\calendar\CoursePrice;
use common\entities\calendar\OptionFee;
use common\entities\shop\ShopCalendarSlot;
use common\entities\worker\WorkerInfo;
use common\helper\ArrayHelper;
use common\helper\DatetimeHelper;
use common\helper\StringHelper;

/**
 * Class BookingOnlineUpdateForm
 * @package backend\modules\calendar\forms\booking
 *
 * @property integer $duration_minute
 * @property integer $start_hour
 * @property integer $start_minute
 *
 * @property array $durationMinuteData
 * @property array $listDurationMinute
 */
class BookingOnlineUpdateForm extends BookingOnlineForm
{
    public $duration_minute, $start_hour, $start_minute;

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['durationMinute', 'course_id', 'start_hour', 'start_minute'], 'required'],
        ]);
    }

    public function toPrepare()
    {
        if ($this->slotInfo) {
            $this->duration_minute = $this->slotInfo->duration_minute;
            $this->start_hour = DatetimeHelper::getHourFromTimeFormat($this->slotInfo->start_time);
            $this->start_minute = DatetimeHelper::getMinuteFromTimeFormat($this->slotInfo->start_time);
            return true;
        }
        return false;
    }

    public function toSave()
    {
        if ($this->validate(['course_id', 'start_hour', 'start_minute', 'comment'])) {
            $trans = \App::$app->db->beginTransaction();
            //1. todo update booking slot
            $bookingSlot = ShopCalendarSlot::findOne($this->slot_id);
            if (!$bookingSlot) {
                $this->addError("slot_id", \App::t("backend.booking.message", "Slot is invalid."));
            }
            $this->save();
            $bookingSlot->start_time = "$this->start_hour:$this->start_minute";
            $bookingSlot->duration_minute = $this->duration_minute;
            $bookingSlot->end_time = date("H:i", strtotime("+$this->duration_minute minutes", strtotime($bookingSlot->start_time)));
            if (!$bookingSlot->save()) {
                $this->addErrors($bookingSlot->getErrors());
            }
            if (!$this->hasErrors()) {
                $trans->commit();
                return true;
            } else {
                $trans->rollBack();
            }
        }
        return false;
    }


    public function extraFields()
    {
        return ArrayHelper::merge(parent::extraFields(), [
        ]);
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        $data = parent::toArray($fields, $expand, $recursive);
        $data["durationMinute"] = $this->duration_minute;
        $data["start_hour"] = $this->start_hour;
        $data["start_minute"] = $this->start_minute;
        return $data;
    }
}