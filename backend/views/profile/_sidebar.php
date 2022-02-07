<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model UserProfileForm
 * @var $tab string
 */
use backend\forms\UserProfileForm;
use yii\bootstrap\Html;

?>
<div class="profile-sidebar">
    <!-- PORTLET MAIN -->
    <div class="portlet light profile-sidebar-portlet">
        <!-- SIDEBAR USER TITLE -->
        <div class="profile-usertitle">
            <div class="profile-usertitle-name">
                <?php echo $model->full_name ?>
            </div>
            <div class="profile-usertitle-job">
                <?php echo $model->getAttributeLabel($model->role) ?>
            </div>
        </div>
        <!-- END SIDEBAR USER TITLE -->
        <!-- SIDEBAR MENU -->
        <div class="profile-usermenu">
            <ul class="nav">
                <li class="<?php echo $tab == "info" ? "active" : ""; ?>">
                    <?php echo Html::a('<i class="icon-home"></i> Thông tin cá nhân', [
                        "/profile/index",
                    ]) ?>
                </li>
                <li class="<?php echo $tab == "password" ? "active" : ""; ?>">
                    <?php echo Html::a('<i class="fa fa-key"></i> Đổi mật khẩu', [
                        "/profile/password",
                    ]) ?>
                </li>
            </ul>
        </div>
        <!-- END MENU -->
    </div>
    <!-- END PORTLET MAIN -->
</div>
