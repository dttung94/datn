<?php
use frontend\assets\MemberAsset;
use common\helper\HtmlHelper;
use frontend\forms\shop\ShopForm;
use common\entities\shop\ShopInfo;
use common\entities\shop\ShopCalendar;
use common\helper\DatetimeHelper;
use common\entities\user\UserInfo;

/**
 * @var $this \yii\web\View
 * @var $content string
 * @var $shops ShopForm[]
 */
$bundle = MemberAsset::register($this);
$shops = ShopInfo::find()
    ->innerJoin(ShopCalendar::tableName(),
        ShopCalendar::tableName() . ".shop_id = " . ShopInfo::tableName() . ".shop_id AND " . ShopCalendar::tableName() . ".date = :date AND " . ShopCalendar::tableName() . ".type = :type AND " . ShopCalendar::tableName() . ".status = :status", [
            ":type" => ShopCalendar::TYPE_WORKING_DAY,
            ":date" => App::$app->request->get("date", DatetimeHelper::now(DatetimeHelper::FULL_DATE)),
            ":status" => ShopCalendar::STATUS_ACTIVE,
        ])
    ->where([
        ShopInfo::tableName() . ".status" => ShopInfo::STATUS_ACTIVE,
    ])
    ->all();;
$shop_id = App::$app->request->get("shop_id");
$userInfo = App::$app->user->identity;
?>
<?php $this->beginContent('@frontend/views/layouts/layout_base.php'); ?>
<head>
    <link rel="stylesheet" href="/resource/css/profile.css" type="text/css">
    <link rel="stylesheet" href="/resource/vendor/bootstrap-toastr/toastr.css" type="text/css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="/resource/css/croppie.css?version=1">

    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?php echo $this->title ?> |
            <?php echo $this->subTitle; ?>
    </title>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-24341147-40"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());
        gtag('config', 'UA-24341147-40');
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script src="/resource/vendor/bootstrap-toastr/toastr.js"></script>
    <script src="/resource/js/croppie.js"></script>
</head>

<body>
    <header>
        <div class="pf-hd-container">
            <a href="/">
            <img class="pf-hd-logo" height="60" width="60" src="<?php echo $bundle->baseUrl ?>/images/header_logo_v1.png">
            </a>
            <a href="/" class="pf-hd-logo-right">
                <span>Quay lại</span>
            </a>
        </div>
        <?php if (App::$app->session->hasFlash("ALERT_MESSAGE")) {
                echo HtmlHelper::tag("span", "", [
                    "class" => "alert-message",
                    "data-alert-message" => App::$app->session->getFlash("ALERT_MESSAGE", App::$app->session->getFlash("ERROR_MESSAGE")),
                ]);
        } ?>
    </header>
    <div class="pf-content">
        <div class="pf-row-container">
            <div class="col-md-2">
                <div class="panel-body panel-body-padding">
                    <div class="pf-nav nav-pc" id="pf_nav">
                        <a class="pf-nav-item your-email item-your-email" href="/profile/index">
                            <span class="pf-nav-item-title">Thông tin cá nhân</span>
                        </a>
                        <a class="pf-nav-item your-email item-your-email" href="/profile/change-password">
                            <span class="pf-nav-item-title">Đổi mật khẩu</span>
                        </a>

                    </div>


                </div>
            </div>
            <div class="col-md-8">
                <div class="panel panel-default profile">
                    <div class="panel-body">
                        <div class="modal-body contact">
                            <div class="content-action active" style="margin-bottom: 20px; margin-top: 20px;">
                                <?php echo $content; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer class="pf-footer"></footer>
</body>
<script src="/resource/js/profile.js"></script>
