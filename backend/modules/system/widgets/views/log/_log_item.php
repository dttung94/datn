<?php
/**
 * Date: 10/26/15
 * Time: 9:20 AM
 *
 * @var $this \app\backend\models\BackendView
 *
 * @var $widget \yii\widgets\ListView
 * @var $model SystemLogSearchForm
 *
 * @var $key
 * @var $index
 */
use app\backend\modules\system\forms\log\SystemLogSearchForm;

$this->registerCss(<<< CSS
.feeds li .col2 {
    float: left;
    width: 175px !important;
    margin-left: -175px !important;
}
CSS
);
$labelClass = "";
switch ($model->level) {
    case $model::LEVEL_INFO:
        $labelClass = "label-info";
        break;
    case $model::LEVEL_WARNING:
        $labelClass = "label-warning";
        break;
    case $model::LEVEL_ERROR:
        $labelClass = "label-danger";
        break;
}
?>
<li>
    <div class="col1">
        <div class="cont">
            <div class="cont-col1">
                <div class="label label-sm <?php echo $labelClass; ?>">
                    <i class="fa fa-bell-o fa-<?php echo $model->category ?>"></i>
                </div>
            </div>
            <div class="cont-col2">
                <div class="desc">
                    <?php echo $model->message; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col2">
        <div class="date" style="font-size: 12px;">
            <?php echo Yii::$app->formatter->asDatetime($model->log_time); ?>
            <i class="fa fa-clock-o"></i>
        </div>
    </div>
</li>
