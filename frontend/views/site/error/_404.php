<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

$this->title = $message;
?>
<div class="row">
    <div class="col-md-12 page-404">
        <div class="details">
            <h3><?php echo $name ?></h3>
            <p>
                <?php echo App::t("frontend.error.message", 'We can not find the page you\'re looking for.<br/><a href="{home-link}">Return home </a>', [
                    "home-link" => App::$app->urlManager->createUrl([
                        "site/index"
                    ]),
                ]); ?>
            </p>
        </div>
    </div>
</div>
