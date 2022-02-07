<?php
namespace common\forms\service;

use common\entities\resource\FileInfo;
use common\entities\service\ServiceMail;
use common\entities\system\SystemConfig;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\swiftmailer\Mailer;

/**
 * Class SendMailForm
 * @package backend\modules\service\forms\mail
 */
class SendMailForm extends ServiceMail
{
    const
        TYPE_TEXT = "text",
        TYPE_HTML = "html";

    const
        EMAIL_TEMPLATE_PRE = "template.mail",
        //todo backend email template
        EMAIL_TEMPLATE_BACKEND_FORGOT_PASSWORD = "backend_reset_password";

    public static $defaultParams = [
        "site_name",
        "home_url",
        "site_copyright",
        "support_email",
        "date_y_m_d",
        "date_y",
        "date_m",
        "date_d",
    ];
    public static $defaultVal = [
        self::EMAIL_TEMPLATE_BACKEND_FORGOT_PASSWORD => [
            "title" => "[{site_name}] Reset password",
            "content" => "Hi {full_name},<br/>
<b>You account have been reset password.</b><br/>
<b>Your account info:</b><br/>
+ <b>Username</b>: {username}.<br/>
+ <b>Reset password link</b>     : {reset_pass_link}.<br/>
{site_name} team!<br/>",
            "params" => [
                "full_name",
                "email",
                "username",
                "reset_pass_link"
            ],
        ],
    ];

    public static function getEmailTemplate($id, $type = "title", $params = [], $languageId = null)
    {
        $params = ArrayHelper::merge($params, [
            "site_name" => SystemConfig::getValue(SystemConfig::CATEGORY_SYSTEM, SystemConfig::SYSTEM_SITE_NAME),
            "home_url" => SystemConfig::getValue(SystemConfig::CATEGORY_SYSTEM, SystemConfig::SYSTEM_HOME_URL),
            "site_copyright" => SystemConfig::getValue(SystemConfig::CATEGORY_SYSTEM, SystemConfig::SYSTEM_SITE_COPYRIGHT),
            "support_email" => SystemConfig::getValue(SystemConfig::CATEGORY_SYSTEM, SystemConfig::SYSTEM_SUPPORT_EMAIL),
            "date_y_m_d" => date("Y m d"),
            "date_y" => date("Y"),
            "date_m" => date("m"),
            "date_d" => date("d"),
        ]);
        $defaultVal = self::$defaultVal;
        if (isset($defaultVal[$id])) {
            $emailTemplate = $defaultVal[$id];
            if (isset($emailTemplate[$type])) {
                return \Yii::t(self::EMAIL_TEMPLATE_PRE . ".$id.$type", $emailTemplate[$type], $params, $languageId);
            }
        }
        return "";
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [["from_email"], "validateFromName"],
            [["to"], "validateTo"],
            [["params"],"validateParams"],
        ]);
    }
    public function validateParams($attribute, $params)
    {
        if (!is_array($this->$attribute)) {
            $this->$attribute = [];
        }
    }
    public function validateFromName($attribute, $params)
    {
        if (is_null($this->from_name) || empty($this->from_name)) {
            $this->from_name = $this->from_email;
        }
    }

    public function validateTo($attribute, $params)
    {
        if (!is_null($this->$attribute) && !empty($this->$attribute)) {
            if (is_string($this->$attribute)) {
                $this->$attribute = [
                    [
                        "email" => $this->$attribute,
                        "name" => $this->$attribute,
                        "type" => "to"
                    ]
                ];
            } else {
                foreach ($this->$attribute as $index => $to) {
                    if (!isset($to["email"])) {
                        $this->addError($attribute, \Yii::t("common.send_mail.validate", "To is mush contain email, and email format"));
                        break;
                    }
                    if (!isset($to["name"])) {
                        $this->$attribute[$index]["name"] = $to['email'];
                    }
                    if (!isset($to["type"])) {
                        $this->$attribute[$index]["type"] = "to";
                    }
                }
            }
            $this->$attribute = Json::encode($this->$attribute);
        }
    }

    public function toSend()
    {
        if (is_null($this->params) || empty($this->params)) {
            $this->params = [];
        }
        $this->status = static::STATUS_PENDING;
        if ($this->save()) {
            try {
                $this->status = static::STATUS_SENT;
                $this->result = Json::encode($this->sendMail());
            } catch (\Exception $ex) {
                $this->status = static::STATUS_FAILED;
                $this->result = Json::encode([
                    "success" => false,
                    "error" => $ex->getMessage(),
                ]);
            }
            return $this->save();
        }
        return false;
    }

    private function sendMail()
    {
        $this->to = Json::decode($this->to);
        $this->params = Json::decode($this->params);
        return $this->sendMailViaGoogleMail();
    }

    private function sendMailViaGoogleMail()
    {
        $result = [];
        /**
         * @var $mailer Mailer
         */
        $mailer = \Yii::$app->mailer;
        $mailer->setViewPath("@common/mail");
        if (is_array($this->to)) {
            foreach ($this->to as $index => $email) {
                $email = (is_array($email) && isset($email["email"])) ? $email['email'] : $email;
                $result[$email] = $mailer->compose("mail", ['content' => $this->content])
                    ->setTo($email)
                    ->setFrom([$this->from_email => 'Hệ thống đặt lịch chuỗi salon tóc nam Tuấn Dũng'])
                    ->setReplyTo('noreply@unpretty.com')
                    ->setSubject($this->subject)
                    ->send();
            }
        }
        return $result;
    }
}
