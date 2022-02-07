<?php


namespace frontend\forms\booking;


use common\entities\calendar\BookingCoupon;
use common\entities\calendar\BookingInfo;
use common\entities\user\UserInfo;
use common\entities\calendar\CourseInfo;
use common\entities\shop\ShopCalendar;
use common\entities\shop\ShopCalendarSlot;
use common\entities\shop\ShopInfo;
use common\entities\system\SystemConfig;
use common\entities\worker\WorkerInfo;
use common\helper\ArrayHelper;
use common\helper\DatetimeHelper;

/**
 * Class BookingEditForm
 * @package frontend\forms\booking
 *
 *
 * @property CourseInfo[] $courses
 */
class BookingOnlineForm extends BookingInfo
{

    public $count;

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['member_id'], 'validateMaxBookingPending'],
            [["slot_id"], 'validateSlot'],
        ]);
    }
    public function validateMaxBookingPending($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $maxOnlineBookingPending = intval(SystemConfig::getValue(SystemConfig::CATEGORY_BOOKING, SystemConfig::BOOKING_MAX_ONLINE_BOOKING_PENDING, 0));
            $totalOnlinePendingBooking = self::find()
                ->where([
                    "status" => self::STATUS_PENDING,
                    "member_id" => $this->$attribute,
                ])
                ->count();
            if ($maxOnlineBookingPending != 0 && (int)$totalOnlinePendingBooking >= $maxOnlineBookingPending) {
                $this->addError($attribute, \App::t("frontend.free-booking.message", "Bạn chỉ có thể gửi yêu cầu đồng thời {total-max} lượt", [
                    "total-pending" => $totalOnlinePendingBooking,
                    "total-max" => $maxOnlineBookingPending,
                ]));
            }
        }
    }

    public function validateSlot($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if ($this->slotInfo == null || !$this->slotInfo->isCanBooking) {
                $this->addError($attribute, \App::t("frontend.online-booking.message", "Vui lòng gửi lại yêu cầu"));
            }
        }
    }

    public function toPrepare($type = null)
    {
        if ($this->slotInfo) {
            $query = parent::find();
            $query->innerJoin(ShopCalendarSlot::tableName(), ShopCalendarSlot::tableName() . ".slot_id = " . BookingInfo::tableName() . ".slot_id");
            $query->where([
                ShopCalendarSlot::tableName() . ".worker_id" => $this->slotInfo->worker_id,
                BookingInfo::tableName() . ".member_id" => \App::$app->user->getId(),
                BookingInfo::tableName() . ".status" => BookingInfo::STATUS_ACCEPTED,
                BookingInfo::tableName() . ".booking_id" => $this->booking_id,
            ]);
            $courses = $this->courses;
            if (!empty($courses)) {
                $this->course_id = intval($courses[0]->course_id);
            }
            return true;
        } else {
            $this->addError("slot_id", \App::t("frontend.online-booking.message", "Không thể thay đổi", [
                "slot-id" => $this->slot_id,
            ]));
        }
        return false;
    }

    public function toSave()
    {
        if ($this->validate(["slot_id", "course_id"])) {
            $trans = \App::$app->db->beginTransaction();
            $this->status = self::STATUS_PENDING;
            $coursePrice = CourseInfo::findOne([
                "course_id" => $this->course_id,
                "status" => CourseInfo::STATUS_ACTIVE,
            ]);
            if ($coursePrice) {
                //todo cal cost of booking
                $this->cost = $coursePrice->price;
                if (!$this->hasErrors()) {
                    $strStart = strtotime($this->slotInfo->start_time);
                    $strEnd = strtotime($this->slotInfo->end_time);

                    $start = date('H:i', $strStart);
                    $end = date('H:i', $strEnd);

                    if ($strEnd < $strStart) {
                        $this->slotInfo->end_time = $this->getTimeTomorrow($this->slotInfo->end_time);
                        $end = $this->slotInfo->end_time;
                    }

                    $condition = [
                        'date' => $this->slotInfo->date,
                        'worker_id' => $this->slotInfo->worker_id,
                        'shop_id' => $this->slotInfo->shop_id,
                    ];

                    $slots = ShopCalendarSlot::find()
                        ->where($condition)
                        ->andWhere(['!=', 'status', ShopCalendarSlot::STATUS_EXPIRED])
                        ->all();
                    $flag = true;
                    foreach ($slots as $slot) {
                        $startTime = date('H:i', strtotime($slot->start_time));
                        if (
                            $this->slotInfo->slot_id != $slot->slot_id &&
                            $startTime > $start &&
                            $startTime < $end
                        ) {
                            if ($slot->status == ShopCalendarSlot::STATUS_BOOKED) {
                                $this->addErrors(['Không thể mở rộng khung làm việc']);
                            } else {
                                $ids[] = $slot->slot_id;
                                $update = ShopCalendarSlot::findOne($slot->slot_id);
                                $update->status = ShopCalendarSlot::STATUS_DELETE;
                                if (!$update->save(false)) {
                                    $flag = false;
                                    break;
                                }
                            }
                        }
                    }
                    if (!$flag) {
                        $this->addErrors(['Không thể xóa khung làm việc']);
                    }
                }

                if (!$this->hasErrors()) {
                    $this->member_id = $this->memberInfo->user_id;
                    $this->cost = $this->cost < 0 ? 0 : $this->cost;

                    $this->save();//todo save booking info

                    //todo update slot status
                    $slotInfo = $this->slotInfo;
                    $slotInfo->status = ShopCalendarSlot::STATUS_BOOKED;
                    if (!$this->hasErrors() && !$slotInfo->save()) {
                        $this->addErrors($slotInfo->getErrors());
                    }

                }

                if (!$this->hasErrors() && $this->save(false)) {
                    $modelShopInfo = new ShopInfo();
                    $listShops = $modelShopInfo->getListShop();
                    $trans->commit();
                    $time = date('H:i', strtotime($this->slotInfo->start_time));
                    $message = "[".$this->memberInfo->phone_number."]の[".$this->memberInfo->full_name."]が[".$listShops[$this->slotInfo->shop_id]."]の[".$this->slotInfo->workerInfo->worker_name."]の[".$time."]を予約しました。";
                    return true;
                }
            } else {
                $this->addError("course_id", \App::t("frontend.online-booking.message", "[{course_id}]このコースはこの枠では選択できません", [
                    "course_id" => $this->course_id,
                ]));
            }
            $trans->rollBack();
        }
        return false;
    }


    private function getTimeTomorrow($time)
    {
        $times = explode(':', $time);
        return (24+(int)$times[0]).':'.$times[1];
    }

    public function getCourses()
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
            "courses",
            'countBooked'
        ]);
    }

    public function toArray(array $fields = [], array $expand = ["slotInfo", "courses"], $recursive = true)
    {
        $data = parent::toArray($fields, $expand, $recursive);
        return $data;
    }
}
