<?php
use common\helper\HtmlHelper;
use frontend\forms\auth\LoginForm;
use yii\bootstrap\ActiveForm;
use yii\helpers\Json;

/**
 * @var $this \frontend\models\FrontendView
 * @var $model LoginForm
 */
$this->subTitle = App::t("frontend.login.title", "Login");
$formData = Json::encode($model->toArray());
?>
<div ng-controller="LoginController"
     ng-init='init(<?php echo $formData; ?>)'>
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
    <h2 class="login-title">Đăng nhập</h2>
    <div class="login-form">
        <?php if ($model->hasErrors()) {
            echo HtmlHelper::tag("div", $form->errorSummary($model, [
                "header" => "",
            ]), [
                "class" => "note note-danger error-message mb20",
            ]);
        } ?>
        <div class="inner-addon right-addon mb20">
            <i class="material-icons">phone_android</i>
            <?php echo HtmlHelper::activeTextInput($model, "username", [
                "placeholder" => "Số điện thoại",
                "class" => "form-control",
                "ng-model" => "formData.username",
                "ng-enter" => "toLogin()",
            ]) ?>
            <p class="error-message"
               ng-show="formData.error.username">
                <span ng-repeat="errorMsg in formData.error.username"
                      ng-bind="errorMsg"></span>
            </p>
        </div>

        <div class="inner-addon right-addon mb20">
            <i class="material-icons">lock_outline</i>
            <?php echo HtmlHelper::activePasswordInput($model, "password", [
                "placeholder" => "Mật khẩu",
                "class" => "form-control",
                "ng-model" => "formData.password",
                "ng-enter" => "toLogin()",
            ]) ?>
            <p class="error-message"
               ng-show="formData.error.password">
                <span ng-repeat="errorMsg in formData.error.password"
                      ng-bind="errorMsg"></span>
            </p>
        </div>

        <div class="box-container box-middle mb20">
            <div class="remember-checkbox">
                <label>
                    <?php echo HtmlHelper::activeCheckbox($model, "rememberMe", [
                        "label" => null,
                        "uncheck" => null,
                        "ng-model" => "formData.rememberMe",
                        "ng-true-value" => 1,
                        "ng-false-value" => 0,
                    ]) ?>
                    <span>Ghi nhớ đăng nhập</span>
                    <span class="checkmark"></span>
                </label>
            </div>
        </div>

        <div class="inner-addon left-addon btn-addon mb20">
            <i class="material-icons text-white">perm_identity</i>
            <?php echo HtmlHelper::button("ĐĂNG NHẬP", [
                "class" => "btn btn-primary btn-lg btn-block",
                "ng-click" => "toLogin()",
            ]); ?>
        </div>

        <p class="mb10 text-center">
            <?php echo HtmlHelper::a(App::t("frontend.login.button", "Quên mật khẩu"), [
                "/site/forgot-password",
            ], [
                "class" => "text-blue",
            ]); ?>
        </p>

        <p class="mb10 text-center">
            <?php echo HtmlHelper::a(App::t("frontend.login.button", "Gửi lại SMS xác thực"), [
                "/site/resend-verify",
            ], [
                "class" => "text-blue",
            ]); ?>
        </p>

        <p class="mb10 text-center">
            <?php echo HtmlHelper::a(App::t("frontend.login.button", "Chưa có tài khoản? Đăng ký ngay"), [
                "/site/sign-up",
            ], [
            ]); ?>
        </p>
    </div>
    <?php ActiveForm::end(); ?>
</div>