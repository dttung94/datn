<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model UserForm
 */
use backend\modules\system\forms\user\UserForm;
use common\helper\HtmlHelper;
use yii\bootstrap\ActiveForm;

$form = ActiveForm::begin([
    "id" => "user-filter-form",
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
            <div class="col-md-2">
                <?php echo HtmlHelper::activeDropDownList($model, 'role', [
                    "" => $model->getAttributeLabel("role"),
                    UserForm::ROLE_ADMIN => UserForm::ROLE_ADMIN,
                    UserForm::ROLE_MANAGER => UserForm::ROLE_MANAGER,
                    UserForm::ROLE_USER => UserForm::ROLE_USER,
                ], [
                    "class" => 'form-control select2me',
                    "data-placeholder" => $model->getAttributeLabel("role")
                ]);
                ?>
            </div>
            <div class="col-md-1">
                <?php echo HtmlHelper::submitButton(App::t("backend.system_user.button", "Search"), [
                    'class' => 'btn btn-default pull-right'
                ]); ?>
            </div>
        </div>
    </div>
<?php ActiveForm::end() ?>