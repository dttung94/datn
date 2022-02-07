<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model BookingHistorySearchForm
 */
use backend\modules\calendar\forms\booking\BookingHistorySearchForm;
use common\helper\HtmlHelper;
use yii\grid\GridView;
use yii\helpers\StringHelper;
use yii\widgets\Pjax;

$this->title = App::t("backend.system_manager.title", "Lịch sử đặt lịch");
$this->subTitle = App::t("backend.system_manager.title", "Quản lý thành viên");
$this->breadcrumbs = [
    [
        "label" => $this->subTitle
    ]
];
?>
<div class="portlet light bordered">
    <div class="portlet-body">
        <?php echo $this->render('_search', [
            'model' => $model
        ]); ?>
        <div class="row">
            <div class="col-xs-12">
                <?php Pjax::begin([
                    "id" => "pjax-grid-view-booking-history"
                ]); ?>
                <?php echo GridView::widget([
                    'id' => 'grid-view-booking-history',
                    'dataProvider' => $model->search(),
                    'filterModel' => null,
                    'columns' => [
                        [
                            'class' => 'yii\grid\SerialColumn',
                            'options' => [
                                'style' => 'width:5px'
                            ]
                        ],
                        [
                            'attribute' => 'course_id',
                            'format' => 'raw',
                            'value' => function (BookingHistorySearchForm $data) {
                                return $data::getListCourse()[$data->course_id];
                            },
                            'options' => [
                                'class' => 'col-md-1'
                            ]
                        ],
                        [
                            'attribute' => 'customer_id',
                            'format' => 'raw',
                            'value' => function (BookingHistorySearchForm $data) {
                                return $data->memberInfo->full_name;
                            },
                            'options' => [
                            ]
                        ],
                        [
                            'attribute' => 'shop_name',
                            'format' => 'raw',
                            'value' => function (BookingHistorySearchForm $data) {
                                return $data->slotInfo->shopInfo->shop_name;
                            },
                            'options' => [
                                'class' => 'col-md-2'
                            ]
                        ],
                        [
                            'attribute' => 'worker_name',
                            'format' => 'raw',
                            'value' => function (BookingHistorySearchForm $data) {
                                return $data->slotInfo->workerInfo->worker_name;
                            },
                            'options' => [
                                'class' => 'col-md-2'
                            ]
                        ],
                        [
                            'attribute' => 'date',
                            "format" => 'date',
                            'value' => function (BookingHistorySearchForm $data) {
                                return $data->slotInfo->date;
                            },
                            'options' => [
                                'class' => 'col-md-1'
                            ]
                        ],
                        [
                            'attribute' => 'cost',
                            'format' => 'currency',
                            'value' => function (BookingHistorySearchForm $data) {
                                return $data->cost;
                            },
                            'options' => [
                                'class' => 'col-md-2'
                            ]
                        ],
//                        [
//                            'attribute' => 'created_at',
//                            'format' => 'datetime',
//                            'value' => function (BookingHistorySearchForm $data) {
//                                return $data->created_at;
//                            },
//                            'options' => [
//                                'class' => 'col-md-2'
//                            ]
//                        ],
                    ],
                    'options' => [
                        'class' => 'table table-striped table-advance table-hover',
                    ],
                    'showHeader' => true,
                    'showFooter' => false,
                    'layout' => '{items}{summary}{pager}',
                    'filterSelector' => "#booking-history-filter-form",
                ]); ?>
                <?php Pjax::end(); ?>
            </div>
        </div>
    </div>
</div>