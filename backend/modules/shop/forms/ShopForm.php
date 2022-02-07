<?php
namespace backend\modules\shop\forms;

use common\entities\calendar\BookingInfo;
use common\entities\calendar\FreeBookingRequest;
use common\entities\customer\CustomerInfo;
use common\entities\shop\ShopCalendarSlot;
use common\entities\shop\ShopConfig;
use common\entities\shop\ShopInfo;
use common\entities\user\UserConfig;
use common\entities\user\UserInfo;
use common\entities\worker\WorkerInfo;
use common\entities\worker\WorkerMappingShop;
use common\helper\DatetimeHelper;
use function Couchbase\defaultDecoder;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Class ShopForm
 * @package backend\modules\shop\forms
 *
 * @property integer $totalBooking
 *
 * @property string $keyword
 *
 * @property integer $open_door_hour
 * @property integer $open_door_minute
 * @property integer $close_door_hour
 * @property integer $close_door_minute
 * @property array $working_day_on_week
 *
 * @property integer $allow_booking_tomorrow
 * @property integer $allow_booking_tomorrow_at_hour
 * @property integer $allow_booking_tomorrow_at_minute
 * @property integer $on_user_booking_hour
 * @property integer $on_user_booking_minute
 */
class ShopForm extends ShopInfo
{
    public $keyword;

    public $open_door_hour, $open_door_minute,
        $close_door_hour, $close_door_minute,

        $working_day_on_week;

