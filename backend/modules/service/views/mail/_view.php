<?php
use backend\modules\service\forms\mail\MailSearchForm;
use yii\helpers\Json;

/**
 * @var $this \backend\models\BackendView
 * @var $model MailSearchForm
 */
$this->subTitle = App::t("backend.service_mail.title", "Lịch sử email");
?>
<div class="inbox-header inbox-view-header">
    <h1 class="pull-left">
        <?php echo $model->subject; ?>
    </h1>
</div>
<div class="inbox-view-info">
    <div class="row">
        <div class="col-md-7">
            <?php
            $tos = Json::decode($model->to);
            ?>
            <span class="bold"><?php echo $tos[0]["name"] ?></span>&nbsp;
            <span>&#60;<?php echo $tos[0]["email"] ?>&#62;</span>&nbsp;
            <i class="fa fa-calendar"></i>&nbsp;<?php echo App::$app->formatter->asDatetime($model->created_at) ?>
        </div>
    </div>
</div>
<div class="inbox-view">
    <?php echo $model->content; ?>
</div>
<hr>
