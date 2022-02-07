<?php
namespace frontend\assets;

/**
 * Class WorkerAsset
 * @package frontend\assets
 */
class WorkerAsset extends AppAsset
{
    public $basePath = '@webroot';
    public $baseUrl = '@web/resource';
    public $css = [
        'https://fonts.googleapis.com/icon?family=Material+Icons',
        'css/structure.css',
        'css/custom.css',
        'css/style.css',
    ];
    public $js = [
        'vendor/bootstrap/bootstrap_3.3.7.min.js',
        'js/libs.js',
        'js/custom.js',
        'js/jquery.ba-throttle-debounce.min.js',
    ];
}