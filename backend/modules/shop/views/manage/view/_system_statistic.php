<?php
/**
 * @var $this \backend\models\BackendView
 *
 * @var $model ShopForm
 *
 * @var string $start
 * @var string $end
 */
use backend\modules\shop\forms\ShopForm;
use common\entities\calendar\BookingInfo;
use common\entities\user\UserInfo;
use common\entities\shop\ShopCalendarSlot;
use common\entities\shop\ShopInfo;
use common\entities\user\UserConfig;
use common\entities\worker\WorkerInfo;
use common\entities\worker\WorkerMappingShop;
use common\entities\calendar\CourseInfo;
use yii\helpers\Json;
use yii\widgets\Pjax;

$start = App::$app->request->get("start");
$end = App::$app->request->get("end");
$dashboardUrl = Yii::$app->urlManager->createUrl([
    "shop/manage/view",
    "id" => $model->shop_id,
]);
$startLabel = !empty($start) ? date("d/m/Y", strtotime($start)) : "";
$endLabel = !empty($end) ? date("d/m/Y", strtotime($end)) : "";
$dateRangeLabel = (!empty($start) && !empty($end)) ? date("d/m/Y", strtotime($start)) . " - " . date("d/m/Y", strtotime($end)) : "Tất cả";
$shopIds = Json::decode(UserConfig::getValue(UserConfig::KEY_MANAGE_SHOP_IDS, \App::$app->user->id, "[]"));

//TODO cal total worker
$totalWorkerQuery = WorkerInfo::find()
    ->innerJoin(WorkerMappingShop::tableName(), WorkerInfo::tableName() . ".worker_id = " . WorkerMappingShop::tableName() . ".worker_id")
    ->where([
        WorkerInfo::tableName() . ".status" => WorkerInfo::STATUS_ACTIVE,
        WorkerMappingShop::tableName() . ".shop_id" => $model->shop_id,
    ]);
if (!empty($start) && !empty($end)) {
    $totalWorkerQuery->andWhere(WorkerInfo::tableName() . ".created_at >= :start AND " . WorkerInfo::tableName() . ".created_at <= :end", [
        ":start" => "$start 0:0:0",
        ":end" => "$end 23:59:59",
    ]);
}
$totalWorker = $totalWorkerQuery->count();
//TODO cal total booking
$totalBookingQuery = BookingInfo::find()
    ->innerJoin(UserInfo::tableName(), UserInfo::tableName() . ".user_id = " . BookingInfo::tableName() . ".member_id")
    ->innerJoin(ShopCalendarSlot::tableName(), ShopCalendarSlot::tableName() . ".slot_id = " . BookingInfo::tableName() . ".slot_id")
    ->innerJoin(ShopInfo::tableName(), ShopCalendarSlot::tableName() . ".shop_id = " . ShopInfo::tableName() . ".shop_id")
    ->innerJoin(WorkerInfo::tableName(), ShopCalendarSlot::tableName() . ".worker_id = " . WorkerInfo::tableName() . ".worker_id")
    ->andWhere(BookingInfo::tableName() . ".status = :STATUS_ACCEPTED", [
        ':STATUS_ACCEPTED' => BookingInfo::STATUS_ACCEPTED,
    ])
    ->andWhere([
        ShopInfo::tableName() . ".shop_id" => $model->shop_id,
    ]);
if (!empty($start) && !empty($end)) {
    $totalBookingQuery->andWhere(BookingInfo::tableName() . ".created_at >= :start AND " . BookingInfo::tableName() . ".created_at <= :end", [
        ":start" => "$start 0:0:0",
        ":end" => "$end 23:59:59",
    ]);
}
$totalBooking = $totalBookingQuery->count();

//TODO cal total booking for CourseType
$dataBookingForCourseTypes = [];
$courses = CourseInfo::find()->where([])->all();
foreach ($courses as $courseInfo) {
    /**
     * @var $courseInfo CourseInfo
     */
    $totalBookingForCourseTypeQuery = BookingInfo::find()
        ->innerJoin(UserInfo::tableName(), UserInfo::tableName() . ".user_id = " . BookingInfo::tableName() . ".member_id")
        ->innerJoin(ShopCalendarSlot::tableName(), ShopCalendarSlot::tableName() . ".slot_id = " . BookingInfo::tableName() . ".slot_id")
        ->innerJoin(ShopInfo::tableName(), ShopCalendarSlot::tableName() . ".shop_id = " . ShopInfo::tableName() . ".shop_id")
        ->innerJoin(WorkerInfo::tableName(), ShopCalendarSlot::tableName() . ".worker_id = " . WorkerInfo::tableName() . ".worker_id")
        ->andWhere(BookingInfo::tableName() . ".status = :STATUS_ACCEPTED", [
            ':STATUS_ACCEPTED' => BookingInfo::STATUS_ACCEPTED
        ])
        ->andWhere([
            BookingInfo::tableName() . ".course_id" => $courseInfo->course_id,
            ShopInfo::tableName() . ".shop_id" => $model->shop_id,
        ]);
    if (!empty($start) && !empty($end)) {
        $totalBookingForCourseTypeQuery->andWhere(BookingInfo::tableName() . ".created_at >= :start AND " . BookingInfo::tableName() . ".created_at <= :end", [
            ":start" => "$start 0:0:0",
            ":end" => "$end 23:59:59",
        ]);
    }
    $total = $totalBookingForCourseTypeQuery->count();
    if ($total > 0) {
        $dataBookingForCourseTypes[] = [
            "bookingType" => $courseInfo->course_name,
            "value" => $total,
        ];
    }
}

