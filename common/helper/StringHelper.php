<?php
namespace common\helper;


class StringHelper extends \yii\helpers\StringHelper
{
    public static function errorToString($error, $breakLine = "<br/>", $pre = "")
    {
        if (is_array($error)) {
            $result = $pre;
            foreach ($error as $key => $value) {
                $result .= self::errorToString($value, $breakLine);
            }
            return $result;
        } else {
            return $error . $breakLine;
        }
    }
}