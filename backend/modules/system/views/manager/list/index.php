<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model ManagerForm
 */
use backend\modules\system\forms\manager\ManagerForm;
use common\helper\HtmlHelper;
use yii\widgets\Pjax;
use common\entities\user\UserPermission;
use yii\grid\GridView;
use yii\helpers\StringHelper;
use common\helper\ArrayHelper;

$this->title = App::t("backend.system_manager.title", "Quản lý nhân sự");
$this->subTitle = App::t("backend.system_manager.title", "");
$this->breadcrumbs = [
    [
        "label" => $this->title
    ]
];
$this->actions = [
//    HtmlHelper::a("<i class='fa fa-cogs'></i> " . Yii::t('common.button', 'パーミッション'), [
//        'permission',
//    ], [
//        'class' => 'btn btn-primary',
//        "data-pjax" => 0,
//    ]),
    HtmlHelper::a("<i class='fa fa-plus'></i> " . Yii::t('common.button', 'Thêm mới'), [
        'create',
    ], [
        'class' => 'btn btn-success',
        "data-pjax" => 0,
    ])
];
if (isset($_GET['type'])) {
    $type = $_GET['type'];
    $operator = 'active';
    $manager = '';
} else {
    $type = "";
    $operator = '';
    $manager = 'active';
}
?>
<div class="portlet light bordered">
    <div class="portlet-body">
        <?php echo $this->render('_search', [
            'model' => $model
        ]); ?>
        <div class="row">
            <div class="col-xs-12">
                <?php Pjax::begin([
                    "id" => "pjax-grid-view-manager"
                ]); ?>
                <?php echo GridView::widget([
                    'id' => 'grid-view-manager',
                    'dataProvider' => $model->search($type),
                    'filterModel' => null,
                    'columns' => [
                        [
                            'class' => 'yii\grid\SerialColumn',
                            'options' => [
                                'style' => 'width:5px'
                            ]
                        ],
//                        [
//                            'attribute' => 'username',
//                            'format' => 'raw',
//                            'value' => function (ManagerForm $data) {
//                                return $data->username;
//                            },
//                            'options' => [
//                                'class' => 'col-md-2'
//                            ]
//                        ],
                        [
                            'attribute' => 'full_name',
                            'format' => 'raw',
                            'value' => function (ManagerForm $data) {
                                return $data->full_name;
                            },
                            'options' => [
                                'class' => 'col-md-2'
                            ]
                        ],
                        [
                            'attribute' => 'phone_number',
                            'format' => 'raw',
                            'value' => function (ManagerForm $data) {
                                return $data->phone_number;
                            },
                            'options' => [
                                'class' => 'col-md-2'
                            ]
                        ],
                        [
                            'attribute' => 'email',
                            "format" => 'raw',
                            'value' => function (ManagerForm $data) {
                                return HtmlHelper::a(StringHelper::truncate($data->email, 35), "mailto:$data->email");
                            },
                        ],
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'value' => function (ManagerForm $data) {
                                return HtmlHelper::checkbox("ManagerForm[$data->user_id]",
                                    $data->status == ManagerForm::STATUS_ACTIVE, [
                                        "class" => "make-switch switch-status",
                                        "data-size" => "mini",
                                        "data-url" => Yii::$app->urlManager->createUrl([
                                            "system/manager/switch-status",
                                            "id" => $data->user_id,
                                        ]),
                                        "data-pjax-id" => "pjax-grid-view-manager",
                                    ]);
                            },
                            'options' => [
                                'class' => 'col-md-1'
                            ]
                        ],
                        [
                            'attribute' => 'created_at',
                            'format' => 'datetime',
                            'value' => function (ManagerForm $data) {
                                return $data->created_at;
                            },
                            'options' => [
                                'class' => 'col-md-2'
                            ]
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{config}&nbsp;&nbsp;{update}&nbsp;&nbsp;{delete}&nbsp;&nbsp;{logout}',
                            'buttons' => [
                                "config" => function ($widget, ManagerForm $data) {
                                    return HtmlHelper::a("<i class='fa fa-cog'></i>", [
                                        "/system/manager/permission",
                                        "ManagerForm[user_id]" => $data->user_id
                                    ], [
                                        "data-pjax" => 0,
                                        "title" => '設定'
                                    ]);
                                },
                                "delete" => function ($widget, ManagerForm $model) {
                                    return HtmlHelper::a("<span class='glyphicon glyphicon-trash font-red permission'></span>", [
                                        "/member/manage/delete",
                                        "id" => $model->user_id
                                    ], [
                                        "class" => "",
                                        "data-pjax" => 0,
                                        "data-method" => "POST",
                                        "data-confirm" => App::t("backend.coupon.message", "Bạn có muốn xóa thành viên này ?"),
                                        "title" => '削除'
                                    ]);
                                },
                            ],
                            'options' => [
                                'style' => 'width:80px;'
                            ]
                        ],
                    ],
                    'options' => [
                        'class' => 'table table-striped table-advance table-hover',
                    ],
                    'showHeader' => true,
                    'showFooter' => false,
                    'layout' => '{items}{summary}{pager}',
                    'filterSelector' => "#user-filter-form",
                ]); ?>
                <?php Pjax::end(); ?>
            </div>
        </div>
    </div>
</div>