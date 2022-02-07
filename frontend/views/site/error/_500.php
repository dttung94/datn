<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

$this->title = $message;
?>
<div class="row">
    <div class="col-md-12 page-500">
        <div class=" details">
<!--            <h3>--><?php //echo $message ?><!--</h3>-->
            <p>
                <?php echo App::t("frontend.error.message", "We are fixing it!<br/>Please come back in a while.<br/><br/>"); ?>
            </p>
        </div>
    </div>
</div>