<?php
namespace backend\modules\calendar\forms\worker;

use backend\modules\calendar\forms\sms\SmsTemplateForm;
use common\entities\calendar\BookingData;
use common\entities\calendar\BookingInfo;
use common\entities\calendar\CouponInfo;
use common\entities\calendar\CouponLog;
use common\entities\calendar\TotalBooking;
use common\entities\calendar\TotalCouponUsed;
use common\entities\service\TemplateMail;
use common\entities\service\TemplateSms;
use common\entities\shop\ShopCalendar;
use common\entities\shop\ShopCalendarSlot;
use common\entities\user\UserData;
use common\entities\worker\WorkerConfig;
use common\entities\worker\WorkerInfo;
use common\entities\calendar\BookingCoupon;
use common\entities\calendar\CouponMember;
use common\forms\service\SendSMSForm;
use common\helper\ArrayHelper;
use common\mail\forms\Mail;

/**
 * Class WorkerConfigForm
 * @package backend\modules\calendar\forms\worker
 */
class WorkerConfigForm extends WorkerInfo
{

    public function toWorkBreak($date, $smsContent, $mailTitle = null)
    {
        $trans = \App::$app->db->beginTransaction();
        $smsSendList = [];
        $mailSendList = [];
        //1. todo delete all booking after now
        $bookings = BookingInfo::find()->with('slotInfo', 'memberInfo')
            ->innerJoin(ShopCalendarSlot::tableName(), ShopCalendarSlot::tableName() . ".slot_id = " . BookingInfo::tableName() . ".slot_id")
            ->where([
                ShopCalendarSlot::tableName() . ".worker_id" => $this->worker_id,
                ShopCalendarSlot::tableName() . ".date" => $date,
            ])
            ->andWhere(['LIKE', BookingInfo::tableName().'.created_at', $date])
            ->andWhere(['NOT IN', BookingInfo::tableName() . ".status", [BookingInfo::STATUS_DELETED, BookingInfo::STATUS_REJECTED]]) // task - 674
            ->all();
        $totalCost = 0;
        foreach ($bookings as $booking) {
            /**
             * @var $booking BookingInfo
             */
            if ($booking->slotInfo) {
                if (strtotime($booking->slotInfo->date . " " . $booking->slotInfo->end_time) >= time()) {
                    if ($booking->memberInfo) {
                        $phoneNumber = '84' . $booking->memberInfo->phone_number;
                        $smsSendList[] = [
                            "params" => [
                                "shop_name" => $booking->slotInfo->shopInfo->shop_name,
                                "worker_name" => $booking->slotInfo->workerInfo->worker_name,
                                "booking_date" => \App::$app->formatter->asDate($booking->slotInfo->date),
                                "booking_time" => \App::$app->formatter->asTime($booking->slotInfo->start_time),
                                "course_id" => ArrayHelper::getValue($booking::getListCourseType(), $booking->course_id),
                                "cost" => \App::$app->formatter->asCurrency($booking->cost),
                                "phone_number" => $booking->slotInfo->shopInfo->phone_number
                            ],
                            "to" => $phoneNumber,
                        ];
                    }
                    if (!$booking->slotInfo->delete()) {//1.1. todo delete booking slot
                        $this->addErrors($booking->slotInfo->getErrors());
                        break;
                    }
                    if (!$booking->delete()) {//1.2. todo delete booking
                        $this->addErrors($booking->getErrors());
                        break;
                    }
                }
            }
        }

        //2. todo remove calendar slot
        if (!$this->hasErrors()) {
            $slots = ShopCalendarSlot::find()
                ->where([
                    ShopCalendarSlot::tableName() . ".worker_id" => $this->worker_id,
                    ShopCalendarSlot::tableName() . ".date" => $date,
                ])
                ->all();
            foreach ($slots as $slot) {
                /**
                 * @var $slot ShopCalendarSlot
                 */
                if (strtotime($slot->date . " " . $slot->end_time) >= time()) {
                    if (!$slot->delete()) {
                        $this->addErrors($slot->getErrors());
                        break;
                    }
                }
            }
        }
        //3. todo update calendar config
        if (!$this->hasErrors()) {
            $calendars = ShopCalendar::find()
                ->where([
                    ShopCalendar::tableName() . ".worker_id" => $this->worker_id,
                    ShopCalendar::tableName() . ".date" => $date,
                ])
                ->all();
            foreach ($calendars as $calendar) {
                /**
                 * @var $calendar ShopCalendar
                 */
                if (strtotime($calendar->date . " " . $calendar->work_start_time) >= time()) {//2.1. todo delete calendar
                    if (!$calendar->delete()) {
                        $this->addErrors($calendar->getErrors());
                        break;
                    }
                } else if (strtotime($calendar->date . " " . $calendar->work_end_time) >= time()) {//2.2. todo update shop calendar
                    $calendar->work_end_time = date("H:i");
                    if (!$calendar->save()) {
                        $this->addErrors($calendar->getErrors());
                        break;
                    }
                }
            }
        }
        if (!$this->hasErrors()) {
            $trans->commit();
            if (count($smsSendList) > 0) {//4. todo send message to MEMBER have been booked
                foreach ($smsSendList as $sms) {
                    SendSMSForm::toSend($sms["to"], $smsContent, $sms["params"], [
                        SmsTemplateForm::TYPE_WORKER_WORK_BREAK,
                    ]);
                }
            }
            return true;
        }
        return false;
    }


}
