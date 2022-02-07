<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model ShopForm
 */
use backend\assets\AppAsset;
use backend\modules\calendar\forms\schedule\ShopForm;
use common\entities\shop\ShopCalendar;
use common\helper\DatetimeHelper;
use yii\bootstrap\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Json;
use common\helper\StringHelper;

$formDataJson = Json::encode($model->toArray([], [
    'open_door_hour',
    'open_door_minute',
    'close_door_hour',
    'close_door_minute',
    'working_day_on_week',

    'date',
    "scheduleWorkers",
]));
$this->title = $model->shop_name;
$this->subTitle = App::t("backend.schedule.title", "Quản lý ca làm việc");
$this->breadcrumbs = [
    [
        "label" => App::t("backend.schedule.title", "Ca làm việc: {shop-name} - {date}", [
            "shop-name" => $model->shop_name,
            "date" => $model->date,
        ])
    ]
];
$this->themeOptions = [
    "bodyClass" => "page-sidebar-closed-hide-logo page-sidebar-closed",
    "sideMenuClass" => "page-sidebar-menu-closed",
    "pageTitleClass" => "font-red font-bold",
];
$this->actions = [
//    Html::a('<i class="fa fa-download"></i>  ' . Yii::t('common.button', 'スケジュールをダウンロード'), [
//        '/calendar/schedule-import/download-template',
//        'shop_id' => $model->shop_id,
//        "date" => $model->date,
//    ], [
//        'class' => 'btn btn-default',
//        "data-pjax" => 0,
//        'data-methdd' => 'post',
//    ]),
    Html::a('<i class="fa fa-pencil"></i>  ' . Yii::t('common.button', 'Cập nhật thông tin cửa hàng'), [
        '/shop/manage/update',
        'id' => $model->shop_id,
    ], [
        'class' => 'btn btn-success',
        "data-pjax" => 0,
    ]),
];

$bundle = App::$app->assetManager->getBundle(AppAsset::className());
$this->registerJsFile($bundle->baseUrl . "/js/controllers/schedule.js", [
    "depends" => [
        AppAsset::className(),
    ]
]);
$this->registerJs(<<<JS
    jQuery(document).ready(function () {
    });
JS
    , \yii\web\View::POS_END);
