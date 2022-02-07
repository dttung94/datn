<?php

use common\entities\user\UserConfig;
use common\entities\worker\WorkerInfo;
use common\helper\HtmlHelper;
use yii\web\View;

$this->registerCssFile("@web/resource/css/rating.css",
    [
        'position' => View::POS_HEAD,
    ]
);

$this->registerJsFile(
    '@web/resource/js/bootstrap-rating.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);
?>
<div class="list-worker-new panel-group mb20">
    <h2 class="text-center" style="margin-bottom: 20px">人気の新人</h2>
    <?php
    echo $this->render("_recommend_item", [
        'model' => $listWorkerNew,
        'workerRemind' => $workerRemind,
    ]);
    ?>
</div>

<div class="list-worker-new panel-group mb20">
    <h2 class="text-center" style="margin-bottom: 20px">総合評価の高い子</h2>
    <?php
        echo $this->render("_recommend_item", [
            'model' => $listWorkerRatingInfo,
            'workerRemind' => $workerRemind,
        ]);
    ?>
</div>

<div class="list-worker-new panel-group mb20">
    <h2 class="text-center" style="margin-bottom: 20px">今人気な子</h2>
    <?php
        echo $this->render("_recommend_item", [
            'model' => $listWorkerNotBookingInfo,
            'workerRemind' => $workerRemind,
        ]);
    ?>
</div>

<div class="list-worker-new panel-group mb20">
    <h2 class="text-center" style="margin-bottom: 20px">好みの近い人気嬢</h2>
    <?php
        echo $this->render("_recommend_item", [
           'model' => $listWorkerInfoUserNotBook,
            'workerRemind' => $workerRemind,
        ]);
    ?>
</div>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-shop">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">店舗リスト</h4>
            </div>
            <div class="modal-body" style="height: auto !important;">
                <table id="info-worker-mapping-shop" class="table table-striped text-center"></table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">キャンセル</button>
            </div>
        </div>
    </div>
</div>

<div id='loader' class="loading">
    <img src='/resource/images/loading.gif' width="100px" height="100px">
</div>

<?php
$this->registerJs(
    "$('input[type=hidden]').rating();",
    View::POS_READY
);
$this->registerJsFile(
    '@web/resource/js/rating.js',
    [
        'depends' => [\yii\web\JqueryAsset::className()],
        'position' => View::POS_END,
    ]
)
?>
