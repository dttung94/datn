<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model ShopForm
 */
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use backend\modules\shop\forms\ShopForm;
use common\helper\DatetimeHelper;
use common\entities\shop\ShopInfo;
use yii\helpers\Json;
use backend\assets\AppAsset;

$bundle = App::$app->assetManager->getBundle(AppAsset::className());
$this->registerJsFile($bundle->baseUrl . "/js/controllers/shop.js", [
    "depends" => [
        AppAsset::className(),
    ]
]);

$formDataJson = Json::encode($model->toArray());

$this->registerJs(
    <<< JS
    jQuery(document).ready(function(){
    });
JS
    , \yii\web\View::POS_END, 'register-js-common-data-form');
?>
<?php $form = ActiveForm::begin([
    'id' => 'shop-form',
    'fieldConfig' => [
        'horizontalCssClasses' => [
            'label' => 'col-sm-4',
            'offset' => '',
            'wrapper' => 'col-sm-8',
            'error' => '',
            'hint' => '',
        ],
    ],
    "options" => [
        "ng-controller" => "ShopController",
        "ng-init" => "init($formDataJson)",
    ],
]); ?>
<?php if ($model->hasErrors()) {
    echo Html::tag("div", $form->errorSummary($model), [
        "class" => "note note-danger"
    ]);
} ?>
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <?= $form->field($model, 'shop_name', [])->textInput([
                'maxlength' => 255,
                'class' => 'form-control',
            ]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <?= $form->field($model, 'shop_email', [])->textInput([
                'maxlength' => 255,
                'class' => 'form-control',
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <?= $form->field($model, 'shop_desc', [])->textarea([
                'class' => 'form-control',
                "rows" => 5
            ]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 col-md-offset-2">
            <?= $form->field($model, 'phone_number', [])->textInput([
                "class" => "form-control",
            ]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <?= $form->field($model, 'shop_address', [])->textarea([
                "class" => "form-control",
                "rows" => 5,
            ]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3 col-md-offset-2">
            <div class="form-group">
                <?php echo $form->field($model, "allow_booking_tomorrow", [])->checkbox([
                    "ng-init" => "shopFormData.allow_booking_tomorrow=$model->allow_booking_tomorrow",
                    "ng-model" => "shopFormData.allow_booking_tomorrow",
                    "ng-true-value" => 1,
                    "ng-false-value" => 0,
                ])->label("Cho phép đặt trước 1 ngày") ?>
            </div>
        </div>
    </div>
    <h3 class="col-md-offset-2">
        <?php echo App::t("backend.shop.title", "Ngày & giờ làm việc") ?>
    </h3>
    <div class="row">
        <div class="col-md-3 col-md-offset-2">
            <div class="form-group">
                <label class="control-label">
                    <?php echo $model->getAttributeLabel("open_door_at"); ?>
                </label>
                <div class="row time-picker-layout">
                    <div class="col-md-6">
                        <?= $form->field($model, 'open_door_hour', [
                            'enableClientValidation' => false,
                        ])->dropDownList(DatetimeHelper::getListHours(), [
                            "class" => "form-control",
                            "convert-to-number" => "convert-to-number",
                            "ng-model" => "shopFormData.open_door_hour",
                            "ng-change" => "shopFormData.open_door_hour == 24?shopFormData.open_door_minute = 0:null;"
                        ])->label(false) ?>
                    </div>
                    <div class="col-md-6"
                         ng-if="shopFormData.open_door_hour != 24">
                        <?= $form->field($model, 'open_door_minute', [
                            'enableClientValidation' => false,
                        ])->dropDownList(DatetimeHelper::getListMinutes(), [
                            "class" => "form-control",
                            "convert-to-number" => "convert-to-number",
                            "ng-model" => "shopFormData.open_door_minute",
                        ])->label(false) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-md-offset-2">
            <div class="form-group">
                <label class="control-label">
                    <?php echo $model->getAttributeLabel("close_door_at"); ?>
                </label>
                <div class="row time-picker-layout">
                    <div class="col-md-6">
                        <?= $form->field($model, 'close_door_hour', [
                            'enableClientValidation' => false,
                        ])->dropDownList(DatetimeHelper::getListHours(), [
                            "class" => "form-control",
                            "convert-to-number" => "convert-to-number",
                            "ng-model" => "shopFormData.close_door_hour",
                            "ng-change" => "shopFormData.close_door_hour == 24?shopFormData.close_door_minute = 0:null;"
                        ])->label(false) ?>
                    </div>
                    <div class="col-md-6"
                         ng-if="shopFormData.close_door_hour != 24">
                        <?= $form->field($model, 'close_door_minute', [
                            'enableClientValidation' => false,
                        ])->dropDownList(DatetimeHelper::getListMinutes(), [
                            "class" => "form-control",
                            "convert-to-number" => "convert-to-number",
                            "ng-model" => "shopFormData.close_door_minute",
                        ])->label(false) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="form-group field-shopform-open_door_at">
                <label class="control-label" for="shopform-open_door_at">
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
                        ]);
                        echo Html::endTag("div");
                    } ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-9 col-md-offset-3">
            <?php echo Html::submitButton(
                "<i class='fa fa-save'></i> " . ($model->isNewRecord ? Yii::t('common.button', 'Lưu') : Yii::t('common.button', 'Thay đổi')),
                [
                    'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary'
                ]) ?>
            <?php
            echo Html::a(
                \Yii::t('common.label', 'Hủy bỏ'),
                Yii::$app->urlManager->createUrl([
                    "shop/manage",
                ]), [
                    "class" => "btn btn-default"
                ]
            );
            ?>
        </div>
    </div>

<?php ActiveForm::end(); ?>