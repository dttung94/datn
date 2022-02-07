<?php

namespace common\entities\system;

use common\helper\ArrayHelper;
use common\models\base\AbstractObject;
use Yii;

/**
 * This is the model class for table "system_config".
 *
 * @property string $id
 * @property string $category
 * @property string $value
 * @property string $modified_at
 */
class SystemConfig extends AbstractObject
{
    public static $dataDesignConfigShopOne;
    public static $dataColor;
    const
        CATEGORY_SYSTEM = 'SYSTEM',
        SYSTEM_SITE_NAME = 'SITE_NAME',
        SYSTEM_SITE_COPYRIGHT = 'SITE_COPYRIGHT',

        SYSTEM_HOME_URL = 'HOME_URL',
        SYSTEM_SUPPORT_EMAIL = 'SUPPORT_EMAIL',
        SYSTEM_NO_REPLY_EMAIL = 'NO_REPLY_EMAIL',

        SYSTEM_OPEN_SLOT_TIME = 'OPEN_SLOT_TIME',
        SYSTEM_END_SLOT_TIME = 'END_SLOT_TIME';

    const
        CATETORY_MAILER_SMTP_TRANSPORT = "MAILER_SMTP_TRANSPORT",
        MAILER_SMTP_TRANSPORT_HOST = "MAILER_SMTP_TRANSPORT_HOST",
        MAILER_SMTP_TRANSPORT_POST = "MAILER_SMTP_TRANSPORT_POST",
        MAILER_SMTP_TRANSPORT_ENCRYPTION = "MAILER_SMTP_TRANSPORT_ENCRYPTION",
        MAILER_SMTP_TRANSPORT_USERNAME = "MAILER_SMTP_TRANSPORT_USERNAME",
        MAILER_SMTP_TRANSPORT_PASSWORD = "MAILER_SMTP_TRANSPORT_PASSWORD";

    const
        CATEGORY_TWILIO_APP = "TWILIO_APP",
        TWILIO_APP_SID = "TWILIO_APP_SID",
        TWILIO_APP_TOKEN = "TWILIO_APP_TOKEN",
        TWILIO_APP_PHONE_NUMBER = "TWILIO_APP_PHONE_NUMBER";

    const
        CATEGORY_GOOGLE_APP = "GOOGLE_APP",
        GOOGLE_URL_SHORTENER_API = "GOOGLE_URL_SHORTENER_API";
    const
        CONFIG_DURATION_TIME_COURSE = 'DURATION_TIME_COURSE',
        DURATION_TIME = 'DURATION_TIME';

    const
        CATEGORY_BOOKING = "BOOKING",
        BOOKING_IS_BOOKING_SORT_TIME = "IS_BOOKING_SORT_TIME",
        BOOKING_IS_BLOCK_USER_BOOKING = "IS_BLOCK_USER_BOOKING",
        BOOKING_TIME_ON_USER_BOOKING = "TIME_ON_USER_BOOKING",
        BOOKING_MAX_ONLINE_BOOKING_PENDING = "MAX_ONLINE_BOOKING_PENDING",
        BOOKING_MAX_TIME_CONFIRM_ONLINE_BOOKING = "MAX_TIME_CONFIRM_ONLINE_BOOKING",
        BOOKING_TIME_CONFIRM_EXPIRED = "TIME_CONFIRM_EXPIRED";


    const
        CATEGORY_SCHEDULE = "SCHEDULE",
        IS_MAP_SCHEDULE = "IS_MAP_SCHEDULE";

