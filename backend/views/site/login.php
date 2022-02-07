<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use backend\assets\GuestAsset;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \backend\forms\LoginForm */

$this->title = App::t("backend.login.title", "Đăng Nhập");
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
<!-- BEGIN LOGIN FORM -->

<?php $form = ActiveForm::begin([
    'id' => 'login-form',
    "method" => "post",
    "options" => [
        "class" => "login-form",
    ],
]); ?>
<h3 class="form-title">
    <?php echo App::t("backend.login.title", "ĐĂNG NHẬP"); ?>
</h3>
<div class="form-group">
    <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
    <label class="control-label visible-ie8 visible-ie9">
        <?php echo $model->getAttributeLabel("username") ?>
    </label>
    <?= $form->field($model, 'username', [])->textInput([
        "class" => "form-control form-control-solid placeholder-no-fix",
        "autocomplete" => "off",
        "placeholder" => $model->getAttributeLabel("username"),
    ]) ?>
</div>
<div class="form-group">
    <label class="control-label visible-ie8 visible-ie9">
        <?php echo $model->getAttributeLabel("password") ?>
    </label>
    <?= $form->field($model, 'password')->passwordInput([
        "class" => "form-control form-control-solid placeholder-no-fix",
        "autocomplete" => "off",
        "placeholder" => $model->getAttributeLabel("password"),
    ]) ?>
</div>
<div class="form-actions">
    <?= Html::submitButton(App::t("backend.login.button", "Đăng nhập"), [
        'class' => 'btn btn-success uppercase',
        'name' => 'login-button'
    ]) ?>
    <label class="rememberme check">
        <?= $form->field($model, 'rememberMe')->checkbox() ?>
    </label>
    <?php echo Html::a(App::t("backend.login.button", "Quên mật khẩu?"), App::$app->urlManager->createUrl([
        "site/forgot-password",
    ]), [
        "class" => "forget-password",
    ]) ?>
</div>
<?php ActiveForm::end(); ?>
<!-- END LOGIN FORM -->