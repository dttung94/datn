<?php

namespace common\entities\shop;

use backend\modules\calendar\forms\booking\CalendarForm;
use common\components\WebSocketClient;
use common\entities\calendar\BookingInfo;
use common\entities\system\SystemConfig;
use common\entities\worker\WorkerInfo;
use common\entities\worker\WorkerMappingShop;
use common\helper\ArrayHelper;
use common\helper\DatetimeHelper;
use common\helper\StringHelper;
use common\models\base\AbstractObject;
use Yii;

/**
 * This is the model class for table "shop_calendar_slot".
 *
 * @property integer $slot_id
 * @property string $type
 *
 * @property integer $shop_id
 * @property integer $worker_id
 * @property string $date
 * @property string $start_time
 * @property string $end_time
 * @property string $duration_minute
 * @property boolean $is_change_duration_minute
 * @property integer $init_minute
 *
 * @property integer status
 * @property string $created_at
 * @property string $modified_at
 *
 * @property ShopInfo $shopInfo
 * @property WorkerInfo $workerInfo
 *
 * @property boolean $isCanBooking
 * @property BookingInfo $bookingInfo
 *
 * @property string $htmlContent
 *
 * @property boolean $isEditable
 * @property boolean $isDeletable
 * @property boolean $isExpired
 * @property boolean $isExpiredEditTime
 * @property string $colorSlot
 * @property WorkerMappingShop $workerMapShop
 */
class ShopCalendarSlot extends AbstractObject
{
    const
        STATUS_CONFLICT = 3,
        STATUS_BOOKED = 4,
        STATUS_EXPIRED = 5,
        STATUS_DELETE = 0;

