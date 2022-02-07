<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model CalendarForm
 * @var $shopColors
 */
use backend\modules\calendar\forms\booking\CalendarForm;
use common\entities\user\UserInfo;
use common\entities\worker\WorkerInfo;
use common\entities\system\SystemConfig;
use common\entities\shop\ShopConfig;
use common\helper\ArrayHelper;
$this->registerCss(<<<CSS
.guide .mix{
    position: relative;
}
.guide .mix .badge{
    position: absolute;
    top: 30%;
    left: 35%;
    font-size: 22px !important;
    height: 30px;
    width: 30px;
    font-weight: 700;
}
.guide .mix .badge{
    background-color: #F1F3F9;
}
.guide .mix.slot-booking-offline .badge{
    color:rgb(0, 0, 0);
}
.guide .mix.slot-booking-online.status-pending .badge{
    color:rgb(213, 51, 37);
}
.guide .mix.slot-booking-offline .badge{
    color:rgb(0, 0, 0);
}
.guide .mix.slot-booking-online.status-accepted .badge{
    color:rgb(66, 29, 255);
}
.guide .mix.slot-booking-online.status-updating .badge{
    color: rgb(255,255,0);
}
.guide .mix.slot-booking-online.status-canceled .badge{
    color:#E87E04;
}
.guide .mix .mix-inner{
    height: 80px;
}

#overlay {
  background: #ffffff;
  color: #666666;
  position: fixed;
  height: 100%;
  width: 100%;
  z-index: 5000;
  top: 0;
  left: 0;
  float: left;
  text-align: center;
  padding-top: 25%;
  opacity: .5;
}
.spinner {
    margin: 0 auto;
    height: 64px;
    width: 64px;
    animation: rotate 0.8s infinite linear;
    border: 5px solid forestgreen;
    border-right-color: transparent;
    border-radius: 50% !important;
}
@keyframes rotate {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}
CSS
);
$headings = [
    'none' => 'Tên cửa hàng',
    'online' => 'ONLINE',
];
$body = [
    'none' => 'trống',
    'accepted' => 'Đã chấp thuận',
    'pending' => 'Đợi phê duyệt',
    'updating' => 'UPDATING',
    'confirming' => 'CONFIRMING',
    'canceled' => 'Đã hủy bỏ (phía KH)',
];
$memos = [
    'none' => [
        'none'
    ],
    'online' => [
        'accepted',
        'pending',
        'updating',
        'confirming',
        'canceled',
    ],
];
$category = SystemConfig::CATEGORY_BOOKING;
$isBlockUserBooking = SystemConfig::getValue($category, SystemConfig::BOOKING_IS_BLOCK_USER_BOOKING);
$isBlockUserBooking = $isBlockUserBooking ? 'checked' : '';

?>
<div class="portlet"
     style="margin-bottom: 0px;">
    <div class="portlet-title">
        <div class="caption">
            <i class="fa fa-gift"></i>Hướng dẫn
        </div>
        <div class="tools" style="width: 30px;">
            <a href="javascript:;" class="expand"></a>
        </div>
    </div>
    <div class="portlet-body" style="display: none;">
        <div class="row guide">
            <div class="col-md-12 row" style="font-size: 14px; margin-bottom: 10px;">
