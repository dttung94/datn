<?php
namespace common\models\base;


use yii\web\View;

/**
 * Class AbstractView
 * @package common\models\base
 *
 * @property $title string
 * @property $subTitle string
 * @property $breadcrumbs array
 * @property $actions array
 *
 */
abstract class AbstractView extends View
{
    public $breadcrumbs = [];
    public $actions = [];
    public $subTitle;
}