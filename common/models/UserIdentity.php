<?php
namespace common\models;

use common\entities\user\UserInfo;
use common\entities\user\UserToken;
use Yii;
use yii\web\IdentityInterface;

/**
 * User model
 */
class UserIdentity extends UserInfo implements IdentityInterface
{
    /**
     * @inheritdoc
     */
    public static function findIdentity($user_id)
    {
        return static::find()
            ->where(['user_id' => $user_id])
            ->andWhere(["not in", "status", [
                self::STATUS_INACTIVE,
                self::STATUS_DELETED
            ]])
            ->one();
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $tokenInfo = UserToken::getInstance($token, UserToken::TYPE_AUTHENTICATION_TOKEN);
        if ($tokenInfo != null && $tokenInfo->isValid()) {
            return static::findOne($tokenInfo->user_id);
        }
        return null;
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return null|UserIdentity
     */
    public static function findByUsername($username)
    {
        return static::find()
            ->andWhere([
                'username' => $username,
            ])
            ->orWhere([
                'email' => $username,
            ])
            ->andWhere(["not in", "status", [
                self::STATUS_INACTIVE,
                self::STATUS_DELETED
            ]])
            ->one();
    }

    /**
     * Finds user by email
     * @param $email
     * @return null|UserIdentity
     */
    public static function findByEmail($email)
    {
        return static::findOne([
            'email' => $email,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|UserIdentity
     */
    public static function findByPasswordResetToken($token)
    {
        $tokenInfo = UserToken::getInstance($token, UserToken::TYPE_RESET_PASSWORD_TOKEN);
        if ($tokenInfo != null && $tokenInfo->isValid()) {
            return static::findOne($tokenInfo->user_id);
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->user_id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->user_id;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->user_id = Yii::$app->security->generateRandomString();
    }
}
