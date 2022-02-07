<?php
namespace backend\modules\calendar\forms\sms;

use common\entities\service\TemplateSms;
use common\helper\ArrayHelper;

/**
 * Class SmsTemplateForm
 * @package backend\modules\calendar\forms\sms
 */
class SmsTemplateForm extends TemplateSms
{
    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        $data = parent::toArray($fields, $expand, $recursive);
        $data["params"] = ArrayHelper::merge(
            self::$commonParams,
            ArrayHelper::getValue(self::$configs, [$this->type, "params"], [])
        );
        return $data;
    }
}