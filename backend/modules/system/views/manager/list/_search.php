<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model ManagerForm
 */
use backend\modules\system\forms\manager\ManagerForm;
use common\helper\HtmlHelper;
use yii\bootstrap\ActiveForm;

$form = ActiveForm::begin([
    "id" => "manager-filter-form",
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
    "options" => [
        "class" => "margin-bottom-10"
    ]
]) ?>
    <div class="form-body">
        <div class="row">
            <div class="col-md-5">
                <?php echo HtmlHelper::activeTextInput($model, 'keyword', [
                    "class" => "form-control",
                    "placeholder" => $model->getAttributeLabel("keyword")
                ]); ?>
            </div>
            <div class="col-md-1">
                <?php echo HtmlHelper::submitButton(App::t("backend.system_manager.button", "Tìm kiếm"), [
                    'class' => 'btn btn-default'
                ]); ?>
            </div>
        </div>
    </div>
<?php ActiveForm::end() ?>