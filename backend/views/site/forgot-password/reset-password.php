<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use backend\assets\GuestAsset;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \backend\forms\ResetPasswordForm */
/* @var $is_done boolean */

$this->title = App::t("backend.reset_password.title", "Reset Password");
$this->params['breadcrumbs'][] = $this->title;

$asset = GuestAsset::register($this);
$this->registerCssFile($asset->baseUrl . "/pages/css/login.css", [
    "depends" => [
        GuestAsset::className()
    ]
]);
$this->registerJs(<<<JS
    jQuery(document).ready(function () {
    });
JS
    , \yii\web\View::POS_END);
?>
<!-- BEGIN REGISTRATION FORM -->
<?php $form = ActiveForm::begin([
    'id' => 'register-form',
    "method" => "post",
    "options" => [
        "class" => "register-form",
    ],
]); ?>
<h3>
    <?php echo App::t("backend.reset_password.title", "Reset Password") ?>
</h3>
<?php if ($is_done) { ?>
    <div class="alert alert-success">
        <?php echo App::t("backend.reset_password.message", 'Mật khẩu của bạn đã được thay đổi. Click vào <a href="{login-url}" class="alert-link"> để đăng nhập </a>.</br>Trình duyệt sẽ tự động chuyển tới <a href="login-url" class="alert-link">trang đăng nhập</a> sau {time} giây', [
            "time" => Html::tag("span", 5, [
                "class" => "label label-danger countdown2redirect",
                "data-redirect-url" => App::$app->urlManager->createUrl([
                    "site/login"
                ]),
            ]),
            "login-url" => App::$app->urlManager->createUrl([
                "site/login"
            ]),
        ]) ?>
    </div>
<?php } else { ?>
    <?php if ($model->hasErrors()) {
        echo Html::tag("div", $form->errorSummary($model), [
            "class" => "note note-danger"
        ]);
    } ?>
    <p class="hint">
        <?php echo App::t("backend.reset_password.title", "Nhập mật khẩu mới") ?>
    </p>
    <div class="form-group">
        <label class="control-label visible-ie8 visible-ie9">
            <?php echo $model->getAttributeLabel("new_password") ?>
        </label>
        <?= $form->field($model, 'new_password')->passwordInput([
            "class" => "form-control form-control-solid placeholder-no-fix",
            "autocomplete" => "off",
            "placeholder" => $model->getAttributeLabel("new_password"),
        ])->label(false) ?>
    </div>
    <div class="form-group">
        <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
        <label class="control-label visible-ie8 visible-ie9">
            <?php echo $model->getAttributeLabel("repeat_password") ?>
        </label>
        <?= $form->field($model, 'repeat_password')->passwordInput([
            "class" => "form-control form-control-solid placeholder-no-fix",
            "autocomplete" => "off",
            "placeholder" => $model->getAttributeLabel("repeat_password"),
        ])->label(false) ?>
    </div>
    <div class="form-actions">
        <?php echo Html::a(App::t("backend.reset_password.button", "Quay lại"), App::$app->urlManager->createUrl([
            "site/login"
        ]), [
            "class" => "btn btn-default col-md-offset-1",
        ]) ?>
        <?= Html::submitButton(App::t("backend.reset_password.button", "Reset Password"), [
            'class' => 'btn btn-success col-md-offset-4',
            'name' => 'login-button'
        ]) ?>
    </div>
<?php } ?>
<?php ActiveForm::end(); ?>
<!-- END REGISTRATION FORM -->