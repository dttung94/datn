<?php

/* @var $this \backend\models\BackendView */
/* @var $model WorkerForm */

use backend\modules\worker\forms\WorkerForm;

$this->title = Yii::t('backend.worker.title', "Quản lý ", [
]);
$this->subTitle = Yii::t('common.label', 'Cập nhật thông tin: {worker}', [
    "worker" => $model->worker_name,
]);

$this->breadcrumbs = [
    [
        'label' => $this->title,
        'url' => Yii::$app->urlManager->createUrl([
            "worker/manage",
        ])
    ], [
        'label' => $this->subTitle
    ]
];
?>
<div class="portlet light">
    <div class="portlet-body">
        <?= $this->render('_form', [
            'model' => $model
        ]) ?>
    </div>
</div>
