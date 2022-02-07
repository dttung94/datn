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
<body ng-app="ShopApp"
      ng-controller="MemberController"
      ng-init="init()">
<?php $this->beginBody() ?>
<header>
    <div class="header-title box-container box-space-between">
        <div class="header-title-left box-container">
            <?php echo HtmlHelper::a('<i class="icon logout mr5"></i><span class="hidden-xs hidden-sm">Đăng xuất</span>', [
                "/site/logout"
            ], [
                "class" => "box-container box-middle",
                "data-method" => "POST",
                "data-confirm" => App::t("frontend.authentication.message", "Bạn muốn đăng xuất ngay?"),
            ]) ?>
        </div>

        <div class="header-title-right box-container">
            <a href="/profile/index"
               class="box-container box-middle" style="border-right: none">
                <i class="material-icons">account_circle</i>
            </a>

            <a href="/booking/history"
               class="box-container box-middle">
                <img src="<?php echo $bundle->baseUrl ?>/images/history.png" style="width: 30px">
            </a>

            <a href="javascript:;"
               class="box-container box-middle"
               ng-click="openBookingHistory()">
                <i class="material-icons">event_available</i>
            </a>
        </div>
    </div>

    <div class="header-wrap box-container box-space-between">
        <div class="header-wrap-avatar box-container box-bottom flex-pc">
            <img src="<?php echo $bundle->baseUrl ?>/images/new-ava.png" alt="">
        </div>

        <div class="header-wrap-logo">
            <div class="block-pc">
                <img src="<?php echo $bundle->baseUrl ?>/images/header_logo_v1.png" style="max-width: 150px; max-height: 150px;">
            </div>
            <div class="header-wrap-gnav box-container box-middle box-row-wrap">
                <?php foreach ($shops as $shop) {
//                    if (!ShopCalendar::isLockUserBooking($shop->shop_id)) {
                        echo HtmlHelper::beginTag("div", [
                            "class" => "nav-item active",
                        ]);
                        echo HtmlHelper::a($shop->shop_name, App::$app->urlManager->createUrl([
                            "/shop/$shop->shop_id"
                        ]));
                        echo HtmlHelper::endTag("div");
//                    }
                } ?>
            </div>
        </div>

    </div>
</header>

<div id="content">
    <?php echo $content; ?>
</div>

<footer class="tac mt20 mb20">
</footer>

<?php
echo $this->render("modals/_modal_booking_history", []);
echo $this->render("modals/_modal_booking_online_edit", []);
echo $this->render("modals/_modal_change_password", []);
?>
<?php if (App::$app->session->hasFlash("ALERT_MESSAGE") || App::$app->session->hasFlash("ERROR_MESSAGE")) {
    echo $this->render("modals/_modal_alert_message", []);
} ?>
<?php $this->endBody() ?>
</body>
<script>
    var token = '<?php echo $_GET['token'] ?? ''?>';
    function openEmailContact() {
        $('#emailContact').modal('show');
        $('#list_worker').val('');
        $('#list_worker_view').hide();
    }
    if (token !== '') {
        openEmailContact();
    }
</script>
<?php $this->endcontent(); ?>
