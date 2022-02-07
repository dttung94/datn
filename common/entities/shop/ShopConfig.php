<?php

namespace common\entities\shop;

use common\models\base\AbstractObject;
use Yii;

/**
 * This is the model class for table "shop_config".
 *
 * @property string $shop_id
 * @property string $key
 * @property string $value
 *
 * @property string $created_at
 * @property string $modified_at
 *
 * @property ShopInfo $shopInfo
 */
class ShopConfig extends AbstractObject
{
    const
        KEY_SHOP_OPEN_DOOR_AT = "SHOP_START_AT",
        KEY_SHOP_CLOSE_DOOR_AT = "SHOP_END_AT",
        KEY_SHOP_WORKING_DAY_ON_WEEK = "SHOP_WORKING_DAY_ON_WEEK",
        KEY_SHOP_ALLOW_FREE_BOOKING = "SHOP_ALLOW_FREE_BOOKING",
        KEY_SHOP_BOOKING_TOMORROW_AT = "SHOP_BOOKING_TOMORROW_AT",
        KEY_SHOP_TIME_ON_USER_BOOKING = "SHOP_TIME_ON_USER_BOOKING",
        KEY_SHOP_ALLOW_BLOCK_BOOKING = "SHOP_ALLOW_BLOCK_BOOKING",
        KEY_SHOP_COLOR = "SHOP_COLOR";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_config';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_id', 'key'], 'required'],
            [['shop_id', 'key'], 'unique', 'targetAttribute' => ['shop_id', 'key']],
            [['key'], 'in', 'range' => [
                self::KEY_SHOP_OPEN_DOOR_AT,
                self::KEY_SHOP_CLOSE_DOOR_AT,
                self::KEY_SHOP_WORKING_DAY_ON_WEEK,
                self::KEY_SHOP_ALLOW_FREE_BOOKING,
                self::KEY_SHOP_BOOKING_TOMORROW_AT,
                self::KEY_SHOP_TIME_ON_USER_BOOKING,
                self::KEY_SHOP_COLOR,
            ]],
            [['value'], 'string'],
            [['shop_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopInfo::className(), 'targetAttribute' => ['shop_id' => 'shop_id']],
            [['created_at', 'modified_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'shop_id' => Yii::t('app.attribute.shop_config.label', 'Shop'),
            'key' => Yii::t('app.attribute.shop_config.label', 'Key'),
            'value' => Yii::t('app.attribute.shop_config.label', 'Value'),
            'created_at' => Yii::t('app.attribute.shop_config.label', 'Created At'),
            'modified_at' => Yii::t('app.attribute.shop_config.label', 'Modified At'),
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
    public function getShopInfo()
    {
        return $this->hasOne(ShopInfo::className(), ['shop_id' => 'shop_id']);
    }

    /**
     * @param $key
     * @param null $shop_id
     * @param string $default
     * @return string
     */
    public static function getValue($key, $shop_id, $default = "")
    {
        $config = self::getConfig($key, $shop_id);
        if ($config == null) {
            return $default;
        }
        return $config->value;
    }

    /**
     * @param $key
     * @param null $shop_id
     * @param $value
     * @return ShopConfig|null
     */
    public static function setValue($key, $shop_id, $value = "")
    {
        $config = self::getConfig($key, $shop_id);
        if ($config == null) {
            $config = new ShopConfig();
            $config->key = $key;
            $config->shop_id = $shop_id;
        }
        $config->value = $value;
        $config->save(false);
        return $config;
    }

    /**
     * @param $key
     * @param null $shop_id
     * @return null | ShopConfig
     */
    public static function getConfig($key, $shop_id)
    {
        $config = self::find()
            ->where(self::tableName() . '.key = :key', [
                ':key' => $key,
            ])
            ->andWhere(self::tableName() . '.shop_id = :shop_id', [
                ':shop_id' => $shop_id
            ])
            ->one();
        return $config;
    }

    public static function removeConfig($key, $shop_id)
    {
        $model = self::getConfig($key, $shop_id);
        if ($model) {
            return $model->delete();
        }
        return true;
    }

    public static function isExist($key, $shop_id)
    {
        return self::getConfig($key, $shop_id) != null;
    }

    public static function getAllShopColor()
    {
        return self::findAll(['key' => ShopConfig::KEY_SHOP_COLOR]);
    }
}