<?php

namespace common\entities\service;

use common\entities\system\SystemConfig;
use common\models\base\AbstractObject;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * This is the model class for table "service_mail".
 *
 * @property integer $mail_id
 * @property string $type
 *
 * @property string $subject
 * @property string $content
 * @property string $params
 * @property string $from_email
 * @property string $from_name
 * @property string $to
 * @property string $tag
 * @property string $result
 * @property string $role
 * @property string $attachments
 *
 * @property string $status
 * @property integer $mail_type
 * @property string $created_at
 * @property string $modified_at
 */
class ServiceMail extends AbstractObject
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
        return 'service_mail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['subject', 'content', 'from_email', 'from_name', 'to'], 'required'],
            [['status'], 'integer'],
            [['from_email', 'from_name'], 'string', 'max' => 300],
            [['subject', 'content', 'result'], 'string'],
            [['from_email', 'to', 'params', 'role', 'created_at', 'mail_type'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'mail_id' => Yii::t('app.attribute.service_mail.label', 'ID'),
            'subject' => Yii::t('app.attribute.service_mail.label', 'Subject'),
            'content' => Yii::t('app.attribute.service_mail.label', 'Content'),
            'from_email' => Yii::t('app.attribute.service_mail.label', 'From Email'),
            'from_name' => Yii::t('app.attribute.service_mail.label', 'From Name'),
            'to' => Yii::t('app.attribute.service_mail.label', 'To'),
            'tag' => Yii::t('app.attribute.service_mail.label', 'Tag'),
            'attachments' => Yii::t('app.attribute.service_mail.label', 'Attachments'),

            'result' => Yii::t('app.attribute.service_mail.label', 'Result'),

            'status' => Yii::t('app.attribute.service_mail.label', 'Status'),
            'created_at' => Yii::t('app.attribute.service_mail.label', 'Created At'),
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if (is_array($this->to)) {
                $this->to = Json::encode($this->to);
            }
            if (is_array($this->params)) {
                $this->params = Json::encode($this->params);
            }
            if ($insert) {
                $this->created_at = static::currentDatetime();
            }
            return true;
        }
        return false;
    }
}
