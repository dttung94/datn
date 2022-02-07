<?php
/**
 * @var $this \backend\models\BackendView
 */
use yii\widgets\Pjax;
use common\helper\ArrayHelper;
use yii\widgets\Breadcrumbs;
?>
<!-- BEGIN PAGE HEADER-->
<!-- BEGIN PAGE BAR -->
<?php Pjax::begin([
    "id" => "pjax-page-header",
    "options" => [
        "class" => "page-bar",
    ]
]); ?>
<?php
$breadcrumbs = [];
foreach ($this->breadcrumbs as $index => $breadcrumb) {
    $breadcrumbs[] = \yii\helpers\ArrayHelper::merge($breadcrumb, [
        "data-pjax" => 0
    ]);
}
echo \yii\widgets\Breadcrumbs::widget([
    "links" => $breadcrumbs,
    "options" => [
        "class" => "page-breadcrumb",
    ],
    "homeLink" => [
        "label" => App::t("backend.menu.primary", "Trang chủ"),
        "url" => Yii::$app->homeUrl,
        "data-pjax" => 0,
        "template" => "<li><i class=\"icon-home\"></i> {link}<i class='fa fa-angle-right'></i></li>\n"
    ],
    "itemTemplate" => "<li>{link}<i class='fa fa-angle-right'></i></li>\n",
    "activeItemTemplate" => "<li><span>{link}</span></li>\n",
]); ?>
<div class="page-toolbar">
    <?php foreach ($this->actions as $action) {
        echo "&nbsp;$action";
    } ?>
</div>
<?php Pjax::end() ?>
<!-- END PAGE BAR -->
<!-- BEGIN PAGE TITLE-->
<?php if (!empty($this->title) || !empty($this->subTitle)) { ?>
    <h3 class="page-title <?php echo ArrayHelper::getValue($this->themeOptions, "pageTitleClass", ""); ?>">
        <?php echo $this->title; ?>
        <small><?php echo $this->subTitle; ?></small>
    </h3>
<?php } ?>
<!-- END PAGE TITLE-->
<!-- END PAGE HEADER-->