<?php
namespace frontend\forms\profile;

use common\entities\calendar\BookingCoupon;
use common\entities\calendar\BookingInfo;
use common\entities\service\TemplateMail;
use common\entities\shop\ShopCalendarSlot;
use common\entities\shop\ShopInfo;
use common\entities\user\UserInfo;
use common\entities\customer\CustomerInfo;
use common\entities\worker\WorkerInfo;
use common\entities\worker\WorkerMappingShop;
use common\forms\service\SendSMSForm;
use common\helper\ArrayHelper;
use common\helper\DatetimeHelper;
use common\mail\forms\Mail;
use yii\base\Model;
use yii\db\Expression;

/**
 * Class MemberBookingHistory
 * @package frontend\forms\profile
 */
class MemberBookingHistory extends Model
{
    const
        BOOKING_TYPE_FREE_REQUEST = "FREE_REQUEST";

    public static function getBookingHistory($user)
    {
        $member_id = $user->user_id;
        $phone_number = $user->phone_number;
        $data = [];
        //todo load member booking
        $bookings = BookingInfo::find()
            ->innerJoin(
                ShopCalendarSlot::tableName(),
                ShopCalendarSlot::tableName() . ".slot_id = " . BookingInfo::tableName() . ".slot_id")
            ->innerJoin(
                UserInfo::tableName(),
                UserInfo::tableName() . ".user_id = " . BookingInfo::tableName() . ".member_id")
            ->where(["IN", BookingInfo::tableName() . ".status", [
                BookingInfo::STATUS_PENDING,
                BookingInfo::STATUS_CONFIRMING,
                BookingInfo::STATUS_ACCEPTED,
                BookingInfo::STATUS_UPDATING,
            ]])
            ->andWhere(['or',
                [BookingInfo::tableName() . ".member_id" => $member_id],
                [UserInfo::tableName() . ".phone_number" => $phone_number]
            ])
            ->andWhere([
                '>', 'STR_TO_DATE(CONCAT(date,\' \',end_time,":00"), \'%Y-%m-%d %H:%i:%s\')', date('Y-m-d H:i:s')
            ])
            ->orderBy(BookingInfo::tableName() . ".created_at DESC")
            ->all();
        foreach ($bookings as $booking) {
            /**
             * @var $booking BookingInfo
             */
            $bookingData = $booking->toArray();
            $bookingData['worker_id'] = $booking->slotInfo->workerInfo->worker_id;
            $bookingData['worker_avatar'] = $booking->slotInfo->workerInfo->avatar_url;
//            $bookingData['avatar_url'] = ShopInfo::getShopUrl($booking->slotInfo->shop_id).
//                WorkerMappingShop::findOne(['worker_id' => $booking->slotInfo->worker_id, 'shop_id' => $booking->slotInfo->shop_id])->ref_id;
//            $bookingData['worker_url'] = $booking->slotInfo->workerInfo->status == WorkerInfo::STATUS_ACTIVE ?
//                ShopInfo::getShopUrl($booking->slotInfo->shop_id).
//                WorkerMappingShop::findOne(['worker_id' => $booking->slotInfo->worker_id, 'shop_id' => $booking->slotInfo->shop_id])->ref_id : '#';
            $bookingData['worker_name'] = $booking->slotInfo->workerInfo->worker_name;
            $bookingData['shop_name'] = $booking->slotInfo->shopInfo->shop_name;
            $bookingData['course_name'] = $booking->courseInfo->course_name;
            $startTime = $booking->slotInfo->start_time;
            $bookingData['start_time'] = \App::$app->formatter->asTime($startTime, 'short');
            $bookingData["id"] = $booking->booking_id;
            $bookingData["course_id_text"] = ArrayHelper::getValue(BookingInfo::getListCourseType(), $booking->course_id);
            $bookingData["isCancelable"] =
                (
                    $booking->status != BookingInfo::STATUS_EXPIRED &&
                    $booking->status != BookingInfo::STATUS_CANCELED &&
                    $booking->status != BookingInfo::STATUS_REJECTED
                ) && strtotime($booking->slotInfo->date . " " . $booking->slotInfo->start_time) > time();
            $bookingData["time"] = $booking->slotInfo->date . " " . $booking->slotInfo->start_time;
            $data[] = $bookingData;
        }
        //todo sort data
        usort($data, function ($item1, $item2) {
            $time1 = strtotime(ArrayHelper::getValue($item1, "time"));
            $time2 = strtotime(ArrayHelper::getValue($item2, "time"));
            if ($time1 <= $time2) {
                return 1;
            }
            return -1;
        });
        return $data;
    }

    public static function toCancelBooking($member_id, $id)
    {
        $booking = BookingInfo::find()
            ->where([
                "member_id" => $member_id,
                "booking_id" => $id,
            ])
            ->andWhere(["IN", BookingInfo::tableName() . ".status", [
                BookingInfo::STATUS_PENDING,
                BookingInfo::STATUS_CONFIRMING,
                BookingInfo::STATUS_ACCEPTED,
                BookingInfo::STATUS_UPDATING,
            ]])
            ->one();
        /**
         * @var $booking BookingInfo
         */
        if ($booking != null && $booking->slotInfo) {
            $trans = \App::$app->db->beginTransaction();
            //todo set booking status to CANCELED
            $booking->status = BookingInfo::STATUS_CANCELED;
            if ($booking->save()) {
                $modelShopInfo = new ShopInfo();
                $listShops = $modelShopInfo->getListShop();
                $trans->commit();
                return $booking;
            } else {
                $trans->rollBack();
            }
        }
        return false;
    }

    public static function getExpectTime($request) {
        $afterTimeHour = DatetimeHelper::getHourFromTimeFormat(date("H:i", strtotime($request->created_at)));
        $afterTimeMinute = DatetimeHelper::getMinuteFromTimeFormat(date("H:i", strtotime($request->created_at)));
        $afterTimeMinute = ($afterTimeMinute % 5) > 0 ? (intval($afterTimeMinute / 5) + 1) * 5 : $afterTimeMinute;

        return date("H:i", strtotime("$afterTimeHour:$afterTimeMinute") + $request->after_minute * 60);
    }

}
