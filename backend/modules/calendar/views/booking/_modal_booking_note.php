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
<script type="text/ng-template" id="modal-booking-note.html">
    <div class="modal-header">
        <button type="button" class="close" aria-hidden="true"
                ng-click="closeModal()"></button>
        <h4 class="modal-title">
            <?php echo App::t("backend.booking-view.title", "Ghi chú"); ?>
        </h4>
    </div>
    <div class="modal-body">
        <?php $form = ActiveForm::begin([
            'id' => 'booking-note-form',
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
        <div class="row">
            <div class="col-md-12">
                <?= Html::textarea("note", "", [
                    "class" => "form-control autosizeme",
                    "ng-model" => "bookingForm.note",
                    "placeholder" => "Ghi chú",
                    "rows" => 3,
                ]) ?>
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
            "ng-click" => "saveBookingNote()",
        ]) ?>
    </div>
</script>