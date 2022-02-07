<?php

namespace common\entities\user;

use common\helper\DatetimeHelper;
use common\models\base\AbstractObject;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "user_token".
 *
 * @property string $token
 * @property string $type
 * @property string $user_id
 *
 * @property string $expire_date
 * @property string $data
 * @property string $status
 * @property string $created_at
 *
 * @property UserInfo $userInfo
 */
class UserToken extends AbstractObject
{
    const
        TYPE_AUTHENTICATION_TOKEN = "AUTHENTICATION_TOKEN",
        TYPE_RESET_PASSWORD_TOKEN = "RESET_PASSWORD_TOKEN",
        TYPE_SIGN_UP_VERIFY_PHONE_NUMBER_TOKEN = "SIGN_UP_VERIFY_PHONE_NUMBER_TOKEN",
        TYPE_SIGN_UP_VERIFY_EMAIL_TOKEN = "SIGN_UP_VERIFY_EMAIL_TOKEN",
        TYPE_ACCESS_TOKEN = "ACCESS_TOKEN";

    const
        STATUS_USED = 2,
        STATUS_EXPIRE_DATE = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_token';
    }

    public function generateToken()
    {
        $this->token = Yii::$app->security->generateRandomString();
    }

    public function setExpireDate($hours = 1)
    {
        $this->expire_date = DatetimeHelper::seconds2Datetime(time() + $hours * 60 * 60);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'user_id', 'type'], 'required'],
            [['token', 'user_id', 'type'], 'unique', 'targetAttribute' => ['token', 'user_id', 'type']],
            [['token'], 'string', 'max' => 200],
            [['user_id'], 'integer'],
            [['type'], 'string', 'max' => 100],
            [['type'], 'in', 'range' => [
                self::TYPE_AUTHENTICATION_TOKEN,
                self::TYPE_RESET_PASSWORD_TOKEN,
                self::TYPE_SIGN_UP_VERIFY_PHONE_NUMBER_TOKEN,
                self::TYPE_SIGN_UP_VERIFY_EMAIL_TOKEN,
            ]],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserInfo::className(), 'targetAttribute' => ['user_id' => 'user_id']],
            [['token', 'type', 'user_id', 'status', 'created_at', 'expire_date'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'token' => Yii::t('app.attribute.user_token.label', 'Token'),
            'user_id' => Yii::t('app.attribute.user_token.label', 'User'),
            'type' => Yii::t('app.attribute.user_token.label', 'Type'),

            'created_at' => Yii::t('app.attribute.user_token.label', 'Created'),
            'expire_date' => Yii::t('app.attribute.user_token.label', 'Expire Date'),

            'data' => Yii::t('app.attribute.user_token.label', 'Data'),
            'status' => Yii::t('app.attribute.user_token.label', 'Status'),
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                if (empty($this->token)) {
                    $this->token = md5(time() . "-$this->user_id");
                }
                $this->created_at = static::currentDatetime();
                $this->status = static::STATUS_ACTIVE;
            }
            return true;
        }
        return false;
    }

    /**
     * TODO check token is valid
     * @return bool
     */
    public function isValid()
    {
        if ($this->status == self::STATUS_ACTIVE) {
            if (strtotime($this->expire_date) > time()) {
                return true;
            } else {
                $this->status = self::STATUS_EXPIRE_DATE;
                $this->save();
            }
        }
        return false;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserInfo()
    {
        return $this->hasOne(UserInfo::className(), ['user_id' => 'user_id']);
    }

    /**
     * TODO get token instance
     * @param $type
     * @param $token
     * @return null|UserToken
     */
    public static function getInstance($token, $type)
    {
        return UserToken::findOne([
            "type" => $type,
            "token" => $token,
            "status" => static::STATUS_ACTIVE,
        ]);
    }

    /**
     * TODO create token
     * @param $type
     * @param $user_id
     * @param $expire_date
     * @param string $data
     * @return UserToken|null
     */
    public static function createToken($type, $user_id, $expire_date, $data = null)
    {
        $token = new UserToken();
        $token->type = $type;
        $token->user_id = $user_id;
        $token->expire_date = $expire_date;
        if ($token->save()) {
            return $token;
        }
        return null;
    }

    public function extraFields()
    {
        return ArrayHelper::merge(parent::extraFields(), [
            "userInfo"
        ]);
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        $fields = [
            "token",
            "created_at",
            "expire_date",
        ];
        $expand = [
            "userInfo"
        ];
        return parent::toArray($fields, $expand, $recursive);
    }
}
