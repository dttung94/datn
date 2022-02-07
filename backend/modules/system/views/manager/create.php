<?php

/* @var $this \backend\models\BackendView */
/* @var $model ManagerForm */

use yii\helpers\Html;
use backend\modules\system\forms\manager\ManagerForm;

$this->title = App::t("backend.system_manager.title", "Quản lý nhân sự");
$this->subTitle = Yii::t('common.label', 'Thêm mới');

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
