<?php

use yii\web\View;
use yii\widgets\LinkPager;
use yii\widgets\ListView;
use common\entities\worker\WorkerMappingShop;

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
<?php
    echo ListView::widget([
        'dataProvider' => $model,
        'itemView' => '_history_item',
        'layout' => '{items}<div class="col-12 text-center">{pager}</div>',

        'pager' => [
            'maxButtonCount' => 4,
            'options' => [
                'class' => 'pagination justify-content-center'
            ],
            'linkOptions' => ['class' => 'page-link'],
            'pageCssClass' => 'page-item'
        ],
    ]);
?>

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Đánh giá</h4>
            </div>
<!--            <form action="/booking/save-rating" id="save-rating" method="post">-->
                <div class="modal-body">
                    <input type="text" id="booking-id" name="booking_id" hidden value="">
                    <input type="text" id="worker-id" name="worker_id" hidden value="">
                    <input type="text" id="rating-id" name="rating_id" hidden value="">
                    <div class="box-container box-space-between mb20">
                        <div class="box-sm-30 box-xs-40 box-container box-middle">
                            <span class="text-blue">1．Thái độ phục vụ</span>
                        </div>

                        <div class="box-sm-75 box-xs-65">
                            <input name="behavior" id="behavior" type="hidden" class="rating" data-stop="5"/>
                        </div>
                    </div>

                    <div class="box-container box-space-between mb20">
                        <div class="box-sm-30 box-xs-40 box-container box-middle">
                            <span class="text-blue">2．Kỹ thuật</span>
                        </div>

                        <div class="box-sm-75 box-xs-65">
                            <input name="technique" id="technique" type="hidden" class="rating" data-stop="5"/>
                        </div>
                    </div>

                    <div class="box-container box-space-between mb20">
                        <div class="box-sm-30 box-xs-40 box-container box-middle">
                            <span class="text-blue">3．Dịch vụ</span>
                        </div>

                        <div class="box-sm-75 box-xs-65">
                            <input name="service" id="service" type="hidden" class="rating" data-stop="5"/>
                        </div>
                    </div>
                    <div class="box-container box-space-between mb20">
                        <div class="box-sm-30 box-xs-40 box-container box-middle">
                            <span class="text-blue">4．Về phí dịch vụ</span>
                        </div>

                        <div class="box-sm-75 box-xs-65">
                            <input name="price" id="price" type="hidden" class="rating" data-stop="5"/>
                        </div>
                    </div>
                    <div class="box-container box-space-between mb20">
                        <div class="box-sm-30 box-xs-40 box-container box-middle">
                            <span class="text-blue">5．Mức độ hài lòng</span>
                        </div>

                        <div class="box-sm-75 box-xs-65">
                            <input name="satisfaction" id="satisfaction" type="hidden" class="rating" data-stop="5"/>
                        </div>
                    </div>


                    <div class="box-container box-space-between mb20">
                        <div class="box-sm-30 box-xs-40 box-container box-middle">
                            <span class="text-blue">Góp ý</span>
                        </div>

                        <div class="box-sm-65 box-xs-55">
                            <textarea type="text" class="form-control text-input" name="memo" id="memo" placeholder="Góp ý (nếu có)" maxlength="200" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Hủy bỏ</button>
                    <button type="button" class="btn btn-primary" id="btn-save-rating">Lưu</button>
                </div>
<!--            </form>-->
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
