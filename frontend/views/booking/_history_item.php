<?php use common\entities\shop\ShopInfo;
use common\entities\worker\WorkerInfo;
use common\entities\worker\WorkerMappingShop;
use common\helper\HtmlHelper;

$workerMappingShop = WorkerMappingShop::findOne(['worker_id' => $model->slotInfo->worker_id, 'shop_id' => $model->slotInfo->shop_id]);
$workerMappingShop = $workerMappingShop == null ? WorkerMappingShop::findOne(['worker_id' => $model->slotInfo->worker_id]) : $workerMappingShop;
$ref_id = $workerMappingShop == null ? '' : $workerMappingShop->worker_id;
?>
<div class="panel-group mb10">
    <div class="panel panel-default">
        <div class="panel-heading box-container box-space-between box-middle">
            <div class="box-container box-middle">
                <?php echo
                    HtmlHelper::img(App::$app->urlManager->createUrl([
                        "file/view?id=".$model->slotInfo->workerInfo->worker_id,
                    ]), [
                        "class" => "img img-circle panel-avatar",
                    ]); ?>
                <div class="box-container box-middle box-container-m">
                    <div>
                        <div class="panel-heading-title">
                            <h4 class="block-pc" style="white-space: nowrap;">
                                <a href="<?php echo $model->slotInfo->workerInfo->status == WorkerInfo::STATUS_ACTIVE ? ShopInfo::getShopUrl($model->slotInfo->shop_id).
                                    $ref_id : '#' ?>"
                                    <?php if ($model->slotInfo->workerInfo->status == WorkerInfo::STATUS_ACTIVE) { ?>target="_blank" <?php } ?>><?php echo $model->slotInfo->workerInfo->worker_name ?></a>
                            </h4>
                            <h4 class="block-sp">
                                <a href="<?php echo $model->slotInfo->workerInfo->status == WorkerInfo::STATUS_ACTIVE ? ShopInfo::getShopUrl($model->slotInfo->shop_id).
                                    $ref_id : '#'; ?>"
                                   <?php if ($model->slotInfo->workerInfo->status == WorkerInfo::STATUS_ACTIVE) { ?>target="_blank" <?php } ?>><?php echo $model->slotInfo->workerInfo->worker_name ?></a>
                                ・<?php echo $model->slotInfo->shopInfo->shop_name ?>
                            </h4>
                            <h5 class="sub-title">
                                <?php echo $model->courseInfo->course_name ?>
                            </h5>
                        </div>
                        <div class="panel-heading-time box-container box-middle">
                            <i class="material-icons block-sp">timelapse</i>
                            <h4 class="block-sp"><?php echo date('Y-m-d', strtotime($model->created_at)) ?></h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4" style="padding: 0;">
                <h4 class="block-pc"><?php echo $model->slotInfo->shopInfo->shop_name ?></h4>
                <a href="#" class="box-container box-middle">
                    <div class="box-container box-middle">
                        <i class="material-icons block-pc">timelapse</i>
                        <h4 class="block-pc"><?php echo date('Y-m-d', strtotime($model->created_at)) ?></h4>
                    </div>
                </a>
            </div>


            <a class="box-container box-middle" style="text-decoration: none;">
                <div class="box-container box-middle">
                    <?php echo $model->slotInfo->workerInfo->getTotalRating($model->slotInfo->workerInfo->rating) ?><span class="material-icons" style="vertical-align: middle; color: #ffd700;">star_rate</span>
                    <?php if ($model->getRating()->count() != 0) { ?>
                        <button type="button" class="btn btn-primary btn-preview-memo btn-rated"
                                data-booking-id = "<?php echo $model->booking_id ?>" data-worker-id = "<?php echo $model->slotInfo->worker_id ?>" data-type="get-rating" onclick="openModalRating(this)"
                                <?php echo strtotime($model->slotInfo->date . " " . $model->slotInfo->end_time) > time() ? 'disabled' : " " ?>>Đã đánh giá
                        </button>
                    <?php } else { ?>
                        <button type="button" class="btn btn-primary btn-preview-memo"
                                data-booking-id = "<?php echo $model->booking_id ?>" data-worker-id = "<?php echo $model->slotInfo->worker_id ?>" data-type="open" onclick="openModalRating(this)"
                                <?php echo strtotime($model->slotInfo->date . " " . $model->slotInfo->end_time) > time() ? 'disabled' : " " ?>>Đánh giá
                        </button>
                    <?php } ?>
                </div>
            </a>
        </div>
    </div>
</div>
