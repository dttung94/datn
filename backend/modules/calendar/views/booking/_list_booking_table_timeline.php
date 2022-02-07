<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model CalendarForm
 * @var $isHeader boolean
 */
use backend\modules\calendar\forms\booking\CalendarForm;
use yii\bootstrap\Html;

$hourTo = 29;
$timeStep = 5;
?>
<thead>
<?php
//todo prepare hour line
$hourLine = "";
$hourLine .= Html::beginTag("tr");
$date = $model->date;
$day = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 days', strtotime($day)));
for ($hour = $hourFrom; $hour < $hourTo; $hour++) {
    $titleDate = $hour > 24 ? $tomorrow : $day;
    $realTime = $hour%24;
    $hourLine .= Html::tag("th", App::t("backend.booking.label", $realTime.":00", [
        "date" => App::$app->formatter->asDate($date, "dd/MM"),
        "hour" => $hour,
    ]), [
        "scope" => "col",
        "class" => "hour-col",
        "colspan" => 60 / $timeStep,
        "data-date" => $date,
        "data-hour" => $hour,
        "title" => $titleDate.' '.$realTime.':0',
    ]);
}
$hourLine .= Html::endTag("tr");
//todo prepare minute line
$minuteLine = "";
$minuteLine .= Html::beginTag("tr", [
    "class" => "timeline-row",
]);
$date = $model->date;
for ($hour = $hourFrom; $hour < $hourTo; $hour++) {
    $titleDate = $hour > 24 ? $tomorrow : $day;
    $realTime = $hour%24;
    for ($minute = 0; $minute < 60; $minute += $timeStep) {
        $minuteLine .= Html::tag("th", "", [
            "class" => "minute-col custom-col",
            "title" => $titleDate.' '.$realTime.':'.$minute,
            "data-minute-step" => $timeStep,
            "data-date" => $date,
            "data-hour" => $hour,
            "data-minute" => $minute,
        ]);
    }
}
$minuteLine .= Html::beginTag("tr");

//todo display timeline
if ($isHeader) {
    echo $hourLine;
    echo $minuteLine;
} else {
    echo $minuteLine;
    echo $hourLine;
}
?>
</thead>
