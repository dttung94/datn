<?php
namespace backend\forms;


use common\helper\ArrayHelper;
use common\models\UserIdentity;
use Yii;

/**
 * Class UserChangePasswordForm
 * @package backend\forms
 *
 * @property string $current_password
 * @property string $new_password
 * @property string $re_new_password
 */
class UserChangePasswordForm extends UserIdentity
{
    public $current_password, $new_password, $re_new_password;

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['current_password', 'new_password', 're_new_password'], 'required'],
            [['current_password'], 'verifyCurrentPassword'],
            [['current_password', 'new_password', 're_new_password'], 'string'],
            ['re_new_password', 'compare', 'compareAttribute' => 'new_password'],
        ]);
    }

    public function attributeLabels()
    {
        return [
            'username' => Yii::t('app.attribute.user_change_password', 'Tài khoản'),
            'current_password' => Yii::t('app.attribute.user_change_password', 'Mật khẩu hiện tại'),
            'new_password' => Yii::t('app.attribute.user_change_password', 'Mật khẩu mới'),
            're_new_password' => Yii::t('app.attribute.user_change_password', 'Nhập lại mật khẩu'),
        ];
    }

    public function validateCurrentPassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if (!$this->validatePassword($this->current_password)) {
                $this->addError($attribute, \App::t("backend.profile.message", "Mật khẩu hiện tại không hợp lệ"));
            }
        }
    }

    public function verifyCurrentPassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if ($this->verifyPassword($this->current_password) == false) {
                $this->addError($attribute, \App::t("backend.profile.message", "Mật khẩu hiện tại không đúng"));
            }
        }
    }

    public function toChangePassword()
    {
        if ($this->validate()) {
            $this->setPassword($this->new_password);
            return $this->save(false);
        }
        return false;
    }
}