<?php

namespace common\entities\user;

use common\entities\customer\CustomerInfo;
use common\entities\calendar\BookingInfo;
use common\entities\referrer\ReferInfo;
use common\entities\shop\ShopInfo;
use common\entities\system\SystemData;
use common\forms\service\SendSMSForm;
use common\forms\service\URLShortener;
use common\helper\ArrayHelper;
use common\helper\DatetimeHelper;
use common\models\base\AbstractObject;
use Yii;

/**
 * This is the model class for table "user_info".
 *
 * @property string $user_id
 *
 *
 * @property string $full_name
 * @property string $username
 * @property string $password
 * @property string $email
 * @property string $phone_number
 * @property string $tag
 * @property boolean $type_notification
 *
 *
 * @property string $status
 * @property string $role
 * @property string $is_online
 * @property boolean $mail_coupon_info
 * @property boolean $mail_new_worker
 * @property boolean $mail_calendar
 * @property boolean $mail_forum
 * @property boolean $sent_mail
 * @property boolean $time_sent_mail
 * @property string $nickname
 * @property integer $status_forum
 * @property boolean $mail_coupon_mega
 *
 * @property string $source_type
 *
 * @property string $created_at
 * @property string $modified_at
 * @property string $avatar
 * @property integer $verify_email
 * @property integer $verify_phone
 * @property integer $referrer_id
 * @property boolean $status_refer
 * @property string $invite_code
 *
 * @property UserToken[] $userTokens
 * @property UserConfig[] $userConfigs
 * @property ShopInfo[] $usedShops
 * @property CustomerInfo[] $customerInfo
 */
class UserInfo extends AbstractObject
{
    const
        ROLE_ADMIN = "ADMIN",
        ROLE_MANAGER = "MANAGER",
        ROLE_USER = "USER",
        ROLE_OPERATOR = "OPERATOR";

    const
        PROVIDER_SYSTEM = "SYSTEM";

    const
        SOURCE_TYPE_APPLICATION = "APPLICATION";

    const
        STATUS_CONFIRMING = 1,
        STATUS_VERIFYING = 2,
        STATUS_SHOP_BLACK_LIST = 3,
        STATUS_WORKER_BLACK_LIST = 4,
        STATUS_CONFIRMING_INVITE = 5;

    const
        IS_ONLINE = 1,
        IS_OFFLINE = 0;

    const
        RECEIVE = 1,
        UNRECEIVE = 0;

    const
        SENT = 1,
        UNSENT = 0;

    const
        TYPE_NOTIFICATION_SMS = 0,
        TYPE_NOTIFICATION_EMAIL = 1,
        TYPE_NOTIFICATION_SMS_AND_EMAIL = 2;

    const STATUS_USER_FORUM_ACTIVE = 1,
        STATUS_USER_FORUM_BLOCK = -1;

    const
        VERIFIED = 1,
        NOT_VERIFIED = 0;

