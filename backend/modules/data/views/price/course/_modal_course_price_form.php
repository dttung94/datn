<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model CoursePriceCreateForm
 */
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use backend\modules\data\forms\price\CoursePriceCreateForm;

$model = new CoursePriceCreateForm();
?>
<script type="text/ng-template" id="modal-course-price-form.html">
    <div class="modal-header">
        <button type="button" class="close" aria-hidden="true"
                ng-click="closeModal()"></button>
        <h4 class="modal-title">コース料金作成</h4>
    </div>
    <div class="modal-body">
        <?php $form = ActiveForm::begin([
            'id' => 'course-price-form',
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
                <?= $form->field($model, 'price', [])->textInput([
                    "class" => "form-control",
                    "ng-model" => "formData.price",
                ])->label("Phí dịch vụ") ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn default"
                ng-click="closeModal()">Hủy bỏ
        </button>
        <button type="button" class="btn blue"
                ng-click="saveCoursePrice()">
            <i class="fa fa-save"></i>&nbsp;&nbsp;Lưu
        </button>
    </div>
</script>