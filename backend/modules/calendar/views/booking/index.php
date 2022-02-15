<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model CalendarForm
 */
use backend\assets\AppAsset;
use backend\modules\calendar\forms\booking\CalendarForm;
use common\entities\shop\ShopConfig;
use common\entities\user\UserInfo;
use common\helper\ArrayHelper;
use common\entities\shop\ShopCalendarSlot;
use yii\bootstrap\Html;
use yii\helpers\Json;

$color400 = [
    "#FFCDD2",
    "#D1C4E9",
    "#B3E5FC",
    "#C8E6C9",
    "#FFE280",
    "#FFCCBC",
    "#CFD8DC",

    "#F8BBD0",
    "#C5CAE9",
    "#B2EBF2",
    "#CEE6B2",
    "#FFE699",
    "#D7CCC8",

    "#E1BEE7",
    "#BBDEFB",
    "#B2DFDB",
    "#E9EFA9",
    "#FFD699",
    "#D9D9D9",
];
$shopColorsDb = ShopConfig::getAllShopColor();
$shopColors = [];
$shopColorsDefault = [];
foreach ($dataShops as $index => $shop) {
    if (!isset($shopColors[$shop->shop_id])) {
        $shopColors[$shop->shop_id] = $color400[$index % count($color400)];
    }
}
if ($shopColorsDb) {
    foreach ($shopColorsDb as $shopColorDb) {
        foreach ($shopColors as $key => $value) {
            if ($key == $shopColorDb->shop_id) {
                $shopColors[$key] = $shopColorDb->value;
            }
        }
    }
}

foreach ($model->allShop as $index => $shop) {
    if (!isset($shopColorsDefault[$shop->shop_id])) {
        $shopColorsDefault[$shop->shop_id] = $color400[$index % count($color400)];
    }
}

foreach ($shopColorsDefault as $key => $shopColorDefault) {
    foreach ($shopColors as $keyColor => $shopColor) {
        if ($keyColor == $key) {
            $shopColorsDefault[$key] = $shopColor;
        }
    }
}

$cssShopColor = "";
foreach ($shopColors as $shop_id => $color) {
    $cssShopColor .= ".minute-col.working-time.col-shop-$shop_id {background-color: $color !important;}\n";
    $cssShopColor .= ".btn.btn-primary.shop-$shop_id {background-color: $color !important;border-color: $color !important;}\n";
}

$this->registerCss($cssShopColor);

$formDataJson = Json::encode($model->toArray([], ['date', 'shop_ids']));

$this->breadcrumbs = [
    [
        "label" => App::t("backend.booking.title",'Quản lý đặt chỗ')]
];
$this->themeOptions = [
    "bodyClass" => "page-sidebar-closed-hide-logo page-sidebar-closed",
    "sideMenuClass" => "page-sidebar-menu-closed",
];
$this->actions = [
];
$this->actions[] = Html::tag("ds-widget-clock", "", [
    "class" => "btn-group btn-group-md btn-group-solid margin-right-10",
    "show-digital" => "",
    "theme" => "blue-light",
    "digital-format" => "'yyyy-MM-dd HH:mm:ss'",
]);
$buttonShop = Html::beginTag("div", [
    "class" => "btn-group btn-group-md btn-group-solid margin-right-10",
]);
foreach ($dataShops as $shop) {
    $buttonShop .= Html::beginTag("button", [
        "class" => "btn btn-badge shop-$shop->shop_id " . (ArrayHelper::isIn($shop->shop_id, $model->shop_ids) ? "btn-primary" : "btn-default"),
        "data-pjax" => 0,
        "onclick" => "actionClickShop(".$shop->shop_id.", this)"
    ]);
    $buttonShop .= $shop->shop_name;
    $buttonShop .= " <span id='pending_shop_$shop->shop_id' class='badge badge-shop-booking'></span>";
    $buttonShop .= Html::endTag("button");
}
$buttonShop .= Html::button('<i class="fa fa-search"></i>', [
    "class" => "btn btn-search btn-primary",
    "onclick" => "actionClickSearch()"
]);
$buttonShop .= Html::endTag("div");
$this->actions[] = $buttonShop;

$buttonDate = Html::beginTag("div", [
    "class" => "btn-group btn-group-md btn-group-solid pull-right",
]);
foreach ($model->dates as $dateData) {
    $params = App::$app->request->getQueryParams();
    $params[0] = "/calendar/booking";
    $params["date"] = $dateData["date"];
    $buttonDate .= Html::a(App::t("backend.booking.label", "{label} ({date})", [
        "label" => $dateData["label"],
        "date" => App::$app->formatter->asDate($dateData["date"], "dd/MM"),
    ]), $params, [
        "class" => "btn btn-default " . ($model->date == $dateData["date"] ? "btn-primary" : ""),
        "data-date" => $dateData["date"],
        "data-pjax" => 0,
    ]);
}
$listShops = $model->getListShop();
$buttonDate .= Html::endTag("div");
$this->actions[] = $buttonDate;

