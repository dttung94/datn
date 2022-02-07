<?php
/**
 * @var $this \frontend\models\FrontendView
 * @var $model ShopForm
 * @var $worker_id integer
 * @var $workers \common\entities\worker\WorkerInfo
 */

use frontend\forms\shop\ShopForm;
use common\helper\HtmlHelper;
use common\entities\worker\WorkerInfo;
?>
<?php foreach ($workers

               as $worker) { ?>
    <div id="container_worker_<?php echo $model->date ?>_<?php echo $worker['worker_id'] ?>"
         ng-hide="(slotManager.slotsData['<?php echo $model->date ?>'].slots[<?php echo $worker['worker_id'] ?>]|filter: slotManager.filterCanBooking).length <= 0"
         class="panel-group mb20">
        <div class="panel panel-default">
            <div class="panel-heading box-container box-space-between box-middle">
                <div class="box-container box-middle">
                    <?php echo
                        HtmlHelper::img(App::$app->urlManager->createUrl([
                            "file/view",
                            "id" => $worker['worker_id'],
                        ]), [
                            "class" => "img img-circle panel-avatar",
                        ]); ?>
                    <a data-toggle="collapse"
                       href="#worker_<?php echo $model->date ?>_<?php echo $worker['worker_id'] ?>"
                       class="box-container box-middle">
                        <div>
                            <div class="panel-heading-title">
                                <h4><?php echo $worker['worker_name'] ?></h4>
                            </div>
                            <div class="panel-heading-time box-container box-middle">
                                <i class="material-icons block-sp">timelapse</i>
                                <h4 class="block-sp">
                                    <?php
                                    echo convertTime($worker['work_start_time']) . " - " . convertTime($worker['work_end_time']);
                                    ?>
                                </h4>
                            </div>
                        </div>
                </div>
                <div class="box-container box-middle">
                    <i class="material-icons block-pc">timelapse</i>
                    <h4 class="block-pc">
                        <?php
                        echo convertTime($worker['work_start_time']) . " - " . convertTime($worker['work_end_time']);
                        ?>
                    </h4>
                    <i class="material-icons dropdown-icon">
                        <?php echo $worker_id == $worker['worker_id'] ? "keyboard_arrow_up" : "keyboard_arrow_down"; ?>
                    </i>
                </div>
                </a>

            </div>

            <div id="worker_<?php echo $model->date ?>_<?php echo $worker['worker_id'] ?>"
                 class="panel-collapse collapse <?php echo $worker_id == $worker['worker_id'] ? "in" : ""; ?>">
                <div class="panel-body">
                    <div class="box-container box-space-between mb10"
                         ng-repeat="slot in slotManager.slotsData['<?php echo $model->date ?>'].slots[<?php echo $worker['worker_id'] ?>] | filter:convertTime"
                         ng-show="!showBookedSlot || slot.isCanBooking">
                        <div class="box-container box-middle box-row-wrap">
                            <i class="material-icons">timelapse</i>
                            <h4>
                                <span style="font-size: 11px;font-style: italic;">({{slot.date}})</span><br/>
                                {{slot.start_time}} - {{slot.duration_minute}} phút
                            </h4>
                        </div>

                        <div class="box-container box-middle">
                            <?php echo HtmlHelper::a(
                                App::t("frontend.shop.button", "Đặt lịch"),
                                "javascript:;", [
                                    "class" => "btn btn-default",
                                    "ng-class" => "slot.isCanBooking?'btn-primary' : 'disabled'",
                                    "ng-click" => "openOnlineBookingModal(slot)",
                                ]
                            ) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
