<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model CalendarForm
 */
use backend\modules\calendar\forms\booking\CalendarForm;
use yii\bootstrap\Html;

?>
<script type="text/ng-template" id="modal-select-timeline-item.html">
    <div class="modal-header">
        <button type="button" class="close" aria-hidden="true"
                ng-click="closeModal()"></button>
        <h4 class="modal-title"
            ng-bind="colData.date + ' ' + colData.hour + ':' + colData.minute"></h4>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-xs-6 col-xs-offset-3">
                <?php echo Html::a(App::t("backend.booking.button", "Tạo khung online"), "javascript:;", [
                    "class" => "btn blue btn-block btn-lg uppercase margin-top-10",
                    "ng-click" => "createBookingSlot()",
                ]) ?>
            </div>
<!--            <div class="col-md-6">-->
<!--                --><?php //echo Html::a(App::t("backend.booking.button", "Tạo lượt đặt lịch offline"), "javascript:;", [
//                    "class" => "btn green btn-block btn-lg uppercase margin-top-10",
//                    "ng-click" => "createOfflineBooking()",
//                ]) ?>
<!--            </div>-->
        </div>
    </div>
</script>