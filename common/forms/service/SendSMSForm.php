<?php
namespace common\forms\service;

use common\entities\service\ServiceSms;
use common\entities\service\TemplateSms;
use common\entities\system\SystemConfig;
use common\entities\user\UserInfo;
use Twilio\Rest\Client;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Class SendMailForm
 * @package backend\modules\service\forms\mail
 */
class SendSMSForm extends TemplateSms
{
    public static function toSendViaTemplateId($toPhoneNumber, $templateId, $params = [], $onlySendSms = false)
    {
        $content = parent::getSMSTemplate($templateId);

        return self::toSend($toPhoneNumber, $content, $params, [$templateId], $onlySendSms);
    }

    public static function toSend($toPhoneNumber, $content, $params = [], $tags = [], $onlySendSms = false)
    {
        $phoneNumber = substr($toPhoneNumber, 2);
        $customer = UserInfo::find()->where(['phone_number' => $phoneNumber])->one();
        if ($customer) {
            $typeNotification = $customer->type_notification;
        } else {
            $typeNotification = null;
        }
        if ($typeNotification == UserInfo::TYPE_NOTIFICATION_SMS || $typeNotification == UserInfo::TYPE_NOTIFICATION_SMS_AND_EMAIL || !$customer || $onlySendSms == true) {
            $params = ArrayHelper::merge($params, [
                "site_name" => SystemConfig::getValue(SystemConfig::CATEGORY_SYSTEM, SystemConfig::SYSTEM_SITE_NAME),
                "home_url" => SystemConfig::getValue(SystemConfig::CATEGORY_SYSTEM, SystemConfig::SYSTEM_HOME_URL),
                "site_copyright" => SystemConfig::getValue(SystemConfig::CATEGORY_SYSTEM, SystemConfig::SYSTEM_SITE_COPYRIGHT),
                "support_email" => SystemConfig::getValue(SystemConfig::CATEGORY_SYSTEM, SystemConfig::SYSTEM_SUPPORT_EMAIL),
                "date_y_m_d" => date("Y-m-d"),
                "date_y" => date("Y"),
                "date_m" => date("m"),
                "date_d" => date("d"),
            ]);
            $model = new ServiceSms();
            $model->to = $toPhoneNumber;
            $model->content = $content;
            $model->params = Json::encode($params);
            $model->status = ServiceSms::STATUS_PENDING;
            $model->tag = Json::encode($tags);
            if ($model->save()) {
                $model->status = ServiceSms::STATUS_SENT;
                try {
                    $model->result = self::toSendSMSViaTwilio($toPhoneNumber, \Yii::t("template.sms.content", $content, $params), true);
                    $model->save(false);
                    return true;
                } catch (\Exception $exception) {
                    \App::error($exception->getTraceAsString());
                    $model->status = ServiceSms::STATUS_FAILED;
                    $model->result = $exception->getMessage();
                    $model->save(false);
                    return false;
                }
            } else {
                \App::error(Json::encode($model->getErrors()));
            }
        }
        return false;
    }




    /**
     * TODO send SMS via Twilio service
     * @param $phoneNumber
     * @param $content
     * @param bool $isThrowError
     * @return string
     */
    public static function toSendSMSViaTwilio($phoneNumber, $content, $isThrowError = false)
    {
        try {
            $phoneNumber = strpos($phoneNumber, "+") !== false ? $phoneNumber : "+$phoneNumber";
            $sid = SystemConfig::getValue(SystemConfig::CATEGORY_TWILIO_APP, SystemConfig::TWILIO_APP_SID, "");
            $token = SystemConfig::getValue(SystemConfig::CATEGORY_TWILIO_APP, SystemConfig::TWILIO_APP_TOKEN, "");
            $from = SystemConfig::getValue(SystemConfig::CATEGORY_TWILIO_APP, SystemConfig::TWILIO_APP_PHONE_NUMBER, "");
            $client = new Client($sid, $token);
            $message = $client->messages->create(
                $phoneNumber,// the number we are sending to - Any phone number
                [
                    'From' => $from, // that you've purchased
                    'Body' => $content, // the sms body
                ]
            );
            return $message->sid;
        } catch (\Exception $ex) {
            if ($isThrowError) {
                throw $ex;
            }
            return false;
        }
    }
}
