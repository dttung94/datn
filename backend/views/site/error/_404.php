<?php
use backend\assets\AppAsset;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

$this->title = $name;
$asset = AppAsset::register($this);
$this->registerCssFile($asset->baseUrl . "/pages/css/error.css", [
    "depends" => [
        AppAsset::className()
    ]
]);
?>
<body class="page-404-full-page">
<?php $this->beginBody() ?>
<div class="row">
    <div class="col-md-12 page-404">
        <div class="number">
            404
        </div>
        <div class="details">
            <h3><?php echo $name ?></h3>
            <p>
                <?php echo App::t("backend.error.message", 'We can not find the page you\'re looking for.<br/><a href="{home-link}">Return home </a>', [
                    "home-link" => App::$app->urlManager->createUrl([
                        "site/index"
                    ]),
                ]); ?>
            </p>
        </div>
    </div>
</div>
<?php $this->endBody() ?>
</body>
