<?php
namespace backend\modules\calendar\forms\booking;

use common\entities\calendar\BookingCoupon;
use common\entities\calendar\BookingData;
use common\entities\calendar\BookingInfo;
use common\entities\calendar\CouponInfo;
use common\entities\calendar\CouponLog;
use common\entities\calendar\CouponMember;
use common\entities\calendar\CouponShop;
use common\entities\calendar\CourseInfo;
use common\entities\calendar\CoursePrice;
use common\entities\calendar\OptionFee;
use common\entities\calendar\TotalBooking;
use common\entities\calendar\TotalCouponUsed;
use common\entities\customer\CustomerInfo;
use common\entities\service\TemplateMail;
use common\entities\service\TemplateSms;
use common\entities\shop\ShopCalendarSlot;
use common\entities\shop\ShopInfo;
use common\entities\user\UserData;
use common\entities\user\UserInfo;
use common\entities\user\UserLog;
use common\entities\worker\WorkerInfo;
use common\forms\service\SendSMSForm;
use common\helper\ArrayHelper;
use common\mail\forms\Mail;

/**
 * Class BookingOnlineForm
 * @package backend\modules\calendar\forms\booking
 *
 * @property BookingOnlineForm $bookingHistories
 * @property UserInfo[] $listMembers
 * @property ShopInfo[] $listShops
 * @property WorkerInfo[] $listWorkers
 */
class BookingOnlineForm extends BookingInfo
{
    public function toAccept($smsContent, $titleEmail)
    {
        if ($this->isAcceptable) {
            $trans = \App::$app->db->beginTransaction();
            $status = $this->status;
            $this->status = self::STATUS_ACCEPTED;
            if ($this->save()) {
                $otherBooking = BookingInfo::find()->where(['slot_id' => $this->slot_id])->andWhere(['!=', 'booking_id', $this->booking_id])->all();
                if ($otherBooking) {
                    foreach ($otherBooking as $item) {
                        $item->status = self::STATUS_DELETED;
                        if (!$item->save()) {
                            $this->addErrors($item->getErrors());
                        }
                    }
                }
            }
            if (!$this->hasErrors()) {
                $trans->commit();
                //todo send SMS to customer
                $phoneNumber = '84'. $this->memberInfo->phone_number;
                $smsParams = [
                    "shop_name" => $this->slotInfo->shopInfo->shop_name,
                    "phone_number" => $this->slotInfo->shopInfo->phone_number,
                    "worker_name" => $this->slotInfo->workerInfo->worker_name,
                    "booking_date" => \App::$app->formatter->asDate($this->slotInfo->date),
                    "booking_time" => \App::$app->formatter->asTime($this->slotInfo->start_time),
                    "course_id" => ArrayHelper::getValue(self::getListCourseType(), $this->course_id),
                    "cost" => \App::$app->formatter->asCurrency($this->cost),
                    "course_time" => $this->slotInfo->duration_minute
                ];
                if ($status == BookingInfo::STATUS_PENDING) {
//                    $smsContent = SendSMSForm::getSMSTemplate(SendSMSForm::TYPE_BOOKING_ONLINE_ACCEPT);
                    SendSMSForm::toSend($phoneNumber, $smsContent, $smsParams, [
                        SendSMSForm::TYPE_BOOKING_ONLINE_ACCEPT,
                    ]);
                } else {
//                    $smsContent = TemplateSms::getSMSTemplate(TemplateSms::TYPE_BOOKING_ONLINE_UPDATE) . $content . "※このメッセージに返信はできません。";
                    SendSMSForm::toSend($phoneNumber, $smsContent, $smsParams, [
                        SendSMSForm::TYPE_BOOKING_ONLINE_UPDATE,
                    ]);
                }
                return true;
            } else {
                $trans->rollBack();
            }
        } else {
            $this->addError("booking_id", \App::t("backend.booking.message", "This booking is invalid to accept."));
        }
        return false;
    }

