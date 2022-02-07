<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model WorkerRatingHistoryForm
 */
use backend\modules\worker\forms\WorkerRatingHistoryForm;
use yii\bootstrap\ActiveForm;

$form = ActiveForm::begin([
    "id" => "rating-history-filter-form",
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
]) ?>
    <div class="form-body">
        <div class="row margin-bottom-10">
            <div class="col-md-3">
                <?php
                echo \yii\helpers\Html::activeTextInput($model, 'keyword', [
                    "class" => "form-control",
                    "placeholder" => $model->getAttributeLabel("Từ khóa")
                ])
                ?>
            </div>
            <div class="col-md-2">
                <?php echo \yii\helpers\Html::activeTextInput($model, 'filter_latest_rating', [
                    "class" => "form-control date-picker",
                    "placeholder" => "Ngày đánh giá",
                    'autocomplete' => 'off'
                ]) ?>
            </div>
            <div class="col-md-1">
                <?php echo \yii\helpers\Html::submitButton(Yii::t('common.button', 'Tìm kiếm'), [
                    'class' => 'btn btn-default'
                ]); ?>
            </div>
        </div>
    </div>
<?php ActiveForm::end() ?>