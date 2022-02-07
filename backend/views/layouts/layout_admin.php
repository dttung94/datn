<?php
use backend\assets\AdminAsset;
use common\entities\system\SystemConfig;
use common\helper\ArrayHelper;
use yii\helpers\Json;

/* @var $this \backend\models\BackendView */
/* @var $content string */
$asset = AdminAsset::register($this);
$urls = Json::encode([
    "memberManage" => Yii::$app->urlManager->createUrl([
        "member/manage",
    ]),
]);
$env_ws = in_array($_SERVER['SERVER_NAME'], ['rsv.unpretty.biz', 'admin.rsv.unpretty.biz']) ? 'wss':'ws';
$serverIpAddr = in_array($_SERVER['SERVER_NAME'], ['rsv.unpretty.biz', 'admin.rsv.unpretty.biz']) ? 'socket.rsv.unpretty.biz':$_SERVER['HTTP_HOST'];
$port = in_array($_SERVER['SERVER_NAME'], ['rsv.unpretty.biz', 'admin.rsv.unpretty.biz']) ? 443:8080;
$this->registerJs(<<<JS
    window.urls = $urls;
    var conn = null;
   
    function startWebSocket() {
        conn = new WebSocket('$env_ws://$serverIpAddr:$port');
        conn.onmessage = function(e) {
            var typeCallGetSession = ['bookingOnlineCreated', 'bookingFreeCreated', 'bookingFreeConfirmAccept', 'bookingFreeConfirmReject', 'bookingCanceled', 'newMemberSignUp', 'bookingOnlineUpdating'];
            var typeCallGetBookingCount = ['bookingCanceledByManager', 'bookingOnlineCreated', 'bookingOnlineConfirmExpired', 'bookingOnlineAccepted', 'bookingOnlineRejected', 'bookingOnlineUpdated', 'bookingOnlineUpdating'];
            var dataEvent = JSON.parse(e.data);
            if (typeCallGetBookingCount.includes(dataEvent.type)) {
                // trigger event bookingOnlineStatusChanged in /calendar/booking
                $(document).trigger("bookingOnlineStatusChanged");
            }
            
            if (typeCallGetSession.indexOf(dataEvent.type) >= 0) {
                $.ajax({
                    url: '/calendar/booking/get-session',
                    type: 'GET',
                    dataType: 'json',
                    contentType: 'json',
                    success: function(res) {
                        var shopIds = res.session;
                        $(document).trigger(dataEvent.type, dataEvent);
                        toastr.info(dataEvent.message);
                    }
                });
            } else {
                $(document).trigger(dataEvent.type, dataEvent);
            }
        };
        conn.onopen = function(e) {
            console.log("Connection established!");
        };
        conn.onerror = function(e) {
            // console.log("Socket error", e);
        };
        conn.onclose = function(e) {
            console.log("Socket close", e);
            setTimeout(function(){
                startWebSocket()
            }, 5000);
        };
    }
    
    jQuery(document).ready(function () {
        Metronic.init(); // init metronic core componets
        Layout.init(); // init layout
        QuickSidebar.init(); // init quick sidebar
        startWebSocket(); //todo start websocket
    });
JS
    , \yii\web\View::POS_END);
?>
<?php $this->beginContent('@backend/views/layouts/layout_base.php'); ?>
    <body class="page-header-fixed page-quick-sidebar-over-content page-container-bg-solid <?php echo ArrayHelper::getValue($this->themeOptions, "bodyClass") ?>"
          ng-controller="MainController">
    <?php $this->beginBody() ?>
    <!-- BEGIN HEADER -->
    <?php echo \backend\widgets\SiteHeaderWidget::widget([]) ?>
    <!-- END HEADER -->
    <div class="clearfix">
    </div>
    <!-- BEGIN CONTAINER -->
    <div class="page-container">
        <!-- BEGIN MENU SIDEBAR -->
        <?php echo \backend\widgets\SideMenuWidget::widget([]) ?>
        <!-- END MENU SIDEBAR -->
        <!-- BEGIN CONTENT -->
        <div class="page-content-wrapper">
            <div class="page-content" style="margin-left: 175px;">
                <!-- BEGIN PAGE HEADER-->
                <?php echo \backend\widgets\PageHeaderWidget::widget([]); ?>
                <!-- END PAGE HEADER-->
                <?php echo $content; ?>
            </div>
        </div>
        <!-- END CONTENT -->
    </div>
    <!-- END CONTAINER -->
    <!-- BEGIN FOOTER -->
    <div class="page-footer">
        <div class="page-footer-inner">
            <?php echo SystemConfig::getValue(SystemConfig::CATEGORY_SYSTEM, SystemConfig::SYSTEM_SITE_COPYRIGHT) ?>
        </div>
        <div class="scroll-to-top">
            <i class="icon-arrow-up"></i>
        </div>
<!--        <script>-->
<!--            function checkIsOnline() {-->
<!--                $.ajax({-->
<!--                    url: '/site/check-is-online',-->
<!--                    type: 'GET',-->
<!--                    success: function (res) {-->
<!--                        if (res === 'is_offline') {-->
<!--                            $.ajax({-->
<!--                                url: '/site/logout',-->
<!--                                type: 'POST'-->
<!--                            })-->
<!--                        }-->
<!--                    }-->
<!--                })-->
<!--            }-->
<!--            checkIsOnline();-->
<!--        </script>-->
    </div>
    <!-- END FOOTER -->
    <?php echo $this->render("_modal_send_sms", []); ?>
    <?php echo $this->render("_modal_send_email", []); ?>
    <?php echo $this->render("_modal_alert_message", []); ?>
    <?php $this->endBody() ?>
    </body>
<?php $this->endcontent(); ?>
