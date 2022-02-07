<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model UserProfileForm
 */
use backend\assets\AppAsset;
use backend\forms\UserProfileForm;
use yii\bootstrap\Html;
use yii\bootstrap\ActiveForm;

$bundle = App::$app->assetManager->getBundle(AppAsset::className());
$this->registerCssFile($bundle->baseUrl . "/pages/css/profile.css", [
    "depends" => [
        AppAsset::className(),
    ]
]);

$this->title = Yii::t('common.label', "Thông tin người dùng", [
]);
$this->subTitle = $model->full_name;

$this->breadcrumbs = [
    [
        "label" => $this->title
    ]
];
$this->actions = [
];
$this->registerJs(
    <<< JS
    jQuery(document).ready(function(){
    });
JS
    , \yii\web\View::POS_END, 'register-js-user-profile');
?>
<!-- BEGIN PAGE CONTENT-->
<div class="row margin-top-20">
    <div class="col-md-12">
        <!-- END BEGIN PROFILE SIDEBAR -->
        <!-- BEGIN PROFILE CONTENT -->
        <div class="profile-content">
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet light">
                        <div class="portlet-body">
                            <?php $form = ActiveForm::begin([
                                'id' => 'user-change-password-form',
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
                            <div class="row">
                                <div class="col-md-8 col-md-offset-2">
                                    <?= $form->field($model, 'username', [])->textInput([
                                        'maxlength' => 255,
                                        'class' => 'form-control',
                                        'readonly' => 'readonly',
                                    ]) ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 col-md-offset-2">
                                    <?= $form->field($model, 'current_password', [])->passwordInput([
                                        'maxlength' => 255,
                                        'class' => 'form-control',
                                    ]) ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 col-md-offset-2">
                                    <?= $form->field($model, 'new_password', [])->passwordInput([
                                        'maxlength' => 255,
                                        'class' => 'form-control',
                                    ]) ?>
                                </div>
                                <div class="col-md-4">
                                    <?= $form->field($model, 're_new_password', [])->passwordInput([
                                        'maxlength' => 255,
                                        'class' => 'form-control',
                                    ]) ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-8 col-md-offset-2">
                                    <?php echo Html::submitButton(
                                        "<i class='fa fa-key'></i> " . Yii::t('common.button', 'Đổi mật khẩu'), [
                                        'class' => 'btn btn-primary pull-right'
                                    ]) ?>
                                </div>
                            </div>

                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END PROFILE CONTENT -->
    </div>
</div>