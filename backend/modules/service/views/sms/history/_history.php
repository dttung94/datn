<?php
use yii\grid\GridView;
use yii\helpers\Json;
use common\helper\HtmlHelper;
use backend\modules\service\forms\sms\SmsHistoryForm;
use common\helper\StringHelper;

/**
 * @var $this \backend\models\BackendView
 * @var $model SmsHistoryForm
 */
?>
<?php echo GridView::widget([
    'id' => 'grid-view-sms-history',
    'dataProvider' => $model->search(),
    'filterModel' => null,
    'columns' => [
        [
            'attribute' => 'content',
            'format' => 'raw',
            'value' => function (SmsHistoryForm $data) {
                $html = $data->smsContent;
                if (!empty($data->tagJson)) {
                    $html .= "<br/><i class='fa fa-tags'></i> " . implode(" ", $data->tagJson);
                }
                return $html;
            },
            'contentOptions' => [
                'class' => 'hidden-xs'
            ]
        ],
        [
            'attribute' => 'to',
            'format' => 'raw',
            'value' => function (SmsHistoryForm $data) {
                switch ($data->status) {
                    case SmsHistoryForm::STATUS_SENT:
                        return HtmlHelper::label($data->to, null, [
                            "class" => "label label-send-to label-success",
                            "tooltips" => "tooltips",
                            "tooltip-template" => $data->result,
                        ]);
                    case SmsHistoryForm::STATUS_FAILED:
                        return HtmlHelper::label($data->to, null, [
                            "class" => "label label-send-to label-danger",
                            "tooltips" => "tooltips",
                            "tooltip-template" => $data->result,
                        ]);
                    default:
                        return HtmlHelper::label($data->to, null, [
                            "class" => "label label-send-to label-default",
                            "tooltips" => "tooltips",
                            "tooltip-template" => $data->result,
                        ]);
                }
            },
            'contentOptions' => [
                'class' => 'col-md-1'
            ]
        ],
        [
            'attribute' => 'created_at',
            'format' => 'datetime',
            'value' => function (SmsHistoryForm $data) {
                return $data->created_at;
            },
            'contentOptions' => [
                'class' => 'col-md-2'
            ]
        ],
    ],
    'options' => [
        'class' => '',
    ],
    'tableOptions' => [
        'class' => 'table table-striped table-advance table-hover',
    ],
    'rowOptions' => function (SmsHistoryForm $model, $key, $index, $grid) {
    },
    'showHeader' => false,
    'showFooter' => false,
    'layout' => '{items}{pager}',
]); ?>