$bundle = App::$app->assetManager->getBundle(AppAsset::className());
$this->registerJsFile($bundle->baseUrl . "/js/controllers/booking.js?v=118", [
    "depends" => [
        AppAsset::className(),
    ]
]);
$this->registerJsFile($bundle->baseUrl . "/js/main.js?v=1", [
    "depends" => [
        AppAsset::className(),
    ]
]);
$this->registerCssFile($bundle->baseUrl . "/pages/css/booking.css?v=1", [
    "depends" => [
        AppAsset::className(),
    ]
]);
$this->registerJs(
    <<< JS
    jQuery(document).ready(function(){
    });
JS
    , \yii\web\View::POS_END, 'register-js-booking-manage');
?>
    <style type="text/css">
        button.btn.btn-badge {
            margin-top: 0 !important;
            padding: 6px 13px;
        }

        button.btn-search {
            padding: 6px 13px;
            margin-top: 0 !important;
            margin-left: 10px !important;
        }

        span.badge-shop-booking {
            background-color: #d9534f !important;
            color: white !important;
        }
    </style>
<?php echo $this->render("_guide", [
    "model" => $model,
    "shopColors" => $shopColorsDefault,
    "shops" => $listShops,
]); ?>
<?php echo Html::beginTag("div", [
    "class" => "portlet light",
    "ng-controller" => "BookingController",
    "ng-init" => "init($formDataJson,'" . App::$app->timeZone . "')",
]); ?>
    <div class="portlet-body">
        <div class="dragdrop-wrap">
            <?php echo $this->render("_modal_confirm", [
                "model" => $model,
            ]); ?>

            <?php echo $this->render("_modal_booking_view", [
                "model" => $model,
                "listCourses" => $listCourses,
            ]); ?>

            <?php echo $this->render("_modal_booking_note", [
                "model" => $model,
            ]); ?>

            <?php echo $this->render("_modal_online_booking_form", [
                "model" => $model,
                "listCourses" => $listCourses,
            ]); ?>




            <?php echo $this->render("_modal_calendar_slot_form", [
                "model" => $model,
            ]); ?>

            <?php echo $this->render("_modal_create_calendar_slot_form", [
                "model" => $model,
            ]); ?>

            <?php echo $this->render("_modal_worker_note", [
            ]); ?>

            <div class="booking-manage-area">
                <?php echo $this->render("_list_booking_table", [
                    "model" => $model,
                    "listShops" => $listShops,
                ]); ?>
            </div>
        </div>
    </div>
<?php echo Html::endTag("div") ?>
<script>
    var checkedShopIds = {};
    var requestShopIds = <?php echo Json::encode($model->toArray([], ['shop_ids'])) ?>.shop_ids;
    requestShopIds.forEach(function (id) {
        checkedShopIds[id] = true;
    });
    var bookingCount = {};

    function actionCopy() {
        var phoneNumber = $('#phone_number').val();
        if (phoneNumber !== '') {
            $('#message').css('display', 'block');
            $('#message').html('Đã sao chép '+phoneNumber);
            setTimeout(function(){
                $("#message").fadeOut(1000);
            },1000);
        }
    }

    function actionClickShop(shopId, element) {
        element.classList.toggle('btn-primary');
        element.classList.toggle('btn-default');
        checkedShopIds[shopId] = !checkedShopIds[shopId];
    }

    function actionClickSearch() {
        var ids = Object.keys(checkedShopIds).filter(function (id) {
            return checkedShopIds[id]
        })
        var params = new URLSearchParams();
        for (var i = 0; i < ids.length; i++) {
            params.append(`shop_ids[${i}]`, ids[i]);
        }

        var oldParams = new URLSearchParams(window.location.search);

        if (oldParams.has('type')) {
            params.append('type', oldParams.get('type'));
        }

        if (oldParams.has('date')) {
            params.append('date', oldParams.get('date'));
        }
        window.location = window.location.pathname + '?' + params.toString();
    }

    function reloadBookingCount() {
        Object.keys(bookingCount).forEach(function (shopId) {
            if (bookingCount[shopId].updating) {
                $('#pending_shop_' + shopId).html(bookingCount[shopId].pending + bookingCount[shopId].updating === 0 ? '' : bookingCount[shopId].pending + bookingCount[shopId].updating);
            } else {
                $('#pending_shop_' + shopId).html(bookingCount[shopId].pending === 0 ? '' : bookingCount[shopId].pending);
            }
        })
    }

    function fetchBookingCount () {
        $.callAJAX({
            url: "/calendar/booking/get-booking-count",
            method: 'GET',
            data: {
                date: '<?php echo $model->date ?>',
            },
            callbackSuccess: function (res) {
                Object.assign(bookingCount, res);
                reloadBookingCount();
            },
            callbackFail: function (status, message) {
                toastr.error(message);
            }
        })
    }

    $(document).on("bookingOnlineStatusChanged", function (event, data) {
        fetchBookingCount();
    });
    $(document).ready(function(){
        fetchBookingCount();
    });
</script>
