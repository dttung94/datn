<?php

/**
 * @var $model MemberFollowWorkerForm;
 */
use backend\modules\member\forms\MemberFollowWorkerForm;
use common\entities\worker\WorkerInfo;
use yii\grid\GridView;
use yii\helpers\Html;

$worker = WorkerInfo::findOne(Yii::$app->request->get('id'));
$title = $worker ? $worker->worker_name : '';
$this->title = $title;
$this->subTitle = Yii::t('backend.worker.title', "Số liệu thống kê");

$this->breadcrumbs = [
    [
        'label' => App::t("backend.worker.title", "Đánh giá xếp hạng"),
        'url' => Yii::$app->urlManager->createUrl([
            "/calendar/rating/ranking",
        ])
    ],
    [
        "label" => $this->title
    ]
];

?>

<div id="user-follow">
    <div class="portlet light">
        <div class="portlet-title">
            <div class="caption" style="display: block; float: inherit"><p>リマインダー一覧</p></div>
            <?php
            echo $this->render('_search', [
                'model' => $model
            ]);
            ?>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?php echo GridView::widget([
                    "id" => "grid-view-forum",
                    'dataProvider' => $model->searchUserRemind(Yii::$app->request->get('id')),
                    'filterModel' => null,
                    'columns' => [
                        [
                            'class' => 'yii\grid\SerialColumn',
                            'options' => [
                                'style' => 'width:5px'
                            ],
                            'contentOptions' => [
                                'class' => 'text-center',
                                'style' => 'vertical-align: middle'
                            ],
                            'headerOptions' => [
                                'class' => 'text-center',
                            ],
                        ],
                        [
                            'attribute' => '名前',
                            'format' => 'raw',
                            'value' => function (MemberFollowWorkerForm $data) {
                                $html = Html::a($data->full_name, ["/member/manage/view", "id" => $data->user_id], ["target" => '_blank']);
                                return $html;
                            },
                            'options' => [
                                "class" => "col-md-11"
                            ],
                            'contentOptions' => [
                                'class' => 'text-center',
                                'style' => 'vertical-align: middle'
                            ],
                            'headerOptions' => [
                                'class' => 'text-center',
                            ],
                        ],
                    ],
                    'options' => [
                        'class' => 'table table-striped table-advance table-hover',
                    ],
                    'showHeader' => true,
                    'showFooter' => false,
                    'layout' => '{items}{summary}{pager}',
                ]) ?>
            </div>
        </div>
    </div>
</div>
