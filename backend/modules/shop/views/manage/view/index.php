<?php
use backend\assets\AdminAsset;
use backend\modules\shop\forms\ShopForm;
use yii\helpers\Html;
/**
 * @var $this \backend\models\BackendView
 * @var $model ShopForm
 */

$this->title = $model->shop_name;
$this->subTitle = Yii::t("backend.shop.title", "Số liệu thống kê");

$this->breadcrumbs = [
    [
        'label' => App::t("backend.shop.title", "Quản lý cửa hàng"),
        'url' => Yii::$app->urlManager->createUrl([
            "shop/manage",
        ])
    ],
    [
        "label" => $this->title
    ]
];
$this->actions = [
    Html::a("<i class='fa fa-pencil'></i> " . Yii::t('common.button', 'Cập nhật thông tin'), [
        'update',
        "id" => $model->shop_id,
    ], [
        'class' => 'btn btn-success',
        "data-pjax" => 0,
    ])
];
$asset = AdminAsset::register($this);
$this->registerJs(<<<JS
JS
    , \yii\web\View::POS_END);
?>
<?php echo $this->render("_system_statistic", [
    "model" => $model,
]) ?>
<?php echo $this->render("_booking_history", [
    "model" => $model,
]) ?>
