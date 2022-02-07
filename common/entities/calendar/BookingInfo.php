<?php
namespace common\entities\calendar;

use common\entities\shop\ShopCalendarSlot;
use common\entities\system\SystemConfig;
use common\entities\user\UserInfo;
use common\helper\ArrayHelper;
use common\helper\StringHelper;
use common\models\base\AbstractObject;
use Yii;

/**
 * This is the model class for table "booking_info".
 *
 * @property integer $booking_id
 * @property string $frequency
 *
 * @property integer $member_id
 * @property integer $customer_id
 * @property integer $member_id_booking_offline
 *
 * @property integer $slot_id
 * @property integer $course_id
 * @property integer $duration_minute
 *
 * @property double $cost
 *
 * @property string $comment
 * @property string $note
 *
 * @property integer $status
 * @property string $created_at
 * @property string $modified_at
 *
 * @property UserInfo $memberInfo
 * @property CourseInfo $courseInfo
 * @property ShopCalendarSlot $slotInfo
 *
 * @property boolean $isAcceptable
 * @property boolean $isRejectAble
 * @property boolean $isEditable
 * @property boolean $isCancelable
 * @property boolean $isDeletable
 * @property boolean $isCanSendSMS
 * @property boolean $isUpdatable
 * @property boolean $isUpdateRejectAble
 * @property boolean $isCancelableForUser
 */
class BookingInfo extends AbstractObject
{
    const
        BOOKING_CANCEL_REQUEST = "Hủy yêu cầu đặt lịch",
        BOOKING_CANCEL_SLOT = "Hủy đặt lịch",
        BOOKING_TIME_EXPIRED_REJECT = "Hết thời gian phê duyệt",
        BOOKING_REJECT = "Từ chối đặt lịch",
        BOOKING_ONLINE = "Đặt chỗ online",
        BOOKING_ONLINE_UPDATE = "Cập nhật lượt đặt lịch";
    const
        STATUS_PENDING = 1,
        STATUS_CONFIRMING = 2,
        STATUS_ACCEPTED = 3,
        STATUS_CANCELED = 4,
        STATUS_EXPIRED = 8,
        STATUS_REJECTED = 9,
        STATUS_DELETED = 0,
        STATUS_UPDATING = 5;

    public static function getListCourse()
    {
        return ArrayHelper::map(
            CourseInfo::find()->all(),
            "course_id",
            "course_name"
        );
    }

