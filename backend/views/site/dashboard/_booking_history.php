<?php
/**
 * @var $this \backend\models\BackendView
 */
use common\entities\calendar\BookingInfo;
use common\entities\shop\ShopCalendarSlot;
use common\entities\shop\ShopInfo;
use common\entities\user\UserConfig;
use common\entities\user\UserInfo;
use common\entities\calendar\TotalBooking;
use yii\helpers\Json;
use common\entities\calendar\BookingMissAccept;

$users = \App::$app->user;
$shopIds = Json::decode(UserConfig::getValue(UserConfig::KEY_MANAGE_SHOP_IDS, $users->id, "[]"));
$now = date('Y-m-d');
$startYear = 2019;
$nowYear = date('Y', strtotime($now));
$subYear = $nowYear - $startYear;
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
if (isset($_GET['month']) && $_GET['year']) {
    $month = $_GET['month'];
} else if (isset($_GET['year']) && empty($_GET['month'])) {
    $month = 0;
} else {
    $month = date('m');
}
$shop = isset($_GET['shop']) ? $_GET['shop'] : 0;
$months = [1,2,3,4,5,6,7,8,9,10,11,12];
$modelShopInfo = new ShopInfo();
$listShops = $modelShopInfo->getListShop();
if ($users->identity->role !== \common\entities\user\UserInfo::ROLE_ADMIN) {
    $shopIds = array_intersect(array_keys($listShops), $shopIds);
    foreach ($shopIds as $id) {
        $listShopsLast[$id] = $listShops[$id];
    }
} else {
    $shopIds = array_keys($listShops);
    $listShopsLast = $listShops;
}

if ((isset($_GET['month']) && !in_array($month, $months)) || $year > date('Y') || $year < $startYear || (isset($_GET['shop']) && !in_array($shop, $shopIds))) {
    throw new \yii\web\NotFoundHttpException();
}
$maxIndex = 12;
$findTimeMonth = $year.'-'.$maxIndex;
$textYear = ' năm '.$year;
$textMonth = "";
if ($month != 0) {
    $maxIndex = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $findTimeMonth = $year.'-'.$month.'-'.$maxIndex;
    $textMonth = 'tháng '.$month;
}
$text = 'Trạng thái đặt lịch trong '.$textMonth.$textYear;
$dataBookingByDay = [];
if (isset($_GET['shop'])) {
    $shopIds = [$_GET['shop']];
}

for ($index = $maxIndex-1; $index >= 0; $index--) {
    $date = $month != 0 ? date('Y-m-d', strtotime("- $index days", strtotime($findTimeMonth))) : date('Y-m', strtotime("- $index months", strtotime($findTimeMonth)));

    $queryTotalBooking = BookingInfo::find()
        ->innerJoin(ShopCalendarSlot::tableName(), ShopCalendarSlot::tableName() . ".slot_id = " . BookingInfo::tableName() . ".slot_id")
        ->where(['like', ShopCalendarSlot::tableName() . ".date", $date])
//        ->andWhere(BookingInfo::tableName() . ".status = :STATUS_ACCEPTED", [
//            ':STATUS_ACCEPTED' => BookingInfo::STATUS_ACCEPTED
//        ])
        ->andWhere(["IN", ShopCalendarSlot::tableName() . ".shop_id", $shopIds])
        ->count();
    $queryTotalBookingAccepted = BookingInfo::find()
        ->innerJoin(ShopCalendarSlot::tableName(), ShopCalendarSlot::tableName() . ".slot_id = " . BookingInfo::tableName() . ".slot_id")
        ->andWhere(['like', ShopCalendarSlot::tableName() . ".date", $date])
        ->andWhere(BookingInfo::tableName() . ".status = :STATUS_ACCEPTED", [
            ':STATUS_ACCEPTED' => BookingInfo::STATUS_ACCEPTED
        ])
        ->andWhere(["IN", ShopCalendarSlot::tableName() . ".shop_id", $shopIds])
        ->count();

    $shopIdBookings = [];

    $dataBookingByDay[$index] = [
        "date" => $month != 0 ? date('m-d', strtotime($date)) : $date,
        "totalBooking" => $queryTotalBooking,
        "totalBookingAccepted" => $queryTotalBookingAccepted,
    ];

    foreach ($shopIds as $shopId) {
        $dataBookingByDay[$index][$shopId] = BookingInfo::find()
            ->innerJoin(ShopCalendarSlot::tableName(), ShopCalendarSlot::tableName() . ".slot_id = " . BookingInfo::tableName() . ".slot_id")
            ->where([BookingInfo::tableName() . ".status" => BookingInfo::STATUS_ACCEPTED,])
            ->andWhere(['like', ShopCalendarSlot::tableName() . ".date", $date])
            ->andWhere([ShopCalendarSlot::tableName() . ".shop_id" => $shopId])
            ->count();
    }

}
$graphs = [
    [
        "stackable" => false,
        "balloonText" => "<span style='font-size:13px;'>[[title]] ngày [[category]]:<b>[[value]]</b> lượt</span>",
        "bullet" => "round",
        "dashLengthField" => "dashLengthLine",
        "lineThickness" => 3,
        "bulletSize" => 7,
        "bulletBorderAlpha" => 1,
        "bulletColor" => "#FFFFFF",
        "useLineColorForBulletBorder" => true,
        "bulletBorderThickness" => 3,
        "fillAlphas" => 0,
        "lineAlpha" => 1,
        "title" => "Tổng số lượt đặt lịch",
        "valueField" => "totalBooking",
        "lineColor" => 'blue'
    ],
];


