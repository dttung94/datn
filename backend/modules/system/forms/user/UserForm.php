<?php
namespace backend\modules\system\forms\user;


use common\entities\resource\FileInfo;
use common\entities\user\UserConfig;
use common\entities\user\UserInfo;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * Class UserForm
 * @package backend\modules\system\models
 *
 * @property string $keyword
 */
class UserForm extends UserInfo
{
    public $keyword;

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['keyword', 'role', 'status'], 'safe'],
        ]);
    }

    /**
     * Todo search user
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = parent::find();
        $query->andWhere("status != :status_deleted", [
            ':status_deleted' => self::STATUS_DELETED
        ]);
        if ($this->status) {
            $query->andWhere("status = :status", [
                ":status" => $this->status
            ]);
        }
        if ($this->role) {
            $query->andWhere("role = :role", [
                ":role" => $this->role
            ]);
        }
        if ($this->keyword != null) {
            $query->andFilterWhere([
                'or',
                ['LIKE', static::tableName() . '.user_id', $this->keyword],
                ['LIKE', static::tableName() . '.username', $this->keyword],
                ['LIKE', static::tableName() . '.email', $this->keyword],
            ]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $dataProvider->setSort([
            'defaultOrder' => ['created_at' => SORT_DESC],
            'attributes' => [
                'user_id',
                'username',
                'email',
                'created_at',
            ]
        ]);

        return $dataProvider;
    }
}