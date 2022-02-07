<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model MemberForm
 */
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap\ActiveForm;
use backend\modules\member\forms\MemberForm;

$form = ActiveForm::begin([
    "id" => "grid-view-member-filter",
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
]) ?>
    <div class="form-body">
        <div class="row margin-bottom-10">
            <div class="col-md-3">
                <?php echo Html::activeTextInput($model, 'keyword', [
                    "class" => "form-control",
                    "placeholder" => $model->getAttributeLabel("keyword")
                ]) ?>
            </div>
            <div class="col-md-3">
                <?php echo Html::activeTextInput($model, 'filter_latest_booking_from', [
                    "class" => "form-control date-picker",
                    "placeholder" => "最終予約",
                    'autocomplete' => 'off'
                ]) ?>
            </div>
            <div class="col-md-2">
                <?php
                echo Html::activeDropDownList($model, "status", [
                    '' => '',
                    MemberForm::STATUS_ACTIVE => Yii::t("common.label", "アクティブ"),
                    MemberForm::STATUS_SHOP_BLACK_LIST => App::t("backend.member.label", "店舗BL/承認"),
                    MemberForm::STATUS_WORKER_BLACK_LIST => App::t("backend.member.label", "NGリスト"),
                    MemberForm::STATUS_VERIFYING => App::t("backend.member.label", "番号認証途中"),
                    MemberForm::STATUS_CONFIRMING => App::t("backend.member.label", "承認待ち"),
                ], [
                    "class" => 'form-control select2me',
                    "data-placeholder" => $model->getAttributeLabel("status"),
                ])
                ?>
            </div>
            <div class="col-md-1">
                <?php echo Html::submitButton(Yii::t('common.button', '検索'), [
                    'class' => 'btn btn-default'
                ]); ?>
            </div>
        </div>
    </div>
<?php ActiveForm::end() ?>