?>
<div class="portlet light calendar"
     ng-controller="ScheduleController">
    <div class="portlet-body"
         ng-init='intShopSchedule(<?php echo $model->shop_id; ?>,<?php echo $formDataJson; ?>)'>
        <?php echo $this->render("_modal_worker_calendar", []) ?>
        <?php $form = ActiveForm::begin([
            'id' => 'shop-schedule-config-form',
            'fieldConfig' => [
                'horizontalCssClasses' => [
                    'label' => 'col-sm-4',
                    'offset' => '',
                    'wrapper' => 'col-sm-8',
                    'error' => '',
                    'hint' => '',
                ],
            ]
        ]); ?>
        <?php if ($model->hasErrors()) {
            echo Html::tag("div", $form->errorSummary($model), [
                "class" => "note note-danger"
            ]);
        } ?>
        <div class="row">
            <div class="col-md-2">
                <div class="form-group">
                    <label class="control-label">
                        <?php echo $model->getAttributeLabel("open_door_at"); ?>
                    </label>
                    <div class="row time-picker-layout">
                        <div class="col-md-6">
                            <?= $form->field($model, 'open_door_hour', [])->dropDownList(DatetimeHelper::getListHours(), [
                                "class" => "form-control disabled",
                                "disabled" => "disabled",
                                "ng-model" => "shopSchedule.formData.open_door_hour",
                                "ng-change" => "shopSchedule.formData.open_door_hour == 24?shopSchedule.formData.open_door_minute = 0:null;"
                            ])->label(false) ?>
                        </div>
                        <div class="col-md-6"
                             ng-if="shopSchedule.formData.open_door_hour != 24">
                            <?= $form->field($model, 'open_door_minute', [])->dropDownList(DatetimeHelper::getListMinutes(), [
                                "class" => "form-control disabled",
                                "disabled" => "disabled",
                                "ng-model" => "shopSchedule.formData.open_door_minute",
                            ])->label(false) ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label class="control-label">
                        <?php echo $model->getAttributeLabel("close_door_at"); ?>
                    </label>
                    <div class="row time-picker-layout">
                        <div class="col-md-6">
                            <?= $form->field($model, 'close_door_hour', [])->dropDownList(DatetimeHelper::getListHours(), [
                                "class" => "form-control disabled",
                                "disabled" => "disabled",
                                "ng-model" => "shopSchedule.formData.close_door_hour",
                                "ng-change" => "shopSchedule.formData.close_door_hour == 24?shopSchedule.formData.close_door_minute = 0:null;"
                            ])->label(false) ?>
                        </div>
                        <div class="col-md-6"
                             ng-if="shopSchedule.formData.close_door_hour != 24">
                            <?= $form->field($model, 'close_door_minute', [])->dropDownList(DatetimeHelper::getListMinutes(), [
                                "class" => "form-control disabled",
                                "disabled" => "disabled",
                                "ng-model" => "shopSchedule.formData.close_door_minute",
                            ])->label(false) ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="form-group">
                    <label class="control-label">
                        <?php echo $model->getAttributeLabel("working_day_on_week"); ?>
                    </label>
                    <div class="row">
                        <?php for ($i = 1; $i <= 7; $i++) {
                            echo Html::beginTag("div", [
                                "class" => "col-md-1",
                                "style" => [
                                    "width" => "100px;"
                                ],
                            ]);
                            echo Html::activeCheckbox($model, "working_day_on_week[$i]", [
                                "label" => DatetimeHelper::getDayOfWeek($i, "ja"),
                                "uncheck" => 0,
                                "disabled" => "disabled",
                            ]);
                            echo Html::endTag("div");
                        } ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row margin-bottom-10">
            <div class="col-md-12">
                <?php
                $today = time();
                for ($i = 0; $i < 11; $i++) {
                    $date = $today + $i * 24 * 60 * 60;
                    echo Html::a(App::t("backend.schedule.label", "{date}({day})", [
                        "date" => App::$app->formatter->asDate($date, "dd/M"),
                        "day" => App::$app->formatter->asDayOfWeek($date),
                    ]), [
                        "config",
                        "shop_id" => $model->shop_id,
                        "date" => date("Y-m-d", $date),
                    ], [
                        "class" => "btn btn-xs " . (date("Y-m-d", $date) == $model->date ? "btn-primary" : "btn-default")
                    ]);
                } ?>
            </div>
        </div>
        <hr/>
        <div class="row margin-bottom-10">
            <div class="col-md-11">
                <div class="row">
                    <div class="col-md-3">
                        <?php echo Html::textInput("keyword", $name, [
                            "id" => "key-search",
                            "class" => "form-control",
                            "placeholder" => "Từ khóa",
                        ]) ?>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-default">Tìm kiếm</button>
                    </div>
                </div>
            </div>
            <div class="col-md-1 text-right">
                <?php echo Html::a("<i class='fa fa-save'></i> " . Yii::t('common.button', 'Lưu lịch biểu'), "javascript:;", ['class' => 'btn btn-primary pull-right',
                    "ng-click" => "shopSchedule.toSave()",]) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <table class="table table-striped table-bordered table-advance <?php echo $model->checkDateIsWorkingDay($model->date) ? "" : "disabled offline" ?>">
                    <tbody>
                    <?php
                    echo Html::beginTag("thead");
                    echo Html::beginTag("tr");
                    echo Html::tag("th",
                        App::$app->formatter->asDate($model->date) . " (" . App::$app->formatter->asDayOfWeek(strtotime($model->date)) . ")",
                        [
                            "colspan" => "5",
                            "style" => [
                                "vertical-align" => "middle",
                            ],
                        ]);
                    echo Html::endTag("tr");
                    echo Html::endTag("thead");
                    if (empty($model->scheduleWorkers)) {
                        echo Html::beginTag("tr");
                        echo Html::tag("td", 'Không có kết quả nào');
                        echo Html::endTag("tr");
                    } else {
                        foreach ($model->scheduleWorkers as $worker_id => $config) {
                            /**
                             * @var $config ShopCalendar
                             */
                            echo Html::beginTag("tr", [
                                "ng-show" => "!shopSchedule.formData.keyword || shopSchedule.formData.scheduleWorkers[$worker_id]['workerInfo']['worker_name'].indexOf(shopSchedule.formData.keyword) > -1"
                            ]);
                            //todo show col worker info
                            echo Html::beginTag("td", []);
                            echo Html::a(StringHelper::truncate($config['workerInfo']['worker_name'], 12), "javascript:;", [
                                "ng-click" => "shopSchedule.viewWorkerCalendar($worker_id, '$model->date')",
                            ]);
                            echo Html::endTag("td");
                            //todo show col IS_WORK_DAY
                            echo Html::beginTag("td", [
                                "class" => "text-center",
                                "style" => [
                                    "width" => "100px"
                                ],
                            ]);
                            echo '<label class="checkbox-switch" ng-show="shopSchedule.formData.scheduleWorkers[' . $worker_id . '].isSwitchable">' .
                                Html::activeCheckbox($model, "scheduleWorkers[$worker_id][is_work_day]", [
                                    "class" => "not-uniform hidden",
                                    "uncheck" => null,
                                    'label' => null,
                                    "ng-true-value" => 1,
                                    "ng-false-value" => 0,
                                    "ng-model" => "shopSchedule.formData.scheduleWorkers[$worker_id]['is_work_day']",
                                    "ng-change" => "shopSchedule.toCheckWorkerSchedule(shopSchedule.formData.scheduleWorkers[$worker_id])",
                                ]) .
                                '<span class="checkbox-slider"></span></label>';
                            echo '<label class="checkbox-switch disabled" ng-show="!shopSchedule.formData.scheduleWorkers[' . $worker_id . '].isSwitchable">' .
                                Html::activeCheckbox($model, "scheduleWorkers[$worker_id][is_work_day]", [
                                    "class" => "not-uniform hidden disabled",
                                    "uncheck" => null,
                                    'label' => null,
                                    "ng-true-value" => 1,
                                    "ng-false-value" => 0,
                                    "ng-value" => 1,
                                    "disabled" => "disabled",
                                ]) .
                                '<span class="checkbox-slider"></span></label>';
                            echo Html::endTag("td");
                            //todo show col start_at
                            echo Html::beginTag("td", [
                                "style" => [
                                    "width" => "200px;"
                                ],
                            ]);
                            echo Html::beginTag("div", [
                                "class" => "row time-picker-layout",
                                "ng-show" => "shopSchedule.formData.scheduleWorkers[$worker_id]['is_work_day']",
                            ]);
                            echo Html::beginTag("div", [
                                "class" => "col-sm-6",
                            ]);
                            echo Html::activeDropDownList($model, "scheduleWorkers[$worker_id][work_start_hour]", DatetimeHelper::getListHours($model->open_door_hour, $model->close_door_hour + 1), [
                                "class" => "form-control",
                                "ng-model" => "shopSchedule.formData.scheduleWorkers[$worker_id]['work_start_hour']",
                                "ng-change" => "shopSchedule.toCheckWorkerSchedule(shopSchedule.formData.scheduleWorkers[$worker_id])",
                            ]);
                            echo Html::endTag("div");
                            echo Html::beginTag("div", [
                                "class" => "col-sm-6",
                            ]);
                            echo Html::activeDropDownList($model, "scheduleWorkers[$worker_id][work_start_minute]", DatetimeHelper::getListMinutes(), [
                                "class" => "form-control",
                                "ng-model" => "shopSchedule.formData.scheduleWorkers[$worker_id]['work_start_minute']",
                                "ng-show" => "shopSchedule.formData.scheduleWorkers[$worker_id]['work_start_hour'] != 24",
                                "ng-change" => "shopSchedule.toCheckWorkerSchedule(shopSchedule.formData.scheduleWorkers[$worker_id])",
                            ]);
                            echo Html::endTag("div");
                            echo Html::endTag("div");
                            echo Html::endTag("td");
                            //todo show col end_at
                            echo Html::beginTag("td", [
                                "style" => [
                                    "width" => "200px;"
                                ],
                            ]);
                            echo Html::beginTag("div", [
                                "class" => "row time-picker-layout",
                                "ng-show" => "shopSchedule.formData.scheduleWorkers[$worker_id]['is_work_day']",
                            ]);
                            echo Html::beginTag("div", [
                                "class" => "col-sm-6",
                            ]);
                            echo Html::activeDropDownList($model, "scheduleWorkers[$worker_id][work_end_hour]", DatetimeHelper::getListHours($model->open_door_hour, $model->close_door_hour + 1), [
                                "class" => "form-control",
                                "ng-model" => "shopSchedule.formData.scheduleWorkers[$worker_id]['work_end_hour']",
                                "ng-change" => "shopSchedule.toCheckWorkerSchedule(shopSchedule.formData.scheduleWorkers[$worker_id])",
                            ]);
                            echo Html::endTag("div");
                            echo Html::beginTag("div", [
                                "class" => "col-sm-6",
                            ]);
                            echo Html::activeDropDownList($model, "scheduleWorkers[$worker_id][work_end_minute]", DatetimeHelper::getListMinutes(), [
                                "class" => "form-control",
                                "ng-show" => "shopSchedule.formData.scheduleWorkers[$worker_id]['work_end_hour'] != 24",
                                "ng-model" => "shopSchedule.formData.scheduleWorkers[$worker_id]['work_end_minute']",
                                "ng-change" => "shopSchedule.toCheckWorkerSchedule(shopSchedule.formData.scheduleWorkers[$worker_id])",
                            ]);
                            echo Html::endTag("div");
                            echo Html::endTag("div");
                            echo Html::endTag("td");
                            //todo show action col
                            echo Html::beginTag("td", [
                                "style" => [
                                    "width" => "20px",
                                ],
                            ]);
                            echo Html::a("<i class='fa fa-calendar'></i>", "javascript:;", [
                                "tooltips" => "tooltips",
                                "tooltip-template" => "{{shopSchedule.formData.error['$model->date-$worker_id'][0]}}",
                                "tooltip-size" => "large",
                                "ng-class" => "{'font-red':shopSchedule.formData.error['$model->date-$worker_id']}",
                                "ng-click" => "shopSchedule.viewWorkerCalendar($worker_id, '$model->date')",
                            ]);
                            echo Html::endTag("td");
                        }
                    }
                    ?>
                    </tbody>
                </table>
                <?php
                    $keyName = $name != "" ? "&name=$name" : "";
                    echo "<input type='hidden' value='$url' id='url'>";
                    $pageNow = isset($_GET['page']) ? $_GET['page'] : 1;
                    if ($pages <= 5) {
                        for ($i=1; $i<=$pages; $i++) {
                            if ($pageNow == $i) {
                                echo "<a class='btn btn-default float-left active' href='$url&page=$i$keyName'>$i</a>";
                            } else {
                                echo "<a class='btn btn-default float-left' href='$url&page=$i$keyName'>$i</a>";
                            }
                        }
                    } else {
                        $pagePrews = ($pageNow - 2) <=1 ? 1 : $pageNow - 2;
                        $pageNexts = ($pageNow + 2) >= $pages ? $pages : $pageNow + 2;
                        if ($pageNexts < 5) {
                            $pageNexts = 5;
                        }
                        if ($pagePrews > 1) {
                            $pagePrew = $pageNow - 1;
                            echo "<a class='btn btn-default float-left' href='$url&page=1$keyName'><<</a>";
                            echo "<a class='btn btn-default float-left' href='$url&page=$pagePrew$keyName'><</a>";
                        }
                        for ($i=$pagePrews; $i<=$pageNexts; $i++) {
                            if ($pageNow == $i) {
                                echo "<a class='btn btn-default float-left active' href='$url&page=$i$keyName'>$i</a>";
                            } else {
                                echo "<a class='btn btn-default float-left' href='$url&page=$i$keyName'>$i</a>";
                            }
                        }
                        if ($pageNexts < $pages) {
                            $pageNxt = $pageNow + 1;
                            echo "<a class='btn btn-default float-left' href='$url&page=$pageNxt$keyName'>></a>";
                            echo "<a class='btn btn-default float-left' href='$url&page=$pages$keyName'>>></a>";
                        }
                    }
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 text-right">
                <?php echo Html::a("<i class='fa fa-save'></i> " . Yii::t('common.button', 'Lưu lịch biểu'),
                    "javascript:;", [
                        'class' => 'btn btn-primary',
                        "ng-click" => "shopSchedule.toSave()",
                    ]) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
