<?php
/**
 * @var $this \backend\models\BackendView
 *
 * @var WorkerForm $model
 *
 * @var string $start
 * @var string $end
 */
use backend\modules\worker\forms\WorkerForm;
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
    "worker/manage/view",
    "id" => $model->worker_id,
]);
$startLabel = !empty($start) ? date("d/m/Y", strtotime($start)) : "";
$endLabel = !empty($end) ? date("d/m/Y", strtotime($end)) : "";
$shopIds = Json::decode(UserConfig::getValue(UserConfig::KEY_MANAGE_SHOP_IDS, \App::$app->user->id, "[]"));
//TODO cal total shop
$totalShopQuery = ShopInfo::find()
    ->innerJoin(WorkerMappingShop::tableName(), WorkerMappingShop::tableName() . ".shop_id = " . ShopInfo::tableName() . ".shop_id")
    ->where([
        ShopInfo::tableName() . ".status" => ShopInfo::STATUS_ACTIVE,
        WorkerMappingShop::tableName() . ".worker_id" => $model->worker_id,
    ]);
if (!empty($start) && !empty($end)) {
    $totalShopQuery->andWhere(ShopInfo::tableName() . ".created_at >= :start AND " . ShopInfo::tableName() . ".created_at <= :end", [
        ":start" => "$start 0:0:0",
        ":end" => "$end 23:59:59",
    ]);
}
$totalShop = $totalShopQuery->count();
//TODO cal total booking
$totalBookingQuery = BookingInfo::find()
    ->innerJoin(UserInfo::tableName(), UserInfo::tableName() . ".user_id = " . BookingInfo::tableName() . ".member_id")
    ->innerJoin(ShopCalendarSlot::tableName(), ShopCalendarSlot::tableName() . ".slot_id = " . BookingInfo::tableName() . ".slot_id")
    ->innerJoin(ShopInfo::tableName(), ShopCalendarSlot::tableName() . ".shop_id = " . ShopInfo::tableName() . ".shop_id")
    ->innerJoin(WorkerInfo::tableName(), ShopCalendarSlot::tableName() . ".worker_id = " . WorkerInfo::tableName() . ".worker_id")
    ->andWhere(BookingInfo::tableName() . ".status = :STATUS_ACCEPTED", [
        ':STATUS_ACCEPTED' => BookingInfo::STATUS_ACCEPTED
    ])
    ->andWhere(WorkerInfo::tableName() . ".worker_id = :worker_id", [
        ':worker_id' => $model->worker_id,
    ]);
if (!empty($start) && !empty($end)) {
    $totalBookingQuery->andWhere(BookingInfo::tableName() . ".created_at >= :start AND " . BookingInfo::tableName() . ".created_at <= :end", [
        ":start" => "$start 0:0:0",
        ":end" => "$end 23:59:59",
    ]);
}
$totalBooking = $totalBookingQuery->count();

//TODO cal total booking by TYPE
$dataBookingForTypes = [];

    $totalBookingForTypeQuery = BookingInfo::find()
        ->innerJoin(UserInfo::tableName(), UserInfo::tableName() . ".user_id = " . BookingInfo::tableName() . ".member_id")
        ->innerJoin(ShopCalendarSlot::tableName(), ShopCalendarSlot::tableName() . ".slot_id = " . BookingInfo::tableName() . ".slot_id")
        ->innerJoin(ShopInfo::tableName(), ShopCalendarSlot::tableName() . ".shop_id = " . ShopInfo::tableName() . ".shop_id")
        ->innerJoin(WorkerInfo::tableName(), ShopCalendarSlot::tableName() . ".worker_id = " . WorkerInfo::tableName() . ".worker_id")
        ->andWhere(BookingInfo::tableName() . ".status = :STATUS_ACCEPTED", [
            ':STATUS_ACCEPTED' => BookingInfo::STATUS_ACCEPTED
        ])
        ->andWhere([
            WorkerInfo::tableName() . ".worker_id" => $model->worker_id,
        ]);
    if (!empty($start) && !empty($end)) {
        $totalBookingForTypeQuery->andWhere(BookingInfo::tableName() . ".created_at >= :start AND " . BookingInfo::tableName() . ".created_at <= :end", [
            ":start" => "$start 0:0:0",
            ":end" => "$end 23:59:59",
        ]);
    }
    $total = $totalBookingForTypeQuery->count();
    if ($total > 0) {
        $dataBookingForTypes[] = [
            "value" => $total,
        ];
    }


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
            WorkerInfo::tableName() . ".worker_id" => $model->worker_id,
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
                <?php echo App::t("backend.worker.title", "Số liệu thống kê", [
                ]) ?>
            </span>
            <span class="caption-helper"></span>
        </div>
    </div>
    <div class="portlet-body">
        <?php Pjax::begin([
            "id" => "pjax-dashboard-page"
        ]) ?>
        <div class="row">
            <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                <div class="dashboard-stat blue-madison">
                    <div class="visual">
                        <i class="fa fa-comments"></i>
                    </div>
                    <div class="details">
                        <div class="number">
                            <?php echo $totalShop; ?>
                        </div>
                        <div class="desc">
                            Là số lượng salon mà nhân viên đang làm việc
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12"
                 id="total-booking"
                 data-booking-for-type='<?php echo Json::encode($dataBookingForTypes); ?>'
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
                            Là số lượt được đặt lịch
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row <?php echo $totalBooking == 0 ? "hide" : ""; ?>">
            <div class="col-md-6">
                <h3>Thống kê theo loại dịch vụ</h3>
                <div id="course-type" class="chart" style="height: 200px;">
                </div>
            </div>
        </div>
        <?php Pjax::end(); ?>
    </div>
</div>
