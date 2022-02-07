<?php


namespace frontend\forms\booking;


use common\entities\calendar\BookingCoupon;
use common\entities\calendar\BookingInfo;
use common\entities\calendar\CouponInfo;
use common\entities\calendar\CouponLog;
use common\entities\calendar\CouponMember;
use common\entities\calendar\CouponShop;
use common\entities\calendar\CourseInfo;
use common\entities\calendar\CoursePrice;
use common\entities\calendar\OptionFee;
use common\entities\customer\CustomerData;
use common\entities\customer\CustomerInfo;
use common\entities\shop\ShopCalendar;
use common\entities\shop\ShopCalendarSlot;
use common\entities\shop\ShopInfo;
use common\entities\system\SystemConfig;
use common\entities\user\UserData;
use common\entities\user\UserLog;
use common\entities\worker\WorkerInfo;
use common\helper\ArrayHelper;
use common\helper\DatetimeHelper;

/**
 * Class BookingEditForm
 * @package frontend\forms\booking
 *
 * @property array $coupon_codes
 *
 * @property CourseInfo[] $courses
 */
class BookingEditForm extends BookingInfo
{

    public $count, $duration_minute;

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
        ]);
    }

    public function toPrepare($type = null)
    {
        if ($this->slotInfo) {
            // don't update frequency for booking free: if change will have bug in table total_booking
                $query = parent::find();
                $query->innerJoin(ShopCalendarSlot::tableName(), ShopCalendarSlot::tableName() . ".slot_id = " . BookingInfo::tableName() . ".slot_id");
                $query->where([
                    ShopCalendarSlot::tableName() . ".worker_id" => $this->slotInfo->worker_id,
                    BookingInfo::tableName() . ".member_id" => \App::$app->user->getId(),
                    BookingInfo::tableName() . ".status" => BookingInfo::STATUS_ACCEPTED,
                    BookingInfo::tableName() . ".booking_id" => $this->booking_id,
                ]);
            return true;
        } else {
            $this->addError("slot_id", \App::t("frontend.online-booking.message", "修正不可.", [
                "slot-id" => $this->slot_id,
            ]));
        }
        return false;
    }

    public function toEdit()
    {
        if ($this->status != self::STATUS_ACCEPTED) {
            $this->addError("update_time", \App::t("frontend.online-booking.message", "管理者が承認していますので、少々お待ちください！"));
            return false;
        } elseif ($this->isUpdatable) {
            $trans = \App::$app->db->beginTransaction();
            $this->status = self::STATUS_UPDATING;
                $coursePrice = CourseInfo::findOne([
                "course_id" => $this->course_id,
                "status" => CourseInfo::STATUS_ACTIVE,
            ]);
            $this->cost = $coursePrice->price;
            if (!$this->hasErrors() && $this->save(false)) {
                $modelShopInfo = new ShopInfo();
                $listShops = $modelShopInfo->getListShop();
                $trans->commit();
                $time = date('H:i', strtotime($this->slotInfo->start_time));
                $message = "[".$this->memberInfo->phone_number."]の[".$this->memberInfo->full_name."]が[".$listShops[$this->slotInfo->shop_id]."]の[".$this->slotInfo->workerInfo->worker_name."]の[".$time."]の予約修正を申請しました。";
                return true;
            } else {
                $this->addError("course_id", \App::t("frontend.online-booking.message", "[{course_id}]このコースはこの枠では選択できません", [
                    "course_id" => $this->course_id,
                ]));
            }
            $trans->rollBack();
        }
        $this->addError("update_time", \App::t("frontend.online-booking.message", "予約を修正できません！"));
        return false;
    }


    private function getTimeTomorrow($time)
    {
        $times = explode(':', $time);
        return (24+(int)$times[0]).':'.$times[1];
    }

    public function getCourseTypes()
    {
        return CourseInfo::find()
            ->where([
                CourseInfo::tableName() . ".status" => CourseInfo::STATUS_ACTIVE,
            ])
            ->all();
    }


    public function extraFields()
    {
        return ArrayHelper::merge(parent::extraFields(), [
            "courseTypes",
            'countBooked'
        ]);
    }

    public function toArray(array $fields = [], array $expand = ["slotInfo", "courseTypes"], $recursive = true)
    {
        $data = parent::toArray($fields, $expand, $recursive);
        return $data;
    }
}
