<?php

namespace backend\modules\rating;

use backend\models\BackendModule;

class RatingModule extends BackendModule
{
    const
        MODULE_ID = "RATING";

    public $controllerNamespace = 'backend\modules\rating\controllers';

    public function init()
    {
        parent::init();
    }
}
