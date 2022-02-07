<?php
/**
 * @var $this \backend\models\BackendView
 *
 * @var $model UserLogForm
 */
use backend\modules\system\forms\user\UserLogForm;
use yii\widgets\ListView;

$this->title = App::t("backend.user_log.title", "システム");
$this->subTitle = App::t("backend.user_log.title", "ログ");

$this->breadcrumbs = [
    [
        "label" => $this->subTitle
    ]
];
$this->actions = [
];
$this->registerJs(
    <<< JS
    jQuery(document).ready(function(){
    });
JS
    , \yii\web\View::POS_END, 'register-js-log');
$this->registerCss(<<<CSS
pre.log{
    height: 50em;
    font-family: monospace;
    font-size: 1em;
    padding: 10px 5px;
    color: #0F0;
    background-color: #111;
    border: 1px solid #030;
    border-radius: 4px;
    overflow: auto;
}
pre.log label.user-log-action {
    display: inline-block;
    width: 150px;
    line-height: 17px;
}
CSS
);
?>
<div class="portlet light">
    <div class="portlet-body">
        <?php echo $this->render("_search", [
            "model" => $model,
        ]) ?>
        <div class="row">
            <div class="col-md-12">
                <?php echo ListView::widget([
                    'dataProvider' => $model->search(),
                    'showOnEmpty' => true,
                    'emptyText' => $this->render("_empty", []),
                    "itemView" => "_log",
                    "layout" => "{summary}\n<pre class='log'>{items}</pre>\n{pager}",
                ]); ?>
            </div>
        </div>
    </div>
</div>
