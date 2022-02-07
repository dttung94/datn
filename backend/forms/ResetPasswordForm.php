<?php
namespace backend\forms;


use common\entities\user\UserInfo;
use common\entities\user\UserToken;
use common\helper\ArrayHelper;
use common\models\base\AbstractForm;

/**
 * Class ResetPasswordForm
 * @package backend\forms
 *
 * @property string $reset_token
 * @property string $new_password
 * @property string $repeat_password
 */
class ResetPasswordForm extends AbstractForm
{
    public $reset_token;
    public $new_password, $repeat_password;

    /**
     * @var UserToken
     */
    public $_userToken;

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [["reset_token", "new_password", "repeat_password"], 'required'],
            [["new_password", "repeat_password"], 'string', "min" => 6, "max" => 20],
            [['reset_token'], 'string'],
            ['repeat_password', 'compare', 'compareAttribute' => 'new_password', "message" => "Mật khẩu không khớp"],
            ['reset_token', 'validateResetPassToken'],
        ]);
    }
    public function attributeLabels()
    {
        return [
            ['new_password' => \Yii::t('backend.forgot_password.message', 'Mật khẩu mới')],
            ['repeat_password' => \Yii::t('backend.forgot_password.message', 'Nhập lại mật khẩu')],
        ];
    }

    public function validateResetPassToken($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $this->_userToken = UserToken::findOne([
                "token" => $this->$attribute,
                "status" => UserToken::STATUS_ACTIVE,
            ]);
            if (!$this->_userToken || !$this->_userToken->isValid()) {
                $this->addError($attribute, \App::t("backend.reset_password.validate", "Reset password token không hợp lệ"));
            }
        }
    }

    public function tokenIsValid()
    {
        return $this->validate(["reset_token"]);
    }

    public function toResetPassword()
    {
        if ($this->validate()) {
            $user = UserInfo::findOne($this->_userToken->user_id);
            $trans = \App::$app->db->beginTransaction();
            /**
             * @var $user UserInfo
             */
            if ($user != null) {
                //todo update new password
                $user->setPassword($this->new_password);
                if (!$user->save()) {
                    $this->addErrors($user->getErrors());
                    $this->addError("new_password", \App::t("backend.reset_password.validate", "Gặp lỗi khi reset password"));
                }
                //todo set token is [used]
                if (!$this->hasErrors()) {
                    $this->_userToken->status = UserToken::STATUS_USED;
                    if (!$this->_userToken->save()) {
                        $this->addErrors($this->_userToken->getErrors());
                        $this->addError("new_password", \App::t("backend.reset_password.validate", "Gặp lỗi khi reset password"));
                    }
                }
            } else {
                $this->addError("new_password", \App::t("backend.reset_password.validate", "Tài khoản không hợp lệ"));
            }
            if (!$this->hasErrors()) {
                $trans->commit();
                return true;
            } else {
                $trans->rollBack();
            }
        }
        return false;
    }
}