$colors = ['#cc4748', '#cd82ad', '#2f4074', '#448e4d', '#b7b83f', '#b9783f', '#b93e3d', '#913167', '#542424'];
foreach ($shopIds as $key => $shopId) {
    $graphs[] = [
        "alphaField" => "alpha",
        "balloonText" => "<span style='font-size:13px;'>[[title]] ngày [[category]]:<b> [[value]]</b> lượt đặt lịch thành công</span>",
        "dashLengthField" =>"dashLengthColumn",
        "fillAlphas" => 1,
        "fillColors" => $colors[$key],
        "lineColor" => $colors[$key],
        "title" =>$listShopsLast[$shopId],
        "type" => "column",
        "valueField" =>  $shopId,
    ];
    $graphsOnlyColumn[] = [
        "alphaField" => "alpha",
        "balloonText" => "<span style='font-size:13px;'>[[category]][[title]]:<b>[[value]]</b> [[additional]]</span>",
        "dashLengthField" =>"dashLengthColumn",
        "fillAlphas" => 1,
        "fillColors" => $colors[$key],
        "lineColor" => $colors[$key],
        "title" => 'Số lượng đặt trước:'.$listShopsLast[$shopId],
        "type" => "column",
        "valueField" =>  $shopId,
    ];
}
$dataBookingByDayJson = Json::encode(array_values($dataBookingByDay));
$graphsJson = Json::encode(array_values($graphs));
$graphsOnlyColumn = !empty($graphsOnlyColumn) ? Json::encode(array_values($graphsOnlyColumn)) : 0;
$this->registerJs(<<<JS
    jQuery(document).ready(function () {
        var chartBookingByDay = AmCharts.makeChart("booking-by-day", {
            "type": "serial",
            "theme": "light",
            "pathToImages": Metronic.getGlobalPluginsPath() + "amcharts/amcharts/images/",
            "autoMargins": false,
            "marginLeft": 40,
            "marginRight": 8,
            "marginTop": 10,
            "marginBottom": 26,

            "fontFamily": 'Open Sans',            
            "color":    '#888',
            
            "dataProvider": $dataBookingByDayJson,
            "valueAxes": [{
                "axisAlpha": 0,
                "position": "left",
                "stackType": "regular",
                "totalText": "[[total]]"
            }],
            "startDuration": 1,
            "graphs": $graphsJson,
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
        <div class="col-md-6">
            <div class="form-group col-md-4">
                <label class="control-label">Vui lòng chọn năm</label>
                <select class="form-control" id="change-year">
                    <?php
                    for ($iY=$subYear; $iY >= 0; $iY--) {
                        $selectedY = "";
                        $optionY = $startYear + $iY;
                        if ($optionY == $year) {
                            $selectedY = 'selected';
                        }
                        echo "<option value='$optionY' $selectedY>$optionY</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label class="control-label">Vui lòng chọn tháng</label>
                <select class="form-control" id="change-month">
                    <option value="0">Tất cả 12 tháng</option>
                    <?php
                    for ($iM=1; $iM <=12; $iM++) {
                        $selectedM = "";
                        if ($iM == $month) {
                            $selectedM = 'selected';
                        }
                        echo "<option value='$iM' $selectedM>$iM</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label class="control-label">Vui lòng chọn tiệm salon</label>
                <select class="form-control" id="change-shop">
                    <option value="0">Toàn bộ các salon</option>
                    <?php
                    foreach ($listShopsLast as $keyS => $valueS) {
                        $selectedS = "";
                        if ($keyS == $shop) {
                            $selectedS = "selected";
                        }
                        echo "<option value='$keyS' $selectedS>$valueS</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="col-md-12">
            <h3><?php echo $text;?></h3>
        </div>
        <div id="booking-by-day" class="chart" style="height: 400px;"></div>

    </div>

</div>
