<?php

namespace common\entities\service;

use common\models\base\AbstractObject;
use Yii;

/**
 * This is the model class for table "template_mail".
 *
 * @property integer $template_id
 * @property string $type
 * @property string $title
 * @property string $content
 * @property string $status
 * @property string $created_at
 * @property string $modified_at
 */
class TemplateMail extends AbstractObject
{
    const
        TYPE_VERIFY_MAIL = "VERIFY_MAIL",
        TYPE_DELETE_MAIL = "DELETE_MAIL",

        TYPE_MAIL_MAGAZINE = "MAIL_MAGAZINE",
        TYPE_WORKER_NEW = "WORKER_NEW",
//        TYPE_CALENDAR_WORKER = "CALENDAR_WORKER",
        TYPE_CANCEL_CALENDAR_WORKER = "CANCEL_CALENDAR_WORKER",
        TYPE_CHANGE_TIME_WORKING = "CHANGE_TIME_WORKING",

        TYPE_BOOKING_ONLINE_ACCEPT = "BOOKING_ONLINE_ACCEPT",
        TYPE_BOOKING_ONLINE_REJECT = "BOOKING_ONLINE_REJECT",
        TYPE_BOOKING_ONLINE_AUTO_REJECT = "BOOKING_ONLINE_AUTO_REJECT",
        TYPE_BOOKING_ONLINE_UPDATE = "BOOKING_ONLINE_UPDATE",


        TYPE_BOOKING_REMOVE_MAIL = "BOOKING_REMOVE_MAIL",
        TYPE_BOOKING_CANCEL_MAIL = "BOOKING_CANCEL_MAIL",

        TYPE_WORKER_WORK_BREAK = "WORKER_WORK_BREAK",

        TYPE_FREE_MAIL = "FREE_MAIL";


    const
        USER_ALL = 'ALL',
        USER_REGISTERED_RECEIVE_WORKER_REMIND = 'USER_REGISTERED_RECEIVE_WORKER_REMIND',
        USER_TIME = 'TIME',
        USER_TAG = 'TAG',
        USER_FAVORITE = 'FAVORITE';

    const
        MAIL_AUTO = 0,
        MAIL_MAGAZINE = 1,
        MAIL_MANUAL = 2;

    public static $commonParams = [
//        "site_name",
//        "home_url",
//        "site_logo",
//        "site_copyright",
//        "support_email",
//        "date_y_m_d",
//        "date_y",
//        "date_m",
//        "date_d",
    ];

