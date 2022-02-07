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
                "booking_type" => self::BOOKING_TYPE_FREE,
                "member_id" => $tokenInfo->user_id,
            ]);
            if ($model) {
                /**
                 * @var $model self
                 */
                $trans = \App::$app->db->beginTransaction();
                $hasError = false;
                if (!$hasError) {
                    $hasError = self::updateTotalBooking($model);
                }
                if (!$hasError) {
                    $hasError = self::updateCouponUsed($model);
                }
                //1. todo set project status to ACCEPTED
                $model->status = self::STATUS_ACCEPTED;
                if ($model->save()) {
                    //2. todo set token to USED
                    $tokenInfo->status = UserToken::STATUS_USED;
                    if ($tokenInfo->save()) {
                        foreach ($model->coupons as $couponInfo) { //3. todo set status USED to this coupon
                            $couponMember = CouponMember::findOne([
                                "coupon_code" => $couponInfo->coupon_code,
                                "member_id" => $model->member_id,
                            ]);
                            if ($couponMember) {
                                /**
                                 * @var $couponMember CouponMember
                                 */
                                $couponMember->status = $couponInfo->coupon_type == CouponInfo::COUPON_TYPE_CAMPAIGN ? CouponInfo::STATUS_ACTIVE : CouponMember::STATUS_USED;
                                if (!$couponMember->save(false)) {
                                    \App::error($couponMember->getErrors(), "AcceptFreeBooking");
                                    $hasError = true;
                                    break;
                                }
                            }
                        }
                        $lastCoupons = [];
                        $coupons = $model->coupons;
                        foreach ($coupons as $value) {
                            $lastCoupons[] = $value->coupon_code;
                        }
                        BookingData::setValue($model->booking_id, BookingData::KEY_LATEST_COUPONS, json_encode($lastCoupons));
                        BookingData::setValue($model->booking_id, BookingData::KEY_LATEST_COMMENT, $model->comment);
                        BookingData::setValue($model->booking_id, BookingData::KEY_LATEST_COST, "$model->cost");
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
                    UserLog::addLog(
                        UserLog::ACTION_FREE_BOOKING_ACCEPT,
                        \App::t("common.user_log.message", "Accept free booking"),
                        [
                            "booking_id" => $model->booking_id,
                        ],
                        $tokenInfo->user_id
                    );
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
                "booking_type" => self::BOOKING_TYPE_FREE,
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
                    //2. todo delete slot
                    $slot = $model->slotInfo;
                    if ($slot->delete()) {
                        //3. todo set status ACTIVE to this coupon
                        foreach ($model->coupons as $couponInfo) {
                            $couponMember = CouponMember::findOne([
                                "coupon_code" => $couponInfo->coupon_code,
                                "member_id" => $model->member_id,
                            ]);
                            /**
                             * @var $couponMember CouponMember
                             */
                            $couponMember->status = CouponMember::STATUS_ACTIVE;

                            $couponInfo = CouponInfo::findOne(['coupon_code' => $couponInfo->coupon_code]);
                            $couponInfo->status = CouponInfo::STATUS_REGISTERED;
                            $couponInfo->date_used = null;
                            if (!$couponMember->save() || !$couponInfo->save(false)) {
                                \App::error($couponMember->getErrors(), "AcceptFreeBooking");
                                $hasError = true;
                            }
                            if (!$hasError) {
                                CouponLog::createRecord(CouponLog::UPDATE_COUPON, $couponInfo,'User reject booking');
                            }
                        }
                        //4. todo delete booking
                        $sqlBookingCoupon = "DELETE FROM booking_coupon WHERE booking_id = $id";
                        \Yii::$app->db->createCommand($sqlBookingCoupon)->execute();
                        $model->delete();
                        // task-334 create slot online when user reject booking free: only one router used
                        if ($slot->type == ShopCalendarSlot::TYPE_BOOKING_FREE) {
                            $shopCalendarSlot = WorkerCalendarSlotForm::getInstance($slot->worker_id, $slot->shop_id, $slot->date, $slot->start_time);
                            $workerInfo = WorkerInfo::getWorkerById($slot->worker_id);
                            if (!$workerInfo->worker_id) {
                                return false;
                            }
                            $_GET['worker_rank'] = $workerInfo->worker_rank;
                            if (!$shopCalendarSlot->toSave(DatetimeHelper::getHourFromTimeFormat($slot->start_time), DatetimeHelper::getMinuteFromTimeFormat($slot->start_time), $slot->duration_minute)) {
                                \App::error($slot->getErrors(), "AcceptFreeBooking");
                            }
                        }
                    } else {
                        \App::error($slot->getErrors(), "AcceptFreeBooking");
                    }
                } else {
                    \App::error($model->getErrors(), "AcceptFreeBooking");
                    $hasError = true;
                }
                if (!$hasError) {
                    $trans->commit();
                    //todo add log
                    UserLog::addLog(
                        UserLog::ACTION_FREE_BOOKING_REJECT,
                        \App::t("common.user_log.message", "Reject free booking"),
                        [
                            "booking_id" => $model->booking_id,
                        ],
                        $tokenInfo->user_id
                    );
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
    protected static function updateTotalBooking($datas)
    {
        $totalBooking = TotalBooking::find()
            ->where([
                'date' => $datas->slotInfo->date,
                'type' => BookingInfo::FREQUENCY_TOTAL,
                'shop_id' => $datas->slotInfo->shop_id
            ])->one();
        if (!$totalBooking) {
            $totalBooking = new TotalBooking();
            $totalBooking->type = BookingInfo::FREQUENCY_TOTAL;
            $totalBooking->date = $datas->slotInfo->date;
            $totalBooking->shop_id = $datas->slotInfo->shop_id;
            $totalBooking->created_at = date('Y-m-d H:i:s');
        }
        $totalBooking->count = !$totalBooking ? 1 : $totalBooking->count + 1;
        if (!$totalBooking->save()) {
            return true;
        }
        return false;
    }

    protected static function updateCouponUsed($datas)
    {
        $coupon = 0;
        if ($datas->coupons) {
            foreach ($datas->coupons as $couponUsed) {
                $coupon += $couponUsed->yield;
            }
        }
        $totalCoupons = TotalCouponUsed::find()
            ->where([
                'date' => $datas->slotInfo->date
            ])->one();
        if (!$totalCoupons) {
            $totalCoupons = new TotalCouponUsed();
            $totalCoupons->created_at = date('Y-m-d H:i:s');
            $totalCoupons->date = $datas->slotInfo->date;
        }
        $totalCoupons->value = !$totalCoupons ? $coupon : $totalCoupons->value + $coupon;
        if (!$totalCoupons->save()) {
            return true;
        }
        return false;
    }
}