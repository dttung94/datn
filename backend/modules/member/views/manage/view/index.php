<?php
use backend\assets\AdminAsset;
use backend\modules\member\forms\MemberForm;
use \common\entities\user\UserInfo;
use yii\bootstrap\Html;
use backend\assets\AppAsset;
/**
 * @var $this \backend\models\BackendView
 * @var $model MemberForm
 */
$this->title = $model->full_name;
$this->subTitle = Yii::t("backend.member.title", "Thống kê");

$bundle = App::$app->assetManager->getBundle(AppAsset::className());
$this->registerJsFile($bundle->baseUrl . "/js/controllers/member_view.js", [
    "depends" => [
        AppAsset::className(),
    ]
]);
$this->registerJsFile($bundle->baseUrl . "/js/controllers/member.js", [
    "depends" => [
        AppAsset::className(),
    ]
]);

$this->breadcrumbs = [
    [
        'label' => App::t("backend.member.title", "Quản lý thành viên"),
        'url' => Yii::$app->urlManager->createUrl([
            "member/manage",
        ])
    ],
    [
        "label" => $this->title
    ]
];
$asset = AdminAsset::register($this);
$this->registerJs(<<<JS
JS
    , \yii\web\View::POS_END);
$users = $users = App::$app->user->identity;
$isOperator = 0;
if ($users->role == UserInfo::ROLE_OPERATOR) {
    $isOperator = 1;
}
?>
<div ng-controller="MemberController"
    ng-init="init(<?php echo $member_id ?>)"
>
    <?php echo $this->render("_system_statistic", [
        "model" => $model,
    ]) ?>
    <?php echo $this->render("_booking_chart", [
        "model" => $model,
    ]) ?>
    <?php echo $this->render("_user_data", [
        "data" => $data,
    ]) ?>

</div>
<div ng-controller="MemberViewController"
    ng-init="init(<?php echo $member_id ?>)"
>
    <?php echo $this->render("_booking_history", [
        "model" => $model,
        "member_id" => $member_id,
    ]) ?>

</div>
<script>
    $(document).ready(function () {
        $('.permission').on('click', function () {
            var isOperator = <?php echo $isOperator?>;
            if (isOperator === 1) {
                toastr.error('Không có quyền thực hiện');
                return false;
            }
        })
    });
</script>
