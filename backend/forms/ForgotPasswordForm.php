<?php
namespace backend\forms;

use common\entities\system\SystemConfig;
use common\entities\user\UserInfo;
use common\entities\user\UserToken;
use common\forms\service\SendMailForm;
use common\helper\ArrayHelper;
use common\models\base\AbstractForm;

/**
 * Class ForgotPasswordForm
 * @package backend\forms
 *
 * @property string $email
 */
class ForgotPasswordForm extends AbstractForm
{
    public $email;

    /**
     * @var UserInfo
     */
    protected $_userInfo;

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            ["email", "required"],
            ["email", "email"],
            ["email", "validateEmail"],
        ]);
    }

    public function attributeLabels()
    {
        return [
            'email' => \Yii::t('backend.forgot_password.message', 'Email')
        ];
    }

    public function validateEmail($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $this->_userInfo = UserInfo::findOne([
                "email" => $this->email,
                "status" => UserInfo::STATUS_ACTIVE,
            ]);
            if (!$this->_userInfo) {
                $this->addError($attribute, \App::t("backend.forgot_password.validate", "Email không chính xác"));
            }
        }
    }

    /**
     * TODO send reset mail to User
     * @return bool
     */
    public function toSendMail()
    {
        if ($this->validate()) {
            //todo disabled old reset password token
            UserToken::updateAll([
                "status" => UserToken::STATUS_INACTIVE
            ], [
                "user_id" => $this->_userInfo->user_id,
                "status" => UserToken::STATUS_ACTIVE,
            ]);
            //todo create reset password token
            $resetToken = new UserToken();
            $resetToken->generateToken();
            $resetToken->setExpireDate(1);
            $resetToken->type = UserToken::TYPE_RESET_PASSWORD_TOKEN;
            $resetToken->user_id = $this->_userInfo->user_id;
            $resetToken->status = UserToken::STATUS_ACTIVE;
            if ($resetToken->save()) {
                //todo send reset password email
                $params = [
                    "full_name" => $this->_userInfo->full_name,
                    "username" => $this->_userInfo->username,
                    "email" => $this->email,
                    "reset_pass_link" => \Yii::$app->urlManager->createAbsoluteUrl([
                        "site/reset-password",
                        "reset_token" => $resetToken->token,
                    ]),
                ];
                $mail = new SendMailForm();
                $mail->from_name = SystemConfig::getValue(SystemConfig::CATEGORY_SYSTEM, SystemConfig::SYSTEM_SITE_NAME);
                $mail->from_email = SystemConfig::getValue(SystemConfig::CATEGORY_SYSTEM, SystemConfig::SYSTEM_NO_REPLY_EMAIL);
                $mail->subject = SendMailForm::getEmailTemplate(SendMailForm::EMAIL_TEMPLATE_BACKEND_FORGOT_PASSWORD, "title", $params);
                $mail->content = SendMailForm::getEmailTemplate(SendMailForm::EMAIL_TEMPLATE_BACKEND_FORGOT_PASSWORD, "content", $params);
                $mail->to = $this->email;
                if (!$mail->toSend()) {
                    $this->addError("email", \App::t("backend.forgot_password.message", "Có lỗi xảy ra khi gửi email"));
                }
            } else {
                $this->addError("email", \App::t("backend.forgot_password.message", "Lỗi khi tạo mã cấp mật khẩu"));
            }
            if (!$this->hasErrors()) {
                return true;
            }
        }
        return false;
    }
}