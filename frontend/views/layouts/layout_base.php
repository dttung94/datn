<?php
use frontend\assets\AppAsset;
use common\entities\system\SystemConfig;
use yii\db\Query;

/**
 * @var $this \frontend\models\FrontendView
 * @var $content string
 */
AppAsset::register($this);
$homeUrl = Yii::$app->urlManager->baseUrl;
$env_ws = 'ws';
$serverIpAddr = $_SERVER['HTTP_HOST'];
$port = 8080;

$openSlotTime = '00:01';
$endSlotTime = '23:59';
$this->registerJs(<<<JS
    window.homeUrl = "$homeUrl";
    var conn = null;
    function startWebSocket() {
        var timezone = 'Asia/Ho_Chi_Minh';
        var now = moment().tz(timezone);
        var nowHour = now.format('YYYY-MM-DD HH:mm');
        var openSlotTime = now.format('YYYY-MM-DD') + ' $openSlotTime';
        var endSlotTime = now.format('YYYY-MM-DD') + ' $endSlotTime';

        if (nowHour < openSlotTime || nowHour > endSlotTime) {
            console.log("Socket cant be established during limit hour");
            return;
        }
        conn = new WebSocket('$env_ws://$serverIpAddr:$port');
        conn.onmessage = function(e) {
            var data = JSON.parse(e.data);
            $(document).trigger(data.type, data);
        };
        conn.onopen = function(e) {
        };
        conn.onerror = function(e) {
            console.log("Socket error", e);
        };
        conn.onclose = function(e) {
            console.log("Socket close", e);
            setTimeout(function(){
                startWebSocket()
            }, 5000);
        };
    }
    jQuery(document).ready(function () {
        startWebSocket();
    });
    jQuery(document).on("ping", function (event, data) {
        console.log("WebSocket ping", event, data);
    });
JS
    , \yii\web\View::POS_END);
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo $this->title ?> | <?php echo $this->subTitle; ?></title>
        <?php $this->head() ?>
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
    </head>
    <?php echo $content; ?>
    </html>
<?php $this->endPage() ?>