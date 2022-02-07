<?php
/**
 * @var $this \frontend\models\FrontendView
 */

use common\entities\worker\WorkerInfo;
use frontend\forms\booking\BookingEditForm;
use frontend\forms\booking\BookingOnlineForm;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$model = new BookingEditForm();
?>
<script type="text/ng-template" id="modal-booking-online-edit.html">
    <div class="modal-header">
        <h4 class="modal-title" ng-bind="booking.worker_name"></h4>
    </div>

    <div class="modal-body">
        <?php ActiveForm::begin([
            "id" => "booking-online-form",
        ])?>

        <div class="box-container box-space-between mb20">
            <div class="box-sm-20 box-xs-30 box-lg-20 box-container box-middle">
                <span class="text-blue">1．Dịch vụ</span>
            </div>

            <div class="box-sm-75 box-xs-65 box-lg-75">
                <div class="inner-addon right-addon">
                    <i class="material-icons">keyboard_arrow_down</i>
                    <?php echo Html::activeDropDownList($model, "course_id", [], [
                        "class" => "form-control",
                        "ng-model" => "editBookingForm.course_id",
                        "ng-options" => "course.course_id as course.course_name for course in editBookingForm.coursesInfo",
                        "ng-change" => 'changeCourseIdBookingOnline()'
                    ]) ?>
                </div>
                <p class="error-message mt20"
                   ng-show="editBookingForm.error.course_id">
                    <span ng-repeat="errorMsg in editBookingForm.error.course_id"
                          ng-bind="errorMsg"></span>
                </p>
            </div>
        </div>

        <div class="box-container box-space-between mb20">
            <div class="box-sm-20 box-xs-30 box-lg-20 box-container box-middle">
                <span class="text-blue">2．Thời gian</span>
            </div>

            <div class="box-sm-75 box-xs-65 box-lg-75">
                <div class="inner-addon right-addon">
                    <?php echo Html::textInput("duration_minute", "", [
                        "class" => "form-control",
                        "disabled" => "disabled",
                        "ng-model" => "duration_minute",
                    ]) ?>
                </div>

                <p class="error-message mt20"
                   ng-show="editBookingForm.error.course_id">
                    <span ng-repeat="errorMsg in editBookingForm.error.duration_minute"
                          ng-bind="errorMsg"></span>
                </p>
            </div>
        </div>

        <div class="mb20 tar"
             ng-init="isShowCommentText = false">
            <?php echo Html::a(App::t("frontend.shop.title", "Thêm ghi chú"), "javascript:;", [
                "style" => "color: red;",
                "ng-click" => "isShowCommentText = true",
                "ng-show" => "!isShowCommentText",
            ]) ?>
            <?php echo Html::activeTextarea($model, "comment", [
                "class" => "form-textarea",
                "placeholder" => App::t("frontend.free-booking.message", "Nếu quý khách có yêu cầu gì về dịch vụ vui lòng ghi vào phần này"),
                "ng-model" => "booking.comment",
                "ng-show" => "isShowCommentText",
                "style" => [
                    "resize" => "vertical",
                ],
            ]); ?>
        </div>

        <hr/>
        <div class="box-container box-space-between mb20"
             style="font-size: 18px;font-weight: 700;">
            <div class="box-xs-70 box-container">
                <div class="box-xs-20">
                    <span>Tổng:</span>
                </div>
            </div>

            <div class="box-xs-30 box-container box-end">
                <span ng-bind="toCalCost()|currency:'VNĐ':0"></span>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>

    <div class="modal-footer">
        <?php echo Html::a(App::t("frontend.online-booking.button", "Cập nhật"), "javascript:;", [
            "class" => "btn btn-primary btn-lg btn-block",
            "ng-click" => "saveEditBooking()",
            "id" => "edit-button"
        ]) ?>
        <?php echo Html::a(App::t("frontend.online-booking.button", "Hủy bỏ"), "javascript:;", [
            "class" => "btn btn-default btn-lg btn-block",
            "ng-click" => "closeModal()",
        ]) ?>
    </div>
</script>

