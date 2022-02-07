<?php
namespace backend\modules\worker\forms;


use common\entities\calendar\BookingInfo;
use common\entities\calendar\Rating;
use common\entities\customer\CustomerInfo;
use common\entities\shop\ShopCalendarSlot;
use common\entities\shop\ShopInfo;
use common\entities\user\UserInfo;
use common\entities\worker\WorkerInfo;
use common\helper\ArrayHelper;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\web\User;

/**
 * @property string $filter_worker_id
 * @property integer $average_point
 */
class WorkerRatingHistoryForm extends Rating
{
    public $filter_worker_id, $filter_latest_rating;
    public $keyword;
    public $average_point, $total_point, $full_name, $created_at, $total_rating, $last_rating;

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [[
                "filter_worker_id", "filter_latest_rating"], 'safe'],
            [['keyword', 'full_name'], 'safe'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            "filter_worker_id" => Yii::t('app.attribute.rating.label', 'Worker'),
            "filter_user_id" => Yii::t('app.attribute.rating.label', 'Member'),
            "keyword" => Yii::t('app.attribute.keyword.label', 'Từ khóa'),
            "full_name" => Yii::t('app.attribute.full_name.label', 'Khách hàng'),
            "last_rating" => Yii::t('app.attribute.last_rating.label', 'Lần đánh giá cuối cùng'),
            "average_point" => Yii::t('app.attribute.average_point.label', 'Điểm đánh giá'),
            "total_point" => Yii::t('app.attribute.total_point.label', 'Tổng điểm đánh giá'),
            "total_booking" => Yii::t('app.attribute.total_booking.label', 'Số lượt sử dụng dịch vụ'),
            "total_rating" => Yii::t('app.attribute.total_rating.label', 'Số lượt đánh giá'),
        ]);
    }

    public function search()
    {
        $query = parent::find();
        $query->select([UserInfo::tableName() . '.full_name',
        UserInfo::tableName() . '.user_id',
        Rating::tableName() . '.worker_id',
        'CAST(AVG(behavior + technique + service + price + satisfaction) AS DECIMAL(18,2)) AS average_point',
        'SUM(behavior + technique + service + price + satisfaction) AS total_point',
        'COUNT(' . BookingInfo::tableName() . '.member_id) AS total_booking',
        'COUNT(' . Rating::tableName() . '.id) AS total_rating',
        'MAX(' . Rating::tableName() . '.created_at) AS last_rating',

        ]);
        $query->innerJoin(UserInfo::tableName(), Rating::tableName() . ".user_id = " . UserInfo::tableName() . ".user_id");
        $query->innerJoin( BookingInfo::tableName(), Rating::tableName() . '.booking_id = ' . BookingInfo::tableName() . ".booking_id");

        if ($this->filter_worker_id) {
            $query->andWhere([Rating::tableName() . ".worker_id" => $this->filter_worker_id]);
        }

        if ($this->keyword != null) {
            $query->andFilterWhere([
                'or',
                ['LIKE', UserInfo::tableName() . '.full_name', $this->keyword]
            ]);
        }
        if (!empty($this->filter_latest_rating)) {
            $queryRating = Rating::find()
                ->select('created_at')
                ->where(['worker_id' => $this->filter_worker_id])
                ->andWhere(['LIKE', 'created_at', $this->filter_latest_rating])
                ->distinct('created_at')
                ->asArray()
                ->all();
            $createdDays = \yii\helpers\ArrayHelper::getColumn($queryRating, 'created_at');
            $query->andWhere([
//                'LIKE', Rating::tableName() . '.created_at', $this->filter_latest_rating
                'LIKE', Rating::tableName() . '.created_at', $this->filter_latest_rating
            ]);
        }
        $query->groupBy(Rating::tableName(). '.user_id');

        $query->all();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $dataProvider->setSort([
            'defaultOrder' => ['full_name' => SORT_ASC],
            'attributes' => [
                'full_name',
                'average_point',
                'total_point',
                'total_booking',
                'last_rating',
                'total_rating'
            ]
        ]);

        return $dataProvider;
    }

    public function getTotalBooking()
    {
        $query = BookingInfo::find();
        $query->innerJoin(UserInfo::tableName(), UserInfo::tableName() . ".user_id = " . BookingInfo::tableName() . ".member_id");
        $query->innerJoin(ShopCalendarSlot::tableName(), ShopCalendarSlot::tableName() . ".slot_id = " . BookingInfo::tableName() . ".slot_id");
        $query->innerJoin(ShopInfo::tableName(), ShopCalendarSlot::tableName() . ".shop_id = " . ShopInfo::tableName() . ".shop_id");
        $query->innerJoin(WorkerInfo::tableName(), ShopCalendarSlot::tableName() . ".worker_id = " . WorkerInfo::tableName() . ".worker_id");
        $query->andWhere(BookingInfo::tableName() . ".status = :STATUS_ACCEPTED", [
            ':STATUS_ACCEPTED' => BookingInfo::STATUS_ACCEPTED
        ]);
        $query->andWhere(BookingInfo::tableName() . ".member_id = :user_id", [
            ':user_id' => $this->user_id,
        ]);
        return $query->count();
    }

    public function getLastRating()
    {
        $query = Rating::find();
        $query->select('created_at');
        $query->andWhere('user_id = :user_id', [':user_id' => $this->user_id]);
        $query->orderBy('created_at DESC');
        return $query->one()['created_at'];
    }

}