<?php
namespace backend\modules\system\forms\user;


use common\entities\user\UserInfo;
use common\entities\user\UserLog;
use common\helper\ArrayHelper;
use yii\data\ActiveDataProvider;
use Yii;

/**
 * Class UserLogForm
 * @package backend\modules\system\forms\user
 *
 * @property string $keyword
 * @property integer $filter_user_id
 * @property string $filter_action
 *
 * @property array $listUsers
 */
class UserLogForm extends UserLog
{
    public $keyword, $filter_user_id, $filter_action;

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [["keyword", "filter_user_id", "filter_action"], 'safe'],
        ]);
    }

    public function search()
    {
        $query = parent::search();
        if ($this->filter_user_id) {
            $query->andWhere(['=', 'user_id', $this->filter_user_id]);
        }
        if ($this->filter_action) {
            $query->andWhere(['=', 'action', $this->filter_action]);
        }
        if ($this->keyword != null) {
            $query->andWhere([
                'OR',
                ['LIKE', self::tableName() . '.message', $this->keyword],
                ['LIKE', self::tableName() . '.user_id', $this->keyword],
                ['LIKE', self::tableName() . '.action', $this->keyword],
            ]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            "pagination" => [
                "pageSize" => 500,
            ]
        ]);
        $dataProvider->setSort([
            'defaultOrder' => ['created_at' => SORT_DESC],
            'attributes' => [
                'action',
                'user_id',
                'created_at',
            ]
        ]);
        return $dataProvider;
    }

    public function attributeLabels()
    {
        return [
            'keyword' => Yii::t('app.attribute.user_log.label', 'キーワード'),
            'filter_user_id' => Yii::t('app.attribute.user_log.label', '会員'),
            'filter_action' => Yii::t('app.attribute.user_log.label', 'アクション'),
        ];
    }

    public function getListUsers()
    {
        return ArrayHelper::map(
            UserInfo::find()->where([
                "role" => UserInfo::ROLE_USER,
            ])->all(),
            "user_id",
            "full_name"
        );
    }
}