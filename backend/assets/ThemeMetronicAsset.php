<?php
namespace backend\assets;


use yii\web\AssetBundle;

class ThemeMetronicAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web/themes/metronic';

    public $css = [
        "global/plugins/jquery-ui/jquery-ui.min.css",
        //GLOBAL MANDATORY STYLES
        "global/plugins/font-awesome/css/font-awesome.min.css",
        "global/plugins/simple-line-icons/simple-line-icons.min.css",
        "global/plugins/bootstrap/css/bootstrap.min.css",
        "global/plugins/uniform/css/uniform.default.css",
        //PAGE LEVEL STYLES
        "global/plugins/bootstrap-toastr/toastr.min.css",
        "global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css",
        "global/plugins/bootstrap-daterangepicker/daterangepicker-bs3.css",
        "global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css",
        "global/plugins/fullcalendar/fullcalendar.min.css",
        "global/plugins/jqvmap/jqvmap/jqvmap.css",
        "global/plugins/select2/select2.css",
        "global/plugins/bootstrap-switch/css/bootstrap-switch.min.css",

        "global/plugins/bootstrap-wysihtml5/bootstrap-wysihtml5.css",
        "global/plugins/bootstrap-wysihtml5/wysiwyg-color.css",

        "global/plugins/bootstrap-fileinput/bootstrap-fileinput.css",
        //THEME STYLES
        "global/css/components.css",
        "global/css/plugins.css",
    ];
    public $js = [
        //CORE PLUGINS
//        "global/plugins/jquery.min.js",
//        "global/plugins/jquery-migrate.min.js",
        "global/plugins/jquery-ui/jquery-ui.min.js",
        "global/plugins/bootstrap/js/bootstrap.min.js",
        "global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js",
        "global/plugins/jquery-slimscroll/jquery.slimscroll.min.js",
        "global/plugins/jquery.blockui.min.js",
        "global/plugins/jquery.cokie.min.js",
        "global/plugins/uniform/jquery.uniform.min.js",
        //PAGE LEVEL PLUGINS
//        "global/plugins/jquery-validation/js/jquery.validate.min.js",
        "global/plugins/bootstrap-toastr/toastr.min.js",
        "global/plugins/jqvmap/jqvmap/jquery.vmap.js",
        "global/plugins/jqvmap/jqvmap/maps/jquery.vmap.russia.js",
        "global/plugins/jqvmap/jqvmap/maps/jquery.vmap.world.js",
        "global/plugins/jqvmap/jqvmap/maps/jquery.vmap.europe.js",
        "global/plugins/jqvmap/jqvmap/maps/jquery.vmap.germany.js",
        "global/plugins/jqvmap/jqvmap/maps/jquery.vmap.usa.js",
        "global/plugins/jqvmap/jqvmap/data/jquery.vmap.sampledata.js",
        "global/plugins/flot/jquery.flot.min.js",
        "global/plugins/flot/jquery.flot.resize.min.js",
        "global/plugins/flot/jquery.flot.categories.min.js",
        "global/plugins/jquery.pulsate.min.js",
        "global/plugins/bootstrap-daterangepicker/moment.min.js",
        "global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js",
        "global/plugins/bootstrap-daterangepicker/daterangepicker.js",
        'global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js',
        "global/plugins/fullcalendar/fullcalendar.min.js",
        "global/plugins/jquery-easypiechart/jquery.easypiechart.min.js",
        "global/plugins/jquery.sparkline.min.js",
        "global/plugins/select2/select2.min.js",
        "global/plugins/bootstrap-switch/js/bootstrap-switch.min.js",
        "global/plugins/bootstrap-fileinput/bootstrap-fileinput.js",

        "global/plugins/bootstrap-wysihtml5/wysihtml5-0.3.0.js",
        "global/plugins/bootstrap-wysihtml5/bootstrap-wysihtml5.js",

        "global/plugins/amcharts/amcharts/amcharts.js",
        "global/plugins/amcharts/amcharts/pie.js",
        "global/plugins/amcharts/amcharts/serial.js",
        "global/plugins/amcharts/amcharts/themes/light.js",

        "global/plugins/autosize/autosize.min.js",

        "global/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js",

        //PAGE LEVEL SCRIPTS
        "global/scripts/metronic.js",
    ];
}