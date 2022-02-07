<?php

/* @var $this \backend\models\BackendView */
/* @var $model MemberForm */
use backend\modules\member\forms\MemberForm;

$this->title = Yii::t('backend.member.title', "会員管理", [
]);
$this->subTitle = $model->full_name;

$this->breadcrumbs = [
    [
        'label' => $this->title,
        'url' => Yii::$app->urlManager->createUrl([
            "member/manage",
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
