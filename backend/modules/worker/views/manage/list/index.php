<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model WorkerForm
 */

use backend\modules\worker\forms\WorkerForm;
use common\entities\shop\ShopInfo;
use common\entities\user\UserInfo;
use common\entities\worker\WorkerConfig;
use common\helper\ArrayHelper;
use yii\bootstrap\Modal;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = Yii::t('backend.worker.title', "Quản lý nhân viên", [
]);
$this->subTitle = Yii::t('common.label', "");

$this->breadcrumbs = [
    [
        "label" => $this->title
    ]
];
$this->actions = [
];
$this->actions[] = Html::a("<i class='fa fa-plus'></i> " . Yii::t('common.button', 'Thêm mới'), [
    'create',
], [
    'class' => 'btn btn-success',
    "data-pjax" => 0,
]);
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

<div class="portlet light">
    <div class="portlet-body">
        <?php echo $this->render('_search', [
            'model' => $model
        ]); ?>
        <div class="row">
            <div class="col-md-12">
                <?php Pjax::begin([
                    "id" => "pjax-grid-view-worker",
                    'timeout' => 5000,
                ]); ?>
                <?php echo GridView::widget([
                    "id" => "grid-view-worker",
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
                            'header' => "",
                            'format' => 'raw',
                            'value' => function (WorkerForm $data) {
                                return Html::img(App::$app->urlManager->createUrl([
                                    "service/file/preview",
                                    "id" => $data->worker_id,
                                ]), [
                                    "class" => "img img-response img-circle",
                                    "style" => [
                                        "width" => "40px;",
                                        "height" => "40px;",
                                    ],
                                ]);
                            },
                            'options' => [
                                'style' => 'width:40px'
                            ]
                        ],
                        [
                            'attribute' => 'worker_name',
                            'format' => 'raw',
                            'value' => function (WorkerForm $data) {
                                $html = Html::a($data->worker_name, ["view", "id" => $data->worker_id]);
                                //todo show booking url
//                                $html .= "<br/>";
//                                $html .= "<b>Booking Url:</b> " . Html::a($data->workerBookingUrl, $data->workerBookingUrl);
                                return $html;
                            },
                            'options' => [
                                "class" => "col-md-2"
                            ]
                        ],
                        [
                            'attribute' => 'history_rate',
                            'format' => 'raw',
                            'value' => function (WorkerForm $data) {
                                return Html::a(Yii::t('app', ' {modelClass}', [
                                    'modelClass' => 'Chi tiết',
                                ]), ['manage/recent-rating', 'id' => $data->worker_id], ['class' => 'label label-info popupModal', "data-pjax" => 0]);
                            },
                            'options' => [
                                "class" => "col-md-2"
                            ]
                        ],
                        [
                            'attribute' => 'totalAllBooking',
                            'format' => 'raw',
                            'value' => function (WorkerForm $data) {
                                if ($data->totalAllBooking) {
                                    return Html::a(
                                        App::t("backend.worker.label", "{totalBooking} lượt", [
                                            "totalBooking" => $data->totalAllBooking,
                                        ]),
                                        App::$app->urlManager->createUrl([
                                            "calendar/booking/history",
                                            "BookingHistorySearchForm[filter_worker_id]" => $data->worker_id,
                                        ])
                                    );
                                } else {
                                    return App::t("backend.worker.label", "Chưa có lượt nào");
                                }
                            },
                            'options' => [
                                "class" => "col-md-2"
                            ]
                        ],
                        [
                            "attribute" => "shops",
                            "format" => 'raw',
                            "value" => function (WorkerForm $data) use ($shops_name) {
                                $html = "";
                                foreach ($data->mappingShops as $mappingShop) {
                                    $html .= Html::tag("div",
                                        $shops_name[$mappingShop->shop_id],  [
                                                'class' => '',
                                        ]);
                                    $html .= "<br/>";
                                }
                                return $html;
                            }
                        ],
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'value' => function (WorkerForm $data) {
                                return Html::checkbox("WorkerForm[status][]", $data->status == WorkerForm::STATUS_ACTIVE, [
                                    "class" => "make-switch switch-status",
                                    "data-size" => "mini",
                                    "data-id" => $data->worker_id,
                                    "data-url" => Yii::$app->urlManager->createUrl([
                                        "worker/manage/switch-status",
                                        "id" => $data->worker_id,
                                    ]),
                                    "data-pjax-id" => "pjax-grid-view-worker",
                                ]);
                            },
                            'options' => [
                                'style' => 'width:100px;'
                            ]
                        ],
//                        [
//                            'attribute' => 'created_at',
//                            'format' => 'date',
//                            'value' => function (WorkerForm $data) {
//                                return $data->created_at;
//                            },
//                            'options' => [
//                                'style' => 'width:100px;'
//                            ]
//                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{update}',
                            'options' => [
                                'style' => 'width:30px;'
                            ]
                        ],
                    ],
                    'options' => [
                        'class' => 'table table-striped table-advance table-hover',
                    ],
                    'showHeader' => true,
                    'showFooter' => false,
                    'layout' => '{items}{summary}{pager}',
                    'filterSelector' => '#grid-view-worker-filter',
                ]); ?>
                <?php Pjax::end(); ?>
            </div>
        </div>
    </div>
</div>
