<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model CalendarForm
 */
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use backend\modules\calendar\forms\booking\CalendarForm;
use backend\modules\calendar\forms\worker\WorkerCreateCalendarSlotForm;

$workerSlotForm = new WorkerCreateCalendarSlotForm();
?>
<script type="text/ng-template" id="modal-create-worker-slot-form.html">
    <div class="modal-header">
        <button type="button" class="close" aria-hidden="true"
                ng-click="closeModal()"></button>
        <h4 class="modal-title">
            <?php echo App::t("backend.booking.title", "Tạo khung làm việc đồng loạt") ?>
        </h4>
    </div>
    <div class="modal-body">
        <?php $form = ActiveForm::begin([
            'id' => 'worker-create-slot-form',
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
            <div class="col-md-8 col-md-offset-2">
                <label class="control-label">
                    Tên nhân viên
                </label>
                <?= Html::textInput("worker_name", "", [
                    "class" => "form-control disabled",
                    "disabled" => "disabled",
                    "ng-model" => "slotForm.worker_name",
                ]) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 col-md-offset-2">
                <label class="control-label">
                    Ngày làm việc
                </label>
                <?= Html::textInput('date', '', [
                    'class' => 'form-control disabled',
                    "ng-model" => "slotForm.date",
                    "readonly" => "readonly",
                ]) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 col-md-offset-2">
                <label class="control-label">
                    Thời gian bắt đầu
                </label>
                <?= Html::textInput('start_time', '', [
                    'class' => 'form-control disabled',
                    "ng-model" => "slotForm.start_time",
                    "readonly" => "readonly",
                ]) ?>
            </div>
            <div class="col-md-4">
                <label class="control-label">
                    Thời gian kết thúc
                </label>
                <?= Html::textInput("end_time", '', [
                    'class' => 'form-control disabled',
                    "ng-model" => "slotForm.end_time",
                    "readonly" => "readonly",
                ]) ?>
            </div>
        </div>
        <hr/>
        <div class="row">
            <div class="col-md-4 col-md-offset-2">
                <label class="control-label">
                    Tạo khung bắt đầu từ
                </label>
                <div class="row time-picker-layout">
                    <div class="col-md-6">
                        <?= Html::dropDownList('start_hour', '', [], [
                            'class' => 'form-control',
                            "ng-model" => "slotForm.start_hour",
                            "ng-options" => "k as v for (k, v) in slotForm.listHour",
                            "convert-to-number" => "convert-to-number",
                        ]) ?>
                    </div>
                    <div class="col-md-6">
                        <?= Html::dropDownList('start_minute', '', [], [
                            'class' => 'form-control',
                            "ng-model" => "slotForm.start_minute",
                            "ng-options" => "k as v for (k, v) in slotForm.listMinute",
                            "convert-to-number" => "convert-to-number",
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 col-md-offset-2">
                <label class="control-label">
                    Thời gian mỗi khung giờ
                </label>
                <?= Html::textInput('duration_minute', '', [
                    'class' => 'form-control disabled',
                    "ng-model" => "slotForm.duration_minute",
                    "readonly" => "readonly",
                ]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($workerSlotForm, 'break_duration_minute', [
                    "enableClientValidation" => false,
                    "options" => [
                        "ng-class" => "slotForm.error.break_duration_minute?'has-error':''"
                    ]
                ])->dropDownList([], [
                    "class" => "form-control",
                    "ng-model" => "slotForm.break_duration_minute",
                    "ng-options" => "k as v for (k, v) in slotForm.listMinute",
                ])->error([
                    "tag" => "div",
                    "class" => "help-block help-block-error",
                    "data-attribute" => "break_duration_minute",
                    "ng-show" => "slotForm.error.break_duration_minute",
                    "ng-repeat" => "errorMsg in slotForm.error.break_duration_minute track by \$index",
                    "ng-bind" => "errorMsg",
                ])->label("Nghỉ giữa 2 khung") ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn default"
                ng-click="closeModal()">Hủy bỏ
        </button>
        <button type="button" class="btn blue"
                ng-click="createWorkerSlot()">
            <i class="fa fa-save"></i>&nbsp;&nbsp;Tạo mới
        </button>
    </div>
</script>