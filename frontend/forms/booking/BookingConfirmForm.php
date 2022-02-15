<?php
namespace frontend\forms\booking;


use backend\modules\calendar\forms\booking\WorkerCalendarSlotForm;
use common\entities\calendar\BookingData;
use common\entities\calendar\BookingInfo;
use common\entities\calendar\CouponInfo;
use common\entities\calendar\CouponLog;
use common\entities\calendar\CouponMember;
use common\entities\calendar\TotalBooking;
use common\entities\calendar\TotalCouponUsed;
use common\entities\shop\ShopCalendarSlot;
use common\entities\user\UserLog;
use common\entities\user\UserToken;
use common\entities\worker\WorkerInfo;
use common\helper\DatetimeHelper;
use common\models\UserIdentity;

/**
 * Class BookingConfirmForm
 * @package frontend\forms\booking
 */
class BookingConfirmForm extends BookingInfo
{
    public static function toAccept($id, $token)
    {
        $tokenInfo = UserToken::findOne([
            "token" => $token,
            "status" => UserToken::STATUS_ACTIVE
        ]);
        if ($tokenInfo && $tokenInfo->isValid()) {
            //todo login
            $userIdentity = UserIdentity::findIdentity($tokenInfo->user_id);
            if ($userIdentity) {
                \App::$app->user->login($userIdentity, 3600 * 24 * 30);
            }
            //todo find booking model
            $model = self::findOne([
                "booking_id" => $id,
                "status" => self::STATUS_CONFIRMING,
                "member_id" => $tokenInfo->user_id,
            ]);
            if ($model) {
                /**
                 * @var $model self
                 */
                $trans = \App::$app->db->beginTransaction();
                $hasError = false;
                //1. todo set project status to ACCEPTED
                $model->status = self::STATUS_ACCEPTED;
                if ($model->save()) {
                    //2. todo set token to USED
                    $tokenInfo->status = UserToken::STATUS_USED;
                    if ($tokenInfo->save()) {
                    } else {
                        $hasError = true;
                        \App::error($tokenInfo->getErrors(), "AcceptFreeBooking");
                    }
                } else {
                    $hasError = true;
                    \App::error($model->getErrors(), "AcceptFreeBooking");
                }
                if (!$hasError) {
                    $trans->commit();
                    //todo add log
                    return $model;
                }
                $trans->rollBack();
            } else {
                \App::error("Booking [$id] is not found.", "AcceptFreeBooking");
            }
        } else {
            \App::error("Token [$token] is not found.", "AcceptFreeBooking");
        }
        return false;
    }

    public static function toReject($id, $token)
    {
        $tokenInfo = UserToken::findOne([
            "token" => $token,
            "status" => UserToken::STATUS_ACTIVE
        ]);
        if ($tokenInfo && $tokenInfo->isValid()) {
            //todo login
            $userIdentity = UserIdentity::findIdentity($tokenInfo->user_id);
            if ($userIdentity) {
                \App::$app->user->login($userIdentity, 3600 * 24 * 30);
            }
            //todo find booking model
            $model = self::findOne([
                "booking_id" => $id,
                "status" => self::STATUS_CONFIRMING,
                "member_id" => $tokenInfo->user_id,
            ]);
            if ($model) {
                /**
                 * @var $model self
                 */
                $trans = \App::$app->db->beginTransaction();
                $hasError = false;
                //1. todo set token to USED
                $tokenInfo->status = UserToken::STATUS_USED;
                if ($tokenInfo->save()) {

                } else {
                    \App::error($model->getErrors(), "AcceptFreeBooking");
                    $hasError = true;
                }
                if (!$hasError) {
                    $trans->commit();
                    //todo add log
                    return $model;
                }
                $trans->rollBack();
            } else {
                \App::error("Booking [$id] is not found.", "AcceptFreeBooking");
            }
        } else {
            \App::error("Token [$token] is not found.", "AcceptFreeBooking");
        }
        return false;
    }

}