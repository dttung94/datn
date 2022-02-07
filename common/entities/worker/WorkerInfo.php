<?php

namespace common\entities\worker;

use common\entities\calendar\BookingInfo;
use common\entities\calendar\Rating;
use common\entities\shop\ShopCalendar;
use common\entities\shop\ShopCalendarSlot;
use common\entities\shop\ShopConfig;
use common\entities\shop\ShopInfo;
use common\entities\system\SystemConfig;
use common\entities\user\UserConfig;
use common\entities\worker\WorkerMappingShop;
use common\entities\worker\WorkerConfig;
use common\helper\ArrayHelper;
use common\models\base\AbstractObject;
use Yii;
use yii\db\Query;

/**
 * This is the model class for table "worker_info".
 *
 * @property string $worker_id
 *
 * @property integer $worker_rank
 * @property string $worker_name
 * @property string $description
 * @property string $avatar
 * @property string $avatar_url
 *
 *
 * @property string $ref_id
 *
 * @property string $status
 * @property string $created_at
 * @property string $modified_at
 *
 * @property integer $bookingRepeatPercent
 * @property WorkerMappingShop[] $mappingShops
 * @property ShopInfo[] $shopInfos
 */
class WorkerInfo extends AbstractObject
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'worker_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['worker_name'], 'required'],
            [[ 'status'], 'integer'],
            [['worker_name', 'description', 'created_at','avatar_file', 'modified_at'], 'string'],
            [['worker_name', 'description', 'avatar_url', 'status', 'created_at', 'modified_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'worker_id' => Yii::t('app.attribute.worker_info.label', 'ID'),
            'worker_name' => Yii::t('app.attribute.worker_info.label', 'Tên nhân viên'),
            'description' => Yii::t('app.attribute.worker_info.label', 'Mô tả'),

            'status' => Yii::t('app.attribute.worker_info.label', 'Trạng thái'),
            'created_at' => Yii::t('app.attribute.worker_info.label', 'Thời điểm tạo'),
            'modified_at' => Yii::t('app.attribute.worker_info.label', 'Modified At'),

            'avatar_url' => Yii::t('app.attribute.worker_info.label', 'Ảnh nhân viên'),
            'history_rate' => Yii::t('app.attribute.worker_info.label', 'Đánh giá gần đây'),
            'totalAllBooking' => Yii::t('app.attribute.worker_info.label', 'Tổng số lượt book'),
            'shops' => Yii::t('app.attribute.worker_info.label', 'Cửa hàng đã đăng ký'),
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

    public function getMappingShops()
    {
        return $this->hasMany(WorkerMappingShop::className(), ["worker_id" => "worker_id"]);
    }

    public function getShopInfos()
    {
        return $this->hasMany(ShopInfo::className(), ['shop_id' => 'shop_id'])
            ->viaTable(WorkerMappingShop::tableName(), [
                "worker_id" => "worker_id"
            ])
            ->where([
                ShopInfo::tableName() . ".status" => ShopInfo::STATUS_ACTIVE,
            ]);
    }

    public function getRating()
    {
        return $this->hasMany(Rating::className(), ["worker_id" => "worker_id"]);
    }

    public function getTotalRating($ratings)
    {
        $totalRating = 0;
        if (count($ratings) > 0) {
            foreach ($ratings as $rating) {
                $temp = ($rating->behavior + $rating->technique + $rating->service + $rating->price + $rating->satisfaction)/5;
                $totalRating += $temp;
            }
        }
        return count($ratings) > 0 ? round($totalRating/count($ratings),2) : 0;
    }

    public function getRatings()
    {
        return $this->hasMany(Rating::className(), ['worker_id' => 'worker_id']);
    }

    public function isWorkingDay($date)
    {
        return ShopCalendar::find()
            ->where([
                "status" => ShopCalendar::STATUS_ACTIVE,
                "type" => ShopCalendar::TYPE_WORKING_DAY,
                "worker_id" => $this->worker_id,
                "date" => $date,
            ])
            ->exists();
    }

    /**
     * @param $date
     * @param null $shopIds
     * @return ShopCalendar[]
     */
    public function getSchedules($date, $shopIds = null)
    {
        $shopQuery = $this->hasMany(ShopInfo::className(), ['shop_id' => 'shop_id'])
            ->viaTable(WorkerMappingShop::tableName(), [
                "worker_id" => "worker_id"
            ]);
        if ($shopIds !== null) {
            $shopQuery->andWhere(["IN", ShopInfo::tableName() . ".shop_id", $shopIds]);
        }
        $schedules = [];
        $shops = $shopQuery->all();
        foreach ($shops as $shop) {
            /**
             * @var $shop ShopInfo
             */
            $model = ShopCalendar::findOne([
                "status" => ShopCalendar::STATUS_ACTIVE,
                "shop_id" => $shop->shop_id,
                "worker_id" => $this->worker_id,
                "date" => $date,
                "type" => ShopCalendar::TYPE_WORKING_DAY,
            ]);
            if ($model)
                $schedules[$shop->shop_id] = $model;
        }
        return $schedules;
    }

    public function getScheduleSecond($date, $shopIds = null, $flag = false)
    {
        $query = ShopCalendar::find()
            ->where([
                'status' => ShopCalendar::STATUS_ACTIVE,
                "date" => $date,
                "type" => ShopCalendar::TYPE_WORKING_DAY,
            ])
            ->andWhere(['IN', 'shop_id', $shopIds])
            ->all();
        if ($flag == false) {
            return $query;
        }

        $schedules = [];
        $startAts = [];
        foreach ($query as $value) {
            $startAts[(int)explode(':', $value->work_start_time)[0]] = true;
            $schedules[$value->worker_id][$value->work_start_time] = $value->shop_id;
            $schedules[$value->worker_id][$value->work_end_time] = $value->shop_id;
        }
        return [
            'schedules' => $schedules,
            'startTime' => !empty($startAts) ? min(array_keys($startAts)) : 0
        ];
    }

    public function getSlotSecond($date, $shop_id = null)
    {
        $query = ShopCalendarSlot::find()
            ->where(["date" => $date]);
        if ($shop_id != null) {
            $query->andWhere([
                "shop_id" => $shop_id
            ]);
        }
        return $query->orderBy("start_time ASC")->all();
    }

    public function getSlotDataSecond($date)
    {
        $result = [];
        $query = BookingInfo::find()
            ->where(['>', 'created_at', $date])
            ->andWhere(['!=', 'status', 0])
            ->all();
        foreach ($query as $value) {
            $result[$value->slotInfo->worker_id][$value->slotInfo->start_time] = $value;
        }
        return $result;
    }

    public function slotExists($date, $shop_id)
    {
        $slots = ShopCalendarSlot::find()
            ->where([
                "date" => $date,
                "shop_id" => $shop_id,
            ])
            ->orderBy("start_time ASC")
            ->with('workerInfo')
            ->with('bookingInfo')
            ->with('shopInfo')
            ->all();
        $result = [];
        foreach ($slots as $slot) {
            $result[$slot->worker_id][$slot->start_time] = $this->isWaitingConfirmSlot($slot);
        }
        return $result;
    }

    protected function isWaitingConfirmSlot($slot)
    {
        $response = $slot->toArray();
        if (
            ($slot->bookingInfo) &&
            ($slot->bookingInfo['status'] == BookingInfo::STATUS_PENDING || $slot->bookingInfo['status'] == BookingInfo::STATUS_UPDATING)
        ) {
            $totalSecondLeft =
                intval(SystemConfig::getValue(SystemConfig::CATEGORY_BOOKING, SystemConfig::BOOKING_MAX_TIME_CONFIRM_ONLINE_BOOKING)) -
                (time() - strtotime($slot->bookingInfo['modified_at']));
            $response["total_second_waiting_expired"] = $totalSecondLeft > 0 ? $totalSecondLeft : 0;
            $response["is_waiting_confirm"] = true;
        }
        return $response;
    }

    /**
     * @param $date
     * @param $shop_id
     * @return null|ShopCalendar
     */
    public function getScheduleOnShop($date, $shop_id)
    {
        $model = ShopCalendar::findOne([
            "shop_id" => $shop_id,
            "worker_id" => $this->worker_id,
            "date" => $date,
            "type" => ShopCalendar::TYPE_WORKING_DAY,
        ]);
        return $model;
    }

    /**
     * @param $date
     * @param null $shop_id => null to get all
     * @return ShopCalendarSlot[]
     */
    public function getSlots($date, $shop_id = null)
    {
        $query = ShopCalendarSlot::find()->where([
            "worker_id" => $this->worker_id,
            "date" => $date,
        ])->andWhere(['!=', 'status', ShopCalendarSlot::STATUS_DELETE]);
        if ($shop_id != null) {
            $query->andWhere([
                "shop_id" => $shop_id
            ]);
        }
        return $query->orderBy("start_time ASC")->all();
    }

    public function getStartTime($date)
    {
        $startTime = null;
        $shopCalendars = $this->getSchedules($date);
        foreach ($shopCalendars as $shopCalendar) {
            if ($startTime == null || strtotime($startTime) > strtotime($shopCalendar->work_start_time)) {
                $startTime = $shopCalendar->work_start_time;
            }
        }
        return $startTime;
    }

    public function getEndTime($date)
    {
        $endTime = null;
        $shopCalendars = $this->getSchedules($date);
        foreach ($shopCalendars as $shopCalendar) {
            if ($endTime == null || strtotime($endTime) < strtotime($shopCalendar->work_end_time)) {
                $endTime = $shopCalendar->work_end_time;
            }
        }
        return $endTime;
    }

    public static function getWorkers()
    {
        $workerIdBlackList = [];
//        $workerBlackList = UserConfig::findOne(['key' => UserConfig::KEY_BLACKLIST_WORKER_IDS, 'user_id' => Yii::$app->user->identity->user_id]);
//        if ($workerBlackList) {
//            $workerIdBlackList = json_decode($workerBlackList->value);
//        }

        return ArrayHelper::map(
            WorkerInfo::find()->where(['status' => WorkerInfo::STATUS_ACTIVE])->all(),
            "worker_id", "worker_name"
        );
    }

    public static function getBookingRepeatPercent($workerId)
    {
        $bookingInfos = BookingInfo::find()
            ->innerJoin(ShopCalendarSlot::tableName(), ShopCalendarSlot::tableName() . ".slot_id = " . BookingInfo::tableName() . ".slot_id")
            ->where([
                BookingInfo::tableName() . ".status" => BookingInfo::STATUS_ACCEPTED,
                ShopCalendarSlot::tableName() . ".worker_id" => $workerId,
            ])
            ->all();
        $newBookings = [];
        $totalRepeat = 0;
        foreach ($bookingInfos as $bookingInfo) {
            /**
             * @var $bookingInfo BookingInfo
             */
            if (!isset($newBookings[$bookingInfo->member_id])) {
                $newBookings[$bookingInfo->member_id] = $bookingInfo;
            } else {
                $totalRepeat += 1;
            }
        }
        return (count($bookingInfos) > 0) ? $totalRepeat / count($bookingInfos) : 0;
    }

    public static function getWorkersInfo($shopId, $listWorker, $date) {
        return (new Query())
            ->from(['wi' => self::tableName()])
            ->andWhere(['wi.status' => self::STATUS_ACTIVE])
            ->innerJoin(['wms' => WorkerMappingShop::tableName()], 'wi.worker_id'.' = wms.worker_id')
            ->leftJoin(['sc' => ShopCalendar::tableName()], 'wi.worker_id = sc.worker_id')
//            ->leftJoin(['wc' => WorkerConfig::tableName()], "wi.worker_id = wc.worker_id and wc.key = '".WorkerConfig::KEY_IS_SHOW_RANK_NAME."'")
            ->select([
                'wi.worker_id',
                'wi.worker_name',
                'wi.avatar_url',
                'sc.work_start_time',
                'sc.work_end_time',
//                'IFNULL(wc.value, 0) as is_show_rank_name'
            ])
            ->where(['in', 'wi.worker_id', $listWorker])
            ->andWhere(["wms.shop_id" => $shopId])
            ->andWhere([
                "sc.shop_id" => $shopId,
                "sc.date" => $date,
                "sc.type" => ShopCalendar::TYPE_WORKING_DAY
            ])
            ->all();
    }

    public function extraFields()
    {
        return ArrayHelper::merge(parent::extraFields(), [
            "shopInfos",
        ]);
    }

    public function toArray(array $fields = [], array $expand = ["shopInfos"], $recursive = true)
    {
        $data = parent::toArray($fields, $expand, $recursive);
//        $data[WorkerConfig::KEY_WORKER_NOTE] = WorkerConfig::getValue(WorkerConfig::KEY_WORKER_NOTE, $this->worker_id, "");
        return $data;
    }

    /**
     * get worker by id
     * @param $user_id
     * @return WorkerInfo|null
     */
    public static function getWorkerById($worker_id)
    {
        return WorkerInfo::findOne([
            "worker_id" => $worker_id,
        ]);
    }

    /**
     * Check worker is active or not
     *
     * @param $worker_id
     * @return bool
     */
    public static function checkWorkerActive($worker_id)
    {
        $worker = self::getWorkerById($worker_id);
        if (empty($worker) || $worker->status != self::STATUS_ACTIVE) {
            return false;
        }

        return true;
    }
}
