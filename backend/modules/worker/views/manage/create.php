<?php

/* @var $this \backend\models\BackendView */
/* @var $model WorkerForm */

use backend\modules\worker\forms\WorkerForm;

$this->title = App::t("backend.worker.title", "Quản lý nhân viên");
$this->subTitle = Yii::t('common.label', 'Thêm mới');

$this->breadcrumbs = [
    [
        'label' => $this->title,
        'url' => Yii::$app->urlManager->createUrl([
            "worker/manage/index",
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
