<?php
namespace backend\widgets;


use yii\bootstrap\Widget;

class PageHeaderWidget extends Widget
{
    public function init()
    {
        echo $this->render("page-header/index", []);
    }
}