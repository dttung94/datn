<?php

namespace common\entities\shop;

use common\entities\worker\WorkerInfo;
use common\entities\worker\WorkerMappingShop;
use common\helper\ArrayHelper;
use common\helper\DatetimeHelper;
use common\models\base\AbstractObject;
use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "shop_info".
 *
 * @property string $shop_id
 *
 * @property string $shop_name
 * @property string $shop_desc
 *
 * @property string $shop_address
 * @property string $phone_number
 * @property string $shop_domain
 * @property string $shop_email
 *
 * @property string $status
 * @property string $is_auto_create
 * @property string $created_at
 * @property string $modified_at
 *
 * @property string $openDoorAt
 * @property string $closeDoorAt
 * @property array $workingDayOnWeek
 *
 * @property bool $isCloseAtEndDay
 * @property bool $isOpenFullDay
 * @property bool $isOpenFullWeek
 * @property boolean $isAllowFreeBooking
 *
 * @property WorkerInfo[] $workers
 */
class ShopInfo extends AbstractObject
{
    const
        DISABLED_BOOKING_FREE = 0,
        TWENTY_MINUTE = 20,
        THIRTY_MINUTE = 30,
        FIFTY_MINUTE = 50,
        AUTO_CREATE_ACTIVE = 1,
        AUTO_CREATE_INACTIVE = 0,
        TYPE_CREATE_COUPON = 'auto-create',
        ALL_SHOP = 'all';

