<?php
namespace backend\modules\system\forms\manager;

use common\entities\shop\ShopInfo;
use common\entities\user\UserConfig;
use common\entities\user\UserInfo;
use common\entities\user\UserPermission;
use common\helper\ArrayHelper;
use yii\data\ActiveDataProvider;
use yii\helpers\Json;

/**
 * Class ManagerForm
 * @package backend\modules\system\forms\manager
 *
 * @property string $raw_password
 * @property string $keyword
 */
class ManagerForm extends UserInfo
{
    public $raw_password, $shop_ids;
    public $keyword;

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['raw_password'], 'required'],
            [['raw_password'], 'string'],
            [['keyword'], 'safe'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'keyword' => \Yii::t('common.label', 'Từ khóa'),
            'raw_password' => \Yii::t('common.label', 'Mật khẩu'),
        ]);
    }

    /**
     * Todo search user
     * @return ActiveDataProvider
     */
    public function search($type = null)
    {
        $role = self::ROLE_MANAGER;
        if (!empty($type)) {
            $role = self::ROLE_OPERATOR;
        }
        $query = parent::find();
        $query->andWhere("status != :status_deleted", [
            ':status_deleted' => self::STATUS_DELETED
        ]);
        if ($this->user_id) {
            $users = UserInfo::find()
                ->where("user_id = :user_id", [
                    ':user_id' => $this->user_id
                ])
                ->one();
            $role = $users->role;
            $query->andWhere("user_id = :user_id", [
                ":user_id" => $this->user_id
            ]);
        }
        $query->andWhere("role = :role", [
            ":role" => $role
        ]);
        if ($this->status) {
            $query->andWhere("status = :status", [
                ":status" => $this->status
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

    public function toSave()
    {
        $trans = \App::$app->db->beginTransaction();
        $this->setPassword($this->raw_password);
        $this->username = $this->email;
        if ($this->validate()) {
            if ($this->save()) {
            }
            if (!$this->hasErrors()) {
                $trans->commit();
                return true;
            }
        }
        $trans->rollBack();
        return false;
    }

    public function toToggleStatus()
    {
        if ($this->status == self::STATUS_ACTIVE) {
            $this->status = self::STATUS_INACTIVE;
        } else {
            $this->status = self::STATUS_ACTIVE;
        }
        return $this->save(false);
    }


    public function toToggleModulePermission($module, $permission)
    {
        if (UserPermission::getValue($this->user_id, $module, $permission, 0) == 0) {
            return UserPermission::setValue($this->user_id, $module, $permission, 1);
        } else {
            return UserPermission::setValue($this->user_id, $module, $permission, 0);
        }
    }

    public function toToggleShop($shop_id)
    {
        $shopIds = Json::decode(UserConfig::getValue(UserConfig::KEY_MANAGE_SHOP_IDS, $this->user_id, "[]"));
        if ($this->isSelectedShop($shop_id)) {
            unset($shopIds[array_search($shop_id, $shopIds)]);
        } else {
            $shopIds[] = $shop_id;
        }
        return UserConfig::setValue(UserConfig::KEY_MANAGE_SHOP_IDS, $this->user_id, Json::encode($shopIds));
    }

    public function getListShops()
    {
        return ArrayHelper::map(ShopInfo::findAll([
            "status" => ShopInfo::STATUS_ACTIVE
        ]), "shop_id", "shop_name");
    }

    public function isSelectedShop($shopId)
    {
        $shopIds = Json::decode(UserConfig::getValue(UserConfig::KEY_MANAGE_SHOP_IDS, $this->user_id, "[]"));
        return ArrayHelper::isIn($shopId, $shopIds);
    }
}