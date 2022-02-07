<?php
/**
 * @var $this \backend\models\BackendView
 */
use yii\bootstrap\Html;

$hourFrom = 0;
$hourTo = 24;
$timeStep = 5;
?>
<thead>
<?php
//todo prepare hour line
$hourLine = "";
$hourLine .= Html::beginTag("tr");
for ($hour = $hourFrom; $hour < $hourTo; $hour++) {
    $hourLine .= Html::tag("th", App::t("backend.booking.label", "{hour} giờ", [
        "hour" => $hour,
    ]), [
        "scope" => "col",
        "class" => "hour-col",
        "colspan" => 60 / $timeStep,
        "data-hour" => $hour,
    ]);
}
$hourLine .= Html::endTag("tr");
//todo prepare minute line
$minuteLine = "";
$minuteLine .= Html::beginTag("tr", [
    "class" => "timeline-row",
]);
for ($hour = $hourFrom; $hour < $hourTo; $hour++) {
    for ($minute = 0; $minute < 60; $minute += $timeStep) {
        $minuteLine .= Html::tag("td", "", [
            "class" => "minute-col",
            "title" => App::$app->formatter->asTime("$hour:$minute"),
            "data-minute-step" => $timeStep,
            "data-hour" => $hour,
            "data-minute" => $minute,
        ]);
    }
}
$minuteLine .= Html::beginTag("tr");

//todo display timeline
echo $hourLine;
echo $minuteLine;
?>
</thead>
