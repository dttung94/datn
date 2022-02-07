<?php
/**
 * @var $this \frontend\models\FrontendView
 */
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

?>
<script type="text/ng-template" id="modal-booking-online-confirm.html">
    <div class="modal-header">
        <h4 class="modal-title">Chi tiết đặt lịch</h4>
    </div>

    <div class="modal-body">
        <?php ActiveForm::begin([
            "id" => "booking-online-form",
        ]); ?>
        <div class="box-container box-space-between mb20">
            <div class="box-sm-25 box-xs-35 box-container box-middle">
                <span class="text-blue">1．Tên nhân viên</span>
            </div>
            <div class="box-sm-75 box-xs-65">
                <span ng-bind="slot.workerInfo.worker_name"></span>
            </div>
        </div>
        <div class="box-container box-space-between mb20">
            <div class="box-sm-25 box-xs-35 box-container box-middle">
                <span class="text-blue">2．Tiệm salon</span>
            </div>
            <div class="box-sm-75 box-xs-65">
                <span ng-bind="slot.shopInfo.shop_name"></span>
            </div>
        </div>
        <div class="box-container box-space-between mb20">
            <div class="box-sm-25 box-xs-35 box-container box-middle">
                <span class="text-blue">3．Địa chỉ</span>
            </div>
            <div class="box-sm-75 box-xs-65">
                <span ng-bind="slot.shopInfo.shop_address"></span>
            </div>
        </div>
        <div class="box-container box-space-between mb20">
            <div class="box-sm-25 box-xs-35 box-container box-middle">
                <span class="text-blue">4．Dịch vụ</span>
            </div>
            <div class="box-sm-75 box-xs-65">
                <?php foreach ($courses as $course) {
                    echo Html::tag("span", $course->course_name, [
                        "ng-show" => "onlineBookingForm.course_id==$course->course_id",
                    ]);
                } ?>
            </div>
        </div>
        <div class="box-container box-space-between mb20">
            <div class="box-sm-25 box-xs-35 box-container box-middle">
                <span class="text-blue">5．Phí dịch vụ</span>
            </div>
            <div class="box-sm-75 box-xs-65">
                <span ng-bind="totalCost|currency:' VNĐ':0"></span>
            </div>
        </div>
        <div class="box-container box-space-between mb20">
            <div class="box-sm-25 box-xs-35 box-container box-middle">
                <span class="text-blue">6．Thời gian</span>
            </div>
            <div class="box-sm-75 box-xs-65">
                <span ng-bind="slot.start_time"></span> - <span ng-bind="slot.end_time"></span>
            </div>
        </div>
        <div class="box-container box-space-between mb20"
             ng-show="onlineBookingForm.comment">
            <div class="box-sm-25 box-xs-35 box-container box-middle">
                <span class="text-blue">7．Ghi chú</span>
            </div>
            <div class="box-sm-75 box-xs-65">
                <span ng-bind="onlineBookingForm.comment"></span>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>

    <div class="modal-footer">
        <?php echo Html::a(App::t("frontend.online-booking.button", "Đặt lịch"), "javascript:;", [
            "class" => "btn btn-primary btn-lg btn-block",
            "ng-click" => "saveOnlineBooking()",
        ]) ?>
        <?php echo Html::a(App::t("frontend.online-booking.button", "Hủy"), "javascript:;", [
            "class" => "btn btn-default btn-lg btn-block",
            "ng-click" => "closeModal()",
        ]) ?>
    </div>
</script>