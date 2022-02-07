<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model CalendarForm
 */
use backend\modules\calendar\forms\booking\CalendarForm;
use common\entities\system\SystemConfig;
use common\entities\worker\WorkerInfo;
//$activeSlotColor = SystemConfig::getValue(SystemConfig::CATEGORY_COLOR, SystemConfig::SLOT_NONE);
//$offlineAcceptedColor = SystemConfig::getValue(SystemConfig::CATEGORY_COLOR, SystemConfig::OFFLINE_ACCEPTED);
//$onlineAcceptedColor = SystemConfig::getValue(SystemConfig::CATEGORY_COLOR, SystemConfig::ONLINE_ACCEPTED);
//$onlinePendingColor = SystemConfig::getValue(SystemConfig::CATEGORY_COLOR, SystemConfig::ONLINE_PENDING);
//$onlineUpdatingColor = SystemConfig::getValue(SystemConfig::CATEGORY_COLOR, SystemConfig::ONLINE_UPDATING);
//$onlinePendingChangeColor = SystemConfig::getValue(SystemConfig::CATEGORY_COLOR, SystemConfig::ONLINE_PENDING_CHANGE);
//$onlineCanceledColor = SystemConfig::getValue(SystemConfig::CATEGORY_COLOR, SystemConfig::ONLINE_CANCELED);
//$freeAcceptedColor = SystemConfig::getValue(SystemConfig::CATEGORY_COLOR, SystemConfig::FREE_ACCEPTED);
//$freeConfirmingColor = SystemConfig::getValue(SystemConfig::CATEGORY_COLOR, SystemConfig::FREE_CONFIRMING);
//$freeCanceledColor = SystemConfig::getValue(SystemConfig::CATEGORY_COLOR, SystemConfig::FREE_CANCELED);
//$cssBookingColor = '';
//$cssBookingColor .= "
//    .minute-col.slot div.info div.body {background: $activeSlotColor !important;}\n
//    .minute-col.slot.slot-booking-offline.status-accepted div.info div.body {background-color: $offlineAcceptedColor !important;}\n
//    .minute-col.slot.slot-booking-online.status-accepted div.info div.body {background-color: $onlineAcceptedColor !important;}\n
//    .minute-col.slot.slot-booking-online.status-pending div.info div.body {background-color: $onlinePendingColor !important;}\n
//    .minute-col.slot.slot-booking-online.status-updating div.info div.body {background-color: $onlineUpdatingColor !important;}\n
//    .minute-col.slot.slot-booking-online.status-pending-change div.info div.body {background-color: $onlinePendingChangeColor !important;}\n
//    .minute-col.slot.slot-booking-online.status-canceled div.info div.body {background-color: $onlineCanceledColor !important;}\n
//    .minute-col.slot.slot-booking-free.status-accepted div.info div.body {background-color: $freeAcceptedColor !important;}\n
//    .minute-col.slot.slot-booking-free.status-confirming div.info div.body {background-color: $freeConfirmingColor !important;}\n
//    .minute-col.slot.slot-booking-free.status-canceled div.info div.body {background-color: $onlineCanceledColor !important;}\n";
//$this->registerCss($cssBookingColor);
$workerInfo = new WorkerInfo();
$hourFrom = $workerInfo->getScheduleSecond($model->date, $model->shop_ids, true)['startTime'];
?>
<input ng-model="timeConfirmExpired" id="time-confirm-expired" hidden>
<input ng-model="idSound" id="id-sound" hidden>
<div class="row margin-top-10 book-list-wrap">
    <div class="col-xs-2 left worker-wrap">
        <?php echo $this->render("_list_booking_table_list_worker", [
            "model" => $model,
            "listShops" => $listShops,
        ]) ?>
    </div>
    <div class="col-xs-9 right calendar-wrap">
        <div class="table-scrollable-top" id="table-scrollable-top">
            <div class="scroll-div1" id="scroll-div1">
            </div>
        </div>
        <div class="table-scrollable custom-table-booking"
             id="table-booking-manage">
            <table class="table" id="table-calendar">
                <?php echo $this->render("_list_booking_table_timeline", [
                    "model" => $model,
                    "isHeader" => true,
                    "hourFrom" => $hourFrom,
                ]) ?>
                <tbody>
                <tr class="worker-row mix-grid"
                    ng-repeat="worker in workers | limitTo:totalDisplayed"
                    data-worker-id="{{worker.worker_id}}">
                    <td ng-if="getColSpanBefore(worker.shop_id, calendarData[worker.worker_id]) > 0"
                        colspan="{{getColSpanBefore(worker.shop_id, calendarData[worker.worker_id])}}"
                        class="minute-col mix holiday-time background" ng-style="{'background-color':background}">
                    </td>
                    <td ng-repeat="colData in calendarData[worker.worker_id]|orderBy: '+totalMin'|filter:{shop_id:worker.shop_id}"
                        class="minute-col mix {{getColClass(colData)}} width-{{colData.colspan?colData.colspan:1}} {{colData.shop_id?'col-shop-' + colData.shop_id:''}}"
                        colspan="{{colData.colspan?colData.colspan:1}}"
                        ng-init="colData.date = '<?php echo $model->date; ?>'"
                        ng-class="{'hide':colData.isInvisible, 'slot':colData.slotData,'start-hour':colData.minute==0, 'active-slot':colData.slotData.status==10, 'working-time':colData.isWorkingTime,'holiday-time':!colData.isWorkingTime,'selected':selectedColMin == colData.worker_id + '-' + colData.totalMin}"
                        data-worker-id="{{worker.worker_id}}"
                        data-shop-id="{{colData.shop_id}}"
                        data-total-min="{{colData.totalMin}}"
                        data-date="<?php echo $model->date; ?>"
                        data-hour="{{colData.hour}}"
                        data-minute="{{colData.minute}}"
                        data-is-working-time="{{colData.isWorkingTime}}"
                        data-slot-data="{{colData.slotData}}"
                        ng-click="toSelectTimelineItem(colData)"
                        data-drop="true"
                        data-test="{{colData}}"
                        style="{{'background-color:' + colData.colorShop + '!important'}}"
                        data-jqyoui-droppable="{beforeDrop: 'freeBookingManage.checkBookingValid'}"
                        data-jqyoui-options="{hoverClass: 'selected'}"
                        title="<?php echo $model->date; ?> {{colData.hour}}:{{colData.minute}}">
                        <div class="mix-inner"
                             ng-show="colData.slotData">
                            <div class='info'>
                                <div class='heading'>
                                    <span ng-bind="colData.slotData.shopInfo.shop_name"></span>
                                </div>
                                <div class='body'
                                     ng-bind-html="colData.slotData.htmlContent"
                                     style="font-size: 8px !important;"
                                     ng-style="{'background-color':colData.slotData.colorSlot}">
                                </div>
                                <div class="count-down"
                                     ng-if="colData.slotData.is_waiting_confirm">
                                    <count-down
                                            class="count-down-time"
                                            id="count-down-{{colData.slotData.bookingInfo.booking_id}}"
                                            from-number="colData.slotData.total_second_waiting_expired"
