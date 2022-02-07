<?php
namespace backend\modules\service;

use backend\models\BackendModule;

class ServiceModule extends BackendModule
{
    const
        MODULE_ID = "SERVICE";

    public $controllerNamespace = 'backend\modules\service\controllers';
}