<?php
use frontend\assets\GuestAsset;
use common\helper\HtmlHelper;

/**
 * @var $this \yii\web\View
 * @var $content string
 */

$bundle = GuestAsset::register($this);
$this->registerJs(<<<JS
    jQuery(document).ready(function () {
    });
JS
    , \yii\web\View::POS_END, "register-js-guest");
?>
<?php $this->beginContent('@frontend/views/layouts/layout_base.php'); ?>
    <body ng-app="ShopApp"
          ng-controller="GuestController"
          ng-init="init()"
    class ="login-container">
    <?php $this->beginBody() ?>
    <header class="layout-guest">
        <div class="header-title box-container box-end flex-pc">
            <div class="header-title-left box-container">
            </div>

            <div class="header-title-center">
            </div>

            <div class="header-title-right box-container">
                <?php echo HtmlHelper::a('<i class="material-icons">perm_identity</i> <span>' . App::t("frontend.guest.button", "Đăng nhập") . '</span>', [
                    "/site/login",
                ], [
                    "class" => "box-container box-middle",
                ]) ?>
                <?php echo HtmlHelper::a('<span>' . App::t("frontend.guest.button", "Đăng ký") . '</span>', [
                    "/site/sign-up",
                ], [
                    "class" => "box-container box-middle",
                ]) ?>
            </div>
        </div>

    </header>
    <div id="content">
        <?php echo $content; ?>
    </div>
    <footer>
    </footer>
    <?php if (App::$app->session->hasFlash("ALERT_MESSAGE") || App::$app->session->hasFlash("ERROR_MESSAGE")) {
        echo $this->render("modals/_modal_alert_message", [
        ]);
    } ?>
    <?php $this->endBody() ?>
    </body>
<?php $this->endcontent(); ?>
