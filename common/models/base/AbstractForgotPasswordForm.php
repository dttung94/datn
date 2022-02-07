<?php
namespace common\models\base;

use common\entities\user\UserIdentity;

/**
 * Class AbstractForgotPasswordForm
 * @package app\common\models\base
 *
 * @property string $email
 */
abstract class AbstractForgotPasswordForm extends AbstractForm
{
    public $email;

    /**
     * @var UserIdentity
     */
    protected $_user;

    public function rules()
    {
        return [
            [['email'], 'required'],
            [['email'], 'email'],
            [['email'], 'validateEmail'],
        ];
    }

    public function validateEmail($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if ($user == null) {
                $this->addError($attribute, \Yii::t("common.message", "This email is not present in our database."));
            }
        }
    }

    abstract public function forgotPassword();

    public function getUser()
    {
        if ($this->_user == null) {
            $this->_user = UserIdentity::findByEmail($this->email);
        }
        return $this->_user;
    }
}