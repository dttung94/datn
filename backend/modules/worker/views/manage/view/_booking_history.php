<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model WorkerForm
 */
use backend\modules\worker\forms\WorkerForm;
use common\entities\calendar\BookingInfo;
use common\entities\calendar\Rating;
use common\entities\user\UserInfo;
use common\entities\shop\ShopCalendarSlot;
use common\entities\shop\ShopInfo;
use common\entities\worker\WorkerInfo;
use yii\helpers\Json;
use common\helper\ArrayHelper;

$dataBookingByDay = [];
for ($index = 30; $index >= 0; $index--) {
    $date = date('Y-m-d', strtotime("- $index days"));
    $dataBookingByDay[] = [
        "date" => date("m/d", strtotime($date)),
        "totalBooking" => BookingInfo::find()
            ->innerJoin(UserInfo::tableName(), UserInfo::tableName() . ".user_id = " . BookingInfo::tableName() . ".member_id")
            ->innerJoin(ShopCalendarSlot::tableName(), ShopCalendarSlot::tableName() . ".slot_id = " . BookingInfo::tableName() . ".slot_id")
            ->innerJoin(ShopInfo::tableName(), ShopCalendarSlot::tableName() . ".shop_id = " . ShopInfo::tableName() . ".shop_id")
            ->innerJoin(WorkerInfo::tableName(), ShopCalendarSlot::tableName() . ".worker_id = " . WorkerInfo::tableName() . ".worker_id")
            ->andWhere(BookingInfo::tableName() . ".status = :STATUS_ACCEPTED", [
                ':STATUS_ACCEPTED' => BookingInfo::STATUS_ACCEPTED
            ])
            ->andWhere([
                ShopCalendarSlot::tableName() . ".date" => $date,
                WorkerInfo::tableName() . ".worker_id" => $model->worker_id,
            ])
            ->count(),
    ];
}
$dataBookingByDay[count($dataBookingByDay) - 1] = ArrayHelper::merge($dataBookingByDay[count($dataBookingByDay) - 1], [
    "alpha" => 0.2,
    "additional" => "(Đang tiến hành)"
]);
$dataBookingByDayJson = Json::encode($dataBookingByDay);
$rating = Json::encode($dataChart);

$this->registerJs(<<<JS
    jQuery(document).ready(function () {
        var chartBookingByDay = AmCharts.makeChart("booking-by-day", {
            "type": "serial",
            "theme": "light",
            "pathToImages": Metronic.getGlobalPluginsPath() + "amcharts/amcharts/images/",
            "autoMargins": false,
            "marginLeft": 30,
            "marginRight": 8,
            "marginTop": 10,
            "marginBottom": 26,

            "fontFamily": 'Open Sans',            
            "color":    '#888',
            
            "dataProvider": $dataBookingByDayJson,
            "valueAxes": [{
                "axisAlpha": 0,
                "position": "left"
            }],
            "startDuration": 1,
            "graphs": [{
                "balloonText": "<span style='font-size:13px;'>[[title]] ngày [[category]]:<b>[[value]]</b> [[additional]]</span>",
                "bullet": "round",
                "dashLengthField": "dashLengthLine",
                "lineThickness": 3,
                "bulletSize": 7,
                "bulletBorderAlpha": 1,
                "bulletColor": "#FFFFFF",
                "useLineColorForBulletBorder": true,
                "bulletBorderThickness": 3,
                "fillAlphas": 0,
                "lineAlpha": 1,
                "title": "Tổng lượt đặt lịch",
                "valueField": "totalBooking"
            }],
            "categoryField": "date",
            "categoryAxis": {
                "gridPosition": "start",
                "axisAlpha": 0,
                "tickLength": 0
            }
        });
        var rating = AmCharts.makeChart("rating-worker", {
            "type": "serial",
            "theme": "none",
           
            "rotate": true,
            "dataProvider": $rating,
            "valueAxes": [{
                "gridColor": "#000000",
                "dashLength": 0,
                "maximum": 5,
                "step": 0.5,
                "tickLength": 0.5,
            }],
            "gridAboveGraphs": true,
            "startDuration": 1,
            "graphs": [ {
                "balloonText": "[[category]]: <b>[[value]]</b>",
                "fillAlphas": 0.8,
                "lineAlpha": 0.2,
                "type": "column",
                "valueField": "rating",
                "fillColors": "#8775a7",
                "lineColor" : "#8775a7",
                "columnWidth" : 0.8,
            }],
            "chartCursor": {
                "categoryBalloonEnabled": false,
                "cursorAlpha": 0,
                "zoomable": false
            },
            "categoryField": "category",
            "categoryAxis": {
                "gridPosition": "start",
                "gridThickness": 0,
            }
        })
    });
JS
    , \yii\web\View::POS_END);
?>
<style>
    .linkButtons {
        display: flex;
        flex-direction: row;
        padding: 0;
        margin: 0;
        list-style: none;
    }
    .button {
        background-color: #45B6AF;
        border-color: #3ea49d;
        width: 80px;
        height: 50px;
        margin-left: 25px;
        margin-bottom: 20px;
        margin-top: 10px;

        line-height: 50px;
        color: white;
        font-weight: bold;
        text-align: center;
    }

    .button:hover {
        background-color: #26a69a;
    }
</style>
<div class="row">
    <div class="col-md-12">
        <h3>Thống kê đặt lịch trong vòng 1 tháng</h3>
        <div id="booking-by-day" class="chart" style="height: 400px;">
        </div>
    </div>
    <h3>Điểm đánh giá trung bình</h3>
    <div id="rating-worker" class="chart" style="height: 500px; width: 50%;"></div>
</div>
