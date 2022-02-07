<?php
use backend\assets\AdminAsset;
use backend\modules\member\forms\MemberFollowWorkerForm;
use backend\modules\worker\forms\WorkerForm;
use yii\helpers\Html;
/**
 * @var $this \backend\models\BackendView
 * @var $model WorkerForm
 * @var $listUserFollow MemberFollowWorkerForm
 */

$this->title = $model->worker_name;
$this->subTitle = Yii::t('backend.worker.title', "Thống kê");

$this->breadcrumbs = [
    [
        'label' => App::t("backend.worker.title", "Quản lý nhân viên"),
        'url' => Yii::$app->urlManager->createUrl([
            "worker/manage",
        ])
    ],
    [
        "label" => $this->title
    ]
];
$this->actions = [
    Html::a("<i class='fa fa-pencil'></i> " . Yii::t('common.button', 'Cập nhật thông tin'), [
        'update',
        "id" => $model->worker_id,
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
    "dataChart" => $dataChart,
]) ?>
