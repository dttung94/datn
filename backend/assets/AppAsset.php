<?php
namespace backend\assets;

use yii\web\AssetBundle;

class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web/resource';
    public $css = [
        "vendor/angular-clock/angular-clock.css",
        //THEME STYLES
        "css/layout.css",
        "css/themes/darkblue.css",
        "css/custom.css?v=1",
        "css/translate-tool.css",
    ];
    public $js = [
        "vendor/ui-bootstrap/ui-bootstrap-tpls-2.5.0.min.js",
        "vendor/angular-clock/angular-clock.js",
        "vendor/clipboard.js/clipboard.min.js",
        "vendor/moment-timezone/moment-timezone.js",
        "vendor/moment-timezone/moment-timezone-with-data.js",
        //PAGE LEVEL SCRIPTS
        "scripts/layout.js",
        "scripts/libs.js",
        "scripts/app.js",
        "scripts/translate-tool.js",
        //Angular JS
        "js/angular-app.js?version=2",
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'backend\assets\AngularAsset',
        'backend\assets\ThemeMetronicAsset',
    ];
}
