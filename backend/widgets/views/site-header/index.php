<?php
use common\helper\HtmlHelper;
use common\entities\international\LanguageInfo;
use backend\assets\AppAsset;
use common\entities\system\SystemConfig;

/**
 * @var $this \yii\web\View
 * @var $asset \backend\assets\AdminAsset
 */
$asset = Yii::$app->assetManager->getBundle(AppAsset::className());

?>
<div class="page-header navbar navbar-fixed-top">
    <!-- BEGIN HEADER INNER -->
    <div class="page-header-inner">
        <!-- BEGIN LOGO -->
        <div class="page-logo">
        </div>
        <!-- END LOGO -->
        <!-- BEGIN RESPONSIVE MENU TOGGLER -->
        <a href="javascript:;" class="menu-toggler responsive-toggler" data-toggle="collapse"
           data-target=".navbar-collapse">
        </a>
        <!-- END RESPONSIVE MENU TOGGLER -->
        <!-- BEGIN TOP NAVIGATION MENU -->
        <div class="top-menu">
            <ul class="nav navbar-nav pull-right">

                <!-- END TODO DROPDOWN -->
                <!-- BEGIN USER LOGIN DROPDOWN -->
                <!-- DOC: Apply "dropdown-dark" class after below "dropdown-extended" to change the dropdown styte -->
                <li class="dropdown dropdown-user">
                    <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown"
                       data-close-others="true">
                        <span class="username username-hide-on-mobile">
                            <?php echo App::$app->user->identity->full_name ?>
                        </span>
                        <i class="fa fa-angle-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-default">
                        <li>
                            <?php echo HtmlHelper::a('<i class="icon-user"></i> Thông tin cá nhân', [
                                "/profile/index"
                            ], []); ?>
                        </li>
                        <li class="divider">
                        </li>
                        <li>
                            <?php echo HtmlHelper::a("<i class=\"icon-key\"></i> " . App::t("backend.menu.profile", "Đăng xuất"), Yii::$app->urlManager->createUrl([
                                "site/logout"
                            ]), [
                                "data-method" => "post"
                            ]); ?>
                        </li>
                    </ul>
                </li>
                <!-- END USER LOGIN DROPDOWN -->
            </ul>
        </div>
        <!-- END TOP NAVIGATION MENU -->
    </div>
    <!-- END HEADER INNER -->
</div>
<!--<script>-->
<!--    var volume;-->
<!--    function inputVolume() {-->
<!--        var iconVolume, classIcon;-->
<!--        iconVolume = $('#icon-volume');-->
<!--        volume = parseInt($('#change-volume').val());-->
<!--        if (volume === 0) {-->
<!--            classIcon = 'fa fa-volume-off';-->
<!--        } else if (volume > 0 && volume < 50) {-->
<!--            classIcon = 'fa fa-volume-down';-->
<!--        } else {-->
<!--            classIcon = 'fa fa-volume-up';-->
<!--        }-->
<!--        iconVolume.removeAttr('class').addClass(classIcon);-->
<!--    }-->
<!---->
<!--    function changeVolume() {-->
<!--        $.ajax({-->
<!--            url: '/site/change-volume?volume='+volume-->
<!--        });-->
<!--    }-->
<!--    inputVolume();-->
<!--    changeVolume();-->
<!---->
<!--</script>-->
