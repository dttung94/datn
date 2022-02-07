<?php
namespace backend\modules\member;

use backend\models\BackendModule;

class MemberModule extends BackendModule
{
    const
        MODULE_ID = "MEMBER";

    public $controllerNamespace = 'backend\modules\member\controllers';

    public function init()
    {
        parent::init();
    }
}