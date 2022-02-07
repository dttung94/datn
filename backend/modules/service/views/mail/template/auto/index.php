<?php
use backend\assets\AppAsset;
use backend\modules\service\forms\mail\MailTemplateForm;
use common\helper\HtmlHelper;
use common\helper\ArrayHelper;

/**
 * @var $this \backend\models\BackendView
 * @var $model MailTemplateForm
 */
$this->title = App::t("backend.service_sms.title", "Template email gửi tự động");
$this->subTitle = App::t("backend.service_sms.title", "");

$this->registerCssFile(
    Yii::$app->assetManager->getBundle(AppAsset::className())->baseUrl . "/pages/css/inbox.css", [
    'depends' => [AppAsset::className()]
]);
$this->breadcrumbs = [
    [
        "label" => $this->title
    ]
];
$this->actions = [
//    HtmlHelper::a("<i class='fa fa-cogs'></i> " . Yii::t('common.button', 'ＳＭＳ履歴'),
//        App::$app->urlManager->createUrl([
//            "service/sms/index",
//        ]), [
//            'class' => 'btn btn-primary',
//            "data-pjax" => 0,
//        ]),
];
?>
<div class="portlet light">
    <div class="portlet-body">
        <div class="row inbox">
            <div class="col-md-5">
                <ul class="inbox-nav margin-bottom-10">
                    <?php foreach ($model::$configs as $type => $config) {
                        if (!$config['isAuto']) {
                            continue;
                        }
                        echo HtmlHelper::tag("li",
                            HtmlHelper::a(ArrayHelper::getValue($config, "title", $model->getAttributeLabel($type)), App::$app->urlManager->createUrl([
                                "service/mail/auto",
                                "type" => $type
                            ]), [
                                "class" => "btn"
                            ]),
                            [
                                "class" => ($type == $model->type ? "active" : "")
                            ]);
                    } ?>
                </ul>
            </div>
            <div class="col-md-7">
                <?php echo $this->render("_form", [
                    "model" => $model,
                ]); ?>
            </div>
        </div>
    </div>
</div>