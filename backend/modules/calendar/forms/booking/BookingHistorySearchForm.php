<?php
namespace backend\modules\calendar\forms\booking;


use common\entities\calendar\BookingInfo;
use common\entities\customer\CustomerInfo;
use common\entities\shop\ShopCalendarSlot;
use common\entities\shop\ShopInfo;
use common\entities\worker\WorkerInfo;
use common\entities\user\UserInfo;
use common\helper\ArrayHelper;
use yii\data\ActiveDataProvider;
use Yii;

/**
 * Class BookingHistorySearchForm
 * @package backend\modules\calendar\forms\booking
 *
 * @property string $filter_booking_type
 * @property string $filter_course_id
 * @property string $filter_user_id
 * @property string $filter_shop_id
 * @property string $filter_worker_id
 * @property string $keyword
 */
class BookingHistorySearchForm extends BookingInfo
{
    public $filter_booking_type, $filter_course_id, $filter_user_id, $filter_shop_id, $filter_worker_id;
    public $keyword;

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [[
                "filter_booking_type", "filter_course_id",
                "filter_user_id", "filter_shop_id", "filter_worker_id"], 'safe'],
            [['keyword'], 'safe'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            "filter_booking_type" => Yii::t('app.attribute.booking_info.label', 'Booking type'),
            "filter_course_id" => Yii::t('app.attribute.booking_info.label', 'Course type'),
            "filter_user_id" => Yii::t('app.attribute.user_info.label', 'Member'),
            "filter_shop_id" => Yii::t('app.attribute.booking_info.label', 'Shop'),
            "filter_worker_id" => Yii::t('app.attribute.booking_info.label', 'Worker'),
            "keyword" => Yii::t('app.attribute.keyword.label', 'Từ khóa'),
            "booking_type" => Yii::t('app.attribute.booking_type.label', 'Thể loại'),
            "shop_name" => Yii::t('app.attribute.shop_name.label', 'Tiệm salon'),
            "worker_name" => Yii::t('app.attribute.worker_name.label', 'Tên nhân viên'),
            "date" => Yii::t('app.attribute.date.label', 'Ngày đặt lịch'),
        ]);
    }

    public function search()
    {
        $query = parent::find();
        $query->innerJoin(UserInfo::tableName(), UserInfo::tableName() . ".user_id = " . self::tableName() . ".member_id");
        $query->innerJoin(ShopCalendarSlot::tableName(), ShopCalendarSlot::tableName() . ".slot_id = " . self::tableName() . ".slot_id");
        $query->innerJoin(ShopInfo::tableName(), ShopCalendarSlot::tableName() . ".shop_id = " . ShopInfo::tableName() . ".shop_id");
        $query->innerJoin(WorkerInfo::tableName(), ShopCalendarSlot::tableName() . ".worker_id = " . WorkerInfo::tableName() . ".worker_id");
        $query->andWhere(self::tableName() . ".status = :STATUS_ACCEPTED", [
            ':STATUS_ACCEPTED' => self::STATUS_ACCEPTED
        ]);
        if ($this->filter_user_id) {
            $user = UserInfo::findOne($this->filter_user_id);
            $query->andWhere([UserInfo::tableName() . ".phone_number" => $user->phone_number]);
        }
        if ($this->keyword != null) {
            $query->andFilterWhere([
                'or',
                ['LIKE', static::tableName() . '.note', $this->keyword],
                ['LIKE', static::tableName() . '.comment', $this->keyword],
            ]);
        }
        if ($this->filter_booking_type) {
            $query->andWhere(["=", self::tableName() . ".booking_type", $this->filter_booking_type]);
        }
        if ($this->filter_course_id) {
            $query->andWhere(["=", self::tableName() . ".course_id", $this->filter_course_id]);
        }
        if ($this->filter_shop_id) {
            $query->andWhere(["=", ShopInfo::tableName() . ".shop_id", $this->filter_shop_id]);
        }
        if ($this->filter_worker_id) {
            $query->andWhere(["=", WorkerInfo::tableName() . ".worker_id", $this->filter_worker_id]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $dataProvider->setSort([
            'defaultOrder' => ['created_at' => SORT_DESC],
            'attributes' => [
            ]
        ]);

        return $dataProvider;
    }

    public static function getListCustomer()
    {
        return ArrayHelper::map(
            UserInfo::find()
                ->where(['!=', 'status', UserInfo::STATUS_DELETED])
                ->andWhere(['role' => UserInfo::ROLE_USER])
                ->all(),
            "user_id", "username"
        );
    }

    public static function getListShop()
    {
        return ArrayHelper::map(ShopInfo::findAll([
            "status" => ShopInfo::STATUS_ACTIVE
        ]), "shop_id", "shop_name");
    }

    public static function getListWorker()
    {
        return ArrayHelper::map(WorkerInfo::findAll([
            "status" => WorkerInfo::STATUS_ACTIVE
        ]), "worker_id", "worker_name");
    }
}