    const
        USER_NEW = 1,
        USER_OLD = 0;
    /**
     * TODO get admin user id
     * @return string
     */
    public static function getAdminUserId()
    {
        $adminUser = self::findOne([
            "role" => self::ROLE_ADMIN,
            "status" => self::STATUS_ACTIVE,
        ]);
        if ($adminUser != null) {
            return $adminUser->user_id;
        }
        return "";
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['full_name', 'username', 'password', 'role'], 'required'],
            [['username'], 'unique'],
            [['phone_number'], 'required'],
            [['phone_number'], 'unique', 'targetAttribute' => ['phone_number', 'role']],
            [['email'], 'unique', 'targetAttribute' => ['email', 'role']],
            [['email'], 'unique', 'when' => function (UserInfo $model) {
                return $model->role == self::ROLE_MANAGER || $model->role == self::ROLE_ADMIN;
            }],
            [['phone_number'], 'string', 'min' => 6, 'max' => 15],
            [['phone_number'], 'validatePhoneNumber'],
            [['email'], 'email'],
            ['role', 'in', 'range' => [
                self::ROLE_ADMIN,
                self::ROLE_MANAGER,
                self::ROLE_USER,
                self::ROLE_OPERATOR,
            ]],
            [['password'], 'string'],
            [['status'], 'integer'],
            [['username', 'role'], 'string', 'max' => 100],
            [
                ['user_id', 'status',
                'created_at', 'modified_at'],
                'safe'
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('app.attribute.user_info.label', 'ID'),

            'full_name' => Yii::t('app.attribute.user_info.label', 'Họ tên'),
            'username' => Yii::t('app.attribute.user_info.label', 'Tài khoản'),
            'password' => Yii::t('app.attribute.user_info.label', 'Mật khẩu'),
            'email' => Yii::t('app.attribute.user_info.label', 'Email'),
            'phone_number' => Yii::t('app.attribute.user_info.label', 'Số điện thoại'),

            'role' => Yii::t('app.attribute.user_info.label', 'Role'),

            'status' => Yii::t('app.attribute.user_info.label', 'Trạng thái'),
            'source_type' => Yii::t('app.attribute.user_info.label', 'Source type'),

            'created_at' => Yii::t('app.attribute.user_info.label', 'Ngày khởi tạo'),
            'modified_at' => Yii::t('app.attribute.user_info.label', 'Modified At'),
        ];
    }


    public function validatePhoneNumber($attribute, $params)
    {
        $pattern = '/^[0-9]+$/';
        if (!$this->hasErrors()) {
            if (!preg_match($pattern, $this->phone_number)) {
                $this->addError($attribute, \App::t("backend.manager.message", 'Số điện thoại không hợp lệ'));
            }
        }
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->created_at = $this->currentDatetime();
            }
            $this->modified_at = $this->currentDatetime();
            return true;
        }
        return false;
    }
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes); // TODO: Change the autogenerated stub
        if($this->status == self::STATUS_VERIFYING) {
            if (($verifyPhoneNumberToken = UserToken::createToken(
                UserToken::TYPE_SIGN_UP_VERIFY_PHONE_NUMBER_TOKEN,
                $this->user_id,
                date(DatetimeHelper::FULL_DATETIME, time() + 60 * 60) //set expire after 1 hour
            ))) {
                $phone_number = '84' . $this->phone_number;
                if (SendSMSForm::toSendViaTemplateId(
                    $phone_number,
                    SendSMSForm::TYPE_MEMBER_REGISTER_VERIFY_PHONE_NUMBER, [
                    "verify_url" => URLShortener::shortenLongUrl(\App::$app->urlManager->createAbsoluteUrl([
                        "site/sign-up-verify-phone-number",
                        "verifyToken" => $verifyPhoneNumberToken->token,
                    ])),
                ])) {
                    return true;
                }
                return false;
            }
        }

    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserConfigs()
    {
        return $this->hasMany(UserConfig::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserTokens()
    {
        return $this->hasMany(UserToken::className(), ['user_id' => 'user_id']);
    }

    public function getCustomerInfo()
    {
        return $this->hasMany(CustomerInfo::className(), ['phone_number' => 'phone_number']);
    }


    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     * @throws \yii\base\Exception
     */
    public function setPassword($password)
    {
        $this->password = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    public function verifyPassword($password)
    {
        return password_verify($password, $this->password);
    }

    public static function getUserById($user_id)
    {
        return UserInfo::findOne([
            "user_id" => $user_id,
        ]);
    }

    public function extraFields()
    {
        return ArrayHelper::merge(parent::extraFields(), [
            "userConfigs",
            "usedShops",
        ]);
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        $data = parent::toArray([
            "user_id",
            "full_name",
            "username",
            "email",
            "phone_number",
            "created_at",
            "role",
            "status",
            "tag",
            "type_notification",
            "avatar"
        ], $expand, $recursive);
        return $data;
    }

    /**
     * Count all booking of user. Has booking offline not save member_id so count in member_id_booking_offline
     *
     * @return mixed
     */
    public function getBookingInfo()
    {
        return $this->hasOne(BookingInfo::className(), ['member_id' => 'user_id'])
            ->where(["NOT IN", BookingInfo::tableName() . ".status", [
                BookingInfo::STATUS_REJECTED,
                BookingInfo::STATUS_DELETED,
            ]])
            ->count();
    }
}
