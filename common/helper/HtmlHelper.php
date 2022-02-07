<?php
namespace common\helper;

use yii\bootstrap\Html;

class HtmlHelper extends Html
{
    public static function activeLabel($model, $attribute, $options = [])
    {
        $for = ArrayHelper::remove($options, 'for', static::getInputId($model, $attribute));
        $attribute = static::getAttributeName($attribute);
        $label = ArrayHelper::remove($options, 'label', $model->getAttributeLabel($attribute));
        return static::label($label, $for, $options);
    }
}