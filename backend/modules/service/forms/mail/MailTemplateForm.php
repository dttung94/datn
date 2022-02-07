<?php
namespace backend\modules\service\forms\mail;

use common\entities\service\TemplateMail;
use common\helper\ArrayHelper;

/**
 * Class MailTemplateForm
 * @package backend\modules\service\forms
 */
class MailTemplateForm extends TemplateMail
{
    public function getTemplateParams()
    {
        return ArrayHelper::merge(self::$commonParams, ArrayHelper::getValue(self::$configs, [$this->type, "params"]));
    }

    public function toSave()
    {
        $this->status = self::STATUS_ACTIVE;
        return $this->save();
    }

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
