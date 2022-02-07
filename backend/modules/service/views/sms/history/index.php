<?php
use backend\assets\AppAsset;
use yii\widgets\Pjax;
use backend\modules\service\forms\sms\SmsHistoryForm;
use common\helper\HtmlHelper;

/**
 * @var $this \backend\models\BackendView
 * @var $model SmsHistoryForm
 */
$this->title = App::t("backend.service_sms.title", "Lịch sử SMS");
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
$this->registerCss(<<<CSS
.label.label-send-to{
    display: block;
    width: 105px;
    text-align: center;
}
CSS
);
?>
<?php Pjax::begin([
    "id" => "pjax-service-mail"
]); ?>
    <div class="portlet light">
        <div class="portlet-body">
            <div class="row inbox">
                <div class="col-md-12">
                    <?php echo $this->render("_history", [
                        "model" => $model,
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
<?php Pjax::end(); ?>