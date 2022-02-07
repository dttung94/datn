<?php
namespace backend\modules\system\forms\user;

use common\forms\service\SendMailForm;
use common\entities\system\SystemConfig;
use common\models\UserIdentity;
use Yii;
use yii\helpers\Html;

/**
 * Login form
 */
class ResetPasswordForm extends UserIdentity
{
    public function toResetPassword()
    {
        $password = Yii::$app->security->generateRandomString(8);
        $this->setPassword($password);
        if ($this->save()) {
            //TODO send email to user
            $mail = new SendMailForm();
            $mail->from_email = SystemConfig::getValue(SystemConfig::CATEGORY_SYSTEM, SystemConfig::SYSTEM_NO_REPLY_EMAIL);
            $mail->subject = Yii::t("template.mail.reset_password.title", "[{site_name}] Reset password", [
                "site_name" => SystemConfig::getValue(SystemConfig::CATEGORY_SYSTEM, SystemConfig::SYSTEM_SITE_NAME)
            ], $this->language_id);
            $mail->content = Yii::t("template.mail.reset_password.content",
                "Hi {email} - {username},<br/>
<b>You account have been reset password.</b><br/>
<b>Your account info:</b><br/>
+ <b>Username</b>: {username}.<br/>
+ <b>Password</b>: {password}<br/>
+ <b>URL</b>     : {link}.<br/>
{homepage} Thanks!<br/>", [
                    "email" => $this->email,
                    "username" => $this->username,
                    "password" => $password,
                    "homepage" => Html::a(Yii::t("common.label", "Homepage"), Yii::$app->urlManager->createAbsoluteUrl(["site/index"])),
                    "link" => Html::a(Yii::t("common.label", "Login"), Yii::$app->urlManager->createAbsoluteUrl(["site/login"]))
                ], $this->language_id);
            $mail->to = $this->email;
            $mail->tag = [
                "AUTHENTICATION",
                "RESET_PASSWORD",
                $this->email
            ];
            return $mail->toSend();
        }
        return false;
    }
}
