<?php
use frontend\forms\auth\PasswordResetRequestForm;
use yii\bootstrap\ActiveForm;
use common\helper\HtmlHelper;

/**
 * @var $this \frontend\models\FrontendView
 * @var $model PasswordResetRequestForm
 */

$this->registerJs(<<<JS
    jQuery(document).ready(function () {
    });
JS
    , \yii\web\View::POS_END, "register-js-sign-up");
?>
<div ng-controller="ForgotPasswordController">
    <?php $form = ActiveForm::begin([
        'id' => 'login-form',
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
    <div class="login-form box-container box-row-wrap box-space-between">
        <?php if ($model->hasErrors()) {
            echo $form->errorSummary($model, [
                "header" => "",
                "class" => "error-message",
            ]);
        } ?>
        <div class="box-xs-10 mb20">
            <i class="material-icons">phone_android</i>
        </div>

        <div class="box-xs-90 mb50">
            Số điện thoại
            <?php echo HtmlHelper::activeTextInput($model, "phone_number", [
                "placeholder" => "Nhập số điện thoại của bạn",
                "class" => "form-control",
                "ng-model" => "formData.phone_number",
            ]) ?>
        </div>

        <div class="box-xs-100 mb20">
            <?php echo HtmlHelper::button("Gửi", [
                "class" => "btn btn-primary btn-lg btn-block",
                "ng-click" => "toForgotPassword()",
            ]); ?>
        </div>

    </div>
    <?php ActiveForm::end(); ?>
</div>
