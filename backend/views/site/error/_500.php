<?php

use backend\assets\AppAsset;

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

$this->title = $message;
$asset = AppAsset::register($this);
$this->registerCssFile($asset->baseUrl . "/pages/css/error.css", [
    "depends" => [
        AppAsset::className()
    ]
]);
?>
<body class="page-500-full-page">
<?php $this->beginBody() ?>
<div class="row">
    <div class="col-md-12 page-500">
        <div class=" number">
            500
        </div>
        <div class=" details">
            <h3><?php echo $message ?></h3>
            <p>
                <?php echo App::t("backend.error.message", "We are fixing it!<br/>Please come back in a while.<br/><br/>"); ?>
            </p>
        </div>
    </div>
</div>
<?php $this->endBody() ?>
</body>
