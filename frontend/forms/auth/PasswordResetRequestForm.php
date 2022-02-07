<?php
namespace frontend\forms\auth;

use common\entities\service\TemplateMail;
use common\entities\user\UserData;
use common\entities\user\UserToken;
use common\forms\service\SendSMSForm;
use common\forms\service\URLShortener;
use common\helper\DatetimeHelper;
use common\mail\forms\Mail;
use yii\base\Model;
use common\entities\user\UserInfo;

/**
 * Class PasswordResetRequestForm
 * @package frontend\forms\auth
 *
 * @property string $phone_number
 */
class PasswordResetRequestForm extends Model
{
    public $phone_number;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['phone_number', 'filter', 'filter' => 'trim'],
            ['phone_number', 'required'],
            ['phone_number', 'exist',
                'targetClass' => UserInfo::className(),
                'filter' => ['status' => UserInfo::STATUS_ACTIVE],
                'message' => 'There is no user with such phone number.'
            ],
        ];
    }

    /**
     * Sends an email with a link, for resetting the password.
     * @param  bool  $onlySendSms
     * @return bool
     */
    public function sendSMS($onlySendSms = false)
    {
        /* @var $user UserInfo */
        $user = UserInfo::findOne([
            'status' => UserInfo::STATUS_ACTIVE,
            'role' => UserInfo::ROLE_USER,
            'phone_number' => $this->phone_number,
        ]);
        if ($user) {
            if (
            ($token = UserToken::createToken(UserToken::TYPE_RESET_PASSWORD_TOKEN, $user->user_id, date(DatetimeHelper::FULL_DATETIME, time() + 60 * 60)))
            ) {//todo create reset password token
                $phoneNumber = '84' . $user->phone_number;
                if (
                SendSMSForm::toSendViaTemplateId(
                    $phoneNumber,
                    SendSMSForm::TYPE_MEMBER_FORGOT_PASSWORD_REQUEST,
                    [
                        "forgot_password_url" => URLShortener::shortenLongUrl(\App::$app->urlManager->createAbsoluteUrl([
                            "site/reset-password",
                            "resetToken" => $token->token,
                        ])),
                    ],
                    $onlySendSms
                )
                ) {
                    return true;
                } else {
                    $this->addError("phone_number", \App::t("frontend.authentication.message", "Gặp lỗi khi gửi SMS"));
                }
            } else {
                $this->addError("phone_number", \App::t("frontend.authentication.message", "Have error when forgot password."));
            }
        } else {
            $this->addError("phone_number", \App::t("frontend.authentication.message", "Số điện thoại [{number}] chưa đăng ký", [
                "number" => $this->phone_number,
            ]));
        }

        return false;
    }
}
