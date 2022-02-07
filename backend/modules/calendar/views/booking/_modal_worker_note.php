<?php
/**
 * @var $this \backend\models\BackendView
 */
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;

?>
<script type="text/ng-template" id="modal-worker-note.html">
    <div class="modal-header">
        <button type="button" class="close" aria-hidden="true"
                ng-click="closeModal()"></button>
        <h4 class="modal-title">
            <?php echo App::t("backend.booking-view.title", "ノートの女の子"); ?>
        </h4>
    </div>
    <div class="modal-body">
        <?php $form = ActiveForm::begin([
            'id' => 'worker-note-form',
        ]); ?>
        <div class="row">
            <div class="col-md-12">
                <?= Html::textarea("value", "", [
                    "class" => "form-control autosizeme",
                    "ng-model" => "value",
                    "rows" => 3,
                    "placeholder" => "ノートの女の子",
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
            "ng-click" => "saveWorkerConfig()",
        ]) ?>
    </div>
</script>