    public $allow_booking_tomorrow;
    public $allow_booking_tomorrow_at_hour = 0;
    public $allow_booking_tomorrow_at_minute = 0;

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['keyword'], 'safe'],
            [['open_door_hour', 'close_door_hour'], 'integer', 'min' => 0, 'max' => 24],
            [["open_door_minute", "close_door_minute"], "integer", "min" => 0, "max" => 60],
            [['open_door_hour', 'open_door_minute', 'close_door_hour', 'close_door_minute'], 'integer'],
            [['open_door_hour', 'open_door_minute', 'close_door_hour', 'close_door_minute', 'working_day_on_week'], 'safe'],
            [['allow_booking_tomorrow', 'allow_booking_tomorrow_at_hour', 'allow_booking_tomorrow_at_minute'], 'safe'],
            [['phone_number'], 'validatePhoneNumber'],
            [['phone_number'], 'string', 'min' => 6, 'max' => 15],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'keyword' => \Yii::t('common.label', 'Keyword')
        ]);
    }

    public function validatePhoneNumber($attribute, $params)
    {
        $pattern = '/^[0-9]+$/';
        if (!$this->hasErrors()) {
            if (!preg_match($pattern, $this->phone_number)) {
                $this->addError($attribute, \App::t("backend.shop.message", '間違った電話番号の形式'));
            }
        }
    }

    public function getTotalBooking()
    {
        $query = BookingInfo::find();
        $query->innerJoin(CustomerInfo::tableName(), CustomerInfo::tableName() . ".customer_id = " . BookingInfo::tableName() . ".customer_id");
        $query->innerJoin(ShopCalendarSlot::tableName(), ShopCalendarSlot::tableName() . ".slot_id = " . BookingInfo::tableName() . ".slot_id");
        $query->innerJoin(ShopInfo::tableName(), ShopCalendarSlot::tableName() . ".shop_id = " . ShopInfo::tableName() . ".shop_id");
        $query->innerJoin(WorkerInfo::tableName(), ShopCalendarSlot::tableName() . ".worker_id = " . WorkerInfo::tableName() . ".worker_id");
        $query->andWhere(BookingInfo::tableName() . ".status = :STATUS_ACCEPTED", [
            ':STATUS_ACCEPTED' => BookingInfo::STATUS_ACCEPTED
        ]);
        $query->andWhere(ShopInfo::tableName() . ".shop_id = :shop_id", [
            ':shop_id' => $this->shop_id,
        ]);
        return $query->count();
    }

    public function search()
    {
        $query = parent::find();
        $query->andWhere("status != :status_deleted", [
            ':status_deleted' => self::STATUS_DELETED
        ]);
        if (\App::$app->user->identity->role == UserInfo::ROLE_MANAGER) {
            $shopIds = Json::decode(UserConfig::getValue(UserConfig::KEY_MANAGE_SHOP_IDS, \App::$app->user->id, "[]"));
            $query->andWhere(["IN", ShopInfo::tableName() . ".shop_id", $shopIds]);
        }
        if ($this->status) {
            $query->andWhere(self::tableName() . ".status = :status", [
                ":status" => $this->status
            ]);
        }
        if ($this->keyword != null) {
            $keyword = str_replace('-', '', $this->keyword);
            $query->andFilterWhere([
                'or',
                ['LIKE', static::tableName() . '.shop_name', $keyword],
                ['LIKE', static::tableName() . '.shop_desc', $keyword],
                ['LIKE', static::tableName() . '.phone_number', $keyword],
                ['LIKE', static::tableName() . '.shop_domain', $keyword],
                ['LIKE', static::tableName() . '.shop_address', $keyword],
            ]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $dataProvider->setSort([
            'defaultOrder' => ['created_at' => SORT_DESC],
            'attributes' => [
                'shop_name',
                'status',
                'created_at',
            ]
        ]);

        return $dataProvider;
    }

    public function searchWorker()
    {
        $query = WorkerInfo::find();
        $query->innerJoin(WorkerMappingShop::tableName(), WorkerMappingShop::tableName() . ".worker_id = " . WorkerInfo::tableName() . ".worker_id");
        $query->andWhere(WorkerInfo::tableName() . ".status != :status_deleted", [
            ':status_deleted' => WorkerInfo::STATUS_DELETED
        ]);
        $query->andWhere(WorkerMappingShop::tableName() . ".shop_id = :shop_id", [
            ":shop_id" => $this->shop_id,
        ]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $dataProvider->setSort([
            'defaultOrder' => ['created_at' => SORT_DESC],
            'attributes' => [
                'status',
                'created_at',
            ]
        ]);

        return $dataProvider;
    }

    public function toPrepare()
    {
        if ($this->isNewRecord) {
            $this->open_door_hour = 0;
            $this->open_door_minute = 0;
            $this->close_door_hour = 24;
            $this->close_door_minute = 0;

            $this->working_day_on_week = [
                1 => 1,
                2 => 1,
                3 => 1,
                4 => 1,
                5 => 1,
                6 => 1,
                7 => 1,
            ];

            $this->allow_booking_tomorrow = 1;
            $this->allow_booking_tomorrow_at_hour = 00;
            $this->allow_booking_tomorrow_at_minute = 00;
        } else {
            $valueTime = ShopConfig::getValue(ShopConfig::KEY_SHOP_TIME_ON_USER_BOOKING, $this->shop_id);
            $timeOn = $valueTime ? date('H:i', strtotime($valueTime)) : '00:00';
            $times = explode(':', $timeOn);
//            $this->on_user_booking_hour = (int)$times[0];
//            $this->on_user_booking_minute = (int)$times[1];
            $this->open_door_hour = DatetimeHelper::getHourFromTimeFormat($this->openDoorAt, ":", 0);
            $this->open_door_minute = DatetimeHelper::getMinuteFromTimeFormat($this->openDoorAt, ":", 0);
            $this->close_door_hour = DatetimeHelper::getHourFromTimeFormat($this->closeDoorAt, ":", 24);
            $this->close_door_minute = DatetimeHelper::getMinuteFromTimeFormat($this->closeDoorAt, ":", 0);
            $this->working_day_on_week = $this->workingDayOnWeek;
            $valueConfigTomorrow = ShopConfig::getValue(ShopConfig::KEY_SHOP_BOOKING_TOMORROW_AT, $this->shop_id);
            $this->allow_booking_tomorrow = $valueConfigTomorrow ? 1 : 0;
            $this->allow_booking_tomorrow_at_hour = DatetimeHelper::getHourFromTimeFormat($valueConfigTomorrow, ":", 23);
            $this->allow_booking_tomorrow_at_minute = DatetimeHelper::getMinuteFromTimeFormat($valueConfigTomorrow, ":", 30);
        }
    }

    public function toSave()
    {
        $trans = \App::$app->db->beginTransaction();
        if ($this->save()) {
            //todo save shop config
            if (!ShopConfig::setValue(ShopConfig::KEY_SHOP_OPEN_DOOR_AT, $this->shop_id, "$this->open_door_hour:$this->open_door_minute")) {
                $this->addError("open_door_at", \App::t("backend.shop.message", "Have error when save config"));
            }
            if ($this->close_door_hour == 24) {
                $this->close_door_minute = 0;
            }
            if (!ShopConfig::setValue(ShopConfig::KEY_SHOP_CLOSE_DOOR_AT, $this->shop_id, "$this->close_door_hour:$this->close_door_minute")) {
                $this->addError("close_door_at", \App::t("backend.shop.message", "Have error when save config"));
            }
            if (!ShopConfig::setValue(ShopConfig::KEY_SHOP_WORKING_DAY_ON_WEEK, $this->shop_id, Json::encode($this->working_day_on_week))) {
                $this->addError("working_day_on_week", \App::t("backend.shop.message", "Have error when save config"));
            }
            if ($this->allow_booking_tomorrow) {
                if (!ShopConfig::setValue(ShopConfig::KEY_SHOP_BOOKING_TOMORROW_AT, $this->shop_id, "$this->allow_booking_tomorrow_at_hour:$this->allow_booking_tomorrow_at_minute")) {
                    $this->addError("allow_booking_tomorrow", \App::t("backend.shop.message", "Have error when save config"));
                }
            } else {
                ShopConfig::removeConfig(ShopConfig::KEY_SHOP_BOOKING_TOMORROW_AT, $this->shop_id);
            }
//            $time = $this->on_user_booking_hour.':'.$this->on_user_booking_minute;
//            $time = date('H:i', strtotime($time));
//            $setTimeOn = ShopConfig::setValue(ShopConfig::KEY_SHOP_TIME_ON_USER_BOOKING, $this->shop_id, $time);
//            if (!$setTimeOn) {
//                $this->addError("time_on_user", \App::t("backend.shop.message", "Have error when save config"));
//            }
        }
        if (!$this->hasErrors()) {
            $trans->commit();
            return true;
        }
        $trans->rollBack();
        return false;
    }

    public function toToggleStatus()
    {
        if ($this->status == self::STATUS_ACTIVE) {
            //todo check shop is valid for inactive
            $totalSlotAvailable = ShopCalendarSlot::find()
                ->where([
                    "shop_id" => $this->shop_id,
                    "status" => ShopCalendarSlot::STATUS_ACTIVE
                ])
                ->count();
            $totalBookingAvailable = ShopCalendarSlot::find()
                ->addSelect([
                    'STR_TO_DATE(CONCAT(CONCAT(CONCAT(shop_calendar_slot.date,\' \'),shop_calendar_slot.start_time),":00"), \'%Y-%m-%d %H:%i:%s\') as "datetime"'
                ])
                ->where([
                    "shop_id" => $this->shop_id,
                    "status" => ShopCalendarSlot::STATUS_BOOKED
                ])
                ->andHaving("datetime >= :now", [
                    ":now" => DatetimeHelper::now(DatetimeHelper::FULL_DATETIME),
                ])
                ->count();
            $totalFreeBookingRequest = FreeBookingRequest::find()
                ->where([
                    "shop_id" => $this->shop_id,
                    "status" => FreeBookingRequest::STATUS_PENDING
                ])
                ->count();
            if ($totalSlotAvailable == 0 && $totalBookingAvailable == 0 && $totalFreeBookingRequest == 0) {
                $this->status = self::STATUS_INACTIVE;
            } else {
                if ($totalSlotAvailable > 0) {
                    $this->addError("shop_id", \App::t("backend.shop.message", "利用可能な枠を持っている店舗を非アクティブにすることはできません。"));
                }
                if ($totalBookingAvailable > 0) {
                    $this->addError("shop_id", \App::t("backend.shop.message", "利用可能な予約を持っている店舗を非アクティブにすることはできません。"));
                }
                if ($totalFreeBookingRequest > 0) {
                    $this->addError("shop_id", \App::t("backend.shop.message", "保留中のフリー予約要求のある、店舗を非アクティブにすることはできません。"));
                }
            }
        } else {
            $this->status = self::STATUS_ACTIVE;
        }
        if (!$this->hasErrors()) {
            return $this->save(false);
        }
        return false;
    }

    public function extraFields()
    {
        return ArrayHelper::merge(parent::rules(), [
            'open_door_hour',
            'open_door_minute',
            'close_door_hour',
            'close_door_minute',
            'working_day_on_week',
        ]);
    }

    public function toArray(array $fields = [], array $expand = [
        'open_door_hour',
        'open_door_minute',
        'close_door_hour',
        'close_door_minute',
        'working_day_on_week',
        ], $recursive = true)
    {
        return parent::toArray($fields, $expand, $recursive);
    }
}