<?php
namespace frontend\forms\auth;


use common\entities\user\UserLog;
use common\entities\user\UserToken;
use common\helper\ArrayHelper;
use common\models\base\AbstractObject;
use common\models\UserIdentity;

/**
 * Class SignUpVerifyPhoneNumberForm
 * @package frontend\forms\auth
 *
 * @property string $token
 */
class SignUpVerifyPhoneNumberForm extends AbstractObject
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
            ["token", 'validateToken'],
        ]);
    }

    public function validateToken($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $this->_tokenInfo = UserToken::findOne([
                "token" => $this->token,
                "status" => UserToken::STATUS_ACTIVE,
                'type' => UserToken::TYPE_SIGN_UP_VERIFY_PHONE_NUMBER_TOKEN,
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
                    $this->_userIdentity->role != UserIdentity::ROLE_USER ||
                    $this->_userIdentity->status != UserIdentity::STATUS_VERIFYING
                ) {
                    $this->addError($attribute, \App::t("frontend.sign-up.message", "Người dùng đã xác thực"));
                }
            } else {
                $this->addError($attribute, \App::t("frontend.sign-up.message", "Đường dẫn không còn hiệu lực"));
            }
        }
    }

    /**
     * @return bool|UserIdentity
     */
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
            if ($this->_userIdentity->verify_phone == UserIdentity::VERIFIED) {
                $this->addError("account", \App::t("frontend.sign-up.message", "Đã xác thực số điện thoại"));
            } elseif ($this->_userIdentity->status == UserIdentity::STATUS_VERIFYING) {
                $this->_userIdentity->verify_phone = UserIdentity::VERIFIED;
                $this->_userIdentity->status = UserIdentity::STATUS_ACTIVE;
                if (!$this->_userIdentity->save()) {
                    $this->addErrors($this->_userIdentity->getErrors());
                }
            } else {
                $this->addError("account", \App::t("frontend.sign-up.message", "Không hợp lệ"));
            }
            if (!$this->hasErrors()) {
                $trans->commit();
                return $this->_userIdentity;
            }
            $trans->rollBack();
        }
        return false;
    }
}