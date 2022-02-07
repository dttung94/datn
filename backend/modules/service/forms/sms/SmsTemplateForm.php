<?php
namespace backend\modules\service\forms\sms;

use common\entities\service\TemplateSms;
use common\helper\ArrayHelper;

/**
 * Class SmsTemplateForm
 * @package backend\modules\service\forms
 */
class SmsTemplateForm extends TemplateSms
{
    public function getTemplateParams()
    {
        $params = ArrayHelper::merge(self::$commonParams, ArrayHelper::getValue(self::$configs, [$this->type, "params"]));
        return $params;
    }

    public function toSave()
    {
        $this->status = self::STATUS_ACTIVE;
        return $this->save();
    }
}