<!--                <div class="col-md-4">-->
<!--                    <span>ホームページからスケジュールを取得する:</span>-->
<!--                    <button class="btn btn-primary" id="map-schedule">取得</button>-->
<!--                    <div id="overlay" style="display:none;">-->
<!--                        <div class="spinner"></div>-->
<!--                    </div>-->
<!--                </div>-->
                <div class="col-md-3">
                    <div class="form-group">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#shopCongigBlockBooking">
                            Chức năng hiển thị khung làm việc ngày hôm sau
                        </button>
                    </div>
                </div>
            </div>
            <?php if (App::$app->user->identity->role == UserInfo::ROLE_ADMIN) {  ?>
            <div class="col-md-12 row" style="font-size: 14px; margin-bottom: 10px">
                <div class="col-md-2">
                    <div class="form-group">
                        <label style="margin-top: 1px">Các mục bạn muốn thay đổi màu</label>
                        <select class="color-change form-control" id="color-change" onchange="changeColorType()">
                            <?php foreach ($memos as $slot => $statuses):?>
                                <?php foreach ($statuses as $status):?>
                                    <option value="<?php echo $slot . '-' . $status?>" data-type="color-slot" data-color="<?php echo SystemConfig::getColor($slot . '-' . $status) ?>"><?php echo $headings[$slot] . ' - ' . $body[$status]?></option>
                                <?php endforeach;?>
                            <?php endforeach;?>
                            <?php foreach ($shops as $shopId => $shopName):?>
                                <option value="<?php echo $shopId?>" data-type="color-shop" data-color="<?php echo $shopColors[$shopId] ?>"><?php echo $shopName?></option>
                            <?php endforeach;?>
                            <option value="background" data-type="color-slot" data-color="<?php echo SystemConfig::getColorToHtml(SystemConfig::BACKGROUND) ?>">Back ground</option>
                        </select>

                    </div>
                </div>
                <div class="col-md-3">
                    <div class="row">
                        <div class="col-md-12">
                            <?php
                            echo '<label class="control-label">Màu sắc</label>';
                            ?>
                            <input type="text" hidden id="color">
                            <div class="color-slider-wrap">
                                <div class="color-hex">
                                    <label for="color-hex-code" style="margin-right: 1rem">Mã màu :</label>
                                    <input type="text" id="color-hex-code" value="#D1C4E9" onchange="changeColorHexCode()">
                                </div>
                                <div class="sliders">
                                    <div class="change-color">
                                        <label for="redNum">R</label>
                                        <input type="number" id="redNum">
                                        <input value="200" type="range" min="0" max="255" id="red">
                                    </div>
                                    <div class="change-color">
                                        <label for="greenNum">G</label>
                                        <input type="number" id="greenNum">
                                        <input value="130" type="range" min="0" max="255" id="green">
                                    </div>
                                    <div class="change-color">
                                        <label for="blueNum">B</label>
                                        <input type="number" id="blueNum">
                                        <input value="180" type="range" min="0" max="255" id="blue">
                                    </div>
                                </div>

                                <div class="color-wrap">
                                    <span class="color-wrap-title">Mẫu màu</span>
                                    <span class="color-wrap-hex-code" id="color-wrap-hex-code"></span>
                                    <div id="color-display"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-1 btn-save-box">
                    <button class="btn btn-primary btn-block btn-save" id="save-color">Lưu</button>
                </div>
            </div>
            <?php } ?>
            <div class="col-md-10 col-sm-10">
                <?php foreach ($memos as $slot => $statuses): ?>
                    <?php foreach ($statuses as $status): ?>
                        <div class="minute-col slot-booking-<?php echo $slot?> status-<?php echo $status?> mix slot">
                            <div class="mix-inner custom-mix">
                                <div class="info">
                                    <div class="heading custom-heading"><?php echo $headings[$slot]?></div>
                                    <div class="body" style="background-color: <?php
                                    switch ($slot . '-' . $status) {
                                        case 'none-none':
                                            echo SystemConfig::getColor(SystemConfig::SLOT_NONE);
                                            break;
                                        case 'offline-accepted':
                                            echo SystemConfig::getColor(SystemConfig::OFFLINE_ACCEPTED);
                                            break;
                                        case 'online-accepted':
                                            echo SystemConfig::getColor(SystemConfig::ONLINE_ACCEPTED);
                                            break;
                                        case 'online-pending':
                                            echo SystemConfig::getColor(SystemConfig::ONLINE_PENDING);
                                            break;
                                        case 'online-pending-change':
                                            echo SystemConfig::getColor(SystemConfig::ONLINE_PENDING_CHANGE);
                                            break;
                                        case 'online-canceled':
                                            echo SystemConfig::getColor(SystemConfig::ONLINE_CANCELED);
                                            break;
                                        case 'online-updating':
                                            echo SystemConfig::getColor(SystemConfig::ONLINE_UPDATING);
                                            break;
                                    }?>"><?php echo $body[$status]?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="shopCongigBlockBooking" tabindex="-1" role="dialog" aria-labelledby="shopCongigBlockBooking" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="exampleModalLabel">Chức năng hiển thị khung làm việc của ngày hôm sau</h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <th width="20%" style="text-align: center">Cửa hàng</th>
                            <th style="text-align: center">Hiển thị khung làm việc trước 0 giờ</th>
                        </thead>
                        <tbody>
                        <?php
                            $dataShopConfig = ShopConfig::find()->all();
                            $dataShopConfig = ArrayHelper::map($dataShopConfig, 'key', 'value', 'shop_id');
                            foreach ($shops as $shopId => $shopName):
                                $existsTimeOn = (array_key_exists($shopId, $dataShopConfig) && array_key_exists(ShopConfig::KEY_SHOP_TIME_ON_USER_BOOKING, $dataShopConfig[$shopId]));
                                $timeOnUserBooking = $existsTimeOn ? $dataShopConfig[$shopId][ShopConfig::KEY_SHOP_TIME_ON_USER_BOOKING] : '00:00';
                                $timeOnUserBooking = date('H:i', strtotime($timeOnUserBooking));
                                $textTime = 'Tự khởi động từ ['.$timeOnUserBooking.'] giờ';

                                $existsBlockBooking = (array_key_exists($shopId, $dataShopConfig) && array_key_exists(ShopConfig::KEY_SHOP_ALLOW_BLOCK_BOOKING, $dataShopConfig[$shopId]));
                                $isBlockUserBooking = $existsBlockBooking ? $dataShopConfig[$shopId][ShopConfig::KEY_SHOP_ALLOW_BLOCK_BOOKING] : 0;
                                $checked = $isBlockUserBooking ? 'checked' : '';
                        ?>
                            <tr>
                                <td><?php echo $shopName?></td>
                                <td>
                                    <input type="checkbox" class="make-switch" data-size="mini" data-shop-id="<?php echo $shopId?>" <?php echo $checked?>>
                                    <?php echo $textTime?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        function getParameterByName(name, url) {
            if (!url) url = window.location.href;
            name = name.replace(/[\[\]]/g, '\\$&');
            var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
                results = regex.exec(url);
            if (!results) return null;
            if (!results[2]) return '';
            return decodeURIComponent(results[2].replace(/\+/g, ' '));
        }

        $('.make-switch').on('switchChange.bootstrapSwitch', function (event, state) {
            var shopId, value;
            shopId = $(this).attr('data-shop-id');
            value = state ? 1 : 0;
            $.ajax({
                url: '/shop/manage/config',
                type: 'POST',
                data: {
                    shop_id: shopId,
                    value: value
                },
                success: function () {
                    toastr.success('保存しました。');
                }
            })
        });

        $("#save-color").on("click", function (){
            let color = $('#color').val();
            let key = $('#color-change').val();
            let dataType = $('#color-change').find(':selected').data('type');
            $('#overlay').fadeIn();
            $.ajax({
                url: '/system/config/change-color',
                type: 'POST',
                data: {color: color, key: key, dataType: dataType},
                success: function () {
                    $("#color-change option[value='" + key + "']").data('color', color);
                    toastr.success('Đã thay đổi màu');
                    $('#overlay').fadeOut();
                },
                error: function () {
                    toastr.error('Error');
                    $('#overlay').fadeOut();
                }
            });
        });
    })
</script>
