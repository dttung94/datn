<?php

namespace common\entities\system;

use common\helper\DatetimeHelper;
use common\models\base\AbstractObject;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "system_sort_url".
 *
 * @property string $id
 * @property string $url
 * @property string $description
 * @property string $expired_at
 * @property integer $total_access
 *
 * @property string $status
 * @property string $created_at
 * @property string $modified_at
 */
class SystemSortURL extends AbstractObject
{
    const
        STATUS_EXPIRED = -2;

    public static function genId($length = 6)
    {
        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxy";
        do {
            $code = "";
            for ($i = 0; $i < $length; $i++) {
                $code .= $chars[mt_rand(0, strlen($chars) - 1)];
            }
        } while (self::find()->where([
            "id" => $code
        ])->exists());
        return $code;
    }

    public function setExpireDate($hours = 1)
    {
        $this->expired_at = DatetimeHelper::seconds2Datetime(time() + $hours * 60 * 60);
    }

    public function isValid()
    {
        if ($this->status == self::STATUS_ACTIVE) {
            if (strtotime($this->expired_at) > time()) {
                return true;
            } else {
                $this->status = self::STATUS_EXPIRED;
                $this->save();
            }
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'system_short_url';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'url'], 'required'],
            [['id'], 'unique'],
            [['url'], 'unique'],
            [['id', 'url', 'description', 'expired_at'], 'string'],
            [['total_access', 'status'], 'integer'],
            [['status'], 'in', 'range' => [
                self::STATUS_ACTIVE,
                self::STATUS_INACTIVE,
                self::STATUS_EXPIRED,
            ]],
            [['status', 'created_at', 'modified_at'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app.attribute.system_sort_url', 'ID'),
            'url' => Yii::t('app.attribute.system_sort_url', 'URL'),
            'value' => Yii::t('app.attribute.system_sort_url', 'Value'),
            'description' => Yii::t('app.attribute.system_sort_url', 'Description'),

            'status' => Yii::t('app.attribute.system_sort_url', 'Status'),
            'created_at' => Yii::t('app.attribute.system_sort_url', 'Created at'),
            'modified_at' => Yii::t('app.attribute.system_sort_url', 'Modified at'),
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                if (empty($this->status)) {
                    $this->status = self::STATUS_ACTIVE;
                }
                $this->created_at = self::currentDatetime();
            }
            $this->modified_at = self::currentDatetime();
            return true;
        }
        return false;
    }
}
