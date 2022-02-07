<?php
namespace backend\modules\member\forms;

use common\entities\calendar\BookingCoupon;
use common\entities\calendar\BookingData;
use common\entities\calendar\BookingInfo;
use common\entities\calendar\CouponInfo;
use common\entities\calendar\CouponMember;
use common\entities\calendar\CouponMemberConfig;
use common\entities\calendar\CouponShop;
use common\entities\calendar\FreeBookingRequest;
use common\entities\calendar\FreeBookingRequestCoupon;
use common\entities\calendar\Rating;
use common\entities\customer\CustomerData;
use common\entities\customer\CustomerInfo;
use common\entities\forum\Comment;
use common\entities\forum\Post;
use common\entities\forum\Reply;
use common\entities\referrer\ReferInfo;
use common\entities\service\TemplateMail;
use common\entities\service\TemplateSms;
use common\entities\shop\ShopCalendarSlot;
use common\entities\shop\ShopInfo;
use common\entities\user\UserConfig;
use common\entities\user\UserData;
use common\entities\user\UserInfo;
use common\entities\user\UserLog;
use common\entities\user\UserPermission;
use common\entities\user\UserToken;
use common\entities\worker\WorkerInfo;
use common\forms\service\SendSMSForm;
use common\forms\service\URLShortener;
use common\helper\DatetimeHelper;
use common\mail\forms\Mail;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
use yii\db\Query;
use yii\web\User;

/**
 * @property string $note
 * @property string $keyword
 * @property string $filter_latest_booking_from
 * @property string $last_booking
 * @property integer $total_booking
 * @property integer $total_time
 * @property integer $total_money
 * @property integer $total_coupon
 *
 * @property CustomerInfo $customerInfo
 * @property CouponMember[] $memberCoupons
 * @property integer $totalBooking
 * @property float $bookingFrequency
 * @property float $totalMoney
 * @property BookingInfo $latestBooking
 * @property string $latestBookingTime
 */
