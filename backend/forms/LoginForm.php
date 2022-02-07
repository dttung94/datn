<?php
namespace backend\forms;

use common\entities\user\UserInfo;
use common\models\UserIdentity;
use Yii;
use yii\base\Model;

/**
 * Class LoginForm
 * @package backend\forms
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user = false;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username'], 'required', 'message' => \App::t("backend.authentication.message", "Vui lòng nhập địa chỉ email")],
            [['password'], 'required', 'message' => \App::t("backend.authentication.message", "Vui lòng nhập mật khẩu")],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('app.attribute.login', 'Email'),
            'password' => Yii::t('app.attribute.login', 'Mật khẩu'),
            'rememberMe' => Yii::t('app.attribute.login', 'Ghi nhớ đăng nhập')
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user) {
                $this->addError($attribute, \App::t("backend.authentication.message", "Tài khoản hoặc mật khẩu không chính xác"));
            } else if (!$user->validatePassword($this->password)) {
                $this->addError($attribute, \App::t("backend.authentication.message", "Mật khẩu không chính xác"));
            } else if ($user->role == UserInfo::ROLE_USER) {
                $this->addError($attribute, \App::t("backend.authentication.message", "Tài khoản hoặc mật khẩu không chính xác"));
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        } else {
            return false;
        }
    }

    /**
     * Finds user by [[username]]
     *
     * @return UserIdentity|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = UserIdentity::findByUsername($this->username);
        }
        return $this->_user;
    }
}
