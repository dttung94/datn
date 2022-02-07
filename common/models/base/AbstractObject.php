<?php
namespace common\models\base;


use common\helper\DatetimeHelper;
use yii\db\ActiveRecord;
use yii\helpers\StringHelper;

/**
 * Class AbstractObject
 * @package common\models\base
 *
 * @property boolean $enableAutoSendNotification
 */
abstract class AbstractObject extends ActiveRecord
{
    const
        STATUS_ACTIVE = 10,
        STATUS_INACTIVE = 9,
        STATUS_ARCHIVED = -1,
        STATUS_DELETED = 0;

    public $enableAutoSendNotification = true;

    public static function getListStatus()
    {
        return [
            self::STATUS_ACTIVE => "Active",
            self::STATUS_INACTIVE => "InActive",
        ];
    }

    public static function getModel()
    {
        return new static;
    }

    public static function genId($prefix = null)
    {
        if ($prefix == null) {
            $prefix = static::tableName();
        }
        return uniqid($prefix);
    }

    public static function currentUserId()
    {
        return (\Yii::$app->has("user")) ? \Yii::$app->user->getId() : "";
    }

    public static function currentDatetime()
    {
        return DatetimeHelper::now();
    }

    public function search()
    {
        return static::find();
    }

    public function getAttributeLabel($attribute)
    {
        $labels = $this->attributeLabels();
        if (isset($labels[$attribute])) {
            return ($labels[$attribute]);
        }

        $actions = self::getFilterActions();
        $text = isset($actions[$attribute]) ? $actions[$attribute] : parent::getAttributeLabel($attribute);
        //print_r($attribute);exit;
        return \Yii::t('app.attribute.' . $this->tableName(), $text);
    }

    public function getAttributeHint($attribute)
    {
        $hints = $this->attributeHints();
        if (isset($hints[$attribute])) {
            return ($hints[$attribute]);
        }
        return "";
    }

    public function attributePlaceholders()
    {
        return [];
    }

    public function getAttributePlaceholder($attribute)
    {
        $placeholders = $this->attributePlaceholders();
        if (isset($placeholders[$attribute])) {
            return ($placeholders[$attribute]);
        }
        return \Yii::t('app.attribute.' . $this->tableName() . ".placeholder", ucwords($attribute));
    }

    public function getClassNameOnly()
    {
        return StringHelper::basename(get_class($this));
    }

