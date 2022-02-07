<?php
namespace frontend\assets;

use yii\web\AssetBundle;
use yii\web\View;

class AngularAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web/angular';
    public $js = [
        'v1.6.9/angular.js',
        'v1.6.9/angular-route.js',
        'v1.6.9/angular-animate.js',
        'v1.6.9/i18n/angular-locale_ja.js',

        'angular-strap/angular-strap.js',
        'ui-bootstrap/ui-bootstrap-tpls-2.5.0.min.js',
    ];
    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];
}
