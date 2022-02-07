<?php

use common\entities\user\UserInfo;
use common\helper\HtmlHelper;

?>

<style>
    .icon-user-refer {
        font-size: 30px;
        border: 1px solid #ccc;
        border-radius: 50%;
        padding: 5px;
    }

    label {
        text-shadow: none !important;
        font-size: 13px !important;
        font-weight: 300 !important;
        padding: 5px 10px !important;
        color: #fff;
        font-family: "Open Sans", sans-serif;
        border-radius: 0 !important;
    }

    .label-default {
        background-color: #c6c6c6;
    }
</style>

<div class="panel panel-default mb20">
    <div class="panel-heading box-container box-space-between box-middle">
        <div class="box-container box-middle col-md-4">
            <?php
            if ($model->avatar != null) {
                echo
                    HtmlHelper::img(App::$app->params['aws.root_path'] . $model->avatar,["class" => "img img-circle panel-avatar",]);
            } else {
                echo '<span class="material-icons icon-user-refer">person</span>';
            }
            ?>
            <a class="box-container box-middle box-container-m">
                <div>
                    <div class="panel-heading-title">
                        <h4><?php echo $model->full_name ?></h4>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4" style="padding: 0;">
            <h4>紹介日</h4>
            <a href="#" class="box-container box-middle">
                <div class="box-container box-middle">
                    <i class="material-icons">timelapse</i>
                    <h4 class="block-pc"><?php echo date('Y-m-d', strtotime($model->created_at))?></h4>
                    <h4 class="block-sp" style="font-size: 12px"><?php echo date('Y-m-d', strtotime($model->created_at))?></h4>
                </div>
            </a>
        </div>

        <a class="box-container box-middle col-md-4" style="text-decoration: none; justify-content: center;">
            <div class="box-container box-middle">
                <?php if ($model->status == UserInfo::STATUS_ACTIVE) { ?>
                    <label class="label label-primary">アクティブ済</label>
                <?php } else { ?>
                    <label class="label label-default">アクティブ中</label>
                <?php } ?>
            </div>
        </a>
    </div>
</div>
