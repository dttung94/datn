<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model ManagerForm
 */
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use backend\modules\system\forms\manager\ManagerForm;

$this->registerJs(
    <<< JS
    jQuery(document).ready(function(){
    });
JS
    , \yii\web\View::POS_END, 'register-js-manager-form');
?>
<?php $form = ActiveForm::begin([
    'id' => 'manager-form',
    'fieldConfig' => [
        'horizontalCssClasses' => [
            'label' => 'col-sm-4',
            'offset' => '',
            'wrapper' => 'col-sm-8',
            'error' => '',
            'hint' => '',
        ],
    ]
]);
$isOperator = "";
if (isset($model['role']) && $model['role'] == \common\entities\user\UserInfo::ROLE_OPERATOR) {
    $isOperator = "selected";
}
?>
<?php if ($model->hasErrors()) {
    echo Html::tag("div", $form->errorSummary($model), [
        "class" => "note note-danger"
    ]);
} ?>
    <div class="row">
        <div class="col-md-4 col-md-offset-2">
            <?= $form->field($model, 'full_name', [])->textInput([
                'maxlength' => 255,
                'class' => 'form-control',
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'phone_number', [])->textInput([
                "class" => "form-control",
            ]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 col-md-offset-2">
            <?= $form->field($model, 'email', [])->textInput([
                "class" => "form-control",
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'raw_password', [])->passwordInput([
                "class" => "form-control",
            ]) ?>
        </div>
    </div>
    <div class="row form-group">
        <div class="col-md-4 col-md-offset-2">
            <label class="label-control">Phân quyền</label>
            <select class="form-control" name="ManagerForm[role]">
                <option value="MANAGER">Quản lý</option>
            </select>
        </div>
    </div>
    <div class="row">
        <div class="col-md-9 col-md-offset-3">
            <?php echo Html::submitButton(
                "<i class='fa fa-save'></i> " . ($model->isNewRecord ? Yii::t('common.button', 'Lưu') : Yii::t('common.button', 'Thay đổi')),
                [
                    'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary'
                ]) ?>
            <?php echo Html::a(
                \Yii::t('common.button', 'Hủy bỏ'),
                Yii::$app->urlManager->createUrl([
                    "system/manager",
                ]), [
                    "class" => "btn btn-default"
                ]
            ); ?>
        </div>
    </div>

<?php ActiveForm::end(); ?>