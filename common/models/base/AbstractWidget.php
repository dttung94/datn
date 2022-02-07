<?php
namespace common\models\base;


use yii\base\Widget;

abstract class AbstractWidget extends Widget
{
    public function currentUserId()
    {
        return \Yii::$app->user->id;
    }
}