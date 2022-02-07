<?php
/**
 * @var $this \frontend\models\FrontendView
 */

use common\entities\shop\ShopInfo;
use common\entities\worker\WorkerMappingShop;
use common\helper\HtmlHelper;
use yii\web\View;

?>
<script type="text/ng-template" id="modal-booking-history.html">
    <div class="modal-header">
        <h4 class="modal-title">
            <?php echo App::t("frontend.booking-history.title", "Danh sách đặt lịch hiện tại") ?>
        </h4>
    </div>

    <div class="modal-body">
        <div class="panel-group panel-group-modal mb20"
             ng-repeat="booking in bookingHistories">
            <div class="panel panel-default">
                <div class="panel-heading box-container box-space-between box-middle">
                    <div class="box-container box-middle col-md-1">
                        <?php echo HtmlHelper::img(App::$app->urlManager->createUrl([
                                "file/view?id={{booking.worker_id}}",
                            ]), [
                                "class" => "img img-circle panel-avatar",
                            ]) ?>
                    </div>
                    <div class="box-container box-middle col-md-4" style="text-align: center !important;">
                        <div class="edit-modal">
                            <div class="panel-heading-title" style="width: auto !important;">
                                <h4 class="block-pc">
                                    <a href="{{booking.worker_url}}" target="_blank">{{booking.worker_name}}</a>
                                </h4>
                                <h4 class="block-sp" style="font-size: 12px">{{booking.type}}・{{booking.shop_name}}</h4>
                                <h4 class="block-sp">
                                    <a href="{{booking.worker_url}}" target="_blank">{{booking.worker_name}}</a>
                                </h4>
                                <h5 class="sub-title">{{booking.course_name}}</h5>
                                <h4 class="block-sp">{{booking.slotInfo.date}}</h4>
                                <h3 class="block-sp">{{booking.start_time}}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4" style="padding: 0; text-align: center">
                        <h4 style="width: auto !important; text-align: center !important;" class="block-pc">{{booking.shop_name}}</h4>
                        <a href="#" class="box-container box-middle">
                            <div class="box-container box-middle">
                                <i class="material-icons block-pc">timelapse</i>
                                <h4 style="width: auto !important; " class="block-pc">{{booking.slotInfo.date}}</h4>
                            </div>
                        </a>
                        <h3 class="block-pc" style="text-align: center !important;">{{booking.start_time}}</h3>
                    </div>


                    <div class="box-container box-middle col-md-3">
                        <div>
                            <div class="box-container box-middle" style="margin-bottom: 10px">
                                <?php echo HtmlHelper::button(App::t("frontend.booking-history.button", "Chỉnh sửa"), [
                                    "class" => "btn btn-edit btn-block block-pc border-button",
                                    "ng-disabled" => "!booking.isUpdatable",
                                    "ng-click" => "toEditBooking(booking)",
                                ]) ?>
                            </div>
                            <div class="box-container box-middle margin-top-10">
                                <?php echo HtmlHelper::a(App::t("frontend.booking-history.button", "Hủy đặt lịch"), "javascript:;", [
                                    "class" => "btn btn-cancel btn-block block-pc border-button",
                                    "ng-show" => "booking.isCancelable",
                                    "ng-click" => "toCancelBooking(booking)",
                                    "ng-disabled" => "!booking.isCancelableForUser",
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <?php echo HtmlHelper::button(App::t("frontend.booking-history.button", "Đóng"), [
            "class" => "btn btn-default btn-lg btn-block",
            "ng-click" => "closeModal()",
        ]) ?>
    </div>
</script>

<script>
    function checkAttrDisabled() {
        console.log($(this).attr("disabled"));
    }
</script>
