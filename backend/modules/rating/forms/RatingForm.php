<?php

namespace backend\modules\rating\forms;

use common\entities\calendar\Rating;
use common\entities\user\UserInfo;
use common\entities\worker\WorkerInfo;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;

/**
 *
 * @property string $worker_name
 */
class RatingForm extends Rating
{
    public $worker_name;

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['worker_name'], 'safe'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'worker_name' => \Yii::t('common.label', 'Worker name')
        ]);
    }

    public function search()
    {
        $query = parent::find();
        $query = $query->with([ 'user',
                                'worker',
        ]);
        $query->andWhere(['not', ['memo' => null]])->andWhere([Rating::tableName() .'.status' => Rating::STATUS_ACTIVE]);

        if ($this->created_at) {
            $query->andWhere(['date( ' . Rating::tableName() . '.created_at)' => $this->created_at]);
        }

        if ($this->worker_name != null) {
            $query->innerJoin(WorkerInfo::tableName(), WorkerInfo::tableName() . '.worker_id = ' . parent::tableName() . '.worker_id');
            $query->innerJoin(UserInfo::tableName(), UserInfo::tableName() . '.user_id = ' . parent::tableName() . '.user_id');
            $query->andFilterWhere([
                'or',
                ['LIKE', WorkerInfo::tableName() . '.worker_name', $this->worker_name],
                ['LIKE', UserInfo::tableName() . '.full_name', $this->worker_name],
            ]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => 30,
            ],
        ]);
        $dataProvider->setSort([
            'defaultOrder' => ['created_at' => SORT_DESC],
            'attributes' => [
                'created_at',
            ],
        ]);
        return $dataProvider;
    }
}
