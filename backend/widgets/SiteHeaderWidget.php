<?php
namespace backend\widgets;


use yii\bootstrap\Widget;

class SiteHeaderWidget extends Widget
{
    public function init()
    {
        echo $this->render("site-header/index", []);
    }
}