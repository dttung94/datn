<?php
namespace backend\modules\member\forms;


use backend\modules\coupon\forms\CouponForm;
use common\entities\service\TemplateSms;
use common\entities\user\UserData;
use common\entities\user\UserInfo;
use common\forms\service\SendSMSForm;
use common\helper\ArrayHelper;
use common\helper\StringHelper;

/**
 * Class AddPrivateCouponForm
 * @package backend\modules\member\forms
 *
 * @property UserInfo $memberInfo
 */
class AddPrivateCouponForm extends CouponForm
{
    public function prepare()
    {
        if (parent::prepare()) {
            $this->coupon_type = self::COUPON_TYPE_ONE_BY_ONE;
            $this->type_expire_date = (string)self::FORTY_FIVE_DAY;
        }
        return true;
    }

    public function toSave($type = null, $typeCouponLog = null, $messageCouponMessage = null)
    {
        if (parent::toSave()) {
            return true;
        }
        return false;
    }

    public function getMemberInfo()
    {
        return $this->hasOne(UserInfo::className(), [
            "user_id" => "member_id",
        ]);
    }

    public function extraFields()
    {
        return ArrayHelper::merge(parent::extraFields(), [
            'isSendSMS',
            'smsContent',
            "memberInfo",
            'type_expire_date'
        ]);
    }
}