<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model MemberForm
 */
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use backend\modules\member\forms\MemberForm;

$this->registerJs(
    <<< JS
    jQuery(document).ready(function(){
    });
JS
    , \yii\web\View::POS_END, 'register-js-common-data-form');
?>
<?php $form = ActiveForm::begin([
    'id' => 'worker-form',
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
        <div class="col-md-8 col-md-offset-2">
            <?= $form->field($model, 'email', [])->textInput([
                'class' => 'form-control'
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <?= $form->field($model, 'note', [])->textarea([
                'class' => 'form-control',
                'rows' => 5,
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-9 col-md-offset-3">
            <?php echo Html::submitButton(
                "<i class='fa fa-save'></i> " . ($model->isNewRecord ? Yii::t('common.button', 'Save') : Yii::t('common.button', 'アップデート')),
                [
                    'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary'
                ]) ?>
            <?php
            echo Html::a(
                \Yii::t('common.label', 'キャンセル'),
                Yii::$app->urlManager->createUrl([
                    "member/manage",
                ]), [
                    "class" => "btn btn-default"
                ]
            );
            ?>
        </div>
    </div>

<?php ActiveForm::end(); ?>