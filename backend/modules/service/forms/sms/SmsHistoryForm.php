<?php
namespace backend\modules\service\forms\sms;


use common\entities\service\ServiceSms;
use yii\data\ActiveDataProvider;

/**
 * Class SmsHistoryForm
 * @package backend\modules\service\forms\sms
 */
class SmsHistoryForm extends ServiceSms
{
    public function search()
    {
        $query = parent::search();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        $dataProvider->setSort([
            'defaultOrder' => ['created_at' => SORT_DESC],
            'attributes' => [
                'to',
                'content',
                'status',
                'created_at',
            ]
        ]);

        return $dataProvider;
    }
}