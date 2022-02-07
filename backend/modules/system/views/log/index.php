<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model \backend\modules\system\forms\log\SystemLogSearchForm
 */
use yii\helpers\Html;
use yii\widgets\ListView;
use yii\helpers\ArrayHelper;
use yii\bootstrap\ActiveForm;
use common\helper\HtmlHelper;

$this->title = App::t("backend.system_log.title", "システム");
$this->subTitle = App::t("backend.system_log.title", "ログ");

$this->breadcrumbs = [
    [
        "label" => $this->subTitle
    ]
];
$this->actions = [
    Html::a("<i class='fa fa-trash'></i> " . App::t("backend.system_log.button", "ログ消去"), ['clean'], [
        'class' => 'btn btn-danger',
        "data-method" => "POST",
        "data-confirm" => App::t("backend.system_log.message", "Do you want to clear log?"),
    ])
];
$this->registerJs(
    <<< JS
    jQuery(document).ready(function(){
    });
JS
    , \yii\web\View::POS_END, 'register-js-log');
$this->registerCss('
pre.log{
    height: 60em;
    font-family: monospace;
    font-size: 1em;
    padding: 2px 5px;
    color: #0F0;
    background-color: #111;
    border: 1px solid #030;
    border-radius: 4px;
    overflow: auto;
}
');
$queryParam = Yii::$app->request->get();
$queryParam = ArrayHelper::merge([
    "system/log/index"
], $queryParam);
?>
<div class="portlet light">
    <div class="portlet-body">
        <div class="row margin-bottom-10">
            <div class="col-md-12">
                ログレベル:
                <?php
                echo Html::a("All", Yii::$app->urlManager->createUrl(ArrayHelper::merge($queryParam, [
                    "level" => 0,
                ])), [
                    "class" => "btn btn-xs margin-right-10 margin-bottom-10 " . (0 == $model->level ? "btn-primary" : "btn-default"),
                ]);
                echo Html::a("E (error)", Yii::$app->urlManager->createUrl(ArrayHelper::merge($queryParam, [
                    "level" => 1,
                ])), [
                    "class" => "btn btn-xs margin-right-10 margin-bottom-10 " . (1 == $model->level ? "btn-danger" : "btn-default"),
                ]);
                echo Html::a("W (warning)", Yii::$app->urlManager->createUrl(ArrayHelper::merge($queryParam, [
                    "level" => 2,
                ])), [
                    "class" => "btn btn-xs margin-right-10 margin-bottom-10 " . (2 == $model->level ? "btn-warning" : "btn-default"),
                ]);
                echo Html::a("I (info)", Yii::$app->urlManager->createUrl(ArrayHelper::merge($queryParam, [
                    "level" => 4,
                ])), [
                    "class" => "btn btn-xs margin-right-10 margin-bottom-10 " . (4 == $model->level ? "btn-info" : "btn-default"),
                ]);
                ?>
            </div>
        </div>
        <div class="row margin-bottom-10">
            <div class="col-md-12">
                ログカテゴリー:
                <?php foreach ($model->getListCategories() as $category) {
                    echo Html::a($category, Yii::$app->urlManager->createUrl(ArrayHelper::merge($queryParam, [
                        "category" => $category,
                    ])), [
                        "class" => "margin-right-10 margin-bottom-10 btn btn-xs " . ($category == $model->category ? "btn-primary" : "btn-default"),
                    ]);
                } ?>
            </div>
        </div>
        <div class="row margin-bottom-10">
            <div class="col-md-12">
                <?php $form = ActiveForm::begin([
                    "id" => "system-log-filter-form",
                    'method' => 'GET',
                    "action" => Yii::$app->urlManager->createUrl($queryParam),
                    'layout' => 'horizontal',
                    'fieldConfig' => [
                        'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
                        'horizontalCssClasses' => [
                            'label' => 'col-sm-4',
                            'offset' => '',
                            'wrapper' => 'col-sm-8',
                            'error' => '',
                            'hint' => '',
                        ],
                    ],
                    "options" => [
                        "class" => "margin-bottom-10"
                    ]
                ]) ?>
                <div class="form-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?php echo HtmlHelper::activeTextInput($model, 'keyword', [
                                "name" => "keyword",
                                "class" => "form-control",
                                "placeholder" => $model->getAttributeLabel("keyword")
                            ]); ?>
                        </div>
                        <div class="col-md-1">
                            <?php echo HtmlHelper::submitButton(App::t("backend.system_log.button", "検索"), [
                                'class' => 'btn btn-default pull-right'
                            ]); ?>
                        </div>
                    </div>
                </div>
                <?php ActiveForm::end() ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?php
                echo ListView::widget([
                    'dataProvider' => $model->search(),
                    'showOnEmpty' => true,
                    'emptyText' => $this->render("_empty", []),
                    "itemView" => "_log",
                    "layout" => "{summary}\n<pre class='log'>{items}</pre>\n{pager}",
                ]);
                ?>
            </div>
        </div>
    </div>
</div>
