<?php
/**
 * @var $this \app\backend\models\BackendView
 * @var array $tabs
 */
use app\backend\modules\system\forms\log\SystemLogSearchForm;
use yii\widgets\ListView;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

?>
<!-- BEGIN PORTLET-->
<div class="portlet light">
    <div class="portlet-title tabbable-line">
        <div class="caption caption-md">
            <i class="fa fa-leaf theme-font-color"></i>
            <span class="caption-subject theme-font-color bold uppercase">
                <?php echo App::t("backend.system_log.title", "TODAY activities log"); ?>
            </span>
        </div>
        <ul class="nav nav-tabs">
            <?php foreach ($tabs as $index => $tab) { ?>
                <li class="<?php echo $index == 0 ? "active" : "" ?>">
                    <a href="#tab_log_system_tab<?php echo $index ?>" data-toggle="tab">
                        <?php echo ArrayHelper::getValue($tab, "title"); ?>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </div>
    <div class="portlet-body">
        <!--BEGIN TABS-->
        <div class="tab-content">
            <?php foreach ($tabs as $index => $tab) { ?>
                <div class="tab-pane <?php echo $index == 0 ? "active" : "" ?>"
                     id="tab_log_system_tab<?php echo $index ?>">
                    <div class="scroller" style="height: 470px;" data-always-visible="1" data-rail-visible1="0"
                         data-handle-color="#D7DCE2">
                        <?php echo ListView::widget([
                            'dataProvider' => ArrayHelper::getValue($tab, "model")->search(),
                            'itemOptions' => ['class' => 'feeds'],
                            'itemView' => function ($model, $key, $index, $widget) {
                                return $this->render("_log_item", [
                                    "key" => $key,
                                    "index" => $index,
                                    "model" => $model
                                ]);
                            },
                            'layout' => "{items}\n{pager}",
                        ]); ?>
                    </div>
                    <?php echo Html::a("view detail", Yii::$app->urlManager->createUrl(ArrayHelper::merge([
                        "system/log/index"
                    ], $tab["search"])), [
                        "class" => "label label-primary pull-right"
                    ]) ?>
                    <div style="clear: right"></div>
                </div>
            <?php } ?>
        </div>
        <!--END TABS-->
    </div>
</div>
<!-- END PORTLET-->
