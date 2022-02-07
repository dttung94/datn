<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model ShopForm
 */
use backend\modules\shop\forms\ShopForm;
use common\entities\shop\ShopConfig;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\helpers\Html;
use common\helper\DatetimeHelper;
use common\entities\user\UserInfo;

$this->title = Yii::t('backend.shop.title', "Quản lý salon", [
]);
$this->subTitle = Yii::t('common.label', "");

$this->breadcrumbs = [
    [
        "label" => $this->title
    ]
];
$this->actions = [
];
if (\App::$app->user->identity->role == UserInfo::ROLE_ADMIN) {
    $this->actions[] = Html::a("<i class='fa fa-plus'></i> " . Yii::t('common.button', 'Tạo mới'), [
        'create',
    ], [
        'class' => 'btn btn-success',
        "data-pjax" => 0,
    ]);
}
$this->registerJs(
    <<< JS
    jQuery(document).ready(function(){
    });
JS
    , \yii\web\View::POS_END, 'register-js-shop-management');
?>
<div class="portlet light">
    <div class="portlet-body">
        <?php echo $this->render('_search', [
            'model' => $model
        ]); ?>
        <div class="row">
            <div class="col-md-12">
                <?php Pjax::begin([
                    "id" => "pjax-grid-view-shop"
                ]); ?>
                <?php echo GridView::widget([
                    "id" => "grid-view-shop",
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
                            'attribute' => 'shop_name',
                            'format' => 'raw',
                            'value' => function (ShopForm $data) {
                                $html = Html::a($data->shop_name, [
                                    "view",
                                    "id" => $data->shop_id,
                                ]);
                                if (ShopConfig::isExist(ShopConfig::KEY_SHOP_BOOKING_TOMORROW_AT, $data->shop_id)) {
                                    $html .= "<br/>";
                                    $html .= "<b>Cho phép đặt chỗ ngày hôm sau từ:</b> " . ShopConfig::getValue(ShopConfig::KEY_SHOP_BOOKING_TOMORROW_AT, $data->shop_id);
                                }
                                if (!empty($data->shop_domain)) {
                                    $html .= "<br/>";
                                    $html .= "<b>ドメイン:</b> " . Html::a($data->shop_domain, "http://$data->shop_domain", [
                                            "target" => "_blank",
                                            "data-pjax" => 0,
                                        ]);
                                }
                                if (!empty($data->phone_number)) {
                                    $html .= "<br/>";
                                    $html .= "<b>Số điện thoại:</b> $data->phone_number";
                                }
                                if (!empty($data->shop_email)) {
                                    $html .= "<br/>";
                                    $html .= "<b>Email:</b> $data->shop_email";
                                }
                                return $html;
                            },
                            'options' => [
                            ]
                        ],
//                        [
//                            'attribute' => 'totalBooking',
//                            'format' => 'raw',
//                            'value' => function (ShopForm $data) {
//                                if ($data->totalBooking) {
//                                    return Html::a(
//                                        App::t("backend.shop.label", "{totalBooking} 本", [
//                                            "totalBooking" => $data->totalBooking,
//                                        ]),
//                                        App::$app->urlManager->createUrl([
//                                            "calendar/booking/history",
//                                            "BookingHistorySearchForm[filter_shop_id]" => $data->shop_id,
//                                        ])
//                                    );
//                                } else {
//                                    return App::t("backend.shop.label", "なし");
//                                }
//                            },
//                            'options' => [
//                                "class" => "col-md-1"
//                            ]
//                        ],
                        [
                            'attribute' => 'calendar',
                            'format' => 'raw',
                            'value' => function (ShopForm $data) {
                                $html = "";
                                $html .= "<i class='fa fa-clock-o'></i> ";
                                $html .= date('H:i', strtotime($data->openDoorAt)).' → '.date('H:i', strtotime($data->closeDoorAt));
                                $html .= "<br/>";
                                $html .= "<i class='fa fa-calendar'></i> ";
                                foreach ($data->workingDayOnWeek as $i => $val) {
                                    $html .= "[" . DatetimeHelper::getDayOfWeek($i) . "] ";
                                }
                                return $html;
                            },
                            'options' => [
                                "class" => "col-md-3"
                            ]
                        ],
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'value' => function (ShopForm $data) {
                                return Html::checkbox("WorkerForm[status][]", $data->status == ShopForm::STATUS_ACTIVE, [
                                    "class" => "make-switch switch-status",
                                    "data-size" => "mini",
                                    "data-id" => $data->shop_id,
                                    "data-url" => Yii::$app->urlManager->createUrl([
                                        "shop/manage/switch-status",
                                        "id" => $data->shop_id,
                                    ]),
                                    "data-pjax-id" => "pjax-grid-view-shop",
                                ]);
                            },
                            'options' => [
                                'style' => 'width:100px;'
                            ]
                        ],
                        [
                            'attribute' => 'created_at',
                            'format' => 'datetime',
                            'value' => function (ShopForm $data) {
                                return $data->created_at;
                            },
                            'options' => [
                                'style' => 'width:120px;'
                            ]
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{update}&nbsp;{calendar}{remove}',
                            'buttons' => [
                                "calendar" => function ($widget, ShopForm $data, $url) {
                                    if ($data->status == ShopForm::STATUS_ACTIVE && count($data->workers) > 0) {
                                        return Html::a("<i class='fa fa-calendar'></i>", [
                                            "/calendar/schedule/config",
                                            "shop_id" => $data->shop_id,
                                        ], [
                                            "data-pjax" => 0,
                                        ]);
                                    }
                                    return "";
                                },
                                "remove" => function ($widget, ShopForm $data, $url) {
                                    if ($data->status == ShopForm::STATUS_INACTIVE) {
                                        return Html::a("<i class='glyphicon glyphicon-trash font-red'></i>", [
                                            "/shop/manage/delete",
                                            "id" => $data->shop_id,
                                        ], [
                                            "data-pjax" => 0,
                                            "data-method" => "POST",
                                            "data-confirm" => App::t("backend.shop.message", "この店舗を削除しますか？"),
                                        ]);
                                    }
                                    return "";
                                }
                            ],
                            'options' => [
                                'style' => 'width:50px;'
                            ]
                        ],
                    ],
                    'options' => [
                        'class' => 'table table-striped table-advance table-hover',
                    ],
                    'showHeader' => true,
                    'showFooter' => false,
                    'layout' => '{items}{summary}{pager}',
                    'filterSelector' => '#grid-view-shop-filter',
                ]); ?>
                <?php Pjax::end(); ?>
            </div>
        </div>
    </div>
</div>