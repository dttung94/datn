<?php

/* @var $this \backend\models\BackendView */
/* @var $model ShopForm */

use yii\helpers\Html;
use backend\modules\shop\forms\ShopForm;

$this->title = Yii::t('common.label', "Quản lý salon", [
]);
$this->subTitle = Yii::t('common.label', 'Cập nhật salon {shop}', [
    "shop" => $model->shop_name,
]);

$this->breadcrumbs = [
    [
        'label' => $this->title,
        'url' => Yii::$app->urlManager->createUrl([
            "shop/manage",
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
