<?php

namespace common\entities\calendar;

use common\entities\user\UserInfo;
use common\entities\worker\WorkerInfo;
use common\helper\ArrayHelper;
use common\models\base\AbstractObject;
use Yii;

/**
 * This is the model class for table "rating".
 *
 * @property int $id
 * @property int $user_id
 * @property int $worker_id
 * @property int $booking_id
 * @property int $behavior
 * @property int $technique
 * @property int $service
 * @property int $price
 * @property int $satisfaction
 * @property int
 * @property string|null $memo
 * @property string $created_at
 * @property string $modified_at
 *
 * @property BookingInfo $booking
 * @property UserInfo $user
 * @property WorkerInfo $worker
 */
class Rating extends AbstractObject
{
    /**
     * {@inheritdoc}
     */

    public static function tableName()
    {
        return 'rating';
    }

    public function getWorkerInfo()
    {
        return $this->hasOne(WorkerInfo::className(), ['worker_id' => 'worker_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'worker_id', 'booking_id', 'price', 'behavior', 'technique', 'service', 'satisfaction'], 'required'],
            [['created_at'], 'safe'],
            [['memo'], 'string', 'max' => 255],
            [['booking_id'], 'exist', 'skipOnError' => true, 'targetClass' => BookingInfo::className(), 'targetAttribute' => ['booking_id' => 'booking_id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserInfo::className(), 'targetAttribute' => ['user_id' => 'user_id']],
            [['worker_id'], 'exist', 'skipOnError' => true, 'targetClass' => WorkerInfo::className(), 'targetAttribute' => ['worker_id' => 'worker_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'worker_id' => 'Worker ID',
            'booking_id' => 'Booking ID',
            'behavior' => 'Thái độ phục vụ',
            'technique' => 'Kỹ thuật',
            'service' => 'Dịch vụ',
            'satisfaction' => 'Mức độ hài lòng',
            'price' => 'Chi phí dịch vụ',
            'memo' => 'Ghi chú',
            'created_at' => 'Created At',
            'modified_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Booking]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBooking()
    {
        return $this->hasOne(BookingInfo::className(), ['booking_id' => 'booking_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(UserInfo::className(), ['user_id' => 'user_id']);
    }

    /**
     * Gets query for [[Worker]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWorker()
    {
        return $this->hasOne(WorkerInfo::className(), ['worker_id' => 'worker_id']);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->created_at = $this->currentDatetime();
            }
            return true;
        }
        return false;
    }

    public function getBookingInfo()
    {
        return $this->hasOne(BookingInfo::className(), ['booking_id' => 'booking_id']);
    }

    public static function getListRatingField()
    {
        return [
            "behavior" => \App::t("backend.rating-ranking.label", "Thái độ phục vụ"),
            "technique" => \App::t("backend.rating-ranking.label", "Kỹ thuật"),
            "service" => \App::t("backend.rating-ranking.label", "Dịch vụ"),
            "price" => \App::t("backend.rating-ranking.label", "Giá dịch vụ"),
            "satisfaction" => \App::t("backend.rating-ranking.label", "Mức độ hài lòng"),
        ];
    }
}