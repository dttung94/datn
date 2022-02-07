<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model MemberForm
 */
use backend\modules\member\forms\MemberForm;
use common\entities\calendar\BookingInfo;
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
                UserInfo::tableName() . ".user_id" => $model->user_id,
            ])
            ->count(),
    ];
}
$dataBookingByDay[count($dataBookingByDay) - 1] = ArrayHelper::merge($dataBookingByDay[count($dataBookingByDay) - 1], [
    "alpha" => 0.2,
    "additional" => "(projection)"
]);
$dataBookingByDayJson = Json::encode($dataBookingByDay);

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
    });
JS
    , \yii\web\View::POS_END);
?>
<div class="row">
    <div class="col-md-12">
        <h3>Tình trạng đặt lịch trong vòng 1 tháng</h3>
        <div id="booking-by-day" class="chart" style="height: 400px;">
        </div>
    </div>
</div>