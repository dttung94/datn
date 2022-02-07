<?php
use backend\assets\GuestAsset;
use yii\helpers\Html;
use common\entities\system\SystemConfig;

/* @var $this \yii\web\View */
/* @var $content string */
$asset = GuestAsset::register($this);
$this->registerJs(<<<JS
    jQuery(document).ready(function () {
        Metronic.init(); // init metronic core components
        Layout.init(); // init current layout
    });
JS
    , \yii\web\View::POS_END);
?>
<?php $this->beginContent('@backend/views/layouts/layout_base.php'); ?>
    <body class="login">
    <?php $this->beginBody() ?>
    <!-- BEGIN SIDEBAR TOGGLER BUTTON -->
    <div class="menu-toggler sidebar-toggler">
    </div>
    <!-- END SIDEBAR TOGGLER BUTTON -->
    <!-- BEGIN LOGO -->
    <div class="logo">
<!--        <a href="--><?php //echo App::$app->homeUrl ?><!--" style="text-decoration: none;">-->
<!--            --><?php
//            $logoImg = SystemConfig::getValue(SystemConfig::CATEGORY_SYSTEM, SystemConfig::SYSTEM_SITE_LOGO);
//            if ($logoImg) {
//                echo Html::img($logoImg, [
//                ]);
//            } else {
//                echo Html::tag("h3", SystemConfig::getValue(SystemConfig::CATEGORY_SYSTEM, SystemConfig::SYSTEM_SITE_NAME), [
//                    "style" => [
//                        "color" => "#fff"
//                    ],
//                ]);
//            }
//            ?>
<!--        </a>-->
    </div>
    <!-- END LOGO -->
    <!-- BEGIN LOGIN -->
    <div class="content">
        <?php echo $content ?>
    </div>
    <div class="copyright">
        <?php echo SystemConfig::getValue(SystemConfig::CATEGORY_SYSTEM, SystemConfig::SYSTEM_SITE_COPYRIGHT) ?>
    </div>
    <!-- END LOGIN -->
    <?php $this->endBody() ?>
    </body>
<?php $this->endcontent(); ?>