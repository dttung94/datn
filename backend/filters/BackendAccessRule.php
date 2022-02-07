<?php
namespace backend\filters;

use common\entities\user\UserInfo;
use yii\filters\AccessRule;

/**
 * Date: 10/23/15
 * Time: 4:23 PM
 */
class BackendAccessRule extends AccessRule
{
    public $module;

    /**
     * @var UserInfo
     */
    private $_user;

    /**
     * @param \yii\web\User $user the user object
     * @return boolean whether the rule applies to the role
     */
    protected function matchRole($user)
    {
        if (empty($this->roles)) {
            return true;
        }
        foreach ($this->roles as $role) {
            if ($role === '?') {
                if ($user->getIsGuest()) {
                    return true;
                }
            } elseif ($role === '@') {
                if (!$user->getIsGuest()) {
                    return true;
                }
            } elseif (in_array($role, [
                UserInfo::ROLE_ADMIN,
                UserInfo::ROLE_MANAGER,
            ])) {
                if (!$user->getIsGuest()) {
                    $this->_user = UserInfo::findOne($user->getId());
                    if ($this->_user != null && $this->_user->role == $role) {
                        return true;
                    }
                }
            } elseif ($user->can($role)) {
                return true;
            }
        }

        return false;
    }
}