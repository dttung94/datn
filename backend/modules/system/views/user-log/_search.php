<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model UserLogForm
 */
use backend\modules\system\forms\user\UserLogForm;
use yii\bootstrap\ActiveForm;
use common\helper\HtmlHelper;
use common\helper\ArrayHelper;

?>
<div class="row">
    <div class="col-md-12">
        <?php $form = ActiveForm::begin([
            "id" => "user-log-filter-form",
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
                <div class="col-md-3">
                    <?php echo HtmlHelper::activeTextInput($model, 'keyword', [
                        "class" => "form-control",
                        "placeholder" => $model->getAttributeLabel("keyword")
                    ]); ?>
                </div>
                <div class="col-md-3">
                    <?php echo HtmlHelper::activeDropDownList($model, 'filter_user_id',
                        ArrayHelper::merge([
                            "" => "",
                        ], $model->listUsers), [
                            "class" => "form-control select2me",
                            "placeholder" => $model->getAttributeLabel("filter_user_id")
                        ]); ?>
                </div>
                <div class="col-md-3">
                    <?php echo HtmlHelper::activeDropDownList($model, 'filter_action', ArrayHelper::merge([
                        "" => "",
                    ], UserLogForm::getListActions()), [
                        "class" => "form-control select2me",
                        "placeholder" => $model->getAttributeLabel("filter_action")
                    ]); ?>
                </div>
                <div class="col-md-1">
                    <?php echo HtmlHelper::submitButton(App::t("backend.user_log.button", "検索"), [
                        'class' => 'btn btn-default pull-right'
                    ]); ?>
                </div>
            </div>
        </div>
        <?php ActiveForm::end() ?>
    </div>
</div>
