<?php
namespace backend\modules\calendar\forms\rating;

use common\entities\calendar\Rating;
use common\entities\user\UserConfig;
use common\entities\user\UserInfo;
use common\entities\worker\WorkerInfo;
use common\helper\ArrayHelper;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Query;

class RatingRankForm extends Rating
{
    public $filter_point_type ;
    public $point, $worker_name;
    public $count, $countS1;

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['filter_point_type'], 'safe']
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            "worker_name" => \Yii::t('app.attribute.worker_name.label', 'Tên nhân viên'),
            "point" => \Yii::t('app.attribute.point.label', 'Điểm đánh giá'),
            "count" => \Yii::t('app.attribute.point.label', 'Số lượt đánh giá'),
        ]);
    }

    public function search()
    {
        $query = parent::find();
        switch ($this->filter_point_type) {
            case null:
                $query->select([
                    WorkerInfo::tableName() . '.worker_name',
                    'SUM(behavior + technique + service + satisfaction + price) AS point',
                    WorkerInfo::tableName() . '.worker_id',
                    'COUNT(*)*5 as count',
                    'COUNT(*) as countS1',
                    ]);
                break;
            case 'behavior':
                $query->select([
                    WorkerInfo::tableName() . '.worker_name',
                    'SUM(behavior) AS point',
                    WorkerInfo::tableName() . '.worker_id',
                    'COUNT(*) as count',
                    'COUNT(*) as countS1',
                ]);
                break;
            case 'technique':
                $query->select([
                    WorkerInfo::tableName() . '.worker_name',
                    'SUM(technique) AS point',
                    WorkerInfo::tableName() . '.worker_id',
                    'COUNT(*) as count',
                    'COUNT(*) as countS1',
                ]);
                break;
            case 'service':
                $query->select([
                    WorkerInfo::tableName() . '.worker_name',
                    'SUM(service) AS point',
                    WorkerInfo::tableName() . '.worker_id',
                    'COUNT(*) as count',
                    'COUNT(*) as countS1',
                ]);
                break;
            case 'price':
                $query->select([
                    WorkerInfo::tableName() . '.worker_name',
                    'SUM(price) AS point',
                    WorkerInfo::tableName() . '.worker_id',
                    'COUNT(*) as count',
                    'COUNT(*) as countS1',
                ]);
                break;
            case 'satisfaction':
                $query->select([
                    WorkerInfo::tableName() . '.worker_name',
                    'SUM(satisfaction) AS point',
                    WorkerInfo::tableName() . '.worker_id',
                    'COUNT(*) as count',
                    'COUNT(*) as countS1',
                ]);
                break;
        }
        $query->innerJoin(WorkerInfo::tableName(), Rating::tableName() . '.worker_id = ' . WorkerInfo::tableName() . '.worker_id');
        $query->groupBy(Rating::tableName() . '.worker_id');
        $query->all();
        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);
        $dataProvider->setSort([
            'defaultOrder' => ['point' => SORT_DESC],
            'attributes' => [
                'point',
                'count',
            ]
        ]);
        return $dataProvider;
    }

    public function reminderSearch ()
    {
        $data = $this->getRemindedWorkerList();
        $array = array_count_values($data);
        arsort($array);
        $model = [];

        $workerInfos = WorkerInfo::find()->where(['in', WorkerInfo::tableName() . '.worker_id', array_keys($array)])->asArray()->all();
        if($workerInfos) {
            $dataWorkers = [];
            foreach($workerInfos as $key => $value) {
                $dataWorkers[$value['worker_id']] = [
                    'worker_name' => $value['worker_name'],
                ];
            }

            foreach ($array as $key => $value) {
                if(array_key_exists($key, $dataWorkers)) {
                    $model[] = [
                        'count_remind' => $value,
                        'worker_name' => $dataWorkers[$key]['worker_name'],
                        'worker_id' => $key,
                    ];
                }
            }
        }
        return $provider = new ArrayDataProvider([
            'allModels' => $model,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'attributes' => ['times']
            ]
        ]);
    }

    public function getRemindedWorkerList()
    {
        $subQuery = UserConfig::find();
        $subQuery->select('value');
        $subQuery->andWhere("`key` = 'RECEIVE_MAIL_WORKER_ID'");
        $results = $subQuery->all();
        $data = [];
        foreach ($results as $result) {
            $resultsArray = json_decode($result['value']);
            foreach ($resultsArray as $resultArray) {
                array_push($data, $resultArray);
            }
        }
        return $data;
    }
}