    const
        CATEGORY_COLOR = 'COLOR',
        WORKER_RANK_STANDARD = 'rank-1',
        WORKER_RANK_PREMIUM = 'rank-8',
        WORKER_RANK_PLATINUM = 'rank-10',
        WORKER_RANK_FREE = 'rank-5',
        SLOT_NONE = 'none-none',
        OFFLINE_ACCEPTED = 'offline-accepted',
        ONLINE_ACCEPTED = 'online-accepted',
        ONLINE_PENDING = 'online-pending',
        ONLINE_UPDATING = 'online-updating',
        ONLINE_PENDING_CHANGE = 'online-pending-change',
        ONLINE_CANCELED = 'online-canceled',
        FREE_ACCEPTED = 'free-accepted',
        FREE_CONFIRMING = 'free-confirming',
        FREE_CANCELED = 'free-canceled',
        BACKGROUND = 'background';
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'system_config';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'category'], 'unique', 'targetAttribute' => ['id', 'category']],
            [['id', 'category'], 'required'],
            [['value'], 'string'],
            [['modified_at'], 'safe'],
            [['id', 'category'], 'string', 'max' => 100],
            [['category'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app.attribute.system_config.label', 'ID'),
            'category' => Yii::t('app.attribute.system_config.label', 'Category'),
            'value' => Yii::t('app.attribute.system_config.label', 'Value'),
            'modified_at' => Yii::t('app.attribute.system_config.label', 'Modified At'),
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->modified_at = static::currentDatetime();
            return true;
        }
        return false;
    }

    /**
     * @param $category
     * @param $key
     * @param string $default
     * @return array|null|string
     */
    public static function getValue($category, $key, $default = "")
    {
        $config = self::getConfig($category, $key);
        if ($config == null) {
            $config = new SystemConfig();
            $config->id = $key;
            $config->category = $category;
            if (empty($default)) {
                if (($value = self::defaultConfigValue($category, $key)) != null) {
                    $config->value = $value;
                }
            } else {
                $config->value = $default;
            }
            $config->save(false);
        }
        return $config->value;
    }

    /**
     * @param $category
     * @param $key
     * @return array|SystemConfig
     */
    public static function getConfig($category, $key)
    {
        $config = self::find()->where('category = :category', [
            ':category' => $category,
        ])->andWhere('id = :id', [
            ':id' => $key
        ])->one();
        return $config;
    }

    public static function getColorToHtml($key)
    {
        $config = SystemConfig::getConfig(SystemConfig::CATEGORY_COLOR, $key);
        return $config ? $config->value : '';
    }

    /**
     * @param null $category
     * @param null $key
     * @return array|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function defaultConfigValue($category = null, $key = null)
    {
        $defaultVal = [
            self::CATEGORY_TWILIO_APP => [
                self::TWILIO_APP_SID => "",
                self::TWILIO_APP_TOKEN => "",
                self::TWILIO_APP_PHONE_NUMBER => "",
            ],
            self::CATEGORY_BOOKING => [
//                self::BOOKING_TIME_ON_USER_BOOKING => '10:00',
                self::BOOKING_MAX_ONLINE_BOOKING_PENDING => 1,
                self::BOOKING_MAX_TIME_CONFIRM_ONLINE_BOOKING => 500,
            ],

            self::CONFIG_DURATION_TIME_COURSE => [
                self::DURATION_TIME => 45,
            ],

            self::CATEGORY_SYSTEM => [
                self::SYSTEM_SITE_NAME => Yii::$app->params['site.name'],
                self::SYSTEM_SITE_COPYRIGHT => Yii::$app->params['site.copyright'],

                self::SYSTEM_HOME_URL => Yii::$app->params['site.home_url'],
                self::SYSTEM_SUPPORT_EMAIL => Yii::$app->params['site.email_support'],
                self::SYSTEM_NO_REPLY_EMAIL => Yii::$app->params['site.email_noreply'],
                self::SYSTEM_OPEN_SLOT_TIME => Yii::$app->params['site.open_slot_time'],
                self::SYSTEM_END_SLOT_TIME => Yii::$app->params['site.end_slot_time'],
            ],

            self::CATEGORY_COLOR => [
                self::SLOT_NONE => 'rgb(91, 155, 209)',
                self::OFFLINE_ACCEPTED => 'rgb(0, 0, 0)',
                self::ONLINE_ACCEPTED => 'rgb(66, 29, 255)',
                self::ONLINE_PENDING => 'rgb(213, 51, 37)',
                self::ONLINE_UPDATING => 'rgb(237, 185, 24)',
                self::ONLINE_PENDING_CHANGE => 'rgb(144, 210, 19)',
                self::ONLINE_CANCELED => 'rgb(232, 126, 4)',
                self::BACKGROUND => 'rgb(255, 255, 255)',
            ],

//            self::CATEGORY_GOOGLE_APP => [
//                self::GOOGLE_URL_SHORTENER_API => "",
//            ],

//            self::CATETORY_MAILER_SMTP_TRANSPORT => [
//                self::MAILER_SMTP_TRANSPORT_HOST => "smtp.gmail.com",
//                self::MAILER_SMTP_TRANSPORT_POST => "587",
//                self::MAILER_SMTP_TRANSPORT_ENCRYPTION => "tls",
//                self::MAILER_SMTP_TRANSPORT_USERNAME => "test@hblab.vn",
//                self::MAILER_SMTP_TRANSPORT_PASSWORD => "dthtkkdomtogwcmv",
//            ],
        ];
        if ($category != null && $key != null) {
            if (isset($defaultVal[$category])) {
                if (isset($defaultVal[$category][$key])) {
                    return $defaultVal[$category][$key];
                }
            }
            return null;
        }
        return $defaultVal;
    }

    public static function getCategory($category)
    {
        return self::findAll(['category' => $category]);
    }

    /**
     * return config number limit worker reminder
     * @return int
     */
    public static function numberLimitWorkerReminder()
    {
        return (int)SystemConfig::getValue(SystemConfig::CONFIG_SITE_MEMBER, SystemConfig::NUMBER_LIMIT_WORKER_REMINDER);
    }

    /**
     * return message number limit worker reminder
     * @return string
     */
    public static function messageNumberLimitWorkerReminder()
    {
        $numberLimitWorkerReminder = self::numberLimitWorkerReminder();
        return "最大 " . $numberLimitWorkerReminder . " 人までの登録となります。";
    }

    public static function getDataDesignConfigShopOne($category)
    {
        if(!self::$dataDesignConfigShopOne) {
            return self::$dataDesignConfigShopOne = self::getCategory($category);
        }
        return self::$dataDesignConfigShopOne;
    }

    public static function setDataColor() {
        if(!self::$dataColor) {
            return self::$dataColor = self::getCategory(self::CATEGORY_COLOR);
        }
        return self::$dataColor;
    }

    public static function getColor($key)
    {
        $dataColor = ArrayHelper::map(self::setDataColor(), 'id', 'value');
        $value = array_key_exists($key, $dataColor) ? $dataColor[$key] : self::defaultConfigValue(self::CATEGORY_COLOR, $key);
        return $value;
    }
}
