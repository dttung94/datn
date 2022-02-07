<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model CalendarForm
 */
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use backend\modules\calendar\forms\booking\WorkerCalendarSlotForm;
use backend\modules\calendar\forms\booking\CalendarForm;
use common\helper\DatetimeHelper;
$workerSlotForm = new WorkerCalendarSlotForm();
?>
<script type="text/ng-template" id="modal-worker-slot-form.html">
    <div class="modal-header">
        <button type="button" class="close" aria-hidden="true"
                ng-click="closeModal()"></button>
        <h4 class="modal-title">
            <?php echo App::t("backend.booking.title", "Khung làm việc") ?>
        </h4>
    </div>
    <div class="modal-body">
        <?php $form = ActiveForm::begin([
            'id' => 'worker-slot-form',
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
            <div class="col-md-4 col-md-offset-2">
                <label class="control-label">
                    Salon
                </label>
                <?= Html::textInput("shop_name", "", [
                    "class" => "form-control disabled",
                    "disabled" => "disabled",
                    "ng-model" => "slotForm.shopInfo.shop_name",
                ]) ?>
            </div>
            <div class="col-md-4">
                <label class="control-label">
                    Tên nhân viên
                </label>
                <?= Html::textInput("worker_name", "", [
                    "class" => "form-control disabled",
                    "disabled" => "disabled",
                    "ng-model" => "slotForm.workerInfo.worker_name",
                ]) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 col-md-offset-2">
                <label class="control-label">
                    Thời gian bắt đầu
                </label>
                <div class="row time-picker-layout">
                    <div class="col-md-6">
                        <?= Html::dropDownList('start_hour', '', DatetimeHelper::getListHours(), [
                            'class' => 'form-control',
                            "ng-model" => "slotForm.start_hour",
                            "ng-change" => "slotForm.start_hour == 24?slotForm.start_minute = 0:null",
                            "convert-to-number" => "convert-to-number",
                        ]) ?>
                    </div>
                    <div class="col-md-6"
                         ng-show="slotForm.start_hour != 24">
                        <?= Html::dropDownList('start_minute', '', DatetimeHelper::getListMinutes(), [
                            'class' => 'form-control',
                            "ng-model" => "slotForm.start_minute",
                            "convert-to-number" => "convert-to-number",
                        ]) ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <label class="control-label">
                    Thời gian phục vụ (phút)
                </label>
                <?= Html::textInput("duration_minute",$workerSlotForm->getDurationMinute(),[
                    "class" => "form-control disabled",
                    "disabled" => "disabled",
                    "ng-model" => "slotForm.duration_minute",
                ]) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn default"
                ng-click="closeModal()">Hủy bỏ
        </button>
<!--        <button type="button" class="btn blue"-->
<!--                ng-click="saveSlot()">-->
<!--            <i class="fa fa-save"></i>&nbsp;&nbsp;変更をセーブ-->
<!--        </button>-->
        <?php echo Html::a('<i class="fa fa-save"></i> ' . App::t("backend.worker.label", "Lưu"), 'javascript:;', [
            "ng-click" => "saveSlot(slotForm.workerInfo.worker_rank)",
            'class' => 'btn blue'
        ]) ?>
    </div>
</script>