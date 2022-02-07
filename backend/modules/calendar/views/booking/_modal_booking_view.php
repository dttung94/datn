<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model CalendarForm
 */
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use backend\modules\calendar\forms\booking\CalendarForm;
use backend\modules\calendar\forms\booking\BookingOnlineForm;

$bookingForm = new BookingOnlineForm();
?>
<style type="text/css">
    .feeds li .col2 {
        width: 150px !important;
        margin-left: -150px !important;
    }
</style>
<script type="text/ng-template" id="modal-booking-view.html">
    <div class="modal-header">
        <button type="button" class="close" aria-hidden="true"
                ng-click="closeModal()"></button>
        <h4 class="modal-title">
            <?php echo App::t("backend.booking-view.title", "Thông tin đặt chỗ"); ?>
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
                        "ng-model" => "bookingForm.memberInfo.full_name",
                    ]) ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group" onclick = actionCopy()>
                    <label class="control-label">Số điện thoại</label>
                    <?= Html::textInput("customer_phone_number", "", [
                        "id" => "phone_number",
                        "class" => "form-control disabled",
                        "disabled" => "disabled",
                        "ng-model" => "bookingForm.memberInfo.phone_number",
                        "copy-clipboard" => "copy",
                        "data-clipboard-text" => "{{bookingForm.memberInfo.phone_number}}",
                    ]) ?>
                    <span id="message" style="color: cornflowerblue;"></span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label class="control-label">
                    <?php echo App::t("backend.booking-view.label", "Ngày và giờ sử dụng dịch vụ") ?>
                </label>
                <div class="row">
                    <div class="col-md-6">
                        <?php echo Html::textInput("start_date", "", [
                            "class" => "form-control disabled",
                            "disabled" => "disabled",
                            "ng-model" => "bookingForm.slotInfo.date",
                        ]);
                        ?>
                    </div>
                    <div class="col-md-6">
                        <?php echo Html::textInput("start_time", "", [
                            "class" => "form-control disabled",
                            "disabled" => "disabled",
                            "ng-model" => "bookingForm.slotInfo.start_time",
                        ]);
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label class="control-label">
                    <?php echo App::t("backend.booking-view.label", "Ghi chú của khách hàng") ?>
                </label>
                <?= Html::textarea("comment", "", [
                    "class" => "form-control disabled text-danger",
                    "disabled" => "disabled",
                    "ng-model" => "bookingForm.comment",
                    "rows" => 5,
                ]) ?>
            </div>
            <div class="col-md-6">
                <label class="control-label">
                    <?php echo App::t("backend.booking-view.label", "Ghi chú của quản lý") ?>
                </label>
                <?= Html::textarea("note", "", [
                    "class" => "form-control disabled",
                    "disabled" => "disabled",
                    "ng-model" => "bookingForm.note",
                    "rows" => 5,
                ]) ?>
            </div>
        </div>
        <hr/>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label">
                        <?php echo App::t("backend.booking-view.label", "Thời gian (phút)") ?>
                    </label>
                    <?php echo Html::textInput("duration_minute", "", [
                        "class" => "form-control disabled",
                        "disabled" => "disabled",
                        "ng-model" => "bookingForm.slotInfo.duration_minute",
                    ]);
                    ?>
                </div>
            </div>
            <div class="col-md-4">
                <?= $form->field($bookingForm, 'course_id', [])->dropDownList($listCourses, [
                    "class" => "form-control disabled",
                    "convert-to-number" => "",
                    "disabled" => "disabled",
                    "ng-model" => "bookingForm.course_id",
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

        <div class="row">
            <div class="col-md-12">
                <h4 ng-click="bookingForm.showHistory = !bookingForm.showHistory">
                    Lịch sử đặt chỗ
                    <label class="badge badge-success"
                           ng-bind="bookingForm.bookingHistories.length"></label>
                    <i class="fa"
                       ng-class="bookingForm.showHistory?'fa-chevron-down':'fa-chevron-up'"></i>
                </h4>
                <div class="scroller" style="height: 150px;overflow-y: scroll;"
                     ng-if="bookingForm.showHistory">
                    <ul class="feeds">
                        <li ng-repeat="booking in bookingForm.bookingHistories">
                            <div class="col1">
                                <div class="cont">
                                    <div class="cont-col1">
                                        <div class="label label-sm label-default">
                                            <i class="fa fa-check"></i>
                                        </div>
                                    </div>
                                    <div class="cont-col2">
                                        <div class="desc">
                                            [{{booking.slotInfo.shopInfo.shop_name}},
                                             nhân viên {{booking.slotInfo.workerInfo.worker_name}}]
                                            <span class="label label-sm label-primary"
                                                  ng-bind="booking.slotInfo.duration_minute + 'phút'"></span>
                                            <span class="label label-sm label-success"
                                                  ng-bind="booking.cost|currency:'<?php echo CalendarForm::CURRENCY_CODE ?>':0"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col2">
                                <div class="date"
                                     ng-bind="booking.slotInfo.date + ' ' + booking.slotInfo.start_time|date:'MM/dd/yyyy @ h:mma'">
                                </div>
                            </div>
                        </li>
                    </ul>
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
        <?php echo Html::button('<i class="fa fa-pencil"></i>&nbsp;&nbsp;' . App::t("backend.booking.button", "Tùy chỉnh"), [
            "class" => "btn green",
            "ng-show" => "bookingForm.isEditable",
            "ng-click" => "editBooking()",
        ]) ?>
        <?php echo Html::button('<i class="fa fa-times"></i>&nbsp;&nbsp;' . App::t("backend.booking.button", "Từ chối"), [
            "class" => "btn red",
            "ng-show" => "bookingForm.isRejectAble && !bookingForm.isUpdateRejectAble",
            "ng-click" => "rejectBooking()",
        ]) ?>
        <?php echo Html::button('<i class="fa fa-check"></i>&nbsp;&nbsp;' . App::t("backend.booking.button", "Chấp thuận"), [
            "class" => "btn blue",
            "ng-show" => "bookingForm.isAcceptable",
            "ng-click" => "acceptBooking()",
        ]) ?>
    </div>
</script>