    public static function getListCourseType()
    {
        return ArrayHelper::map(
            CourseInfo::find()
                ->where([
                    "status" => CourseInfo::STATUS_ACTIVE
                ])
                ->all(),
            "course_id",
            "course_name"
        );
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'booking_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['slot_id', 'course_id', 'cost'], 'required'],
            [['course_id'], 'exist', 'skipOnError' => true, 'targetClass' => CourseInfo::className(), 'targetAttribute' => ['course_id' => 'course_id']],
            [['member_id'], 'required'],

            ['status', 'in', 'range' => [
                self::STATUS_PENDING,
                self::STATUS_CONFIRMING,
                self::STATUS_ACCEPTED,
                self::STATUS_CANCELED,
                self::STATUS_REJECTED,
                self::STATUS_EXPIRED,
                self::STATUS_DELETED,
                self::STATUS_UPDATING,
            ]],
            [['member_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserInfo::className(), 'targetAttribute' => ['member_id' => 'user_id']],
            [['slot_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopCalendarSlot::className(), 'targetAttribute' => ['slot_id' => 'slot_id']],
            [['cost'], 'double'],
            [['booking_id', 'member_id', 'slot_id', 'status'], 'integer'],
            [[ 'comment', 'note', 'created_at', 'modified_at'], 'string'],
            [['booking_id', 'member_id','slot_id', 'cost', 'comment', 'note', 'status', 'created_at', 'modified_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'booking_id' => Yii::t('app.attribute.booking_info.label', 'ID'),

            'course_id' => Yii::t('app.attribute.booking_info.label', 'Dịch vụ'),
            'frequency' => Yii::t('app.attribute.booking_info.label', '指名'),

            'member_id' => Yii::t('app.attribute.booking_info.label', 'Member'),
            'customer_id' => Yii::t('app.attribute.booking_info.label', 'Khách hàng'),

            'slot_id' => Yii::t('app.attribute.booking_info.label', 'Slot'),
            'cost' => Yii::t('app.attribute.booking_info.label', 'Phí dịch vụ'),
            'comment' => Yii::t('app.attribute.booking_info.label', 'Ghi chú của khách'),
            'note' => Yii::t('app.attribute.booking_info.label', 'Ghi chú'),

            'status' => Yii::t('app.attribute.booking_info.label', 'Status'),
            'created_at' => Yii::t('app.attribute.booking_info.label', 'Created At'),
            'modified_at' => Yii::t('app.attribute.booking_info.label', 'Modified At'),
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

    public function getSlotInfo()
    {
        return $this->hasOne(ShopCalendarSlot::className(), ["slot_id" => "slot_id"]);
    }

    public function getMemberInfo()
    {
        return $this->hasOne(UserInfo::className(), ["user_id" => "member_id"]);
    }

    public function getCourseInfo()
    {
        return $this->hasOne(CourseInfo::className(), ["course_id" => "course_id"]);
    }

    public function getCoursesInfo()
    {
        return CourseInfo::find()->all();
    }

    public function getRating()
    {
        return $this->hasOne(Rating::className(), ['booking_id' => 'booking_id']);
    }

    public function getBookingData()
    {
        return $this->hasMany(BookingData::className(), ['booking_id' => 'booking_id']);
    }

    public function getIsAcceptable()
    {
        if ($this->status == self::STATUS_PENDING || $this->status == self::STATUS_UPDATING) {
            if (!$this->slotInfo->isExpired) {
                return true;
            }
            $this->status = self::STATUS_EXPIRED;
            $this->save(false);
        }
        return false;
    }

    public function getIsUpdatable()
    {
        if ($this && $this->slotInfo) {
            if ($this->status == self::STATUS_ACCEPTED && $this->slotInfo->isExpiredEditTime) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function getIsUpdateRejectAble()
    {
        if ($this->status == self::STATUS_UPDATING) {
            return true;
        }
        return false;
    }

    public function getIsRejectAble()
    {
        if ($this->status == self::STATUS_PENDING || $this->status == self::STATUS_UPDATING) {
            if (!$this->slotInfo->isExpired) {
                return true;
            }
            $this->status = self::STATUS_EXPIRED;
            $this->save(false);
        }
        return false;
    }

    public function getIsEditable()
    {
        if ($this->status == self::STATUS_PENDING || $this->status == self::STATUS_UPDATING) {
            if (!$this->slotInfo->isExpired) {
                return true;
            }
            $this->status = self::STATUS_EXPIRED;
            $this->save(false);
        } else if ($this->status == self::STATUS_ACCEPTED) {
            return true;
        }
        return false;
    }

    public function getIsCancelable()
    {
        if (
            $this->status == self::STATUS_PENDING ||
            $this->status == self::STATUS_CONFIRMING ||
            $this->status == self::STATUS_ACCEPTED ||
            $this->status == self::STATUS_UPDATING
        ) {
            return true;
        }
        return false;
    }

    public function getIsCancelableForUser()
    {
        if ( $this->status != self::STATUS_ACCEPTED) {
            return false;
        }
        return true;
    }

    public function getIsDeletable()
    {
        if (
            $this->status == self::STATUS_EXPIRED ||
            $this->status == self::STATUS_CANCELED ||
            $this->status == self::STATUS_UPDATING
        ) {
            return true;
        }
        return false;
    }

    public function getIsCanSendSMS()
    {
        if ($this->status == self::STATUS_ACCEPTED) {
            return true;
        }
        return false;
    }

    public function mappingBookingColorWithSystemConfig()
    {
        switch (true) {
            case ($this->status == self::STATUS_PENDING):
                return SystemConfig::getColorToHtml(SystemConfig::ONLINE_PENDING);
                break;
            case ($this->status == self::STATUS_ACCEPTED):
                return SystemConfig::getColorToHtml(SystemConfig::ONLINE_ACCEPTED);
                break;
            case ($this->status == self::STATUS_CANCELED):
                return SystemConfig::getColorToHtml(SystemConfig::ONLINE_CANCELED);
                break;
            case ($this->status == self::STATUS_UPDATING):
                return SystemConfig::getColorToHtml(SystemConfig::ONLINE_UPDATING);
                break;

            case ($this->status == self::STATUS_CONFIRMING):
                return SystemConfig::getColorToHtml(SystemConfig::FREE_CONFIRMING);
                break;
        }
        return "";
    }

    public function getDurationMinute() {
        return SystemConfig::getValue(SystemConfig::CONFIG_DURATION_TIME_COURSE, SystemConfig::DURATION_TIME);
    }



    public function extraFields()
    {
        return ArrayHelper::merge(parent::extraFields(), [
            "coursesInfo",
            'courseInfo',
            "slotInfo",
            "memberInfo",
            "durationMinute",

            "isAcceptable",
            "isRejectAble",
            "isEditable",
            "isUpdatable",
            "isCancelable",
            "isDeletable",
            "isCanSendSMS",
            "isUpdateRejectAble",
            "isCancelableForUser",
        ]);
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        $expand = ArrayHelper::merge($expand, [
            "coursesInfo",
            "courseInfo",
            "slotInfo",
            "memberInfo",
            "durationMinute",

            "isAcceptable",
            "isRejectAble",
            "isEditable",
            "isUpdatable",
            "isCancelable",
            "isDeletable",
            "isCanSendSMS",
            "isUpdateRejectAble",
            "isCancelableForUser",
        ]);
        return parent::toArray($fields, $expand, $recursive);
    }
}
