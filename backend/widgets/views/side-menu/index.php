<?php
use common\helper\ArrayHelper;

/**
 * @var $this \backend\models\BackendView
 * @var $asset \backend\assets\AdminAsset
 * @var $menus array
 */
?>
<div class="page-sidebar-wrapper">
    <!-- DOC: Set data-auto-scroll="false" to disable the sidebar from auto scrolling/focusing -->
    <!-- DOC: Change data-auto-speed="200" to adjust the sub menu slide up/down speed -->
    <div class="page-sidebar navbar-collapse collapse" style="width: 175px">
        <!-- BEGIN SIDEBAR MENU -->
        <!-- DOC: Apply "page-sidebar-menu-light" class right after "page-sidebar-menu" to enable light sidebar menu style(without borders) -->
        <!-- DOC: Apply "page-sidebar-menu-hover-submenu" class right after "page-sidebar-menu" to enable hoverable(hover vs accordion) sub menu mode -->
        <!-- DOC: Apply "page-sidebar-menu-closed" class right after "page-sidebar-menu" to collapse("page-sidebar-closed" class must be applied to the body element) the sidebar sub menu mode -->
        <!-- DOC: Set data-auto-scroll="false" to disable the sidebar from auto scrolling/focusing -->
        <!-- DOC: Set data-keep-expand="true" to keep the submenues expanded -->
        <!-- DOC: Set data-auto-speed="200" to adjust the sub menu slide up/down speed -->
        <ul class="page-sidebar-menu <?php echo ArrayHelper::getValue($this->themeOptions, "sideMenuClass") ?>"
            data-keep-expanded="false"
            data-auto-scroll="true"
            data-slide-speed="200"
            style="width: 175px">
            <!-- DOC: To remove the sidebar toggler from the sidebar you just need to completely remove the below "sidebar-toggler-wrapper" LI element -->
            <li class="sidebar-toggler-wrapper margin-bottom-10">
                <!-- BEGIN SIDEBAR TOGGLER BUTTON -->
                <div class="sidebar-toggler">
                </div>
                <!-- END SIDEBAR TOGGLER BUTTON -->
            </li>
            <?php foreach ($menus as $menu) { ?>
                <?php if (!is_array($menu)) { ?>
                    <li class="heading">
                        <h3 class="uppercase"><?php echo $menu ?></h3>
                    </li>
                <?php } else { ?>
                    <li class="<?php echo isset($menu["class"]) ? $menu["class"] : "" ?> <?php echo isset($menu["is_selected"]) && $menu["is_selected"] ? "active open" : "" ?> <?php echo isset($menu["tooltip"]) ? "tooltips" : "" ?>"
                        data-container="body" data-placement="right" data-html="true"
                        data-original-title="<?php echo isset($menu["tooltip"]) ? $menu["tooltip"] : "" ?>">
                        <?php if(!empty($menu['notification'])) { ?>
                            <div style="position: relative; top: 12px; z-index: 1000;"><?php echo $menu['notification']; ?></div>
                        <?php } ?>
                        <a href="<?php echo isset($menu["url"]) ? $menu["url"] : "javascript:;" ?>">
                            <i class="<?php echo isset($menu["icon"]) ? $menu["icon"] : "" ?>"></i>
                            <span class="title"><?php echo isset($menu["label"]) ? $menu["label"] : "" ?></span>
                            <?php echo isset($menu["is_selected"]) && $menu["is_selected"] ? '<span class="selected"></span>' : "" ?>
                            <?php if (isset($menu["subs"])) { ?>
                                <span class="arrow <?php echo isset($menu["is_selected"]) && $menu["is_selected"] ? "open" : "" ?>"></span>
                            <?php } ?>
                        </a>
                        <?php if (isset($menu["subs"])) { ?>
                            <ul class="sub-menu">
                                <?php foreach ($menu["subs"] as $sub) { ?>
                                    <li class="<?php echo isset($sub["is_selected"]) && $sub["is_selected"] ? "active" : "" ?>">
                                        <a href="<?php echo $sub["url"] ?>" style="<?php echo ($sub['label'] == '書き込み管理' || $sub['label'] == 'コメント管理') ? 'padding-right:0' : '' ?>">
                                            <i class="<?php echo isset($sub["icon"]) ? $sub["icon"] : "" ?>"></i>
                                            <?php echo isset($sub["label"]) ? $sub["label"] : "" ?>
                                            <?php if(!empty($sub['notification'])) { ?>
                                                <?php echo $sub['notification']; ?>
                                            <?php } ?>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                        <?php } ?>
                    </li>
                <?php } ?>
            <?php } ?>
        </ul>

        <!-- END SIDEBAR MENU -->
    </div>
</div>
