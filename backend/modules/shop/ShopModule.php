<?php
namespace backend\modules\shop;

use backend\models\BackendModule;

class ShopModule extends BackendModule
{
    const MODULE_ID = "SHOP";
    public $controllerNamespace = 'backend\modules\shop\controllers';

    public function init()
    {
        parent::init();
    }
}