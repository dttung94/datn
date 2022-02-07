<?php


namespace frontend\forms\auth;

use common\entities\user\UserLog;
use common\entities\user\UserToken;
use common\helper\ArrayHelper;
use common\models\base\AbstractObject;
use common\models\UserIdentity;

/**
 * Class SignUpVerifyEmailForm
 * @package frontend\forms\auth
 *
 * @property string $token
 */
class SignUpVerifyEmailForm extends AbstractObject
{
    public $token;

    /**
     * @var UserToken
     */
    private $_tokenInfo;

    /**
     * @var UserIdentity
     */
    private $_userIdentity;

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            ["token", 'required'],
            ["token", 'string'],
            ["token", 'validateToken']
        ]);
    }

    public function validateToken($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $this->_tokenInfo = UserToken::findOne([
                "token" => $this->token,
                "status" => UserToken::STATUS_ACTIVE,
                "type" => UserToken::TYPE_SIGN_UP_VERIFY_EMAIL_TOKEN,
            ]);
            if (
                $this->_tokenInfo &&
                $this->_tokenInfo->isValid()
            ) {
                $this->_userIdentity = UserIdentity::findOne([
                    "user_id" => $this->_tokenInfo->user_id,
                ]);
                if (
                    !$this->_userIdentity ||
                    $this->_userIdentity->role != UserIdentity::ROLE_USER
                ) {
                    $this->addError($attribute, \App::t("frontend.sign-up.message", "User is valid."));
                }
            } else {
                $this->addError($attribute, \App::t("frontend.sign-up.message", "操作は無効です"));
            }
        }
    }

    public function toVerify()
    {
        if ($this->validate()) {
            $trans = \App::$app->db->beginTransaction();
            //todo update token
            $this->_tokenInfo->status = UserToken::STATUS_USED;
            if (!$this->_tokenInfo->save()) {
                $this->addErrors($this->_tokenInfo->getErrors());
            }
            //todo set user is CONFIRMING (to waiting admin confirm)
            if ($this->_userIdentity->verify_email == UserIdentity::VERIFIED && $this->_userIdentity->status != UserIdentity::STATUS_ACTIVE) {
                $this->addError("account", \App::t("frontend.sign-up.message", "メールをすでに認証されています"));
            } elseif ($this->_userIdentity->status == UserIdentity::STATUS_VERIFYING) {
                if ($this->_userIdentity->verify_phone == UserIdentity::VERIFIED) {
                    $this->_userIdentity->verify_email = UserIdentity::VERIFIED;
                    $this->_userIdentity->status = UserIdentity::STATUS_ACTIVE;
                    $this->_userIdentity->mail_coupon_info = UserIdentity::RECEIVE;
                    $this->_userIdentity->mail_new_worker = UserIdentity::RECEIVE;
                    $this->_userIdentity->mail_calendar = UserIdentity::UNRECEIVE;
                    $this->_userIdentity->type_notification = UserIdentity::TYPE_NOTIFICATION_EMAIL;
                } else {
                    $this->_userIdentity->verify_email = UserIdentity::VERIFIED;
                }
                if (!$this->_userIdentity->save()) {
                    $this->addErrors($this->_userIdentity->getErrors());
                }
            } else {
                $this->addError("account", \App::t("frontend.sign-up.message", "操作は無効です"));
            }
            if (!$this->hasErrors()) {
                $trans->commit();
                //todo add log
                UserLog::addLog(
                    UserLog::ACTION_VERIFY_EMAIL,
                    \App::t("common.user_log.message", "[".$this->_userIdentity->phone_number."][".$this->_userIdentity->full_name."]ユーザーメールアドレス認証"),
                    [],
                    $this->_userIdentity->user_id
                );
                return $this->_userIdentity;
            }
            $trans->rollBack();
        }
        return false;
    }
}