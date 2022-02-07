<?php use common\entities\shop\ShopInfo;
use common\entities\worker\WorkerMappingShop;
use common\helper\HtmlHelper;
use common\entities\worker\WorkerInfo;

foreach ($model as $item) {
    $shop = $item->getMappingShops()->where([WorkerMappingShop::tableName().'.status' => WorkerMappingShop::STATUS_ACTIVE])->all();
    ?>
    <div class="panel panel-default">
        <div class="panel-heading box-container box-space-between box-middle">
            <div class="box-container box-middle box-width-4 <?php echo count($shop) > 1 ? 'box-info-worker' : '' ?>" data-worker-id="<?php echo $item['worker_id'] ?>">
                <?php
                    if (count($shop) == 1) {
                        echo HtmlHelper::a(
                            HtmlHelper::img(App::$app->urlManager->createUrl([
                                "file/".$item['avatar']."/avatar/view",
                            ]), [
                                "class" => "img img-circle panel-avatar",
                            ]), ShopInfo::getShopUrl($shop[0]->shop_id) . $shop[0]->ref_id, ["target" => "_blank",]
                        );
                    } else {
                        echo HtmlHelper::a(
                            HtmlHelper::img(App::$app->urlManager->createUrl([
                                "file/".$item['avatar']."/avatar/view",
                            ]), [
                                "class" => "img img-circle panel-avatar",
                            ]));
                    }
                ?>
                <a class="box-container box-middle box-container-m" href="<?php echo count($shop) == 1 ? ShopInfo::getShopUrl($shop[0]->shop_id) . $shop[0]->ref_id : '#' ?>" <?php echo count($shop) == 1 ? 'target="_blank"' : '' ?>>
                    <div>
                        <div class="panel-heading-title">
                            <h4><?php echo $item['worker_name'] ?></h4>
                        </div>
                    </div>
                </a>
            </div>
            <a class="box-container box-middle" style="text-decoration: none;">
                <?php echo $item->getTotalRating($item->rating) ?><span class="material-icons" style="vertical-align: middle; color: #ffd700;">star_rate</span>
            </a>
            <a href="#" class="box-container box-middle box-width-4">
                <div class="box-container box-middle">
                    <button id="btn-remind-<?php echo $item['worker_id'] ?>" type="button" class="btn btn-primary btn-preview-memo"
                        <?php if ($workerRemind) {echo in_array($item['worker_id'], $workerRemind) || $item['status'] != WorkerInfo::STATUS_ACTIVE ? "disabled" : '';} ?>
                            data-worker-id = "<?php echo $item['worker_id'] ?>"
                            onclick="receiveCalendarWorker(this)">出勤リマインダー
                    </button>
                </div>
            </a>
        </div>
        <!--        code mobile old-->
        <!--        <div class="box-container box-middle block-sp">-->
        <!--            <button type="button" class="btn btn-primary btn-preview-memo" --><?php //if ($workerRemind) {in_array($item['worker_id'], $workerRemind) ? "disabled" : '';} ?>
        <!--                    data-worker-id = "--><?php //echo $item['worker_id'] ?><!--"-->
        <!--                    onclick="receiveCalendarWorker(this)">出勤リマインダー-->
        <!--            </button>-->
        <!--            <button type="button" class="btn btn-primary btn-preview-memo"-->
        <!--                    data-worker-id="--><?php //echo $item['worker_id'] ?><!--"-->
        <!--                    onclick="openModalRatingRecommend(this)">総合評価-->
        <!--            </button>-->
        <!--        </div>-->
    </div>
<?php } ?>