<?php
namespace frontend\models;


use common\entities\system\SystemConfig;
use common\models\base\AbstractView;

/**
 * Class FrontendView
 * @package frontend\models
 *
 * @property string $title
 * @property string $subTitle
 * @property array $breadcrumbs
 * @property array $actions
 */
class FrontendView extends AbstractView
{
    public $subTitle;
    public $breadcrumbs = [];
    public $actions = [];

    public function __construct(array $config = [])
    {
        $this->title = SystemConfig::getValue(SystemConfig::CATEGORY_SYSTEM, SystemConfig::SYSTEM_SITE_NAME);
        parent::__construct($config);
    }
}