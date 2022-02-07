<?php
namespace backend\modules\system;

use backend\models\BackendModule;

class SystemModule extends BackendModule
{
    const
        MODULE_ID = "SYSTEM";

    public $controllerNamespace = 'backend\modules\system\controllers';
}