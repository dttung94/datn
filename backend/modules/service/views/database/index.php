<?php
/**
 * @var $this \backend\models\BackendView
 *
 * @var array $model
 * @var array $columns
 */
use yii\helpers\Html;

$this->title = App::t("backend.service_database.title", "Service");
$this->subTitle = App::t("backend.service_database.title", "Database");
$this->breadcrumbs = [
    [
        "label" => $this->subTitle
    ]
];
$this->actions = [
];
?>
<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-striped table-advance table-hover">
                        <thead>
                        <tr>
                            <th>#</th>
                            <?php foreach ($columns as $index => $key) {
                                echo Html::tag("th", $key, [
                                    "class" => "col"
                                ]);
                            } ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($model as $index => $row) {
                            $content = Html::tag("td", $index + 1);
                            foreach ($columns as $key) {
                                $content .= Html::tag("td", $row[$key]);
                            }
                            echo Html::tag("tr", $content);
                        } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