    public function toReject($smsContent, $text, $titleEmail = "")
    {
        if ($this->isRejectAble && !$this->isUpdateRejectAble) {
            $trans = \App::$app->db->beginTransaction();
            //todo set status REJECT to this booking
            $this->status = self::STATUS_REJECTED;
            if ($this->save()) {
                if ($this->slotInfo) {//todo set status ACTIVE to this slot
                    $slotInfo = $this->slotInfo;
                    $duration_minute = $this->slotInfo->duration_minute;
                    $endTime = date('H:i', strtotime("+$duration_minute minutes", strtotime($slotInfo->start_time)));

                    $start = date('H:i', strtotime($slotInfo->start_time));
                    $end = $slotInfo->end_time < '24:00' ?
                        date('H:i', strtotime($slotInfo->end_time)) :
                        $slotInfo->end_time;
                    $slotInfo->status = ShopCalendarSlot::STATUS_ACTIVE;
                    $slotInfo->duration_minute = $duration_minute;
                    $slotInfo->end_time = $endTime;
                    if (!$slotInfo->save()) {
                        $this->addErrors($slotInfo->getErrors());
                    }
                    $condition = [
                        'date' => $slotInfo->date,
                        'worker_id' => $slotInfo->worker_id,
                        'shop_id' => $slotInfo->shop_id,
                        'status' => ShopCalendarSlot::STATUS_DELETE
                    ];
                    $slots = ShopCalendarSlot::find()
                        ->where($condition)
                        ->all();
                    $flag = true;
                    foreach ($slots as $slot) {
                        $startTime = date('H:i', strtotime($slot->start_time));
                        if (
                            $slotInfo->slot_id != $slot->slot_id &&
                            $startTime > $start &&
                            $startTime <= $end
                        ) {
                            $ids[] = $slot->slot_id;
                            $update = ShopCalendarSlot::findOne($slot->slot_id);
                            $update->status = ShopCalendarSlot::STATUS_ACTIVE;
                            if (!$update->save(false)) {
                                $flag = false;
                                break;
                            }
                        }
                    }
                    if (!$flag) {
                        $this->addErrors($slotInfo->getErrors());
                    }
                }
            }
            if (!$this->hasErrors()) {
                $trans->commit();
                //todo send SMS to customer
                $phoneNumber = '84' . $this->memberInfo->phone_number;
                $smsParams = [
                    "shop_name" => $this->slotInfo->shopInfo->shop_name,
                    "phone_number" => $this->slotInfo->shopInfo->phone_number,
                    "worker_name" => $this->slotInfo->workerInfo->worker_name,
                    "booking_date" => \App::$app->formatter->asDate($this->slotInfo->date),
                    "booking_time" => \App::$app->formatter->asTime($this->slotInfo->start_time),
                    "course_id" => ArrayHelper::getValue(self::getListCourseType(), $this->course_id),
                    "cost" => \App::$app->formatter->asCurrency($this->cost),
                ];
                SendSMSForm::toSend($phoneNumber, $smsContent, $smsParams, [
                    SendSMSForm::TYPE_BOOKING_ONLINE_REJECT,
                ]);
                return true;
            }
        } else {
            $this->addError("booking_id", \App::t("backend.booking.message", "Không cho phép từ chối"));
        }
        return false;
    }

    protected function addUserLog($action, $type)
    {
        $times = explode(':', $this->slotInfo->start_time);
        if ((int)$times[1] < 10) {
            $times[1] = "0".$times[1];
        }
        $time = implode(":", $times);
    }

    public function getBookingHistories()
    {
        return self::find()
            ->innerJoin(ShopCalendarSlot::tableName(), ShopCalendarSlot::tableName() . ".slot_id = " . BookingInfo::tableName() . ".slot_id")
            ->where([
                "member_id" => $this->member_id,
                self::tableName() . ".status" => BookingInfo::STATUS_ACCEPTED,
            ])
            ->orderBy(self::tableName() . ".created_at DESC")
            ->all();
    }

    public function getListMembers()
    {
        return ArrayHelper::map(UserInfo::findAll([
            "role" => UserInfo::ROLE_USER
        ]), "user_id", "full_name");
    }

    public function getListCustomers()
    {
        return ArrayHelper::map(CustomerInfo::findAll([
            "status" => CustomerInfo::STATUS_ACTIVE
        ]), "customer_id", "customer_name");
    }

    public function getListShops()
    {
        return ArrayHelper::map(ShopInfo::findAll([
            "status" => ShopInfo::STATUS_ACTIVE,
        ]), "shop_id", "shop_name");
    }

    public function getListWorkers()
    {
        return ArrayHelper::map(WorkerInfo::findAll([
            "status" => WorkerInfo::STATUS_ACTIVE,
        ]), "worker_id", "worker_name");
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        $data = parent::toArray($fields, [
            "slotInfo",
        ], $recursive);
        if ($this->slotInfo && $this->slotInfo->workerInfo) {
            $course = CourseInfo::findOne([
                'course_id' => $this->course_id,
                'status' => CourseInfo::STATUS_ACTIVE,
            ]);
            if (!$course) {
                $data["course_cost"] = $course->price;
            }
        }
        return $data;
    }
}