    public static function getInstances($shop_id, $worker_id, $date)
    {
        return self::findAll([
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
        return 'shop_calendar_slot';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_id', 'worker_id', 'date', 'start_time'], 'unique', 'targetAttribute' => ['shop_id', 'worker_id', 'date', 'start_time']],
            [['start_time', 'duration_minute', 'end_time'], 'required'],
            [['shop_id', 'worker_id', 'date'], 'required'],
            [['date'], 'date', 'format' => 'php:Y-m-d'],
            [['start_time'], 'date', 'format' => 'php:H:m'],
//            [['end_time'], 'date', 'format' => 'php:H:m'],
            [["start_time"], "validateStartTime"],
            [['duration_minute'], 'validateDurationTime', 'when' => function (ShopCalendarSlot $model) {
                return $model->isNewRecord;
            }],
            [['status'], 'in', 'range' => [
                self::STATUS_ACTIVE,
                self::STATUS_INACTIVE,
                self::STATUS_CONFLICT,
                self::STATUS_EXPIRED,
                self::STATUS_BOOKED,
                self::STATUS_DELETE,
            ]],
            [['shop_id', 'worker_id', 'duration_minute', 'status'], 'integer'],
            ['duration_minute', 'compare', 'compareValue' => 20, 'operator' => '>='],
            [['shop_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopInfo::className(), 'targetAttribute' => ['shop_id' => 'shop_id']],
            [['worker_id'], 'exist', 'skipOnError' => true, 'targetClass' => WorkerInfo::className(), 'targetAttribute' => ['worker_id' => 'worker_id']],
            [['slot_id', 'shop_id', 'worker_id', 'date', 'start_time', 'end_time', 'duration_minute', 'status', 'created_at', 'modified_at'], 'safe'],
        ];
    }

    public function validateStartTime($attribute, $params)
    {
        if (!$this->hasErrors() && $this->workerInfo) {//todo validate TIME is CONFLICT with another time of WORKER
            $todaySlots = $this->workerInfo->getSlots($this->date);
            $startTime = DatetimeHelper::timeFormat2Seconds($this->start_time);
            $endTime = DatetimeHelper::timeFormat2Seconds($this->end_time);
            foreach ($todaySlots as $slot) {
                $slotStartTime = DatetimeHelper::timeFormat2Seconds($slot->start_time);
                $slotEndTime = DatetimeHelper::timeFormat2Seconds($slot->end_time);
                if (
                    ($this->slot_id != $slot->slot_id)
                    &&
                    (
                        (($slotStartTime < $startTime) && ($slotEndTime > $startTime)) ||
                        (($slotStartTime < $endTime) && ($slotEndTime > $endTime)) ||
                        (($startTime < $slotStartTime) && ($endTime > $slotStartTime)) ||
                        (($startTime < $slotEndTime) && ($endTime > $slotEndTime))
                    )
                ) {
                    $this->addError($attribute, \App::t("common.shop_calendar.message", "khung [{start-time} - {end-time}]bị trùng với khung làm việc của [{worker}]", [
                        "worker" => $this->workerInfo->worker_name,
                        "start-time" => \App::$app->formatter->asTime($this->start_time),
                        "end-time" => \App::$app->formatter->asTime($this->end_time),
                    ]));
                    break;
                }
            }
        }
    }

    public function validateDurationTime($attribute, $params)
    {
        if (!$this->hasErrors() && $this->shopInfo && $this->workerInfo) {
            $shopCalendar = ShopCalendar::findOne([
                "status" => ShopCalendar::STATUS_ACTIVE,
                "shop_id" => $this->shop_id,
                "worker_id" => $this->worker_id,
                "date" => $this->date,
                "type" => ShopCalendar::TYPE_WORKING_DAY
            ]);
            if (!$shopCalendar) {
                $this->addError($attribute, \App::t("common.shop_calendar.message", "Hết giờ"));
            } else {
                if (!(
                    strtotime($this->start_time) >= strtotime($shopCalendar->work_start_time) &&
                    strtotime($this->end_time) <= strtotime($shopCalendar->work_end_time)
                )
                ) {
                    $this->addError($attribute, \App::t("common.shop_calendar.message", "Hết giờ"));
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
            'slot_id' => Yii::t('app.attribute.shop_calendar_slot.label', 'ID'),
            'shop_id' => Yii::t('app.attribute.shop_calendar_slot.label', 'Shop'),
            'worker_id' => Yii::t('app.attribute.shop_calendar_slot.label', 'Worker'),
            'date' => Yii::t('app.attribute.shop_calendar_slot.label', 'Date'),
            'start_time' => Yii::t('app.attribute.shop_calendar_slot.label', 'Start at'),
            'end_time' => Yii::t('app.attribute.shop_calendar_slot.label', 'End at'),
            'duration_minute' => Yii::t('app.attribute.shop_calendar_slot.label', 'Duration'),

            'status' => Yii::t('app.attribute.shop_calendar_slot.label', 'Status'),
            'created_at' => Yii::t('app.attribute.shop_calendar_slot.label', 'Created At'),
            'modified_at' => Yii::t('app.attribute.shop_calendar_slot.label', 'Modified At'),
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

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($this->enableAutoSendNotification) {
            $oldModel = null;
            if (!$insert) {
                $oldModel = clone $this;
                foreach ($changedAttributes as $attribute => $value) {
                    if ($oldModel->hasAttribute($attribute)) {
                        $oldModel->$attribute = $value;
                    }
                }
            }
            \App::$app->webServiceClient->send(
                WebSocketClient::EVENT_SHOP_CALENDAR_SLOT_CHANGED,
                \App::t("common.notice.message", "Slot updated."), [
                    "shop_id" => $this->shop_id,
                    "worker_id" => $this->worker_id,
                    "date" => $this->date,
                    "oldCalendarData" => $oldModel ? CalendarForm::getSlotDataRemove($oldModel) : [],
                    "newCalendarData" => CalendarForm::getSlotData($this),
                ]
            );
        }
    }

    public function afterDelete()
    {
        parent::afterDelete();
        if ($this->enableAutoSendNotification) {
            \App::$app->webServiceClient->send(
                WebSocketClient::EVENT_SHOP_CALENDAR_SLOT_CHANGED,
                \App::t("common.notice.message", "Slot deleted."), [
                    "shop_id" => $this->shop_id,
                    "worker_id" => $this->worker_id,
                    "date" => $this->date,
                    "oldCalendarData" => CalendarForm::getSlotDataRemove($this),
                    "newCalendarData" => [],
                ]
            );
        }
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

    public function getBookingInfo()
    {
        return $this->hasOne(BookingInfo::className(), ['slot_id' => 'slot_id'])
            ->where(["NOT IN", BookingInfo::tableName() . ".status", [
                BookingInfo::STATUS_REJECTED,
                BookingInfo::STATUS_DELETED,
            ]]);
    }

    public function getWorkerMappingShop($shopId = null, $workerId = null)
    {
        return WorkerMappingShop::findOne(['shop_id' => $shopId, 'worker_id' => $workerId]);
    }

    public function getWorkerMapShop()
    {
        return $this->hasOne(WorkerMappingShop::className(), [
            'shop_id' => 'shop_id',
            'worker_id' => 'worker_id'
        ]);
    }

    public function getHtmlContent()
    {
        switch ($this->status) {
            case self::STATUS_ACTIVE:
                return \App::$app->formatter->asTime($this->start_time, 'short') . "<br/>$this->duration_minute phút";
                break;
            case self::STATUS_EXPIRED:
                return "Hết giờ<br/>$this->duration_minute phút";
                break;
            case self::STATUS_BOOKED:
                if ($this->bookingInfo) {
                    switch ($this->bookingInfo->status) {
                        case BookingInfo::STATUS_CANCELED:
                            $html = "Khách hàng đã hủy đặt lịch";
                            return $html;
                            break;
                        default:
                            $html = "<div class='custom-body'>";
                            $html .= "<p>" . \App::$app->formatter->asTime($this->start_time, 'short') . "</p>";
                            if (!$this->bookingInfo->memberInfo) {
                                $html .= "<p>-</p>";
                            } elseif (!empty($this->bookingInfo->memberInfo->phone_number)) {
                                $html .= "<p>" . $this->bookingInfo->memberInfo->phone_number . "</p>";
                            } else {
                                $html .= StringHelper::truncate($this->bookingInfo->memberInfo->full_name, $this->duration_minute > 30 ? 20 : 15, "..") ."<br/>";
                            }
                            $html .= "$this->duration_minute phút";
                            $html .= '</div>';
                            return $html;
                    }
                }
                break;
        }
        return "";
    }

    public function getIsExpired()
    {
        $maxTimeConfirmBooking = SystemConfig::getValue(SystemConfig::CATEGORY_BOOKING, SystemConfig::BOOKING_MAX_TIME_CONFIRM_ONLINE_BOOKING);
        return strtotime("$this->date $this->start_time + $maxTimeConfirmBooking minute") <= time();
    }

    public function getIsExpiredEditTime()
    {
        return strtotime("$this->date $this->start_time") - time() > 900;
    }

    public function getIsCanBooking()
    {
        return $this->status == self::STATUS_ACTIVE && !$this->isExpired;
    }

    public function getIsEditable()
    {
        if ($this->status == self::STATUS_ACTIVE) {
            if (!$this->isExpired) {
                return true;
            }
        } elseif ($this->status == self::STATUS_BOOKED && !$this->isExpired) {
            //todo check is OFFLINE booking
            $bookingInfo = BookingInfo::findOne([
                "slot_id" => $this->slot_id
            ]);
            return $bookingInfo != null;
        }
        return false;
    }

    public function getIsDeletable()
    {
        if ($this->status == self::STATUS_ACTIVE || $this->status == self::STATUS_EXPIRED) {
            return true;
        } elseif ($this->status == self::STATUS_BOOKED && !$this->isExpired) {
            //todo check is OFFLINE booking
            $bookingInfo = BookingInfo::findOne([
                "slot_id" => $this->slot_id
            ]);
            return $bookingInfo != null;
        }
        return false;
    }

    public function getColorSlot()
    {
        if ($this->bookingInfo) {
            return $this->bookingInfo->mappingBookingColorWithSystemConfig();
        }
//        if ($this->is_change_duration_minute) {
//            return SystemConfig::getColorToHtml(SystemConfig::ONLINE_PENDING_CHANGE);
//        }
        return SystemConfig::getColorToHtml(SystemConfig::SLOT_NONE);
    }

    public function extraFields()
    {
        return ArrayHelper::merge(parent::extraFields(), [
            "isCanBooking",
            "bookingInfo",
            "shopInfo",
            "workerInfo",
            "htmlContent",

            "isEditable",
            "isDeletable",
            "colorSlot",
        ]);
    }

    public function toArray(array $fields = [], array $expand = ["isCanBooking", "bookingInfo"], $recursive = true)
    {
        $expand = ArrayHelper::merge($expand, [
            "htmlContent",
            "shopInfo",
            "workerInfo",

            "isEditable",
            "isDeletable",
            "colorSlot",
        ]);
        $data = parent::toArray($fields, $expand, $recursive);
        return $data;
    }
}
