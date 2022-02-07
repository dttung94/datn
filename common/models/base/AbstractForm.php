<?php
namespace common\models\base;


use common\helper\StringHelper;
use yii\helpers\Inflector;

abstract class AbstractForm extends AbstractObject
{
    public static function tableName()
    {
        return Inflector::camel2id(StringHelper::basename(get_called_class()), '_');
    }
}