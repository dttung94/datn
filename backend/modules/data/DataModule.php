<?php
namespace backend\modules\data;

use backend\models\BackendModule;

class DataModule extends BackendModule
{
    const
        MODULE_ID = "DATA";

    public $controllerNamespace = 'backend\modules\data\controllers';

    public function init()
    {
        parent::init();
    }
}