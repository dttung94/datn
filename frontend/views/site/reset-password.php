<?php
use frontend\forms\auth\ResetPasswordForm;
use common\helper\HtmlHelper;
use yii\bootstrap\ActiveForm;

/**
 * @var $this \frontend\models\FrontendView
 * @var $model ResetPasswordForm
 * @var $isValidToken bool
 * @var $isSuccess bool
 */
$this->subTitle = App::t("frontend.forgot_password.message", "Reset password");
$this->registerJs(<<<JS
    jQuery(document).ready(function () {
    });
JS
    , \yii\web\View::POS_END, "register-js-reset-password");
?>
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

        <div class="box-xs-10 mb20">
            <i class="material-icons">lock_outline</i>
        </div>

        <div class="box-xs-90 mb20">
            Mật khẩu
            <?php echo HtmlHelper::activePasswordInput($model, "password", [
                "placeholder" => "Mật khẩu có ít nhất 6 kí tự",
                "class" => "form-control",
            ]) ?>
        </div>

        <div class="box-xs-10 mb20">
            <i class="material-icons">lock_outline</i>
        </div>

        <div class="box-xs-90 mb40">
            Nhập lại mật khẩu
            <?php echo HtmlHelper::activePasswordInput($model, "re_password", [
                "placeholder" => "Mật khẩu có ít nhất 6 kí tự",
                "class" => "form-control",
            ]) ?>
        </div>

        <div class="box-xs-100 mb20">
            <?php echo HtmlHelper::submitButton("Cập nhật", [
                "class" => "btn btn-primary btn-lg btn-block"
            ]); ?>
        </div>

    </div>
<?php ActiveForm::end(); ?>