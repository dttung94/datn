<?php
namespace frontend\forms\auth;

use common\entities\user\UserInfo;
use common\entities\user\UserToken;
use yii\base\Model;

/**
 * Class ResetPasswordForm
 * @package frontend\forms\auth
 *
 * @property string $token
 * @property string $password
 * @property string $re_password
 */
class ResetPasswordForm extends Model
{
    public $token, $password, $re_password;

    /**
     * @var UserToken
     */
    private $_token;
    /**
     * @var UserInfo
     */
    private $_user;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['token', 'password', 're_password'], 'required'],
            [['token'], 'validateToken'],
            [['password'], 'compare', 'compareAttribute' => 're_password', "message" => "パスワードが一致しません"],
            [['token', 'password', 're_password'], 'string'],
            [['password'], 'string', 'min' => 6, "tooShort" => \App::t("frontend.forgot_password.message", "６文字以上でお願いします")],
            [['token', 'password', 're_password'], 'safe'],
        ];
    }

    public function validateToken($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $this->_token = UserToken::findOne([
                "token" => $this->token,
                "type" => UserToken::TYPE_RESET_PASSWORD_TOKEN
            ]);
            if (
                $this->_token && $this->_token->isValid()
            ) {
                $this->_user = UserInfo::findOne($this->_token->user_id);
                if (
                    !$this->_user ||
                    $this->_user->role != UserInfo::ROLE_USER ||
                    $this->_user->status != UserInfo::STATUS_ACTIVE
                ) {
                    $this->addError($attribute, \App::t("frontend.forgot_password.message", "User is valid."));
                }
            } else {
                $this->addError($attribute, \App::t("frontend.forgot_password.message", "Reset password token is valid."));
            }
        }
    }

    public function isTokenValid()
    {
        return $this->validate(["token"]);
    }

    public function resetPassword()
    {
        if ($this->validate()) {
            $trans = \App::$app->db->beginTransaction();
            //todo update user password
            $this->_user->setPassword($this->password);
            if (!$this->_user->save()) {
                $this->addErrors($this->_user->getErrors());
            }
            //todo update token status
            $this->_token->status = UserToken::STATUS_USED;
            if (!$this->_token->save()) {
                $this->addErrors($this->_token->getErrors());
            }
            if (!$this->hasErrors()) {
                $trans->commit();
                return true;
            }
            $trans->rollBack();
        }
        return false;
    }
}
