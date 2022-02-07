<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model ShopForm
 */
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap\ActiveForm;
use backend\modules\shop\forms\ShopForm;

$form = ActiveForm::begin([
    "id" => "grid-view-shop-filter",
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
            <div class="col-md-5">
                <?php
                echo Html::activeTextInput($model, 'keyword', [
                    "class" => "form-control",
                    "placeholder" => "Từ khóa",
                ])
                ?>
            </div>
            <div class="col-md-1">
                <?php echo Html::submitButton(Yii::t('common.button', 'Tìm kiếm'), [
                    'class' => 'btn btn-default'
                ]); ?>
            </div>
        </div>
    </div>
<?php ActiveForm::end() ?>