$this->registerJs(<<<JS
    function initDashboardDaterange() {
            if (!jQuery().daterangepicker) {
                return;
            }
            $('#dashboard-report-date-range').daterangepicker({
                "ranges": {
                    'Tất cả' : [moment().add(1, 'days'),moment().add(1, 'days')],
                    'Hôm nay': [moment(), moment()],
                    'Hôm qua': [moment().subtract('days', 1), moment().subtract('days', 1)],
                    '7 ngày trước': [moment().subtract('days', 6), moment()],
                    '30 ngày trước': [moment().subtract('days', 29), moment()],
                    'Tháng này': [moment().startOf('month'), moment().endOf('month')],
                    'Tháng trước': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
                },
                "locale": {
                    "format": "MM/DD/YYYY",
                    "separator": " - ",
                    "applyLabel": "Áp dụng",
                    "cancelLabel": "Hủy bỏ",
                    "fromLabel": "Từ ngày",
                    "toLabel": "Đến ngày",
                    "customRangeLabel": "Tùy chọn",
                    "daysOfWeek": [
                        "Sun",
                        "Mon",
                        "Tue",
                        "Wed",
                        "Thu",
                        "Fri",
                        "Sat",
                    ],
                    "monthNames": [
                        "Tháng 1",
                        "Tháng 2",
                        "Tháng 3",
                        "Tháng 4",
                        "Tháng 5",
                        "Tháng 6",
                        "Tháng 7",
                        "Tháng 8",
                        "Tháng 9",
                        "Tháng 10",
                        "Tháng 11",
                        "Tháng 12"
                    ],
                    "firstDay": 1
                },
                "startDate": ("$startLabel" != "")?moment("$startLabel","DD/M/YYYY"):moment().add(1, 'days'),
            "endDate": ("$endLabel" != "")?moment("$endLabel","DD/M/YYYY"):moment().add(1, 'days'),
            opens: 'left',
        }, function(start, end, label) {
            if(label == 'Tất cả'){
                $('#dashboard-report-date-range span').html(label);
                reloadDashboardPage("","");
            }else{
                $('#dashboard-report-date-range span').html(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
                reloadDashboardPage(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
            }
        });

            // $('#dashboard-report-date-range span').html(moment().subtract('days', 29).format('MMMM D, YYYY') + ' - ' + moment().format('MMMM D, YYYY'));
            $('#dashboard-report-date-range span').html('$dateRangeLabel');
            $('#dashboard-report-date-range').show();
        };
        function reloadDashboardPage(start,end) {
            if(start == "" || end == ""){
                $.pjax.reload({
                    url: "$dashboardUrl",
                    container: "#pjax-dashboard-page",
                    async: false
                });
            }else{
                $.pjax.reload({
                    url: "$dashboardUrl",
                    container: "#pjax-dashboard-page",
                    async: false,
                    data: {
                        start:start,
                        end: end
                    }
                });
            }
        }
    function initChart() {
        var totalBookingEl = $("#total-booking");
        var chartBookingCourse = AmCharts.makeChart("course-type", {
            "type": "pie",
            "theme": "light",
            "fontFamily": 'Open Sans',
            "color":    '#888',
            "dataProvider": totalBookingEl.data("booking-for-course"),
            "valueField": "value",
            "titleField": "bookingType",
            "outlineAlpha": 0.4,
            "depth3D": 15,
            "balloonText": "[[title]]<br><span style='font-size:16px'><b>[[value]]</b> ([[percents]]%)</span>",
            "angle": 30,
        });
    }
    jQuery(document).ready(function () {
        initDashboardDaterange();
        initChart();
        $("#pjax-dashboard-page").on('ready pjax:success', function () {
            initChart();
        });
        
    });
JS
    , \yii\web\View::POS_END);
?>
<div class="portlet light">
    <div class="portlet-title">
        <div class="caption">
            <i class="fa fa-bar-chart font-green-haze"></i>
            <span class="caption-subject font-green-haze bold uppercase">
                <?php echo App::t("backend.dashboard.title", "Số liệu thống kê", [
                ]) ?>
            </span>
            <span class="caption-helper"></span>
        </div>
        <div class="actions">
            <div id="dashboard-report-date-range" class="pull-right tooltips btn btn-fit-height green"
                 data-placement="top" data-original-title="Thay đổi phạm vi thời gian">
                <i class="icon-calendar"></i>&nbsp;
                <span class="thin uppercase hidden-xs"></span>&nbsp;
                <i class="fa fa-angle-down"></i>
            </div>
        </div>
    </div>
    <div class="portlet-body">
        <?php Pjax::begin([
            "id" => "pjax-dashboard-page"
        ]) ?>
        <div class="row">
            <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                <div class="dashboard-stat red-intense">
                    <div class="visual">
                        <i class="fa fa-bar-chart-o"></i>
                    </div>
                    <div class="details">
                        <div class="number">
                            <?php echo $totalWorker; ?>
                        </div>
                        <div class="desc">
                            Lượng nhân viên đã đăng ký
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12"
                 id="total-booking"
                 data-booking-for-course='<?php echo Json::encode($dataBookingForCourseTypes); ?>'>
                <div class="dashboard-stat purple-plum">
                    <div class="visual">
                        <i class="fa fa-cart-plus"></i>
                    </div>
                    <div class="details">
                        <div class="number">
                            <?php echo $totalBooking; ?>
                        </div>
                        <div class="desc">
                            Số lượng đặt lịch trước thành công
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row <?php echo $totalBooking == 0 ? "hide" : ""; ?>">

            <div class="col-md-6">
                <h3>Tỉ lệ sử dụng dịch vụ</h3>
                <div id="course-type" class="chart" style="height: 200px;">
                </div>
            </div>
        </div>
        <?php Pjax::end(); ?>
    </div>
</div>
