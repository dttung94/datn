<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$form = ActiveForm::begin([
    "id" => "grid-view-rating-filter",
    'method' => 'GET',
    'layout' => 'horizontal',
    'fieldConfig' => [
        'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
        'horizontalCssClasses' => [
            'label' => 'col-sm-4',
            'offset' => '',
            'wrapper' => 'col-sm-8',
            'error' => '',
            'hint' => '',
        ],
    ],
]);

?>
    <div class="form-body">
        <div class="row margin-bottom-10">
            <div class="col-md-5">
                <?php
                    echo Html::activeTextInput($model, 'worker_name', [
                        "class" => "form-control",
                        "placeholder" => 'Từ khóa'
                    ])
                ?>
            </div>
            <div class="col-md-3">
                <?php echo Html::activeTextInput($model, 'created_at', [
                    "class" => "form-control date-picker",
                    "placeholder" => "Ngày khởi tạo",
                    'autocomplete' => 'off'
                ]) ?>
            </div>
            <div class="col-md-1">
                <?php echo Html::submitButton(Yii::t('common.button', '検索'), [
                    'class' => 'btn btn-default'
                ]); ?>
            </div>
        </div>
    </div>
<?php ActiveForm::end() ?>
