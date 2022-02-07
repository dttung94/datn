<?php
namespace backend\widgets;


use yii\bootstrap\Widget;

class SideBarWidget extends Widget
{
    public function init()
    {
        echo $this->render("side-bar/index", []);
    }
}