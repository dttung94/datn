<?php
use backend\modules\service\forms\mail\MailSearchForm;
use yii\grid\GridView;
use yii\helpers\Json;

/**
 * @var $this \backend\models\BackendView
 * @var $model MailSearchForm
 */

$this->subTitle = App::t("backend.service_mail.title", "Lịch sử email");
?>
<?php
    echo $this->render('_search', ['model' => $model]);
?>
<?php echo GridView::widget([
    'id' => 'grid-view-mail-history',
    'dataProvider' => $model->search(),
    'filterModel' => null,
    'columns' => [
        [
            'attribute' => 'subject',
            'value' => function (MailSearchForm $data) {
                return $data->subject;
            },
            'contentOptions' => [
                'class' => 'view-message hidden-xs'
            ]
        ],
        [
            'attribute' => 'content',
            'value' => function (MailSearchForm $data) {
                $tos = Json::decode($data->to);
                return $tos[0]["email"];
            },
            'contentOptions' => [
                'class' => 'view-message text-right'
            ]
        ],
        [
            'attribute' => 'created_at',
            'format' => 'datetime',
            'value' => function (MailSearchForm $data) {
                return $data->created_at;
            },
            'contentOptions' => [
                'class' => 'view-message text-right'
            ]
        ],
    ],
    'options' => [
        'class' => '',
    ],
    'tableOptions' => [
        'class' => 'table table-striped table-advance table-hover',
    ],
    'rowOptions' => function (MailSearchForm $model, $key, $index, $grid) {
        return [
            "data-id" => $model->mail_id,
            "data-click-url" => App::$app->urlManager->createUrl([
                "service/mail",
                "type" => "history-view",
                "id" => $model->mail_id,
            ]),
        ];
    },
    'showHeader' => false,
    'showFooter' => false,
    'layout' => '{items}{pager}',
]); ?>
