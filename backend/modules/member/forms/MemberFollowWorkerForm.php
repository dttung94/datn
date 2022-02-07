<?php

namespace backend\modules\member\forms;

use common\entities\user\UserConfig;
use common\entities\user\UserInfo;
use common\helper\ArrayHelper;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;

/**
 * Class MemberFollowWorkerForm
 * @package backend\modules\member\forms
 *
 * @property string $keyword
 */

class MemberFollowWorkerForm extends UserInfo
{
    public $keyword;

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['keyword'], 'safe']
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'keyword' => \Yii::t('common.label', 'Keyword')
        ]);
    }

    public function searchUserRemind($workerId)
    {
        $query = parent::find();
        $query->innerJoin(UserConfig::tableName(), UserInfo::tableName() . '.user_id = ' . UserConfig::tableName() . '.user_id')
            ->where(['key' => UserConfig::KEY_RECEIVE_MAIL_WORKER_ID])->andFilterWhere(['LIKE', 'value', $workerId]);
        if ($this->keyword != null) {
            $query->andFilterWhere([
                'or',
                ['LIKE', 'username', $this->keyword],
                ['LIKE', 'full_name', $this->keyword],
            ]);
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $query->all(),
            'pagination' => [
                'defaultPageSize' => 30,
            ],
        ]);

        $dataProvider->setSort([
            'defaultOrder' => ['username' => SORT_DESC],
            'attributes' => [
                'username',
            ],
        ]);
        return $dataProvider;
    }
}
