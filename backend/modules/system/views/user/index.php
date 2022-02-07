<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model UserForm
 */
use backend\modules\system\forms\user\UserForm;
use common\helper\HtmlHelper;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\helpers\StringHelper;

$this->title = App::t("backend.system_user.title", "System");
$this->subTitle = App::t("backend.system_user.title", "System User");
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
            <div class="col-md-12">
                <?php
                Pjax::begin([
                    "id" => "pjax-grid-view-user"
                ]);
                echo GridView::widget([
                    'id' => 'grid-view-user',
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
                            'attribute' => 'username',
                            'format' => 'raw',
                            'value' => function (UserForm $data) {
                                return $data->username;
                            },
                            'options' => [
                                'class' => 'col-md-2'
                            ]
                        ],
                        [
                            'attribute' => 'phone_number',
                            'format' => 'raw',
                            'value' => function (UserForm $data) {
                                return $data->phone_number;
                            },
                            'options' => [
                                'class' => 'col-md-2'
                            ]
                        ],
                        [
                            'attribute' => 'email',
                            "format" => 'raw',
                            'value' => function (UserForm $data) {
                                return HtmlHelper::a(StringHelper::truncate($data->email, 25), "mailto:$data->email");
                            },
                        ],
                        [
                            'attribute' => 'role',
                            "format" => 'raw',
                            'value' => function (UserForm $data) {
                                switch ($data->role) {
                                    case UserForm::ROLE_ADMIN:
                                        return "<label class='label label-danger'>$data->role</label>";
                                        break;
                                    case UserForm::ROLE_MANAGER:
                                        return "<label class='label label-warning'>$data->role</label>";
                                        break;
                                    case UserForm::ROLE_USER:
                                        return "<label class='label label-success'>$data->role</label>";
                                        break;
                                }
                                return "";
                            },
                            'options' => [
                                'class' => 'col-md-1'
                            ]
                        ],
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'value' => function (UserForm $data) {
                                return HtmlHelper::checkbox("UserForm[status][]", $data->status == UserForm::STATUS_ACTIVE, [
                                    "class" => "make-switch",
                                    "data-size" => "mini",
                                    "disabled" => "disabled",
                                ]);
                            },
                            'options' => [
                                'class' => 'col-md-1'
                            ]
                        ],
                        [
                            'attribute' => 'created_at',
                            'format' => 'datetime',
                            'value' => function (UserForm $data) {
                                return $data->created_at;
                            },
                            'options' => [
                                'class' => 'col-md-2'
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
                ]);
                Pjax::end();
                ?>
            </div>
        </div>
    </div>
</div>