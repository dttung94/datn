<?php

namespace common\entities\shop;

use common\entities\worker\WorkerInfo;
use common\helper\ArrayHelper;
use common\helper\DatetimeHelper;
use common\models\base\AbstractObject;
use Yii;

/**
 * This is the model class for table "shop_calendar".
 *
 * @property integer $shop_id
 * @property integer $worker_id
 * @property string $date
 *
 * @property string $type
 *
 * @property string $work_start_time
 * @property string $work_end_time
 *
 * @property integer status
 * @property string $created_at
 * @property string $modified_at
 *
 * @property ShopInfo $shopInfo
 * @property WorkerInfo $workerInfo
 */
class ShopCalendar extends AbstractObject
{
    const
        TYPE_WORKING_DAY = "WORKING_DAY",
        TYPE_HOLIDAY = "HOLIDAY";

    public static $dataShopConfig;

    public static function getInstance($shop_id, $worker_id, $date)
    {
        return self::findOne([
            "shop_id" => $shop_id,
            "worker_id" => $worker_id,
            "date" => $date,
        ]);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_calendar';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_id', 'worker_id', 'date'], 'unique', 'targetAttribute' => ['shop_id', 'worker_id', 'date']],
            [['work_start_time', 'work_end_time'], 'required', 'when' => function (ShopCalendar $model) {
                return $model->type == self::TYPE_WORKING_DAY;
            }],
            [['date'], 'validateDate', 'when' => function (ShopCalendar $mode) {
                return $mode->type == ShopCalendar::TYPE_WORKING_DAY;
            }],
            [['shop_id', 'worker_id', 'date', 'type'], 'required'],
            [['date'], 'date', 'format' => 'php:Y-m-d'],
            [['work_start_time'], 'date', 'format' => 'php:H:m', 'when' => function (ShopCalendar $model) {
                return $model->type == self::TYPE_WORKING_DAY;
            }],
            [['work_end_time'], 'date', 'format' => 'php:H:m', 'when' => function (ShopCalendar $model) {
                return $model->type == self::TYPE_WORKING_DAY && $model->work_end_time != "24:0";
            }],
            [['type'], 'in', 'range' => [
                self::TYPE_WORKING_DAY,
                self::TYPE_HOLIDAY,
            ]],
            [['status'], 'in', 'range' => [
                self::STATUS_ACTIVE,
            ]],
            [['shop_id', 'worker_id', 'status'], 'integer'],
            [['shop_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopInfo::className(), 'targetAttribute' => ['shop_id' => 'shop_id']],
            [['worker_id'], 'exist', 'skipOnError' => true, 'targetClass' => WorkerInfo::className(), 'targetAttribute' => ['worker_id' => 'worker_id']],
            [['shop_id', 'worker_id', 'date', 'work_start_time', 'work_end_time', 'status', 'created_at', 'modified_at'], 'safe'],
        ];
    }

    public function validateDate($attribute, $params)
    {
        if (!$this->hasErrors()) {
            //1. todo check time is valid with shop
            $shopInfo = $this->shopInfo;
            if ($shopInfo && !$this->hasErrors()) {
                //1.1. todo check time is working time of shop
                if (!(
                    strtotime($this->work_start_time) >= strtotime($shopInfo->openDoorAt) &&
                    strtotime($this->work_end_time) <= strtotime($shopInfo->closeDoorAt)
                )
                ) {
                    $this->addError($attribute, \App::t("common.shop_calendar.message", "Working time of Shop not allow for from {start-time} to {end-time}", [
                        "start-time" => \App::$app->formatter->asTime($this->work_start_time),
                        "end-time" => \App::$app->formatter->asTime($this->work_end_time),
                    ]));
                }
                //1.2. todo check date is working date of shop
                $dayOfWeek = DatetimeHelper::getDayOfWeekFromDate($this->date);
                if (ArrayHelper::getValue($shopInfo->workingDayOnWeek, $dayOfWeek, 0) != "1") {
                    $this->addError($attribute, \App::t("common.shop_calendar.message", "Shop do not allow working on [{date} ({day-of-week})]", [
                        "date" => $this->date,
                        "day-of-week" => DatetimeHelper::getDayOfWeek($dayOfWeek),
                    ]));
                }
            }
            //todo check time is valid with worker
            $workerCalendars = ShopCalendar::find()
                ->innerJoin(ShopInfo::tableName(), ShopInfo::tableName() . ".shop_id = " . ShopCalendar::tableName() . ".shop_id")
                ->andWhere([
                    ShopCalendar::tableName() . ".type" => self::TYPE_WORKING_DAY,
                    ShopCalendar::tableName() . ".worker_id" => $this->worker_id,
                    ShopCalendar::tableName() . ".date" => $this->date,
                    ShopCalendar::tableName() . ".status" => ShopCalendar::STATUS_ACTIVE,
                    ShopInfo::tableName() . ".status" => ShopCalendar::STATUS_ACTIVE,
                ])
                ->andWhere(["!=", ShopInfo::tableName() . ".shop_id", $this->shop_id])
                ->all();
            if (!$this->hasErrors() && !empty($workerCalendars)) {
                foreach ($workerCalendars as $workerCalendar) {
                    /**
                     * @var $workerCalendar ShopCalendar
                     */
                    if (
                        (strtotime($this->work_start_time) <= strtotime($workerCalendar->work_start_time) && strtotime($this->work_end_time) >= strtotime($workerCalendar->work_start_time)) ||
                        (strtotime($this->work_start_time) >= strtotime($workerCalendar->work_start_time) && strtotime($this->work_end_time) <= strtotime($workerCalendar->work_end_time)) ||
                        (strtotime($this->work_start_time) <= strtotime($workerCalendar->work_end_time) && strtotime($this->work_end_time) >= strtotime($workerCalendar->work_end_time))
                    ) {
                        $this->addError($attribute, \App::t("common.shop_calendar.message", "Bị trùng lịch", [
                            "shop-name" => $workerCalendar->shopInfo->shop_name,
                            "start-time" => \App::$app->formatter->asTime($workerCalendar->work_start_time),
                            "end-time" => $workerCalendar->work_end_time,
                        ]));
                    }
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'shop_id' => Yii::t('app.attribute.shop_calendar.label', 'Shop'),
            'worker_id' => Yii::t('app.attribute.shop_calendar.label', 'Worker'),
            'date' => Yii::t('app.attribute.shop_calendar.label', 'Date'),
            'type' => Yii::t('app.attribute.shop_calendar.label', 'Type'),

            'work_start_time' => Yii::t('app.attribute.shop_calendar.label', 'Work start at'),
            'work_end_time' => Yii::t('app.attribute.shop_calendar.label', 'Work end at'),

            'status' => Yii::t('app.attribute.shop_calendar.label', 'Status'),
            'created_at' => Yii::t('app.attribute.shop_calendar.label', 'Created At'),
            'modified_at' => Yii::t('app.attribute.shop_calendar.label', 'Modified At'),
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->created_at = self::currentDatetime();
            }
            $this->modified_at = self::currentDatetime();
            return true;
        }
        return false;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopInfo()
    {
        return $this->hasOne(ShopInfo::className(), ['shop_id' => 'shop_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWorkerInfo()
    {
        return $this->hasOne(WorkerInfo::className(), ['worker_id' => 'worker_id']);
    }

    public function extraFields()
    {
        return ArrayHelper::merge(parent::extraFields(), [
            "workerInfo"
        ]);
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        $data = parent::toArray($fields, $expand, $recursive);
        $data["work_start_hour"] = DatetimeHelper::getHourFromTimeFormat($this->work_start_time);
        $data["work_start_time"] = DatetimeHelper::getMinuteFromTimeFormat($this->work_start_time);
        $data["work_end_hour"] = DatetimeHelper::getHourFromTimeFormat($this->work_end_time);
        $data["work_end_time"] = DatetimeHelper::getMinuteFromTimeFormat($this->work_end_time);
        return $data;
    }

    public static  function getDataShopConfig()
    {
        if(!self::$dataShopConfig) {
            return self::$dataShopConfig = ShopConfig::find()->select(['shop_id','key', 'value'])->all();
        }
        return self::$dataShopConfig;
    }

    public static function isLockUserBooking($shopId)
    {
        $data = self::getDataShopConfig();
        $data = ArrayHelper::map($data, 'key', 'value', 'shop_id');
        $existsTimeOn = (array_key_exists($shopId, $data) && array_key_exists(ShopConfig::KEY_SHOP_TIME_ON_USER_BOOKING, $data[$shopId])) ?
            $data[$shopId][ShopConfig::KEY_SHOP_TIME_ON_USER_BOOKING] : null;
        $timeOnUserBooking = $existsTimeOn ? $existsTimeOn : '00:00';
        $timeOnUserBooking = date('H:i', strtotime($timeOnUserBooking));


        $existsBlockBooking = (array_key_exists($shopId, $data) && array_key_exists(ShopConfig::KEY_SHOP_ALLOW_BLOCK_BOOKING,  $data[$shopId])) ?
            $data[$shopId][ShopConfig::KEY_SHOP_ALLOW_BLOCK_BOOKING] : null;
        $isBlockUserBooking = $existsBlockBooking ? $existsBlockBooking : 0;

        return !$isBlockUserBooking && strtotime($timeOnUserBooking) >= strtotime(date('H:i'));
    }
}