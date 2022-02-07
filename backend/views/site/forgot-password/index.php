<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use backend\assets\GuestAsset;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \backend\forms\LoginForm */
/* @var $is_done boolean */

$this->title = App::t("backend.forgot_password.title", "Quên mật khẩu");
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
<!-- BEGIN FORGOT PASSWORD FORM -->
<?php $form = ActiveForm::begin([
    'id' => 'forget-form',
    "method" => "post",
    "options" => [
        "class" => "forget-form",
    ],
]); ?>
<h3>
    <?php echo App::t("backend.forgot_password.title", "QUÊN MẬT KHẨU") ?>
</h3>
<?php if ($is_done) { ?>
    <div class="alert alert-success">
        <?php echo App::t("backend.forgot_password.message", 'Email cấp lại mật khẩu đã gửi tới email của bạn.</br>Trình duyệt sẽ tự động chuyển tới <a href="{login-url}" class="alert-link">trang login</a> sau {time} giây', [
            "time" => Html::tag("span", 10, [
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
    <p>
        <?php echo App::t("backend.forgot_password.message", "Nhập email để xác nhận") ?>
    </p>
    <div class="form-group">
        <?= $form->field($model, 'email', [])->textInput([
            "class" => "form-control form-control-solid placeholder-no-fix",
            "autocomplete" => "off",
            "placeholder" => $model->getAttributeLabel("email"),
        ])->label(false) ?>
    </div>
    <div class="form-actions">
        <?php echo Html::a(App::t("backend.forgot_password.button", "Quay lại"), App::$app->urlManager->createUrl([
            "site/login"
        ]), [
            "class" => "btn btn-default col-md-offset-3",
        ]) ?>
        <?= Html::submitButton(App::t("backend.forgot_password.button", "Gửi"), [
            'class' => 'btn btn-success col-md-offset-6',
        ]) ?>
    </div>
<?php } ?>
<?php ActiveForm::end(); ?>
<!-- END FORGOT PASSWORD FORM -->