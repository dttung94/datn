<?php

namespace common\entities\user;

use common\entities\system\SystemConfig;
use common\models\base\AbstractObject;
use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "user_config".
 *
 * @property string $user_id
 * @property string $key
 * @property string $value
 *
 * @property string $created_at
 * @property string $modified_at
 *
 * @property UserInfo $user
 */
class UserConfig extends AbstractObject
{
    const
        KEY_MANAGE_SHOP_IDS = "MANAGE_SHOP_IDS";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_config';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'key'], 'required'],
            [['user_id', 'key'], 'unique', 'targetAttribute' => ['user_id', 'key']],
            ['key', 'in', 'range' => [
                self::KEY_MANAGE_SHOP_IDS,
            ]],
            [['value'], 'string'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserInfo::className(), 'targetAttribute' => ['user_id' => 'user_id']],
            [['created_at', 'modified_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('app.attribute.user_config.label', 'User'),
            'key' => Yii::t('app.attribute.user_config.label', 'Key'),
            'value' => Yii::t('app.attribute.user_config.label', 'Value'),
            'created_at' => Yii::t('app.attribute.user_config.label', 'Created At'),
            'modified_at' => Yii::t('app.attribute.user_config.label', 'Modified At'),
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->created_at = self::currentDatetime();
            }
            $this->modified_at = self::currentDatetime();
            return true;
        }
        return false;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(UserInfo::className(), ['user_id' => 'user_id']);
    }

    /**
     * @param $key
     * @param null $user_id
     * @param string $default
     * @return string
     */
    public static function getValue($key, $user_id = null, $default = "")
    {
        $user_id = ($user_id == null) ? self::currentUserId() : $user_id;
        $config = self::getConfig($key, $user_id);
        if ($config == null) {
            $config = new UserConfig();
            $config->key = $key;
            $config->user_id = $user_id;
            $config->value = $default;
            $config->save(false);
        }
        return $config->value;
    }

    /**
     * @param $key
     * @param null $user_id
     * @param $value
     * @return UserConfig|null
     */
    public static function setValue($key, $user_id = null, $value = "")
    {
        $user_id = ($user_id == null) ? self::currentUserId() : $user_id;
        $config = self::getConfig($key, $user_id);
        if ($config == null) {
            $config = new UserConfig();
            $config->key = $key;
            $config->user_id = $user_id;
        }
        $config->value = $value;
        $config->save(false);
        return $config;
    }

    /**
     * @param $key
     * @param null $user_id
     * @return null | UserConfig
     */
    public static function getConfig($key, $user_id = null)
    {
        $user_id = ($user_id == null) ? self::currentUserId() : $user_id;
        $config = self::find()
            ->where(self::tableName() . '.key = :key', [
                ':key' => $key,
            ])
            ->andWhere(self::tableName() . '.user_id = :user_id', [
                ':user_id' => $user_id
            ])
            ->one();
        return $config;
    }

}
