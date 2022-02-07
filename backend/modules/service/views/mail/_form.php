<?php
use backend\modules\service\forms\mail\MailTemplateSearchForm;
use yii\bootstrap\ActiveForm;
use backend\assets\ThemeMetronicAsset;
use common\helper\HtmlHelper;
/**
 * @var $this \backend\models\BackendView
 * @var $model MailTemplateSearchForm
 */

$this->subTitle = App::t("backend.service_mail.title", "メールテンプレート");

$stylesheets = Yii::$app->assetManager->getBundle(ThemeMetronicAsset::className())->baseUrl . "/global/plugins/bootstrap-wysihtml5/wysiwyg-color.css";

$url = Yii::$app->urlManager->createUrl([
    "service/mail",
    "type" => "template-update",
    "key" => $model->key,
    "language" => ""
]);
$this->registerJs(<<<JS
    jQuery(document).ready(function () {
        //todo init wysihtml5
        $('.inbox-wysihtml5').wysihtml5({
            "stylesheets": ["$stylesheets"]
        });
        //todo redirect when change language
        $("#template-language").change(function(el){
            var lang = el.val;
            window.location.replace("$url" + lang);
        });
    });
JS
    , \yii\web\View::POS_END);
?>
<div class="inbox-header">
    <h1 class="pull-left">Inbox</h1>
    <form class="form-inline pull-right" method="get" action="index.html">
        <?php echo HtmlHelper::activeDropDownList($model, "language", $model->getListLanguage(), [
            "class" => "form-control select2me",
            "id" => "template-language"
        ]) ?>
    </form>
</div>
<div class="inbox-content">
    <?php $form = ActiveForm::begin([
        'id' => 'mail-template-form',
        'layout' => 'horizontal',
        "options" => [
            "class" => "inbox-compose form-horizontal",
            "enctype" => "multipart/form-data",
        ],
    ]); ?>
    <div class="inbox-compose-btn">
        <?php foreach ($model->getTemplateParams() as $param) {
            echo HtmlHelper::a($param, "javascript:;", [
                "class" => "btn btn-default btn-xs",
                "data-clipboard" => "copy",
                "data-clipboard-text" => "{" . $param . "}",
            ]);
        } ?>
    </div>
    <div class="inbox-form-group">
        <label class="control-label">
            <?php echo $model->getAttributeLabel("title"); ?>
        </label>
        <div class="controls">
            <?php echo HtmlHelper::activeTextInput($model, "title", [
                "class" => "form-control"
            ]) ?>
        </div>
    </div>
    <div class="inbox-form-group">
        <?php echo HtmlHelper::activeTextarea($model, "content", [
            "class" => "inbox-editor inbox-wysihtml5 form-control",
            "rows" => "12",
        ]) ?>
    </div>
    <div class="inbox-compose-btn">
        <?php echo HtmlHelper::submitButton(App::t("backend.service_mail.button", "Save"), [
            "class" => "btn blue"
        ]) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>