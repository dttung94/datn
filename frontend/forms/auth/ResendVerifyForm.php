<?php


namespace frontend\forms\auth;


use common\entities\service\TemplateMail;
use common\entities\user\UserData;
use common\entities\user\UserInfo;
use common\entities\user\UserToken;
use common\forms\service\SendSMSForm;
use common\forms\service\URLShortener;
use common\helper\DatetimeHelper;
use common\mail\forms\Mail;
use yii\base\Model;
use yii\web\User;
/**
 * Class ResendVerifyForm
 * @package frontend\forms\auth
 *
 * @property string $phone_number
 */

class ResendVerifyForm extends Model
{
    public $phone_number;

    public function rules()
    {
        return [
            ['phone_number', 'filter', 'filter' => 'trim'],
            ['phone_number', 'required'],
        ];
    }

    public function sendVerifyLink()
    {
        /* @var $user UserInfo */
        $user = UserInfo::findOne([
            'status' => UserInfo::STATUS_VERIFYING,
            'role' => UserInfo::ROLE_USER,
            'phone_number' => $this->phone_number,
        ]);
        if ($user) {
            $query = UserToken::find();
            $query->where(["user_id" => $user->user_id]);
            $query->andwhere(["type" => UserToken::TYPE_SIGN_UP_VERIFY_PHONE_NUMBER_TOKEN]);
            $query->orderBy('created_at DESC');
            $lastToken = $query->one();
            $resendTime = strtotime($lastToken->created_at) + 300; //resend verify link after 5 minutes
            if ($resendTime <= time()) {
                if ($user->verify_phone == UserInfo::NOT_VERIFIED) {
                    if (($verifyPhoneNumberToken = UserToken::createToken(
                        UserToken::TYPE_SIGN_UP_VERIFY_PHONE_NUMBER_TOKEN,
                        $user->user_id,
                        date(DatetimeHelper::FULL_DATETIME, time() + 60 * 60)))
                    ) {
                        $this->sendSMS($user, $verifyPhoneNumberToken);
                        return true;
                    } else {
                        $this->addError("phone_number",
                            \App::t("frontend.authentication.message", "Gặp lỗi khi gửi SMS"));
                    }
                }
            } else {
                $this->addError("resend", \App::t("frontend.member.message", "Chúng tôi vừa gửi SMS xác thực trước đó, vui lòng quay lại sau 5 phút"));
            }
        } else {
            $this->addError("phone_number", \App::t("frontend.authentication.message", "Số điện thoại [{number}] chưa đăng ký", [
                "number" => $this->phone_number,
            ]));
        }
        return false;
    }

    public function sendMail($user, $token)
    {
        $email = $user->email;
        $userName = $user->username;
        $userId = $user->user_id;
        $text = URLShortener::shortenLongUrl(\App::$app->urlManager->createAbsoluteUrl([
            "site/sign-up-verify-email",
            "verifyToken" => $token->token
        ]));
        $link = '<a href="' . $text . '">' . $text . '</a>';
        $template = TemplateMail::getMailTemplate(TemplateMail::TYPE_VERIFY_MAIL);
        $mailParams = [
            'verify_url' => $link
        ];
        $data = [
            'email' => $email,
            'name' => $userName,
            'subject' => $template->title,
            'content' => $template->content,
            'params' => $mailParams
        ];
        $mail = new Mail();
        if ($mail->toSend($data, false)) {
            UserInfo::updateAll([
                'time_sent_mail' => date('Y-m-d H:i:s')
            ], [
                'user_id' => $userId
            ]);
            return true;
        } else {
            $this->addError('phone_number', \App::t('frontend.authentication.message', "メールアドレス送信時にエラーがでました。"));
        }
        return false;
    }

    public function sendSMS($user, $token)
    {
        $phone_number = '84' . $user->phone_number;
        if (
        SendSMSForm::toSendViaTemplateId(
            $phone_number,
            SendSMSForm::TYPE_MEMBER_REGISTER_VERIFY_PHONE_NUMBER, [
            "verify_url" => URLShortener::shortenLongUrl(\App::$app->urlManager->createAbsoluteUrl([
                "site/sign-up-verify-phone-number",
                "verifyToken" => $token->token,
            ])),
        ])
        ) {
            return true;
        } else {
            $this->addError('phone_number', \App::t('frontend.authentication.message', "SMS送信時にエラーがでました。"));
        }
        return false;
    }
}