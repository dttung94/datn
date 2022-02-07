<?php

use backend\modules\rating\forms\RatingForm;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = Yii::t('common.label', "メモ閲覧", []);
$this->breadcrumbs = [
    [
        "label" => $this->title
    ]
];
?>
<div class="portlet light">
    <div class="portlet-body">
        <?php echo $this->render('_search', [
            'model' => $model
        ]); ?>
        <div class="row">
            <div class="col-md-12">
                <?php Pjax::begin([
                    "id" => "pjax-grid-view-rating",
                    'timeout' => 5000,
                ]); ?>
                <?php echo GridView::widget([
                    "id" => "grid-view-rating",
                    'dataProvider' => $model->search(),
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
                            'attribute' => 'Ghi chú',
                            'format' => 'raw',
                            'value' => function (RatingForm $data) {
                                return $data->memo;
                            },
                            'options' => [
                                "class" => "col-md-5"
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
                            'attribute' => 'ユーザー名',
                            'format' => 'raw',
                            'value' => function (RatingForm $data) {
                                $html = Html::a($data->user->full_name, ["/member/manage/view", "id" => $data->user_id]);
                                return $html;
                            },
                            'options' => [
                                "class" => "col-md-2"
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
                            'attribute' => '子の名前',
                            'format' => 'raw',
                            'value' => function (RatingForm $data) {
                                $html = Html::a($data->worker->worker_name, ["/worker/manage/view", "id" => $data->worker_id]);
                                return $html;
                            },
                            'options' => [
                                "class" => "col-md-2"
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
                            'attribute' => '登録日',
                            'format' => 'raw',
                            'value' => function (RatingForm $data) {
                                return date('Y-m-d', strtotime($data->created_at));
                            },
                            'options' => [
                                "class" => "col-md-2"
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
                    'filterSelector' => '#grid-view-rating-filter',
                ]) ?>
                <?php Pjax::end(); ?>
            </div>
        </div>
    </div>
</div>
