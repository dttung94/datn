<?php
namespace frontend\forms\auth;

use common\entities\referrer\ReferInfo;
use common\entities\shop\ShopInfo;
use common\entities\system\SystemConfig;
use common\entities\system\SystemData;
use common\entities\user\UserData;
use common\entities\user\UserInfo;
use common\entities\user\UserLog;
use common\forms\service\SendMailForm;
use common\helper\ArrayHelper;
use yii\base\Model;
use yii\helpers\Json;

/**
 * Class SignupForm
 * @package frontend\forms\auth
 *
 * @property int $country_code
 * @property string $phone_number
 * @property string $email
 * @property string $password
 * @property string $re_password
 * @property array $hobbies
 * @property string $used_service_shop
 * @property string $invite_code
 */
class SignupForm extends Model
{
    public $country_code;
    public $phone_number;
    public $password;
    public $re_password;
    public $hobbies;
    public $full_name;
    public $used_service_shop;
    public $role;
    public $email;
    public $invite_code;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phone_number'], 'filter', 'filter' => 'trim'],
            [['phone_number'], 'required'],
            [['phone_number'], 'string'],
            [['phone_number'], 'unique', 'targetAttribute' => ['phone_number'], 'targetClass' => UserInfo::className(), 'message' =>  "Số điện thoại đã được sử dụng"],
            [["phone_number"], "validatePhoneNumber"],
//            [['email'], 'required'],
            [['full_name'], 'required'],
            [['phone_number'], 'string'],
//            [['email'], 'email'],
            [['password', 're_password'], 'required'],
            [['password'], 'string', 'min' => 6, "tooShort" => \App::t("frontend.sign-up.message", "Mật khẩu ít nhất 6 ký tự")],
            [['re_password'], 'compare', 'compareAttribute' => 'password', "message" => "Mật khẩu không khớp"],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'phone_number' => \Yii::t('frontend.sign-up.message', 'Số điện thoại'),
//            'email' => \Yii::t('frontend.sign-up.message', 'Email'),
            'full_name' => \Yii::t('frontend.sign-up.message', 'Tên KH'),
            'password' => \Yii::t('frontend.sign-up.message', 'Mật khẩu'),
            're_password' => \Yii::t('frontend.sign-up.message', 'Nhập lại mật khẩu')
        ];
    }

    public function validatePhoneNumber($attribute, $params)
    {
        $pattern = '/^[0-9]+$/';
        if (!$this->hasErrors()) {
            if (!preg_match($pattern, $this->phone_number)) {
                $this->addError($attribute, \App::t("frontend.sign-up.message", 'Số điện thoại không hợp lệ'));
            } elseif (strlen($this->phone_number) != 10) {
                $this->addError($attribute, \App::t("frontend.sign-up.message", 'Số điện thoại chỉ có 10 chữ số'));
            }
        }
    }


    /**
     * Signs user up.
     *
     * @return UserInfo|null the saved model or null if saving fails
     */
    public function toSignUp()
    {
        if ($this->validate()) {
            $trans = \App::$app->db->beginTransaction();
            $user = new UserInfo();
            $user->full_name = $this->full_name;
            $user->username = $this->phone_number;
            $user->phone_number = $this->phone_number;
//            $user->email = $this->email;
            $user->setPassword($this->password);
            $user->status = UserInfo::STATUS_VERIFYING;
            $user->role = UserInfo::ROLE_USER;
//            $user->type_notification = UserInfo::TYPE_NOTIFICATION_SMS_AND_EMAIL;
            if ($user->save()) {//todo create user
                    $trans->commit();
                    return $user;

            } else {
                $this->addErrors($user->getErrors());
            }
            $trans->rollBack();
        }
        return null;
    }

    private function getStringRandom($createdAt) {
        $randomString = substr(md5(uniqid($createdAt, true)), 0, 10);
        return $randomString;
    }

    public function getShopList()
    {
        return ArrayHelper::map(
            ShopInfo::find()
                ->where([
                    "status" => ShopInfo::STATUS_ACTIVE
                ])->all(), "shop_id", "shop_name");
    }

    protected function toSendMail($email)
    {
        $subject = '[Đăng ký thành viên]';
        $content = '';
        $mail = new SendMailForm();
        $mail->from_name = SystemConfig::getValue(SystemConfig::CATEGORY_SYSTEM, SystemConfig::SYSTEM_SITE_NAME);
        $mail->from_email = SystemConfig::getValue(SystemConfig::CATEGORY_SYSTEM, SystemConfig::SYSTEM_NO_REPLY_EMAIL);
        $mail->subject = $subject;
        $mail->content = $content;
        $mail->to = $email;
        $mail->tag = [
            $email
        ];
        return $mail->toSend();
    }
}