<!--                                            callback-done="checkSlotBookingConfirm(colData.slotData.bookingInfo)"-->
                                    ></count-down>
                                </div>
                            </div>
                            <div class="mix-details">
                                <!-- button to delete slot -->
                                <a class="action"
                                   ng-show="!colData.slotData.bookingInfo && colData.slotData.isDeletable"
                                   ng-click='scheduleSlot.deleteSlot(colData)'>
                                    <i class="fa fa-times"></i>
                                </a>
                                <!-- button to edit slot -->
                                <a class="action"
                                   ng-show="!colData.slotData.bookingInfo && colData.slotData.isEditable"
                                   ng-click="scheduleSlot.openFormModal(colData)">
                                    <i class="fa fa-edit"></i>
                                </a>

                                <!-- button to delete booking -->
                                <a class="action"
                                   ng-show="colData.slotData.bookingInfo && colData.slotData.bookingInfo.isDeletable"
                                   ng-click="bookingManage.removeBookingInfo(colData.slotData.bookingInfo)">
                                    <i class="fa fa-times"></i>
                                </a>
                                <!-- button to delete booking -->
                                <a class="action"
                                   ng-show="colData.slotData.bookingInfo && colData.slotData.bookingInfo.isCancelable"
                                   ng-click="bookingManage.cancelBookingInfo(colData.slotData.bookingInfo)">
                                    <i class="fa fa-times"></i>
                                </a>
                                <!-- button to edit booking info -->
                                <a class="action"
                                   ng-show="colData.slotData.bookingInfo && colData.slotData.bookingInfo.isEditable"
                                   ng-click="bookingManage.editBookingInfo(colData.slotData.bookingInfo)">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <!-- button to send free sms to member -->
                                <a class="action"
                                   ng-show="colData.slotData.bookingInfo && colData.slotData.bookingInfo.isCanSendSMS && colData.slotData.bookingInfo.booking_type != 'OFFLINE'"
                                   ng-click='bookingManage.sendSMS(colData)'>
                                    S
                                </a>
                                <!-- button to view booking detail -->
                                <a class="action"
                                   ng-show="colData.slotData.bookingInfo"
                                   ng-click='bookingManage.viewBookingInfo(colData.slotData.bookingInfo.booking_id)'>
                                    <i class="fa fa-info-circle"></i>
                                </a>
                                <!-- button to view/update booking note -->
                                <a class="action"
                                   ng-show="colData.slotData.bookingInfo"
                                   ng-click='bookingManage.updateBookingNote(colData.slotData.bookingInfo.booking_id)'>
                                    <i class="fa fa-quote-left"></i>
                                </a>
                            </div>
                        </div>
                    </td>
                    <td ng-if="getColSpanAfter(worker.shop_id, calendarData[worker.worker_id]) > 0"
                        colspan="{{getColSpanAfter(worker.shop_id, calendarData[worker.worker_id])}}"
                        class="minute-col mix holiday-time background" ng-style="{'background-color':background}">
                    </td>
                </tr>
                </tbody>
                <?php echo $this->render("_list_booking_table_timeline", [
                    "model" => $model,
                    "isHeader" => false,
                    "hourFrom" => $hourFrom,
                ]) ?>
            </table>
        </div>
    </div>
</div>
<script>
    $(window).on('load', function() {
        var top, bottom;
        top = $(".table-scrollable-top");
        bottom = $(".table-scrollable");
        top.scroll(function(){
            bottom.scrollLeft(top.scrollLeft());
        });
        bottom.scroll(function(){
            top.scrollLeft(bottom.scrollLeft());
        });
    });

    $("body").on('DOMSubtreeModified', "count-down", function() {
        var countDown, len, i, time, timeConfirmExpired, idSound;
        countDown = $('.count-down-time');
        timeConfirmExpired = $('#time-confirm-expired').val();
        idSound = $('#id-sound').val();
        len = countDown.length;
        for (i=0; i< len; i++) {
            if (countDown[i].innerHTML === timeConfirmExpired) {
                document.getElementById('audio_'+idSound).play();
            }
        }
    });

</script>
