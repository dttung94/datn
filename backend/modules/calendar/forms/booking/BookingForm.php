<?php
namespace backend\modules\calendar\forms\booking;


use common\entities\calendar\BookingCoupon;
use common\entities\calendar\BookingData;
use common\entities\calendar\BookingInfo;
use common\entities\calendar\CouponInfo;
use common\entities\calendar\CouponLog;
use common\entities\calendar\CouponMember;
use common\entities\calendar\TotalBooking;
use common\entities\calendar\TotalCouponUsed;
use common\entities\customer\CustomerData;
use common\entities\service\TemplateMail;
use common\entities\shop\ShopCalendarSlot;
use common\entities\worker\WorkerInfo;
use common\forms\service\SendSMSForm;
use common\helper\ArrayHelper;
use common\helper\DatetimeHelper;
use common\mail\forms\Mail;

/**
 * Class BookingForm
 * @package backend\modules\calendar\forms\booking
 */
class BookingForm extends BookingInfo
{
    public function toDelete($callback = null)
    {
        $trans = \App::$app->db->beginTransaction();
        //todo set status REJECT to this booking
        $this->status = self::STATUS_DELETED;
        if ($this->save()) {
            if ($this->slotInfo) {
                $slotInfo = $this->slotInfo;
                    //todo set status ACTIVE to this slot
                    $slotInfo->status = ShopCalendarSlot::STATUS_ACTIVE;
                    if (!$slotInfo->save()) {
                        $this->addErrors($slotInfo->getErrors());
                    }
                // check booking is free and countdown reject booking
            }
        }
        if (!$this->hasErrors()) {
            $trans->commit();
            if ($callback) {
                call_user_func($callback);
            }
            return true;
        } else {
            $trans->rollBack();
        }
        return false;
    }

    public function toCancel($smsContent)
    {
        return $this->toDelete(function () use ($smsContent) {

            if ($smsContent != "BOOKING_FREE") {
                //todo send SMS to customer
                $phoneNumber = '84' . $this->memberInfo->phone_number;
                if ($smsContent === "") {
                    $smsTemplate = SendSMSForm::TYPE_BOOKING_REMOVE_SMS;
                    $mailTemplate = TemplateMail::TYPE_BOOKING_REMOVE_MAIL;
                } else {
                    $smsTemplate = SendSMSForm::TYPE_BOOKING_ONLINE_AUTO_REJECT;
                    $mailTemplate = TemplateMail::TYPE_BOOKING_ONLINE_AUTO_REJECT;
                }
                $smsParams = [
                    "shop_name" => $this->slotInfo->shopInfo->shop_name,
                    "phone_number" => $this->slotInfo->shopInfo->phone_number,
                    "worker_name" => $this->slotInfo->workerInfo->worker_name,
                    "booking_date" => \App::$app->formatter->asDate($this->slotInfo->date),
                    "booking_time" => \App::$app->formatter->asTime($this->slotInfo->start_time),
                    "course_id" => ArrayHelper::getValue(self::getListCourseType(), $this->course_id),
                    "cost" => \App::$app->formatter->asCurrency($this->cost),
                    "course_time" => $this->slotInfo->duration_minute,
                ];
                SendSMSForm::toSendViaTemplateId($phoneNumber, $smsTemplate, $smsParams);
            }
        });
    }

    public function toUpdateNote($note)
    {
        $this->note = $note;
        return $this->save();
    }

    protected function updateTotalBooking($type)
    {
        $totalBooking = TotalBooking::findOne([
            'date' => $this->slotInfo->date,
            'type' => $type,
            'shop_id' => $this->slotInfo->shop_id
        ]);
        $totalBooking->count -= 1;
        if (!$totalBooking->save()) {
            $this->addErrors($totalBooking->getErrors());
        }
    }

    protected function updateCouponUsed()
    {
        $coupon = 0;
        if ($this->coupons) {
            foreach ($this->coupons as $couponUsed) {
                $coupon += $couponUsed->yield;
            }
        }
        $totalCoupons = TotalCouponUsed::find()
            ->where([
                'date' => $this->slotInfo->date
            ])->one();
        if (!empty($totalCoupons)) {
            $totalCoupons->value -= $coupon;
            if (!$totalCoupons->save()) {
                $this->addErrors($totalCoupons->getErrors());
            }
        }
    }
}
