<?php

/* @var $this \backend\models\BackendView */
/* @var $model ShopForm */

use yii\helpers\Html;
use backend\modules\shop\forms\ShopForm;

$this->title = App::t("backend.shop.title", "Quản lý Salon");
$this->subTitle = Yii::t('common.label', 'Tạo mới');

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
