<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model ManagerForm
 */
use backend\modules\system\forms\manager\ManagerForm;
use common\entities\user\UserPermission;
use common\helper\ArrayHelper;
use common\helper\HtmlHelper;
use yii\widgets\Pjax;

$this->title = App::t("backend.system_manager.title", "Quản lý bộ phận quản lý");
$this->subTitle = App::t("backend.system_manager.title", "Với manager");
$this->breadcrumbs = [
    [
        "label" => $this->title,
        "url" => App::$app->urlManager->createUrl([
            "system/manager"
        ]),
    ],
    [
        "label" => $this->subTitle
    ]
];
$this->actions = [
    HtmlHelper::a("<i class='fa fa-plus'></i> " . Yii::t('common.button', 'Thêm mới'), [
        'create',
    ], [
        'class' => 'btn btn-success',
        "data-pjax" => 0,
    ])
];
?>
<div class="portlet light bordered">
    <div class="portlet-body">
        <div class="row">
            <div class="col-xs-12">
                <?php Pjax::begin([
                    "id" => "pjax-grid-view-manager"
                ]); ?>
                <div class="table-scrollable">
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th scope="col" style="width: 100px;">&nbsp;</th>
                            <th scope="col" style="width: 200px;">&nbsp;</th>
                            <?php foreach ($model->search()->query->all() as $manager) {
                                /**
                                 * @var $manager ManagerForm
                                 */
                                echo HtmlHelper::beginTag("th", [
                                    "scope" => "col",
                                ]);
                                echo $manager->full_name;
                                echo HtmlHelper::endTag("th");
                            } ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $shopIndex = 0;
                        $listShops = $model->getListShops();
                        $managers = $model->search()->query->all();
                        foreach ( $listShops as $shop_id => $shop_name) {
                            echo HtmlHelper::beginTag("tr");
                            if ($shopIndex == 0) {
                                echo HtmlHelper::tag("td", "Danh sách cửa hàng", [
                                    "rowspan" => count($model->getListShops()),
                                ]);
                            }
                            echo HtmlHelper::tag("td", $shop_name);
                            foreach ($managers as $manager) {
                                /**
                                 * @var $manager ManagerForm
                                 */
                                echo HtmlHelper::beginTag("td");
                                echo HtmlHelper::checkbox("ManagerPermission[$manager->user_id][Shop][$shop_id]",
                                    ($manager->status == ManagerForm::STATUS_ACTIVE) ? $manager->isSelectedShop($shop_id) : false,
                                    ArrayHelper::merge([
                                        "class" => "make-switch switch-status",
                                        "data-size" => "mini",
                                        "data-url" => Yii::$app->urlManager->createUrl([
                                            "system/manager/switch-shop",
                                            "id" => $manager->user_id,
                                            "shop_id" => $shop_id,
                                        ]),
                                        "data-pjax-id" => "pjax-grid-view-manager",
                                    ], $manager->status == ManagerForm::STATUS_ACTIVE ? [] : [
                                        "disabled" => "disabled"
                                    ]));
                                echo HtmlHelper::endTag("td");
                            }
                            echo HtmlHelper::endTag("tr");
                            $shopIndex += 1;
                        } ?>
<!--                        --><?php //foreach (UserPermission::getListPermission() as $module => $permissions) {
//                            $modulePermissionIndex = 0;
//                            foreach ($permissions as $permission => $level) {
//                                echo HtmlHelper::beginTag("tr");
//                                if ($modulePermissionIndex == 0) {
//                                    echo HtmlHelper::tag("td", "店舗", [
//                                        "rowspan" => count($permissions),
//                                    ]);
//                                }
//                                echo HtmlHelper::tag("td", UserPermission::getModulePermissionLabel($permission));
//                                foreach ($model->search()->query->all() as $manager) {
//                                    /**
//                                     * @var $manager ManagerForm
//                                     */
//                                    echo HtmlHelper::beginTag("td");
//                                    echo HtmlHelper::checkbox("ManagerPermission[$manager->user_id][$module][$permission]",
//                                        ($manager->status == ManagerForm::STATUS_ACTIVE) ? UserPermission::getValue($manager->user_id, $module, $permission, 0) == 1 : false,
//                                        ArrayHelper::merge([
//                                            "class" => "make-switch switch-status",
//                                            "data-size" => "mini",
//                                            "data-url" => Yii::$app->urlManager->createUrl([
//                                                "system/manager/switch-module-permission",
//                                                "id" => $manager->user_id,
//                                                "module" => $module,
//                                                "permission" => $permission,
//                                            ]),
//                                            "data-pjax-id" => "pjax-grid-view-manager",
//                                        ], $manager->status == ManagerForm::STATUS_ACTIVE ? [] : [
//                                            "disabled" => "disabled"
//                                        ]));
//                                    echo HtmlHelper::endTag("td");
//                                }
//                                echo HtmlHelper::endTag("tr");
//                                $modulePermissionIndex += 1;
//                            }
//                        } ?>
                        </tbody>
                    </table>
                </div>
                <?php Pjax::end(); ?>
            </div>
        </div>
    </div>
</div>