<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model CalendarForm
 */
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use common\helper\DatetimeHelper;
use backend\modules\calendar\forms\booking\CalendarForm;
use backend\modules\calendar\forms\booking\BookingOnlineUpdateForm;

$bookingForm = new BookingOnlineUpdateForm();
?>
<script type="text/ng-template" id="modal-online-booking-form.html">
    <div class="modal-header">
        <button type="button" class="close" aria-hidden="true"
                ng-click="closeModal()"></button>
        <h4 class="modal-title">
            <?php echo App::t("backend.booking-form.title", "Thay đổi yêu cầu"); ?>
        </h4>
    </div>
    <div class="modal-body">
        <?php $form = ActiveForm::begin([
            'id' => 'booking-form',
            'fieldConfig' => [
                'horizontalCssClasses' => [
                    'label' => 'col-sm-4',
                    'offset' => '',
                    'wrapper' => 'col-sm-8',
                    'error' => '',
                    'hint' => '',
                ],
            ]
        ]); ?>
        <?= Html::activeHiddenInput($bookingForm, 'slot_id', []) ?>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label">
                        <?php echo App::t("backend.booking-view.label", "Tên salon") ?>
                    </label>
                    <?= Html::textInput("shop_name", "", [
                        "class" => "form-control disabled",
                        "disabled" => "disabled",
                        "ng-model" => "bookingForm.slotInfo.shopInfo.shop_name",
                    ]) ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label">
                        <?php echo App::t("backend.booking-view.label", "Tên nhân viên") ?>
                    </label>
                    <?= Html::textInput("worker_name", "", [
                        "class" => "form-control disabled",
                        "disabled" => "disabled",
                        "ng-model" => "bookingForm.slotInfo.workerInfo.worker_name",
                    ]) ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label">
                        <?php echo App::t("backend.booking-view.label", "Tên khách hàng") ?>
                    </label>
                    <?= Html::textInput("customer_name", "", [
                        "class" => "form-control disabled",
                        "disabled" => "disabled",
                        "ng-model" => "bookingForm.customerInfo.customer_name",
                    ]) ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label">Số điện thoại</label>
                    <?= Html::textInput("customer_phone_number", "", [
                        "class" => "form-control disabled",
                        "disabled" => "disabled",
                        "ng-model" => "bookingForm.customerInfo.phone_number",
                        "copy-clipboard" => "copy",
                        "data-clipboard-text" => "{{bookingForm.customerInfo.phone_number}}",
                    ]) ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
                <label class="control-label">
                    <?php echo App::t("backend.booking-view.label", "Ngày & giờ sử dụng dịch vụ") ?>
                </label>
                <div class="row">
                    <div class="col-md-5">
                        <?php echo Html::textInput("start_date", "", [
                            "class" => "form-control disabled",
                            "disabled" => "disabled",
                            "ng-model" => "bookingForm.slotInfo.date",
                        ]);
                        ?>
                    </div>
                    <div class="col-md-7">
                        <div class="row time-picker-layout">
                            <div class="col-md-6">
                                <?= Html::dropDownList('start_hour', '', DatetimeHelper::getListHours(0), [
                                    'class' => 'form-control',
                                    "ng-model" => "bookingForm.start_hour",
                                    "convert-to-number" => "convert-to-number",
                                    "ng-change" => "bookingForm.start_hour == 24?bookingForm.start_minute = 0:null",
                                ]) ?>
                            </div>
                            <div class="col-md-6"
                                 ng-show="bookingForm.start_hour != 24">
                                <?= Html::dropDownList('start_minute', '', DatetimeHelper::getListMinutes(), [
                                    'class' => 'form-control',
                                    "ng-model" => "bookingForm.start_minute",
                                    "convert-to-number" => "convert-to-number",
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label">
                        <?php echo App::t("backend.booking-view.label", "Ghi chú của khách") ?>
                    </label>
                    <?= Html::textarea("comment", "", [
                        "class" => "form-control autosizeme",
                        "ng-model" => "bookingForm.comment",
                        "rows" => 3,
                    ]) ?>
                </div>
            </div>
        </div>
        <hr/>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label">
                        <?php echo App::t("backend.booking-view.label", "Với thời gian") ?>
                    </label>
                    <?= Html::textInput("duration_minute", "", [
                        "class" => "form-control disabled",
                        "disabled" => "disabled",
                        "ng-model" => "bookingForm.durationMinute",
                    ]) ?>
                </div>
            </div>
            <div class="col-md-4">
                <?= $form->field($bookingForm, 'course_id', [])->dropDownList($listCourses, [
                    "class" => "form-control",
                    "convert-to-number" => "convert-to-number",
                    "ng-model" => "bookingForm.course_id",
                    "ng-change" => "bookingCostChange()",
                ]) ?>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                        <?= $form->field($bookingForm, 'cost', [])->textInput([
                            "class" => "form-control disabled",
                            "disabled" => "disabled",
                            "ng-value" => "bookingForm.cost|currency:'" . CalendarForm::CURRENCY_CODE . "':0",
                        ]) ?>
                </div>
            </div>
        </div>
        <div class="row"
             ng-show="bookingForm.cost && bookingForm.coupons"
             ng-repeat="coupon in bookingForm.coupons">
            <div class="col-md-8 col-md-offset-4">
                <div class="form-group">
                    <label class="control-label">
                        ({{$index + 1}})&nbsp;
                        <?php echo $model->getAttributeLabel("クーポンコード") ?>
                    </label>
                    <div class="row">
                        <div class="col-md-6">
                            <?= Html::textInput('coupon_code', "", [
                                "class" => "form-control disabled",
                                "disabled" => "disabled",
                                "ng-model" => "coupon.coupon_code",
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo Html::textInput("coupon_yield", "", [
                                "class" => "form-control disabled",
                                "disabled" => "disabled",
                                "ng-value" => "coupon.yield|currency:'" . CalendarForm::CURRENCY_CODE . "':0",
                            ]);
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
    <div class="modal-footer">
        <?php echo Html::button(App::t("backend.booking.button", "Hủy bỏ"), [
            "class" => "btn default",
            "ng-click" => "closeModal()",
        ]) ?>
        <?php echo Html::button('<i class="fa fa-save"></i>&nbsp;&nbsp;' . App::t("backend.booking.button", "Lưu"), [
            "class" => "btn blue",
            "ng-show" => "bookingForm.isEditable",
            "ng-click" => "saveBooking()",
        ]) ?>
    </div>
</script>