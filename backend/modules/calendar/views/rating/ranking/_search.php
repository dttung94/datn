<?php
/**
 * @var $this BackendView
 * @var $model RatingRankForm
 */

use backend\models\BackendView;
use backend\modules\calendar\forms\rating\RatingRankForm;
use common\helper\ArrayHelper;
use common\helper\HtmlHelper;
use yii\bootstrap\ActiveForm;

$form = ActiveForm::begin([
    "id" => "rating-rank-filter-form",
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
            <?php echo HtmlHelper::activeDropDownList($model, 'filter_point_type', ArrayHelper::merge([
                ""  => App::t("backend.rating-ranking.label", "Tổng điểm"),
            ], $model::getListRatingField()), [
                "class" => "form-control select2me"
            ])?>
        </div>
<!--        <div class="col-md-1">-->
<!--            --><?php //echo HtmlHelper::submitButton(App::t("backend.system_manager.button", "検索"), [
//                'class' => 'btn btn-default pull-right'
//            ]); ?>
<!--        </div>-->
    </div>
</div>
<?php ActiveForm::end() ?>