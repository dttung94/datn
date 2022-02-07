<?php
/**
 * @var $this BackendView
 * @var $model RatingRankForm
 */

use backend\models\BackendView;
use backend\modules\calendar\forms\rating\RatingRankForm;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;
use yii\bootstrap\Modal;
use yii\widgets\Pjax;

$this->title = App::t("backend.system_manager.title", "Đánh giá");
$this->subTitle = App::t("backend.system_manager.title", "Xếp hạng nhân viên");
$this->breadcrumbs = [
    [
        "label" => $this->subTitle
    ]
];
$this->registerJs(
    <<< JS
showRatingModal();

$(document).on('ready pjax:success', function() {
    showRatingModal()
});

$(document).on('ready pjax:end', function() {
    showRatingModal()
});

function showRatingModal(){
    $('.popupModal').click(function(e) {
        e.preventDefault();
        $('#modal').modal('show').find('.modal-body')
            .load($(this).attr('href'));
});
}
JS
);
Modal::begin([
    'id' => 'modal',
    'size' => 'modal-lg',
    'header' => '<h3>Đánh giá gần đây</h3>'
]);
Modal::end();
?>
<div class="portlet light bordered">
    <div class="portlet-body col-md-offset-1">
        <?php echo $this->render('_search', [
            'model' => $model
        ]); ?>
        <div class="row">
            <div class="col-xs-6">
                <?php Pjax::begin([
                    "id" => "pjax-grid-view-rating-rank",
                    "timeout" => 30000,
                ]); ?>
                <?php echo GridView::widget([
                    'id' => 'grid-view-rating-rank',
                    'dataProvider' => $model->search(),
                    'filterModel' => null,
                    'columns' => [
                        [
                            'class' => 'yii\grid\SerialColumn',
                            'options' => [
                                'style' => 'width:5px',
                            ]
                        ],
                        [
                            'attribute' => 'worker_name',
                            'format' => 'raw',
                            'value' => function (RatingRankForm $data) {
                                return Html::a(Yii::t('app', ' {modelClass}', [
                                    'modelClass' => $data->worker_name,
                                ]), ['../worker/manage/recent-rating', 'id' => $data->worker_id], ['class' => 'btn btn-link btn-sm popupModal', "data-pjax" => 0]);
                            },
                            'options' => [
                                "class" => "col-md-2"
                            ]
                        ],
                        [
                            'attribute' => 'point',
                            'format' => 'raw',
                            'value' => function (RatingRankForm $data) {
                                return round($data->point/$data->count, 2);
                            }
                        ],
                        [
                            'attribute' => 'count',
                            'format' => 'raw',
                            'value' => function (RatingRankForm $data) {
                                return $data->countS1;
                            }
                        ]
                    ],
                    'options' => [
                        'class' => 'table table-striped table-advance table-hover',
                    ],
                    'showHeader' => true,
                    'showFooter' => false,
                    'layout' => '{items}{summary}{pager}',
                    'filterSelector' => "#rating-rank-filter-form",
                ]) ?>
                <?php Pjax::end(); ?>
            </div>
        </div>
    </div>
</div>


