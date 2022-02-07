<?php
use yii\bootstrap\ActiveForm;
use backend\assets\ThemeMetronicAsset;
use common\helper\HtmlHelper;
use backend\modules\service\forms\sms\SmsTemplateForm;

/**
 * @var $this \backend\models\BackendView
 * @var $model SmsTemplateForm
 */

$this->registerJs(<<<JS
    jQuery(document).ready(function () {
    });
JS
    , \yii\web\View::POS_END);
?>
<div class="inbox-content">
    <?php $form = ActiveForm::begin([
        'id' => 'sms-template-form',
        'layout' => 'horizontal',
        "options" => [
            "class" => "inbox-compose form-horizontal",
            "enctype" => "multipart/form-data",
        ],
    ]); ?>
    <?php echo HtmlHelper::activeHiddenInput($model, "type") ?>
    <?php if ($model->hasErrors()) {
        echo HtmlHelper::tag("div", $form->errorSummary($model), [
            "class" => "note note-danger"
        ]);
    } ?>
    <div class="inbox-compose-btn">
        <?php foreach ($model->getTemplateParams() as $param) {
            echo HtmlHelper::a($param, "javascript:;", [
                "class" => "btn btn-default btn-xs margin-top-10",
                "copy-clipboard" => "copy",
                "data-clipboard-text" => "{" . $param . "}",
            ]);
        } ?>
    </div>
    <div class="inbox-form-group">
        <?php echo HtmlHelper::activeTextarea($model, "content", [
            "class" => "form-control autosizeme maxlength-validate",
            "maxlength" => 500,
            "rows" => "8",
            "style" => [
                "resize" => "vertical",
            ],
        ]) ?>
    </div>
    <div class="inbox-compose-btn">
        <?php echo HtmlHelper::submitButton(App::t("backend.sms_template.button", "Lưu"), [
            "class" => "btn blue"
        ]) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>