    public function getFilterActions()
    {
        return [
            'SHOP_CALENDAR_MANAGE' => '店舗カレンダー',
            'SHOP_BOOKING_MANAGE' => '店舗予約操作',
            'USER_SIGN_UP' => 'ユーザーサインアップ',
            'USER_CONFIRM' => 'ユーザー承認',
            'USER_VERIFY_PHONE_NUMBER' => 'ユーザー電話番号認証',
            'ONLINE_BOOKING_ADD' => 'オンライン予約追加',
            'ONLINE_BOOKING_CANCEL' => 'オンライン予約キャンセル',
            'ONLINE_BOOKING_ACCEPT' => '予約の承認',
            'ONLINE_BOOKING_UPDATE' => 'オンライン予約修正',
            'ONLINE_BOOKING_END_TIME' => '予約承認の時間切れ',
            'ONLINE_BOOKING_REJECT' => '店からの予約の拒否',
            'SITE_NAME' => 'Tên trang web',
            'SITE_COPYRIGHT' => 'Copyright',
            'SUPPORT_EMAIL' => 'Địa chỉ email nhận yêu cầu hỗ trợ',
            'NO_REPLY_EMAIL' => 'Địa chỉ email No-reply',
            'DURATION_TIME' => 'Khoảng thời gian',
            'MAX_ONLINE_BOOKING_PENDING' => 'Số lượng tối đa lượt đặt lịch đồng thời',
            'MAX_FREE_BOOKING_PENDING' => '最大同時フリー予約申請数',
            'MAX_TIME_CONFIRM_FREE_BOOKING' => 'フリー予約承認時間（秒）',
            'MAX_TIME_CONFIRM_ONLINE_BOOKING' => 'Thời gian phê duyệt đặt lịch online (giây)',
            'TIME_CONFIRM_EXPIRED' => 'Đếm thời gian trước khi hết thời gian phê duyệt',
            'COUPON_BLOCK_VALUE' => 'クーポン利用最小単位（円）',
            'TWILIO_APP_PHONE_NUMBER' => 'Twilio Phone Number',
            'BOOKING' => 'Đặt lịch',
            'TWILIO_APP' => 'Twilio',
            'OPEN_SLOT_TIME' => 'Giờ mở cửa',
            'END_SLOT_TIME' => 'Giờ đóng cửa',
            'BOOKING_IS_BOOKING_SORT_TIME' => '現在ある枠より短い枠に変更申請ができる',
            'TIME_ON_USER_BOOKING' => '次の日の枠を表示する機能が無効になる時間',
            'VERIFY_EMAIL' => '認証用のメール',
            'DELETE_EMAIL' => '削除用のメール',
            'DELETE_COUPON' => '削除用クーポン',
            'rank-1' => 'もうすぐプレミアガール',
            'rank-8' => 'プレミアガール',
            'rank-10' => 'プラチナガール',
            'rank-5' => 'フリー用',
            'none-none' => '店舗名 - 空',
            'offline-accepted' => '予約済み - 承認済',
            'online-accepted' => 'オンライン予約 - 承認済',
            'online-pending' => 'オンライン予約 - 承認待ち',
            'online-updating' => 'オンライン予約 - 修正承認待ち',
            'online-pending-change' => 'オンライン予約 - 時間変更申請の承認待ち',
            'online-canceled' => 'オンライン予約 - キャンセルされた',
            'free-accepted' => 'フリー枠 - 承認済',
            'free-confirming' => 'フリー枠 - 承認待ち',
            'free-canceled' => 'フリー枠 - キャンセルされた',
            'COLOR' => '色設定',
            // add config for coupon business - task 287
            'LAST_TIME_USER_RECEIVED_COUPON' => '最終クーポンを発行してから○○日以上',
            'TOTAL_COUPON_OF_USER_LESS' => '所持クーポン（○○円未満）',
            'TOTAL_COUPON_OF_USER_MORE' => 'クーポン総発行料（○○円以上）',
            'TOTAL_BOOKING' => '総予約数（○○以上）',
            'DAYS_TO_REMIND' => '営業クーポン発行を催促する日数',
            'EMAIL_ADDRESS_TO_RECEIVE_NOTIFICATION' => '営業クーポン発行を催促して送られるメール',

            'COUPON_MAX_VALUE_INTRODUCE' => '紹介された側のボーナス',
            'COUPON_MAX_VALUE_BOOKING_REFERRER' => '子ユーザー利用時に親ユーザーが受け取る額',
            'TIME_USE_COUPON_REFER' => '紹介クーポンの有効期限（日）',
            'TIME_REFER_RECEIVE_COUPON' => '紹介プログラム1ユーザーあたりの期限（日）',
            'COUPON_MAX_VALUE_INVITE' => '紹介者ボーナス',


            //config shop-one
            'logo-site' => 'ロゴの画像',
            'character-site' => 'キャラクターの画像',
            'intro-site' => '紹介の画像',
            'background-header' => '背景ヘッダー',
            'background-button-free' => 'フリー予約というボタンの背景',
            'color-text-button-free' => 'フリー予約というボタンの文章の色',
            'color-border-button-free' => 'フリー予約というボタンのボーダー',
            'border-radius-button-free' => 'フリー予約というボタンの丸み',
            'background-button-booking' => '予約というボタンの背景',
            'color-text-button-booking' => '予約というボタンの文章の色',
            'color-border-button-booking' => '予約というボタンのボーダー',
            'border-radius-button-booking' => '予約というボタンの丸み'
        ];
    }
}
