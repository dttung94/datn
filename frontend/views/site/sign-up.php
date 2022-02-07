<?php
use common\helper\HtmlHelper;
use yii\bootstrap\ActiveForm;
use frontend\forms\auth\SignupForm;
use yii\helpers\Json;

/**
 * @var $this \frontend\models\FrontendView
 * @var $model SignupForm
 */

$formData = Json::encode($model->toArray());

$this->subTitle = App::t("frontend.sign-up.title", "Sign up");
$this->registerJs(<<<JS
    jQuery(document).ready(function () {
    });
JS
    , \yii\web\View::POS_END, "register-js-sign-up");
?>
<div ng-controller="SignUpController"
     ng-init='init(<?php echo $formData; ?>)'>
    <?php $form = ActiveForm::begin([
        'id' => 'sign-up-form',
        "options" => [
        ],
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
        echo HtmlHelper::tag("div", $form->errorSummary($model), [
            "class" => "note note-danger box-container box-center text-danger mb10"
        ]);
    } ?>
    <div class="login-form">
        <div class="inner-addon right-addon mb20">
<!--            <i class="material-icons text-info">done</i>-->
            <?php echo HtmlHelper::activeTextInput($model, "phone_number", [
                "placeholder" => "Số điện thoại",
                "class" => "form-control",
                "ng-model" => "signUpForm.phone_number",
            ]) ?>
            <p class="error-message"
               ng-show="signUpForm.error.phone_number">
                <span ng-repeat="errorMsg in signUpForm.error.phone_number"
                      ng-bind="errorMsg"></span>
            </p>
        </div>



        <div class="inner-addon right-addon mb20">
<!--            <i class="material-icons text-info">done</i>-->
            <?php echo HtmlHelper::activeTextInput($model, "full_name", [
                "placeholder" => "Họ tên",
                "class" => "form-control",
                "ng-model" => "signUpForm.full_name",
            ]) ?>
<!--            <p class="error-message"-->
<!--               ng-show="signUpForm.error.email">-->
<!--                <span ng-repeat="errorMsg in signUpForm.error.email"-->
<!--                      ng-bind="errorMsg"></span>-->
<!--            </p>-->
        </div>

        <div class="inner-addon right-addon mb20">
<!--            <i class="material-icons text-info">done</i>-->
            <?php echo HtmlHelper::activePasswordInput($model, "password", [
                "placeholder" => "Mật khẩu (tối thiểu 6 ký tự)",
                "class" => "form-control",
                "ng-model" => "signUpForm.password",
            ]) ?>
            <p class="error-message"
               ng-show="signUpForm.error.password">
                <span ng-repeat="errorMsg in signUpForm.error.password"
                      ng-bind="errorMsg"></span>
            </p>
        </div>

        <div class="inner-addon right-addon mb20">
<!--            <i class="material-icons text-info">done</i>-->
            <?php echo HtmlHelper::activePasswordInput($model, "re_password", [
                "placeholder" => "Nhập lại mật khẩu",
                "class" => "form-control",
                "ng-model" => "signUpForm.re_password",
            ]) ?>
            <p class="error-message"
               ng-show="signUpForm.error.re_password">
                <span ng-repeat="errorMsg in signUpForm.error.re_password"
                      ng-bind="errorMsg"></span>
            </p>
        </div>



    </div>

    <div class="login-form">
        <div class="inner-addon left-addon btn-addon mb20">
            <i class="material-icons text-white"> perm_identity</i>
            <?php echo HtmlHelper::button("Đăng ký", [
                "class" => "btn btn-primary btn-lg btn-block",
                "ng-click" => "toSignUp()"
            ]); ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
    <p class="mb50 text-center" style="color: red">
        <?php echo HtmlHelper::a(App::t("frontend.login.button", "Bạn đã có tài khoản? Đăng nhập ngay"), ["/site/login",], ["class" => "text-blue"]); ?>
    </p>

</div>

<script type="text/javascript">
</script>