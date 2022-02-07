<?php
/**
 * @var $this \backend\models\BackendView
 */
use common\helper\HtmlHelper;

?>
<div ng-controller="ShopController">
    <div class="login-form">
    </div>
    <div class="text-center mt20">
        <?php echo HtmlHelper::tag("h4", App::t("frontend.shop.message", "Không có nhân viên làm việc", [])); ?>
    </div>
</div>