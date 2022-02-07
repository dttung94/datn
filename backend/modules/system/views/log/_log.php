<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model SystemLogSearchForm
 */
use backend\modules\system\forms\log\SystemLogSearchForm;
use yii\helpers\Html;

$time = Yii::$app->formatter->asDatetime($model->log_time);
$type = "";
switch ($model->level) {
    case $model::LEVEL_ERROR:
        $type = "<label class='label label-danger'>E</label>";
        break;
    case $model::LEVEL_WARNING:
        $type = "<label class='label label-warning'>W</label>";
        break;
    case $model::LEVEL_INFO:
        $type = "<label class='label label-info'>I</label>";
        break;
    default:
        $type = "";
        break;
}
echo "<span style='color: white;'>$type $time</span> <span style='color: red;'>[$model->category]</span> $model->prefix<br/>";
echo nl2br($model->message);