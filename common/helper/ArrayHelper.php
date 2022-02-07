<?php
namespace common\helper;


class ArrayHelper extends \yii\helpers\ArrayHelper
{
    public static function selectRandomInArray($array, $amount) {
        $selectedKeys = array_rand($array, $amount);
        if ($amount == 1) {
            $selectedKeys = [$selectedKeys];
        }
        $result = [];
        foreach ($selectedKeys as $key) {
            $result[] = $array[$key];
        }
        return $result;
    }
}