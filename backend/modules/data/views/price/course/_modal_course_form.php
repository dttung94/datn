<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model CourseForm
 */
use yii\bootstrap\ActiveForm;
use backend\modules\data\forms\price\CourseForm;
use backend\modules\coupon\forms\CouponForm;

$model = new CourseForm();
?>
<script type="text/ng-template" id="modal-course-form.html">
    <div class="modal-header">
        <button type="button" class="close" aria-hidden="true"
                ng-click="closeModal()"></button>
        <h4 class="modal-title">Dịch vụ</h4>
    </div>
    <div class="modal-body">
        <?php $form = ActiveForm::begin([
            'id' => 'course-form',
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
                <?= $form->field($model, 'course_name', [])->textInput([
                    "class" => "form-control",
                    "ng-model" => "courseForm.course_name",
                ]) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'price', [])->textInput([
                    "class" => "form-control",
                    "ng-model" => "courseForm.price",
                ]) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'description', [])->textarea([
                    "class" => "form-control",
                    "ng-model" => "courseForm.description",
                ]) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn default"
                ng-click="closeModal()">Hủy
        </button>
        <button type="button" class="btn blue"
                ng-click="saveCourse()">
            <i class="fa fa-save"></i>&nbsp;&nbsp;Lưu
        </button>
    </div>
</script>