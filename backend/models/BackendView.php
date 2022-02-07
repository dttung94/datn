<?php
namespace backend\models;


use common\entities\system\SystemConfig;
use yii\web\View;

/**
 * Class BackendAdminView
 * @package backend\models
 *
 * @property string $title
 * @property string $subTitle
 * @property array $breadcrumbs
 * @property array $actions
 * @property array $themeOptions
 */
class BackendView extends View
{
    public $subTitle;
    public $breadcrumbs = [];
    public $actions = [];
    public $themeOptions = [];

    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }
}