    public static $dataShop, $timeOpenDoor, $timeShopEnd, $workingDayOnWeek;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_name'], 'required'],
            [['status'], 'integer'],
            [['shop_email'], 'email'],
            [['shop_name', 'shop_desc', 'shop_address', 'phone_number', 'created_at', 'modified_at', 'shop_email'], 'string'],
            [['shop_name', 'shop_desc', 'shop_address', 'phone_number', 'status', 'created_at', 'modified_at', 'shop_email', 'is_auto_create'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'shop_id' => Yii::t('app.attribute.shop_info.label', 'ID'),

            'shop_name' => Yii::t('app.attribute.shop_info.label', 'Tên Salon tóc'),
            'shop_desc' => Yii::t('app.attribute.shop_info.label', 'Mô tả'),

            'shop_address' => Yii::t('app.attribute.shop_info.label', 'Địa chỉ'),
            'phone_number' => Yii::t('app.attribute.shop_info.label', 'Số điện thoại'),

            'status' => Yii::t('app.attribute.shop_info.label', 'Trạng thái'),
            'created_at' => Yii::t('app.attribute.shop_info.label', 'Ngày tạo'),
            'modified_at' => Yii::t('app.attribute.shop_info.label', 'Modified At'),
            'calendar' => Yii::t('app.attribute.shop_info.label', 'Lịch làm việc'),

            "open_door_at" => Yii::t('app.attribute.shop_info.label', 'Giờ mở cửa'),
            "close_door_at" => Yii::t('app.attribute.shop_info.label', 'Giờ đóng cửa'),
            "working_day_on_week" => Yii::t('app.attribute.shop_info.label', 'Ngày làm việc'),

            "shop_email" => Yii::t('app.attribute.shop_info.label', 'Email'),
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->created_at = $this->currentDatetime();
            }
            $this->modified_at = $this->currentDatetime();
            return true;
        }
        return false;
    }

    public static function getShopColorDefault()
    {
        return [
            1 => 'rgb(255,205,210)',
            2 => 'rgb(209,196,233)',
            3 => 'rgb(179,229,252)',
            4 => 'rgb(200,230,201)',
            5 => 'rgb(255,226,128)',
            6 => 'rgb(255,204,188)',
            11 => 'rgb(207,216,220)',
            12 => 'rgb(248,187,208)',
            13 => 'rgb(197,202,233)',
        ];
    }

    public function getWorkingDayOnWeek()
    {
        if (!self::$workingDayOnWeek) {
            self::$workingDayOnWeek = Json::decode(ShopConfig::getValue(ShopConfig::KEY_SHOP_WORKING_DAY_ON_WEEK, $this->shop_id, "[]"));
            return self::$workingDayOnWeek;
        }
        return self::$workingDayOnWeek;
    }

    public function getOpenDoorAt()
    {
        if (!self::$timeOpenDoor) {
            self::$timeOpenDoor = ShopConfig::getValue(ShopConfig::KEY_SHOP_OPEN_DOOR_AT, $this->shop_id, "00:00");
            return self::$timeOpenDoor;
        }
        return self::$timeOpenDoor;
    }

    public function getCloseDoorAt()
    {
        if (!self::$timeShopEnd) {
            self::$timeShopEnd = ShopConfig::getValue(ShopConfig::KEY_SHOP_CLOSE_DOOR_AT, $this->shop_id, "24:00");
            return self::$timeShopEnd;
        }
        return self::$timeShopEnd;
    }

    public function getIsCloseAtEndDay()
    {
        return DatetimeHelper::timeFormat2Seconds($this->closeDoorAt) == DatetimeHelper::timeFormat2Seconds("00:00") ||
            DatetimeHelper::timeFormat2Seconds($this->closeDoorAt) == DatetimeHelper::timeFormat2Seconds("24:00");
    }

    public function getIsOpenFullDay()
    {
        return DatetimeHelper::timeFormat2Seconds($this->openDoorAt) == DatetimeHelper::timeFormat2Seconds("00:00") && $this->isCloseAtEndDay;
    }

    public function isOpenDay($date)
    {
        if (count($this->workers) > 0) {
            $schedules = ShopCalendar::findAll([
                "shop_id" => $this->shop_id,
                "date" => $date,
            ]);
            if (count($schedules) > 0) {
                foreach ($schedules as $schedule) {
                    if ($schedule->type == ShopCalendar::TYPE_WORKING_DAY) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function getIsOpenFullWeek()
    {
        return empty($this->workingDayOnWeek);
    }

    public function getWorkers()
    {
        return $this->hasMany(WorkerInfo::className(), ['worker_id' => 'worker_id'])
            ->viaTable(WorkerMappingShop::tableName(), [
                "shop_id" => "shop_id"
            ])
            ->where([
                WorkerInfo::tableName() . ".status" => WorkerInfo::STATUS_ACTIVE,
            ]);
    }

    public function getIsAllowFreeBooking()
    {
        $value = ShopConfig::getValue(ShopConfig::KEY_SHOP_ALLOW_FREE_BOOKING, $this->shop_id, 1);
        return intval($value);
    }

    public function extraFields()
    {
        return ArrayHelper::merge(parent::extraFields(), [
            "isAllowFreeBooking"
        ]);
    }

    public function getListShop($type = null)
    {
        $query = ShopInfo::find()
            ->where("status = :status", [
                ":status" => ShopInfo::STATUS_ACTIVE
            ]);
        $shops = $query->all();
        if ($type == self::ALL_SHOP) {
            return $shops;
        }
        $result = [];
        foreach ($shops as $shop) {
            $result[$shop['shop_id']] = $shop['shop_name'];
        }
        return $result;
    }

    public static function getDataShop()
    {
        if (!self::$dataShop)
        {
            self::$dataShop = self::find()->select('shop_id, shop_address')
                ->where("status = :status", [
                    ":status" => ShopInfo::STATUS_ACTIVE
                ])
                ->all();
            return self::$dataShop;
        }
        return self::$dataShop;
    }

    public static function getShopUrl($shopId) {
        $data = ArrayHelper::map(self::getDataShop(), 'shop_id', 'shop_address');
        $shopAddress = array_key_exists($shopId, $data) ? $data[$shopId] : null;
        return rtrim($shopAddress, '/').'/sp/profile.php?id=';
    }

    public static function getShopName($shopId)
    {
        $shopInfo = self::findOne([
            'shop_id' => $shopId
        ]);
        return $shopInfo ? $shopInfo->shop_name : null;
    }
}
