<?php

namespace common\entities\service;

use common\models\base\AbstractObject;
use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "service_sms".
 *
 * @property integer $sms_id
 *
 * @property string $content
 * @property string $params
 * @property string $to
 * @property string $tag
 * @property string $result
 *
 * @property string $status
 * @property string $created_at
 * @property string $modified_at
 *
 * @property array $tagJson
 * @property array $paramsJson
 * @property string $smsContent
 */
class ServiceSms extends AbstractObject
{
    const
        STATUS_PENDING = 4,
        STATUS_SENT = 5,
        STATUS_FAILED = 6,
        STATUS_REJECT = 7;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'service_sms';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content', 'to', 'status'], 'required'],
            ['status', 'in', 'range' => [
                self::STATUS_PENDING,
                self::STATUS_SENT,
                self::STATUS_FAILED,
                self::STATUS_REJECT,
            ]],
            [['sms_id', 'status'], 'integer'],
            [['content', 'params', 'to', 'tag', 'result'], 'string'],
            [['sms_id', 'content', 'params', 'to', 'tag', 'result', 'status', 'created_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'sms_id' => Yii::t('app.attribute.service_sms.label', 'ID'),
            'content' => Yii::t('app.attribute.service_sms.label', 'Content'),
            'params' => Yii::t('app.attribute.service_sms.label', 'Params'),
            'to' => Yii::t('app.attribute.service_sms.label', 'To'),
            'tag' => Yii::t('app.attribute.service_sms.label', 'Tag'),
            'result' => Yii::t('app.attribute.service_sms.label', 'Result'),
            'status' => Yii::t('app.attribute.service_sms.label', 'Status'),
            'created_at' => Yii::t('app.attribute.service_sms.label', 'Created At'),
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->created_at = static::currentDatetime();
            }
            return true;
        }
        return false;
    }

    public function getTagJson()
    {
        try {
            return array_values(Json::decode($this->tag));
        } catch (\Exception $ex) {
            return [];
        }
    }

    public function getParamsJson()
    {
        try {
            return Json::decode($this->params, true);
        } catch (\Exception $ex) {
            return [];
        }
    }

    public function getSmsContent()
    {
        return \Yii::t("template.sms.content", $this->content, $this->paramsJson);
    }
}
