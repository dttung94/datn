<?php

/* @var $this \backend\models\BackendView */
/* @var $model ManagerForm */

use yii\helpers\Html;
use backend\modules\system\forms\manager\ManagerForm;

$this->title = Yii::t('common.label', "Quản lý nhân sự", [
]);
$this->subTitle = $model->full_name;

$this->breadcrumbs = [
    [
        'label' => $this->title,
        'url' => Yii::$app->urlManager->createUrl([
            "system/manager",
        ])
    ], [
        'label' => $this->subTitle
    ]
];
?>
<div class="portlet light">
    <div class="portlet-body">
        <?= $this->render('_form', [
            'model' => $model,
        ]) ?>
    </div>
</div>
