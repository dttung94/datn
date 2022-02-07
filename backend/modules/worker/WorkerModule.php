<?php
namespace backend\modules\worker;

use backend\models\BackendModule;

class WorkerModule extends BackendModule
{
    const MODULE_ID = "WORKER";
    public $controllerNamespace = 'backend\modules\worker\controllers';

    public function init()
    {
        parent::init();
    }
}