    public static $configs = [
        self::TYPE_VERIFY_MAIL => [
            "title" => 'Xác thực địa chỉ email',
            "isSingle" => true,
            "isAuto" => true,
            "params" => [
                "verify_url"
            ],
        ],
        self::TYPE_DELETE_MAIL => [
            "title" => 'Bỏ nhận thông báo email',
            "isSingle" => true,
            "isAuto" => true,
            "params" => [
            ],
        ],
        self::TYPE_BOOKING_ONLINE_ACCEPT => [
            "title" => 'Đã hoàn thành đặt lịch',
            "isSingle" => true,
            "isAuto" => true,
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
            "title" => 'Đặt lịch không thành công (từ chối)',
            "isSingle" => true,
            "isAuto" => true,
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
        self::TYPE_BOOKING_ONLINE_AUTO_REJECT => [
            "title" => 'Đặt lịch không thành công do quá hạn xử lý',
            "isSingle" => true,
            "isAuto" => true,
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
            "title" => '指名予約の修正完了',
            "isSingle" => true,
            "isAuto" => true,
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

        self::TYPE_BOOKING_REMOVE_MAIL => [
            "title" => 'Hủy lịch từ phía cửa hàng',
            "isSingle" => true,
            "isAuto" => true,
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
        self::TYPE_BOOKING_CANCEL_MAIL => [
            "title" => 'Hủy đặt lịch từ phía khách hàng',
            "isSingle" => true,
            "isAuto" => true,
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
            "title" => 'Nhân viên nghỉ đột xuất',
            "isSingle" => true,
            "isAuto" => true,
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


        self::TYPE_WORKER_NEW => [
            "title" => '新人入店情報',
            "isSingle" => true,
            "isAuto" => false,
            "params" => [
            ],
        ],
        self::TYPE_CANCEL_CALENDAR_WORKER => [
            "title" => '出勤取り消し',
            "isSingle" => true,
            "isAuto" => false,
            "params" => [
                'link-on-off-receive'
            ],
        ],
        self::TYPE_CHANGE_TIME_WORKING => [
            "title" => '出勤時間変更',
            "isSingle" => true,
            "isAuto" => false,
            "params" => [
                'link-on-off-receive'
            ],
        ],
        self::TYPE_FREE_MAIL => [
            "title" => '自由メール',
            "isSingle" => true,
            "isAuto" => false,
            "params" => [],
        ],
    ];

    /**
     * get list select config user receive email
     *
     * @param $type : type send mail in page
     * @return array
     */
    public static function getUserSend($type)
    {
        // only 2 typeMail has setting in client
        if (in_array($type, [self::TYPE_MAIL_MAGAZINE, self::TYPE_WORKER_NEW])) {
            $array = [
                self::USER_REGISTERED_RECEIVE_WORKER_REMIND => '受信可能対象',
                self::USER_TIME => '最終予約がX日間より前（Xは自由に入力可能）',
                self::USER_TAG => '会員管理画面の任意のタグがついている会員。',
                self::USER_FAVORITE => '好みのタイプを２つ選択して２つ被った会員 ',
                self::USER_ALL => '登録者全員（要注意）',
            ];
        } else {
            $array = [
                self::USER_ALL => '登録者全員',
                self::USER_TIME => '最終予約がX日間より前（Xは自由に入力可能）',
                self::USER_TAG => '会員管理画面の任意のタグがついている会員。',
                self::USER_FAVORITE => '好みのタイプを２つ選択して２つ被った会員 '
            ];
        }

        return $array;
    }

    public static function getTypeShowButtonSend()
    {
        return [
            self::TYPE_WORKER_NEW,
            self::TYPE_FREE_MAIL,
            self::TYPE_CANCEL_CALENDAR_WORKER,
            self::TYPE_CHANGE_TIME_WORKING,
        ];
    }

    public static function getMailTemplate($type)
    {
        return self::findOne([
            "type" => $type,
            "status" => self::STATUS_ACTIVE,
        ]);
    }

    public static function convertString($string, $params = [])
    {
        return \Yii::t("template.mail.content", nl2br($string), $params);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'template_mail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'title', 'content', 'status'], 'required'],
            ['type', 'in', 'range' => [
                self::TYPE_VERIFY_MAIL,
                self::TYPE_DELETE_MAIL,

                self::TYPE_WORKER_NEW,
//                self::TYPE_CALENDAR_WORKER,
                self::TYPE_CANCEL_CALENDAR_WORKER,
                self::TYPE_CHANGE_TIME_WORKING,

                self::TYPE_BOOKING_ONLINE_ACCEPT,
                self::TYPE_BOOKING_ONLINE_REJECT,
                self::TYPE_BOOKING_ONLINE_AUTO_REJECT,
                self::TYPE_BOOKING_ONLINE_UPDATE,


                self::TYPE_BOOKING_REMOVE_MAIL,
                self::TYPE_BOOKING_CANCEL_MAIL,

                self::TYPE_WORKER_WORK_BREAK,

                self::TYPE_FREE_MAIL,
            ]],
            ['status', 'in', 'range' => [
                self::STATUS_ACTIVE,
                self::STATUS_INACTIVE,
                self::STATUS_DELETED,
            ]],
            [['template_id', 'status'], 'integer'],
            [['title', 'content'], 'string'],
            [['template_id', 'type', 'title', 'content', 'status', 'created_at', 'modified_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'template_id' => Yii::t('app.attribute.template_mail.label', 'ID'),
            'type' => Yii::t('app.attribute.template_mail.label', 'Type'),
            'title' => Yii::t('app.attribute.template_mail.label', 'Title'),
            'content' => Yii::t('app.attribute.template_mail.label', 'Content'),
            'status' => Yii::t('app.attribute.template_mail.label', 'Status'),
            'created_at' => Yii::t('app.attribute.template_mail.label', 'Created At'),
            'modified_at' => Yii::t('app.attribute.template_mail.label', 'Modified At'),
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
