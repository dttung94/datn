<?php
namespace backend\assets;

use yii\web\AssetBundle;
use yii\web\View;

class AngularAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web/angular';
    public $js = [
        'v1.6.9/angular.js',
        'v1.6.9/angular-route.js',
        'v1.6.9/angular-sanitize.js',
        'v1.6.9/angular-animate.js',
        'v1.6.9/i18n/angular-locale_ja.js',

        'angular-strap/angular-strap.js',
        'angular-dragdrop/angular-dragdrop.min.js',
        'select2/select2.js',
        'angular-tooltips/angular-tooltips.js',
    ];
    public $css = [
        'angular-tooltips/angular-tooltips.css',
    ];
    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];
    public $depends = [
        'yii\web\YiiAsset'
    ];
}
