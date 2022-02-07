<?php
/**
 * @var $this BackendView
 * @var $model WorkerRatingHistoryForm
 */

use backend\modules\worker\forms\WorkerRatingHistoryForm;
use backend\models\BackendView;
use yii\bootstrap\Modal;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = App::t("backend.system_manager.title", "Hệ thống");
$this->subTitle = App::t("backend.system_manager.title", "Quản lý đánh giá");
$this->breadcrumbs = [
    [
        "label" => $this->subTitle,
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
    $('.ratingModal').click(function(e) {
        e.preventDefault();
        $('#rating-modal').modal('show').find('.modal-body')
            .load($(this).attr('href'));
});
}
JS
);

Modal::begin([
    'id' => 'rating-modal',
    'size' => 'modal-lg',
    'header' => '<h3>Chi tiết các lượt đánh giá</h3>'
]);
Modal::end();
?>
<div class="portlet light bordered">
    <div class="portlet-body">
        <?php echo $this->render('_search', [
            'model' => $model
        ]); ?>
        <div class="row">
            <div class="col-xs-12">
                <?php Pjax::begin([
                    "id" => "pjax-grid-view-rating-history"
                ]); ?>
                <?php echo GridView::widget([
                    "id" => "grid-view-rating-history",
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
                            'attribute' => 'full_name',
                            'format' => 'raw',
                            'value' => function (WorkerRatingHistoryForm $data) {
                                return $data->full_name;
                            }
                        ],
                        [
                            'attribute' => 'last_rating',
                            'format' => 'raw',
                            'value' => function (WorkerRatingHistoryForm $data) {
//                                return $data->last_rating;
                                return Html::a($data->getLastRating(), ['/member/manage/booking-days', 'id' => $data->user_id, 'worker_id' => $data->worker_id], ['class' => 'ratingModal', 'data-pjax' => 0]);
                            }
                        ],
                        [
                            'attribute' => 'average_point',
                            'format' => 'raw',
                            'value' => function (WorkerRatingHistoryForm $data) {
                                return round($data->average_point/5, 2);
                            }
                        ],
                        [
                            'attribute' => 'total_point',
                            'format' => 'raw',
                            'value' => function (WorkerRatingHistoryForm $data) {
                                return $data->total_point;
                            }
                        ],
                        [
                            'attribute' => 'total_rating',
                            'format' => 'raw',
                            'value' => function (WorkerRatingHistoryForm $data) {
                                return $data->total_rating;
                            }
                        ],
                        [
                            'attribute' => 'total_booking',
                            'format' => 'raw',
                            'value' => function (WorkerRatingHistoryForm $data) {
                                return $data->getTotalBooking();
                            }
                        ],
                    ],
                    'options' => [
                        'class' => 'table table-striped table-advance table-hover',
                    ],
                    'showHeader' => true,
                    'showFooter' => false,
                    'layout' => '{items}{summary}{pager}',
                    'filterSelector' => "#rating-history-filter-form",
                ]); ?>
                <?php Pjax::end(); ?>
            </div>
        </div>
    </div>
</div>