class MemberForm extends UserInfo
{
    public $note;
    public $keyword, $filter_latest_booking_from;
    public $last_booking, $total_booking, $total_time, $total_money, $total_coupon, $last_coupon_released, $total_coupon_yield;

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['note'], 'safe'],
            [['keyword', 'filter_latest_booking_from'], 'safe'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'user_id' => \Yii::t('app.attribute.member_info.label', 'ID'),

            'provider' => \Yii::t('app.attribute.member_info.label', 'Provider'),
            'provider_id' => \Yii::t('app.attribute.member_info.label', 'Provider ID'),

            'full_name' => \Yii::t('app.attribute.member_info.label', '名前'),
            'username' => \Yii::t('app.attribute.member_info.label', 'Username'),
            'password' => \Yii::t('app.attribute.member_info.label', 'Password'),
            'email' => \Yii::t('app.attribute.member_info.label', 'Email'),
            'phone_number' => \Yii::t('app.attribute.member_info.label', 'Số điện thoại'),
            'note' => \Yii::t('app.attribute.member_info.label', 'ノート'),

            'role' => \Yii::t('app.attribute.member_info.label', 'Role'),

            'status' => \Yii::t('app.attribute.member_info.label', 'ステータス'),
            'source_type' => \Yii::t('app.attribute.member_info.label', 'Source type'),

            'created_at' => \Yii::t('app.attribute.member_info.label', '登録日'),
            'modified_at' => \Yii::t('app.attribute.member_info.label', 'Modified At'),

            'favorite' => \Yii::t('app.attribute.member_info.label', '好み'),
            'totalBooking' => \Yii::t('app.attribute.member_info.label', '総予約数'),
            'bookingFrequency' => \Yii::t('app.attribute.member_info.label', 'Frequency'),
            'totalMoney' => \Yii::t('app.attribute.member_info.label', 'Money'),
            "latestBookingTime" => \Yii::t('app.attribute.member_info.label', 'Latest booking'),

            'keyword' => \Yii::t('common.label', 'Từ khóa'),
            'filter_latest_booking_from' => \Yii::t('app.attribute.member_info.label', 'Latest booking'),
        ]);
    }

    public function toToggleStatus()
    {
        if ($this->status == self::STATUS_ACTIVE) {
            $this->status = self::STATUS_INACTIVE;
        } else {
            $this->status = self::STATUS_ACTIVE;
        }
        return $this->save();
    }

    /**
     * Todo search user
     * @return ActiveDataProvider
     */
    public function search($flag = true)
    {
        $query = parent::find();
        $query->andWhere("status != :status_deleted", [
            ':status_deleted' => self::STATUS_DELETED
        ]);
        $query->andWhere(["in", "status", [
            self::STATUS_ACTIVE,
            self::STATUS_CONFIRMING,
            self::STATUS_VERIFYING,
            self::STATUS_SHOP_BLACK_LIST,
            self::STATUS_WORKER_BLACK_LIST,
        ]]);
        $query->andWhere("role = :member_role", [
            ':member_role' => self::ROLE_USER
        ]);
        if ($flag == false) {
            return $query->all();
        }
        if ($this->status) {
            $query->andWhere(self::tableName() . ".status = :status", [
                ":status" => $this->status
            ]);
        }
        if ($this->keyword != null) {
            $query->andFilterWhere([
                'or',
                ['LIKE', static::tableName() . '.full_name', $this->keyword],
                ['LIKE', static::tableName() . '.phone_number', $this->keyword],
                ['LIKE', static::tableName() . '.tag', $this->keyword],
            ]);
        }

        if (!empty($this->filter_latest_booking_from)) {
            $queryBooking = BookingInfo::find()
                ->select('member_id')
                ->where(['status' => BookingInfo::STATUS_ACCEPTED])
                ->andWhere(['>=', 'created_at', $this->filter_latest_booking_from])
                ->distinct('member_id')
                ->asArray()
                ->all();
            $userIds = ArrayHelper::getColumn($queryBooking, 'member_id');
            $query->andWhere([
                'IN', 'user_id', $userIds
            ]);
//            $filter_latest_booking_from = $this->filter_latest_booking_from;
//            $allModels = array_filter($allModels, function (MemberForm $model) use ($filter_latest_booking_from) {
//                return $model->latestBookingTime != null &&
//                    strtotime($model->latestBookingTime) >= strtotime($filter_latest_booking_from);
//            });
        }

        $allModels = $query->all();

        $provider = new ArrayDataProvider([
            'allModels' => $allModels,
            'sort' => [
                'attributes' => ['latestBookingTime', 'bookingFrequency', 'totalMoney', 'totalBooking', 'username', 'status', 'created_at'],
                'defaultOrder' => [
//                    'latestBookingTime' => SORT_DESC,
                    'status' => SORT_ASC,
                    'created_at' => SORT_DESC,
                ],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
            'totalCount' => $query->count()
        ]);
        return $provider;
    }

    /**
     * Todo search user
     * @return ActiveDataProvider
     */
    public function searchExport(int $page)
    {
        $perPage = 2000;
        $page = $page < 1 ? 1 : $page;
        $query = $this->getMemberDetailQuery();
        $query->orderBy([
            'ui.status' => SORT_ASC,
            'ui.created_at' => SORT_DESC,
        ]);
        $query->offset(($page - 1) * $perPage)->limit($perPage);

        return $query->all();
    }

    public function toDelete()
    {
        $trans = \App::$app->db->beginTransaction();
        $hasError = false;

        //todo delete all free_booking_request of Member
        $freeBookingRequests = FreeBookingRequest::findAll([
            "member_id" => $this->user_id,
        ]);
        foreach ($freeBookingRequests as $freeBookingRequest) {
            //todo delete free_booking_request_coupon
            FreeBookingRequestCoupon::deleteAll([
                "request_id" => $freeBookingRequest->request_id,
            ]);
            //todo delete free_booking_request
            $freeBookingRequest->delete();
        }

        //todo delete all rating of Member
        $ratings = Rating::findAll([
            "user_id" => $this->user_id,
        ]);
        foreach ($ratings as $rating) {
            $rating->delete();
        }

        // todo delete info in table refer_info
        ReferInfo::deleteAll([
            "user_id" => $this->user_id,
        ]);

        // update status INACTIVE for all member parent in table refer
        ReferInfo::updateAll([
            "status" => ReferInfo::STATUS_INACTIVE,
        ], [
            "refer_id" => $this->user_id,
        ]);

        //todo delete all reply of Member
        Reply::deleteAll([
            "user_id" => $this->user_id,
        ]);

        //todo delete all comment of Member
        Comment::deleteAll([
            "user_id" => $this->user_id,
        ]);
        //todo delete all post of Member
        Post::deleteAll([
            "user_id" => $this->user_id,
        ]);

        $customer_ids = CustomerInfo::find()->select('customer_id')->where(['phone_number' => $this->phone_number])->all();
        //todo delete all booking_info of Member
        $bookings = BookingInfo::find()->where(["member_id" => $this->user_id,])->orWhere(['in', 'customer_id', $customer_ids])->all();
        foreach ($bookings as $booking) {
            //todo delete booking_data
            BookingData::deleteAll([
                "booking_id" => $booking->booking_id,
            ]);
            //todo delete booking_coupon
            BookingCoupon::deleteAll([
                "booking_id" => $booking->booking_id,
            ]);
            //todo delete shop_calendar_slot
            ShopCalendarSlot::deleteAll([
                "slot_id" => $booking->slot_id,
            ]);
            //todo delete booking_info
            $booking->delete();
        }

        //todo delete user_config
        UserConfig::deleteAll([
            "user_id" => $this->user_id,
        ]);
        //todo delete user_token
        UserToken::deleteAll([
            "user_id" => $this->user_id,
        ]);
        //todo delete user_info
        $this->delete();

        if (!$hasError) {
            $trans->commit();
            return true;
        } else {
            $trans->rollBack();
        }
        return false;
    }


    public function toApprove()
    {
        if ($this->status == self::STATUS_CONFIRMING) {
            $trans = \App::$app->db->beginTransaction();
            $this->status = self::STATUS_VERIFYING;
            //todo create verify phone number token
            if (($verifyPhoneNumberToken = UserToken::createToken(
                UserToken::TYPE_SIGN_UP_VERIFY_PHONE_NUMBER_TOKEN,
                $this->user_id,
                date(DatetimeHelper::FULL_DATETIME, time() + 60 * 60) //set expire after 1 hour
            )) && ($verifyEmailToken = UserToken::createToken(
                UserToken::TYPE_SIGN_UP_VERIFY_EMAIL_TOKEN,
                $this->user_id,
                date(DatetimeHelper::FULL_DATETIME, time() + 60 * 60 ) //set expire after 1 hour
                ))
            ) {
                //todo send SMS to verify phone number
                $phone_number = '84' . $this->phone_number;
                if (
                SendSMSForm::toSendViaTemplateId(
                    $phone_number,
                    SendSMSForm::TYPE_MEMBER_REGISTER_VERIFY_PHONE_NUMBER, [
                    "verify_url" => URLShortener::shortenLongUrl(\App::$app->urlManager->createAbsoluteUrl([
                        "site/sign-up-verify-phone-number",
                        "verifyToken" => $verifyPhoneNumberToken->token,
                    ])),
                ])
                ) {
                    $email = $this->email;
                    $userName = $this->username;
                    $userId = $this->user_id;
                    $text = URLShortener::shortenLongUrl(\App::$app->urlManager->createAbsoluteUrl(["site/sign-up-verify-email", "verifyToken" => $verifyEmailToken->token]));
                    $link = '<a href="'.$text.'">'.$text.'</a>';
                    $template = TemplateMail::getMailTemplate(TemplateMail::TYPE_VERIFY_MAIL);
                    $mailParams = [
                        'verify_url' => $link
                    ];
                    $data = [
                        'email' => $email,
                        'name' => $userName,
                        'subject' => $template->title,
                        'content' => $template->content,
                        'params' => $mailParams
                    ];
                    $mail = new Mail();
                    if ($mail->toSend($data, false)) {
                        UserInfo::updateAll([
                            'time_sent_mail' => date('Y-m-d H:i:s')
                        ], [
                            'user_id' => $userId
                        ]);
                    }
                    if ($this->save(false)) {
                        $trans->commit();
                        //todo add log
                        return true;
                    }
                } else {
                    $this->addError("user_id", \App::t("backend.member.message", "電話番号認証SMSの送信に失敗しました。", [
                        "phone-number" => $phone_number,
                    ]));
                }
            } else {
                $this->addError("user_id", \App::t("backend.member.message", "Have error when create user token."));
            }
            $trans->rollBack();
        } else {
            $this->addError("user_id", \App::t("backend.member.message", "This use is invalid for approve."));
        }
        return false;
    }


    public function getCustomerInfo()
    {
        return CustomerInfo::findOne([
            "member_id" => $this->user_id,
        ]);
    }

    public function getTotalBooking()
    {
        if ($this->customerInfo) {
            $query = BookingInfo::find();
            $query->innerJoin(CustomerInfo::tableName(), CustomerInfo::tableName() . ".customer_id = " . BookingInfo::tableName() . ".customer_id");
            $query->innerJoin(ShopCalendarSlot::tableName(), ShopCalendarSlot::tableName() . ".slot_id = " . BookingInfo::tableName() . ".slot_id");
            $query->innerJoin(ShopInfo::tableName(), ShopCalendarSlot::tableName() . ".shop_id = " . ShopInfo::tableName() . ".shop_id");
            $query->innerJoin(WorkerInfo::tableName(), ShopCalendarSlot::tableName() . ".worker_id = " . WorkerInfo::tableName() . ".worker_id");
            $query->where([
                BookingInfo::tableName() . ".status" => BookingInfo::STATUS_ACCEPTED,
                BookingInfo::tableName() . ".customer_id" => $this->customerInfo->customer_id,
            ]);
            return $query->count();
        }
        return null;
    }

    public function getMemberCoupons()
    {
        return CouponMember::find()
            ->innerJoin(CouponInfo::tableName(), CouponInfo::tableName() . ".coupon_code = " . CouponMember::tableName() . ".coupon_code")
            ->andWhere([
                CouponMember::tableName() . ".status" => CouponMember::STATUS_ACTIVE,
                CouponMember::tableName() . ".member_id" => $this->user_id,
            ])
            ->andWhere(["!=", CouponInfo::tableName() . ".status", CouponInfo::STATUS_EXPIRED])
            ->orderBy(CouponInfo::tableName() . ".expire_date ASC")
            ->all();
    }

    public function getBookingFrequency()
    {
        //todo get all online booking
        $bookings = BookingInfo::find()
            ->innerJoin(ShopCalendarSlot::tableName(), ShopCalendarSlot::tableName() . ".slot_id = " . BookingInfo::tableName() . ".slot_id")
            ->where([
                BookingInfo::tableName() . ".member_id" => $this->user_id
            ])
            ->andWhere([
                "IN",
                BookingInfo::tableName() . ".status", [
                    BookingInfo::STATUS_ACCEPTED,
                ]
            ])
            ->orderBy(
                ShopCalendarSlot::tableName() . ".date DESC, " . ShopCalendarSlot::tableName() . ".start_time" . " DESC"
            )
            ->all();
        $totalTime = 0;
        $totalBooking = 0;

        /*for ($i = 0; $i < count($bookings) - 1; $i++) {
            $booking = $bookings[$i];
            $nextBooking = $bookings[$i + 1];

            $totalTime = strtotime($booking->slotInfo->date . " " . $booking->slotInfo->start_time)
                - strtotime($nextBooking->slotInfo->date . " " . $nextBooking->slotInfo->start_time);
            $totalBooking += 1;
        }
        if ($totalBooking != 0) {
            $frequencySecond = $totalTime / $totalBooking;
            return $frequencySecond / 60;
        }*/

        for ($i = 0; $i < count($bookings) - 1; $i++) {
            $booking = $bookings[$i];
            $totalTime += $booking->slotInfo->duration_minute;
            $totalBooking += 1;
        }

        if ($totalBooking != 0) {
            return $totalTime / 60;
        }

        return null;
    }

    public function getTotalMoney()
    {
        //todo get all online booking
        $bookings = BookingInfo::find()
            ->innerJoin(ShopCalendarSlot::tableName(), ShopCalendarSlot::tableName() . ".slot_id = " . BookingInfo::tableName() . ".slot_id")
            ->where([
                BookingInfo::tableName() . ".member_id" => $this->user_id
            ])
            ->andWhere([
                "IN",
                BookingInfo::tableName() . ".status", [
                    BookingInfo::STATUS_ACCEPTED,
                ]
            ])
            ->orderBy(
                ShopCalendarSlot::tableName() . ".date DESC, " . ShopCalendarSlot::tableName() . ".start_time" . " DESC"
            )
            ->all();
        $total = 0;
        foreach ($bookings as $booking) {
            /**
             * @var $booking BookingInfo
             */
            $total += $booking->cost;
        }
        return $total;
    }

    public function getLatestBooking()
    {
        //todo get latest booking
        $latestBooking = BookingInfo::find()
            ->innerJoin(ShopCalendarSlot::tableName(), ShopCalendarSlot::tableName() . ".slot_id = " . BookingInfo::tableName() . ".slot_id")
            ->where([
                BookingInfo::tableName() . ".member_id" => $this->user_id
            ])
            ->orderBy(
                ShopCalendarSlot::tableName() . ".date DESC, " . ShopCalendarSlot::tableName() . ".start_time" . " DESC"
            )
            ->limit(1)
            ->one();
        return $latestBooking;
    }

    public function getLatestBookingTime()
    {
        $latestBooking = $this->latestBooking;
        if ($latestBooking) {
            return $this->latestBooking->slotInfo->date . " " . $this->latestBooking->slotInfo->start_time;
        }
        return null;
    }

    public static function getWorkerRating($member_id = null)
    {
        $subQuery = (new Query())
            ->from(Rating::tableName())
            ->select('worker_id')
            ->where('user_id=:member_id', [':member_id' => $member_id]);
        return $ratingData = (new Query())
            ->from(['r' => Rating::tableName()])
            ->innerJoin(['wi' => WorkerInfo::tableName()], "r.worker_id = wi.worker_id")
            ->select([
                'wi.worker_name',
                'AVG(behavior + technique + service + satisfaction) AS average_point',
                'wi.worker_id AS url'
            ])
            ->where(['in', 'r.worker_id', $subQuery])
            ->groupBy('r.worker_id');
    }
    public static function getMemberDetailQuery($member_id = null) {
//        $userData = (new Query())
//            ->from(['ui' => UserInfo::tableName()])
//            ->leftJoin(['ud1' => UserData::tableName()], "ui.user_id = ud1.user_id and ud1.field_key = '".UserData::KEY_USED_SERVICE_SHOP."'")
//            ->leftJoin(['ud2' => UserData::tableName()], "ui.user_id = ud2.user_id and ud2.field_key = '".UserData::KEY_USER_HOBBIES."'")
//            ->leftJoin(['uc' => UserConfig::tableName()], "ui.user_id = uc.user_id and uc.key = '".UserConfig::KEY_NOTE."'")
//            ->select([
//                'ui.user_id',
//                'ui.full_name',
//                'ui.phone_number',
//                'ui.email',
//                'ui.role',
//                'ui.tag',
//                'ui.status',
//                'ui.created_at',
//                'uc.value as note',
//                'ud1.value as used_shops',
//                'ud2.value as hobbies',
//                'ui.verify_phone',
//                'ui.verify_email',
//            ])
//            ->where(['!=', 'status', UserInfo::STATUS_DELETED])
//            ->andWhere(['role' => UserInfo::ROLE_USER]);
//        $bookingStat = (new Query())
//            ->from(['bi' => BookingInfo::tableName()])
//            ->innerJoin(['scs' => ShopCalendarSlot::tableName()], 'bi.slot_id = scs.slot_id')
//            ->innerJoin(['ci' => CustomerInfo::tableName()], 'bi.customer_id = ci.customer_id')
//            ->select([
//                '(CASE
//                    WHEN bi.member_id IS NOT NULL THEN bi.member_id
//                    ELSE (SELECT user_id FROM user_info WHERE phone_number = ci.phone_number AND role = "USER")
//                END) as bs_member_id',
//                'MAX(bi.created_at) as last_booking',
//                'SUM(bi.cost) as total_money',
//                'COUNT(bi.booking_id) as total_booking',
//                'SUM(scs.duration_minute) as total_time',
//            ])
//            ->groupBy('bs_member_id')
//            ->where(['bi.status' => BookingInfo::STATUS_ACCEPTED]);
//        $couponStat = (new Query())
//            ->from(['cm' => CouponMember::tableName()])
//            ->innerJoin(['ci' => CouponInfo::tableName()], 'cm.coupon_code = ci.coupon_code')
//            ->select([
//                'cm.member_id as cs_member_id',
//                'DATE(MAX(ci.created_at)) as last_coupon_released',
//                'SUM(ci.yield) as total_coupon_yield',
//            ])
//            ->groupBy('cm.member_id')
//            ->where(['NOT IN', 'cm.status', [CouponMember::STATUS_WAIT_CONFIRM]]);
//        return (new Query())
//            ->from(['ud' => $userData])
//            ->leftJoin(['bs' => $bookingStat], 'ud.user_id = bs.bs_member_id')
//            ->leftJoin(['cs' => $couponStat], 'ud.user_id = cs.cs_member_id')
//            ->andFilterWhere(['ud.user_id' => $member_id])
//            ->orderBy(['created_at' => SORT_DESC]);

        $query = (new Query())->from(['ui' => UserInfo::tableName()])
//            ->leftJoin(['uc' => UserConfig::tableName()], 'ui.user_id = uc.user_id AND uc.key = "NOTE"')
            ->leftJoin(['bi' => BookingInfo::tableName()], 'ui.user_id = bi.member_id AND bi.status = 3')
            ->leftJoin(['scs' => ShopCalendarSlot::tableName()], 'bi.slot_id = scs.slot_id')
            ->where(['!=', 'ui.status', UserInfo::STATUS_DELETED])
            ->andWhere(['ui.role' => UserInfo::ROLE_USER])
//            ->andWhere(['ui.user_id' => 11451])
            ->select([
                'ui.user_id',
                'ui.full_name',
                'ui.phone_number',
                'ui.email',
                'ui.role',
                'ui.status',
                'ui.created_at',
                'ui.verify_phone',
                'ui.verify_email',
                'MAX(bi.created_at) AS last_booking',
                'FLOOR(SUM(bi.cost)*count(DISTINCT bi.booking_id)/count(*)) AS total_money',
                'COUNT(DISTINCT bi.booking_id) AS total_booking',
            ])
            ->groupBy(['ui.user_id'])
            ->andFilterWhere(['ui.user_id' => $member_id])
            ->orderBy(['ui.created_at' =>SORT_DESC]);

        return $query;
    }

    public static function getTotalRatingDays($id, $worker_id)
    {
        return (new Query())
            ->from(Rating::tableName())
            ->select('*')
            ->where(['user_id' => $id])
            ->andWhere(['worker_id' => $worker_id])
            ->orderBy('created_at DESC');
    }
}
