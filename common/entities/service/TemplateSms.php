<?php

namespace common\entities\service;

use common\models\base\AbstractObject;
use Yii;

/**
 * This is the model class for table "template_sms".
 *
 * @property integer $template_id
 * @property string $type
 * @property string $content
 * @property string $status
 * @property string $created_at
 * @property string $modified_at
 */
class TemplateSms extends AbstractObject
{
    const
        TYPE_MEMBER_REGISTER_VERIFY_PHONE_NUMBER = "MEMBER_REGISTER_VERIFY_PHONE_NUMBER",

        TYPE_MEMBER_FORGOT_PASSWORD_REQUEST = "MEMBER_FORGOT_PASSWORD_REQUEST",

        TYPE_BOOKING_ONLINE_ACCEPT = "BOOKING_ONLINE_ACCEPT",
        TYPE_BOOKING_ONLINE_REJECT = "BOOKING_ONLINE_REJECT",
        TYPE_BOOKING_ONLINE_AUTO_REJECT = "BOOKING_ONLINE_AUTO_REJECT",
        TYPE_BOOKING_ONLINE_UPDATE = "BOOKING_ONLINE_UPDATE",

        TYPE_BOOKING_FREE_SMS = "BOOKING_FREE_SMS",

        TYPE_BOOKING_REMOVE_SMS = "BOOKING_REMOVE_SMS",

        TYPE_WORKER_WORK_BREAK = "WORKER_WORK_BREAK";


    public static $commonParams = [
        "site_name",
        "home_url",
        "site_copyright",
        "support_email",
        "date_y_m_d",
        "date_y",
        "date_m",
        "date_d",
    ];

    public static $configs = [
        self::TYPE_MEMBER_REGISTER_VERIFY_PHONE_NUMBER => [
            "title" => 'Xác minh số điện thoại đăng ký',
            "isSingle" => true,
            "isAuto" => true,
            "params" => [
                "verify_url"
            ],
        ],
        self::TYPE_MEMBER_FORGOT_PASSWORD_REQUEST => [
            "title" => 'Quên mật khẩu',
            "isSingle" => true,
            "isAuto" => true,
            "params" => [
                "forgot_password_url"
            ],
        ],
        self::TYPE_BOOKING_ONLINE_ACCEPT => [
            "title" => 'Chấp thuận lượt đặt lịch',
            "isSingle" => true,
            "isAuto" => false,
            "params" => [
                "shop_name",
                "phone_number",
                "worker_name",
                "booking_date",
                "booking_time",
                "course_id",
                "cost",
                "course_time"
            ],
        ],
        self::TYPE_BOOKING_ONLINE_REJECT => [
            "title" => 'Từ chối yêu cầu đặt lịch',
            "isSingle" => true,
            "isAuto" => false,
            "params" => [
                "shop_name",
                "phone_number",
                "worker_name",
                "booking_date",
                "booking_time",
                "course_id",
                "cost",
            ],
        ],
        self::TYPE_BOOKING_ONLINE_UPDATE => [
            "title" => 'Đồng ý yêu cầu điều chỉnh của khách hàng',
            "isSingle" => true,
            "isAuto" => false,
            "params" => [
                "shop_name",
                "phone_number",
                "worker_name",
                "booking_date",
                "booking_time",
                "course_id",
                "cost",
                "course_time"
            ],
        ],


        self::TYPE_BOOKING_ONLINE_AUTO_REJECT => [
            "title" => 'Tự động từ chối lượt book (quá hạn xử lý)',
            "isSingle" => true,
            "isAuto" => false,
            "params" => [
                "shop_name",
                "phone_number",
                "worker_name",
                "booking_date",
                "booking_time",
                "course_id",
                "cost",
            ],
        ],
        self::TYPE_BOOKING_FREE_SMS => [
            "title" => 'Thông báo KH đến cửa hàng',
            "isSingle" => true,
            "isAuto" => false,
            "params" => [
                "shop_address",
                "shop_name",
                "phone_number",
                "booking_date",
                "booking_time",
                "course_id",
                "cost",
            ],
        ],
        self::TYPE_BOOKING_REMOVE_SMS => [
            "title" => 'Xóa yêu cầu',
            "isSingle" => true,
            "isAuto" => false,
            "params" => [
                "shop_name",
                "phone_number",
                "booking_date",
                "booking_time",
                "course_id",
                "cost",
                "course_time"
            ],
        ],
        self::TYPE_WORKER_WORK_BREAK => [
            "title" => 'Nhân viên nghỉ/bận',
            "isSingle" => true,
            "isAuto" => false,
            "params" => [
                "shop_name",
                "phone_number",
                "worker_name",
                "booking_date",
                "booking_time",
                "course_id",
                "cost",
            ],
        ],
    ];

    public static function getSMSTemplate($type)
    {
        $model = self::findOne([
            "type" => $type,
            "status" => self::STATUS_ACTIVE,
        ]);
        if ($model) {
            return $model->content;
        }
        return "";
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'template_sms';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'content', 'status'], 'required'],
            ['type', 'in', 'range' => [
                self::TYPE_MEMBER_REGISTER_VERIFY_PHONE_NUMBER,
                self::TYPE_MEMBER_FORGOT_PASSWORD_REQUEST,

                self::TYPE_BOOKING_ONLINE_ACCEPT,
                self::TYPE_BOOKING_ONLINE_REJECT,
                self::TYPE_BOOKING_ONLINE_AUTO_REJECT,
                self::TYPE_BOOKING_ONLINE_UPDATE,

                self::TYPE_BOOKING_FREE_SMS,

                self::TYPE_BOOKING_REMOVE_SMS,

                self::TYPE_WORKER_WORK_BREAK,
            ]],
            ['status', 'in', 'range' => [
                self::STATUS_ACTIVE,
                self::STATUS_INACTIVE,
                self::STATUS_DELETED,
            ]],
            [['template_id', 'status'], 'integer'],
            [['content'], 'string'],
            [['template_id', 'type', 'content', 'status', 'created_at', 'modified_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'template_id' => Yii::t('app.attribute.template_sms.label', 'ID'),
            'type' => Yii::t('app.attribute.template_sms.label', 'Type'),
            'content' => Yii::t('app.attribute.template_sms.label', 'Content'),
            'status' => Yii::t('app.attribute.template_sms.label', 'Status'),
            'created_at' => Yii::t('app.attribute.template_sms.label', 'Created At'),
            'modified_at' => Yii::t('app.attribute.template_sms.label', 'Modified At'),
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->created_at = static::currentDatetime();
            }
            $this->modified_at = static::currentDatetime();
            return true;
        }
        return false;
    }
}
