<?php
use yii\bootstrap\ActiveForm;
use common\helper\HtmlHelper;
use backend\modules\service\forms\mail\MailTemplateForm;
use common\entities\service\TemplateMail;

/**
 * @var $this \backend\models\BackendView
 * @var $model MailTemplateForm
 */
$params = $model->getTemplateParams();
$this->registerJs(<<<JS
    jQuery(document).ready(function () {
    });
JS
    , \yii\web\View::POS_END);
?>
<div class="inbox-content">
    <?php $form = ActiveForm::begin([
        'id' => 'mail-template-form',
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
    <?php if (!empty($params)): ?>
    <div class="inbox-compose-btn" style="margin-bottom: 10px">
        <b>パラメーター</b><br>
        <?php foreach ($params as $param) {
            echo HtmlHelper::a($param, "javascript:;", [
                "class" => "btn btn-default btn-xs margin-top-10",
                "style" => [
                    "margin-bottom" => "10px",
                ],
                "copy-clipboard" => "copy",
                "data-clipboard-text" => "{" . $param . "}",
            ]);
        } ?>
    </div>
    <?php endif; ?>
    <div class="inbox-form-group">
        <b>タイトル</b>
        <?php echo HtmlHelper::activeTextInput($model, "title", [
            "class" => "form-control autosizeme maxlength-validate",
            "maxlength" => 150,
            "style" => [
                "margin-bottom" => "10px",
            ],
            'placeholder' => 'Title Mail',
            'autocomplete' => 'off'
        ]) ?>
        <b>本文</b>
        <?php echo HtmlHelper::activeTextarea($model, "content", [
            "class" => "form-control autosizeme maxlength-validate",
            "maxlength" => 500,
            "rows" => "8",
            "style" => [
                "resize" => "vertical",
            ],
            'placeholder' => 'Content Mail'
        ]) ?>
    </div>
    <div class="inbox-compose-btn">
        <button class="btn blue" type="submit" name="save" value="save">
            <i class="fa fa-save"></i>
            Lưu
        </button>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<script>
    // Task URS-511
    // fix lỗi gửi lại mail khi mà trang đó f5/reload
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }
</script>