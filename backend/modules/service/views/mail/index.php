    <?php
use backend\assets\AppAsset;
use common\helper\HtmlHelper;
use common\entities\user\UserInfo;
use yii\widgets\Pjax;
use backend\modules\service\forms\mail\MailSearchForm;
use backend\modules\service\forms\mail\MailTemplateSearchForm;

/**
 * @var $this \backend\models\BackendView
 * @var $type string
 * @var $mailHistory MailSearchForm
 * @var $mailTemplate MailTemplateSearchForm
 */
$this->title = App::t("backend.service_mail.title", "Lịch sử email");

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
//    HtmlHelper::a("<i class='fa fa-cog'></i>&nbsp;" . App::t("backend.service_mail.button", "初期テンプレート"),
//        ['template-init'], [
//            'class' => 'btn btn-danger',
//            "data-confirm" => App::t("backend.service_mail.message", "全てのメールテンプレートを初期化しますか？"),
//            "data-method" => "post",
//        ]
//    ),
];
$activeStaff = !empty($_GET['role']) ? '' : 'active';
$activeRole = !empty($_GET['role']) ? 'active' : '';
?>
<?php Pjax::begin([
    "id" => "pjax-service-mail"
]); ?>
    <div class="portlet light">
        <div class="portlet-body">
            <div class="row inbox">
<!--                <div class="col-md-2">-->
<!--                    <ul class="inbox-nav margin-bottom-10">-->
<!--                        <li class="--><?php //echo ($type == "history" || $type == "history-view") ? "active" : "" ?><!--">-->
<!--                            --><?php //echo HtmlHelper::a(App::t("backend.service_main.label", "メール履歴"), App::$app->urlManager->createUrl([
//                                "service/mail",
//                                "type" => "history"
//                            ]), [
//                                "class" => "btn"
//                            ]) ?>
<!--                            <b></b>-->
<!--                        </li>-->
<!--                        <li class="--><?php //echo ($type == "template" || $type == "template-update") ? "active" : "" ?><!--">-->
<!--                            --><?php //echo HtmlHelper::a(App::t("backend.service_main.label", "メールテンプレート"), App::$app->urlManager->createUrl([
//                                "service/mail",
//                                "type" => "template"
//                            ]), [
//                                "class" => "btn"
//                            ]) ?>
<!--                            <b></b>-->
<!--                        </li>-->
<!--                    </ul>-->
<!--                </div>-->
                <div class="col-md-12" style="margin-bottom: 10px">
                    <a href="/service/mail/index" class="btn btn-default <?php echo $activeStaff?>">Mail gửi nhân viên</a>
                </div>
                <div class="col-md-12">
                    <?php switch ($type) {
                        case "template":
                            echo $this->render("_template", [
                                "model" => $mailTemplate,
                            ]);
                            break;
                        case "template-update":
                            echo $this->render("_form", [
                                "model" => $mailTemplate,
                            ]);
                            break;
                        case "history-view":
                            echo $this->render("_view", [
                                "model" => $mailHistory,
                            ]);
                            break;
                        default:
                            echo $this->render("_history", [
                                "model" => $mailHistory,
                            ]);
                            break;
                    } ?>
                </div>
            </div>
        </div>
    </div>
<?php Pjax::end(); ?>