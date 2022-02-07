<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model BookingHistorySearchForm
 */
use backend\modules\calendar\forms\booking\BookingHistorySearchForm;
use common\helper\HtmlHelper;
use yii\bootstrap\ActiveForm;
use common\helper\ArrayHelper;

$form = ActiveForm::begin([
    "id" => "booking-history-filter-form",
    'method' => 'GET',
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
            <div class="col-md-3">
                <?php echo HtmlHelper::activeDropDownList($model, 'filter_course_id', ArrayHelper::merge([
                    "" => App::t("backend.booking-history.label", "Tất cả các loại dịch vụ"),
                ], $model::getListCourse()), [
                    "class" => "form-control select2me",
                    "data-placeholder" => $model->getAttributeLabel("filter_course_id"),
                ]); ?>
            </div>
        </div>
        <div class="row margin-top-20">
            <div class="col-md-3">
                <?php echo HtmlHelper::activeDropDownList($model, 'filter_user_id', ArrayHelper::merge([
                    "" => App::t("backend.booking-history.label", "Tất cả thành viên"),
                ], $model::getListCustomer()), [
                    "class" => "form-control select2me",
                    "data-placeholder" => $model->getAttributeLabel("filter_user_id"),
                ]); ?>
            </div>
            <div class="col-md-3">
                <?php echo HtmlHelper::activeDropDownList($model, 'filter_shop_id', ArrayHelper::merge([
                    "" => App::t("backend.booking-history.label", "Tất cả các tiệm salon"),
                ], $model::getListShop()), [
                    "class" => "form-control select2me",
                    "data-placeholder" => $model->getAttributeLabel("filter_shop_id"),
                ]); ?>
            </div>
            <div class="col-md-3">
                <?php echo HtmlHelper::activeDropDownList($model, 'filter_worker_id', ArrayHelper::merge([
                    "" => App::t("backend.booking-history.label", "Tất cả nhân viên"),
                ], $model::getListWorker()), [
                    "class" => "form-control select2me",
                    "data-placeholder" => $model->getAttributeLabel("filter_worker_id"),
                ]); ?>
            </div>
        </div>
        <div class="row margin-top-20">
            <div class="col-md-5">
                <?php echo HtmlHelper::activeTextInput($model, 'keyword', [
                    "class" => "form-control",
                    "placeholder" => $model->getAttributeLabel("keyword")
                ]); ?>
            </div>
            <div class="col-md-1">
                <?php echo HtmlHelper::submitButton(App::t("backend.system_manager.button", "Tìm kiếm"), [
                    'class' => 'btn btn-default pull-right'
                ]); ?>
            </div>
        </div>
    </div>
<?php ActiveForm::end() ?>