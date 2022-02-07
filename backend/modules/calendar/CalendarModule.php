<?php
namespace backend\modules\calendar;

use backend\models\BackendModule;

class CalendarModule extends BackendModule
{
    const
        MODULE_ID = "CALENDAR";

    public $controllerNamespace = 'backend\modules\calendar\controllers';

    public function init()
    {
        parent::init();
    }
}