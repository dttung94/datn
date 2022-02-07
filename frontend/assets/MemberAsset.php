<?php
namespace frontend\assets;

/**
 * Class GuestAsset
 * @package frontend\assets
 */
class MemberAsset extends AppAsset
{
    public $basePath = '@webroot';
    public $baseUrl = '@web/resource';
    public $css = [
        'https://fonts.googleapis.com/icon?family=Material+Icons',
        "vendor/bootstrap-toastr/toastr.css",
        "vendor/jquery-select2/select2.css",
        'css/structure.css',
        'css/custom.css',
        'css/style.css',
    ];
    public $js = [
        'vendor/bootstrap/bootstrap_3.3.7.min.js',
        "vendor/bootstrap-toastr/toastr.js",
        "vendor/jquery-blockui/jquery.blockUI.js",
        "vendor/jquery-select2/select2.js",
        "vendor/momentjs/moment.js",
        "vendor/moment-timezone/moment-timezone.js",
        "vendor/moment-timezone/moment-timezone-with-data.js",
        'js/libs.js',
        'js/custom.js',
        'angular/app.js',
        'angular/controllers.js',
        'js/jquery.ba-throttle-debounce.min.js',
    ];
}
