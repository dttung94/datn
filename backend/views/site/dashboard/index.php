<?php
use common\entities\system\SystemConfig;
use backend\assets\AppAsset;
use yii\bootstrap\Html;

/**
 * @var $this \backend\models\BackendView
 */

$this->title = SystemConfig::getValue(SystemConfig::CATEGORY_SYSTEM, SystemConfig::SYSTEM_SITE_NAME);
$this->subTitle = Yii::t('backend.dashboard.title', "Thống kê");

$this->breadcrumbs = [
    [
        "label" => $this->title
    ]
];
$bundle = App::$app->assetManager->getBundle(AppAsset::className());
$this->actions[] = Html::tag("label", date('Y-m-d H:i'), [
    "style" => "padding-top: 10px",
]);
$this->registerJsFile($bundle->baseUrl . "/js/controllers/dashboard.js", [
    "depends" => [
        AppAsset::className(),
    ]
]);
$this->registerJsFile($bundle->baseUrl . "/js/controllers/dashboard-filter.js", [
    "depends" => [
        AppAsset::className(),
    ]
]);
$this->registerJs(<<<JS
JS
    , \yii\web\View::POS_END);
?>
<div ng-controller="DashboardController">
    <?php echo $this->render("_system_statistic", [])?>
    <?php echo $this->render("_booking_history", []) ?>
</div>
