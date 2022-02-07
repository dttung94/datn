<?php
use backend\assets\AppAsset;
use yii\helpers\Html;
use common\entities\system\SystemConfig;
use common\entities\system\SystemSound;

/* @var $this \yii\web\View */
/* @var $content string */

$assetBundle = AppAsset::register($this);
$homeUrl = Yii::$app->urlManager->baseUrl;
$isOpenTranslate = isset($_GET['translate']) && $_GET['translate'] ? 1 : 0;
$timezone = App::$app->timeZone;
//$sounds = SystemSound::find()->all();
//$audioSocket = "";
//foreach ($sounds as $sound) {
//    $nameSound = $sound['name_sound'];
//    $idSound = 'audio_'.$sound['id'];
//    $audioSocket .= "<audio id='$idSound' preload='auto'><source type='audio/wav' src='$assetBundle->baseUrl/audio/$nameSound'></audio>";
//}
$this->registerJs(<<<JS
    jQuery(document).ready(function () {
        App.init();
        if($isOpenTranslate){
            TranslateTool.init();
        }
        window.homeUrl = "$homeUrl";
        window.timezone = "$timezone";
    });
JS
    , \yii\web\View::POS_END);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" ng-app="BackendApp">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet"
          type="text/css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <script src="https://cdn.amcharts.com/lib/4/core.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/charts.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>

    <!--    <audio id="audio-alert" preload="auto">-->
<!--        <source type="audio/mp3" src="--><?php //echo $assetBundle->baseUrl; ?><!--/audio/alert.mp3">-->
<!--    </audio>-->
<!--    <audio id="audio-fail" preload="auto">-->
<!--        <source type="audio/mp3" src="--><?php //echo $assetBundle->baseUrl; ?><!--/audio/fail.mp3">-->
<!--    </audio>-->
<!--    <audio id="audio-notice" preload="auto">-->
<!--        <source type="audio/wav" src="--><?php //echo $assetBundle->baseUrl; ?><!--/audio/yoyaku.wav">-->
<!--    </audio>-->
<!--    <audio id="audio-shinsei" preload="auto">-->
<!--        <source type="audio/wav" src="--><?php //echo $assetBundle->baseUrl; ?><!--/audio/tourokusinsei.wav">-->
<!--    </audio>-->


    <audio id="audio_0" preload="auto">
        <source type="audio/wav" src="">
    </audio>

    <?= Html::csrfMetaTags() ?>
    <title>
        <?= Html::encode(SystemConfig::getValue(SystemConfig::CATEGORY_SYSTEM, SystemConfig::SYSTEM_SITE_NAME)) ?>
        <?= !empty($this->title) ? " | " . Html::encode($this->title) : "" ?></title>
    <?php $this->head() ?>
</head>
<?php echo $content; ?>
</html>
<?php $this->endPage() ?>
