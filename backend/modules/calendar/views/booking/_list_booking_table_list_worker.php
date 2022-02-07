<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model CalendarForm
 */
use yii\bootstrap\Html;
use common\entities\worker\WorkerConfig;
use backend\modules\calendar\forms\booking\CalendarForm;

?>
<table class="table table-bordered">
    <thead>
    <tr>
        <th style="height: 26px;vertical-align: middle;">
        </th>
    </tr>
    </thead>
    <tbody>
    <tr class="worker-row"
        ng-repeat="worker in workers | limitTo:totalDisplayed">
        <td class="row custom-row-left">
            <div class="name background-{{worker.worker_rank}} rank-{{worker.worker_rank}} col-md-6 name-worker" style="background-color: {{worker.color}}">
                <div class="btn-group custom-btn-group">
                    <a class="worker-name" href="javascript:;" data-toggle="dropdown">
                        <span ng-bind="worker.working_shop_count==1 ? worker.worker_name : worker.worker_name + '「' + worker.working_shop_count + '」'"
                            title="{{worker.worker_name}}">
                        </span>
                        <i class="fa fa-angle-down"></i>
                    </a>
                    <ul class="dropdown-menu" role="menu">
                        <li>
                            <?php echo Html::a('<i class="fa fa-ban"></i> ' . App::t("backend.worker.label", "Nghỉ ca làm việc"), 'javascript:;', [
                                "ng-click" => "workerInfo.toWorkBreak(worker.worker_id, '$model->date')",
                            ]) ?>
                        </li>
                        <li>
                            <?php echo Html::a('<i class="icon-basket"></i> ' . App::t("backend.worker.label", "Tạo khung hàng loạt"), 'javascript:;', [
                                "ng-click" => "workerInfo.toCreateWorkerSlot(worker.worker_id, '$model->date', worker.worker_rank)",
                            ]) ?>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <table class="table worker-info">
                    <tr class="hidden-xs">
                        <td colspan="2"
                            onmousemove="showBtnEditTime(this)"
                            onmouseout="hideBtnEditTime(this)"
                            class="working-time edit-time"
                            style="
                                position: relative;
                                vertical-align: middle">
                            <span>{{worker.startTime}}</span> -
                            <span>{{worker.endTime}}</span>
                            <i class="fa fa-pencil btn-edit edit-time-worker"
                               onclick="setModalTimeWorker(this)"
                               data-shop-id="{{worker.shop_id}}"
                               data-worker-id="{{worker.worker_id}}"
                               data-worker-name="{{worker.worker_name}}"
                               data-start-time="{{worker.startTime}}"
                               data-end-time="{{worker.endTime}}"
                               data-toggle="modal"
                               data-target="#changeTimeWork"
                               style="display: none; cursor: pointer;"></i>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr>
    </tbody>
    <thead>
    <tr>
        <th style="height: 25px;vertical-align: middle;">
        </th>
    </tr>
    </thead>
</table>
<div class="modal fade" id="changeTimeWork" tabindex="-1" role="dialog" aria-labelledby="changeTimeWork" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="exampleModalLabel">
                    【<span id="modal_shop_name"></span>】nhân viên <span id="modal_worker_name"></span>
                </h3>
            </div>
            <div class="modal-body row">
                <input type="hidden" id="modal_worker_id">
                <input type="hidden" id="modal_shop_id">
                <div class="col-md-6">
                    <label class="control-label">Bắt đầu ca</label>
                    <div class="row">
                        <div class="col-md-6">
                            <select class="form-control" id="modal_start_hour"></select>
                        </div>
                        <div class="col-md-6">
                            <select class="form-control" id="modal_start_minute"></select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="control-label">Kết thúc ca</label>
                    <div class="row">
                        <div class="col-md-6">
                            <select class="form-control" id="modal_end_hour"></select>
                        </div>
                        <div class="col-md-6">
                            <select class="form-control" id="modal_end_minute"></select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy bỏ</button>
                <button type="button" class="btn btn-primary" onclick="toChangeTime()">
                    <i class="fa fa-save"></i>Lưu</button>
            </div>
        </div>
    </div>
</div>
<script>
    function showBtnEditTime(element) {
        hideBtnEditTime();
        element.getElementsByTagName('i')[0].style.display = 'block';
    }
    function hideBtnEditTime() {
        $('.edit-time-worker').hide();
    }
    function setModalTimeWorker(element) {
        var shopId, shopName, workerId, name, startTimes, endTimes, i, selectedStart, selectedEnd;
        var startHour = '', startMinute = '', endHour = '', endMinute = '';
        var shops = <?php echo json_encode($listShops)?>;
        workerId = element.getAttribute('data-worker-id');
        shopId = element.getAttribute('data-shop-id');
        name = element.getAttribute('data-worker-name');

        startTimes = convertTimeToArray(element.getAttribute('data-start-time'));
        endTimes = convertTimeToArray(element.getAttribute('data-end-time'));

        for (i=0; i<25; i++) {
            selectedStart = startTimes[0] == i ? 'selected' : '';
            startHour += '<option value="'+i+'"'+selectedStart+'>'+i+' giờ</option>';

            selectedEnd = endTimes[0] == i ? 'selected' : '';
            endHour += '<option value="'+i+'"'+selectedEnd+'>'+i+' giờ</option>';
        }

        for (i=0; i<12; i++) {
            selectedStart = startTimes[1] == i*5 ? 'selected' : '';
            startMinute += '<option value="'+i*5+'"'+selectedStart+'>'+i*5+' phút</option>';

            selectedEnd = endTimes[1] == i*5 ? 'selected' : '';
            endMinute += '<option value="'+i*5+'"'+selectedEnd+'>'+i*5+' phút</option>';
        }

        $('#modal_shop_id').val(shopId);
        $('#modal_shop_name').text(shops[shopId]);
        $('#modal_worker_id').val(workerId);
        $('#modal_worker_name').text(name);
        $('#modal_start_hour').html(startHour);
        $('#modal_start_minute').html(startMinute);
        $('#modal_end_hour').html(endHour);
        $('#modal_end_minute').html(endMinute);
    }

    function toChangeTime() {
        var startHour, startMinute, endHour, endMinute, startTime, endTime;
        startHour = $('#modal_start_hour').val();
        startMinute = $('#modal_start_minute').val();
        endHour = $('#modal_end_hour').val();
        endMinute = $('#modal_end_minute').val();

        startTime = startHour +':'+ startMinute;
        endTime = endHour +':'+ endMinute;

        var data = {
            shop_id: $('#modal_shop_id').val(),
            worker_id: $('#modal_worker_id').val(),
            start_time: startTime,
            end_time: endTime,
            date: '<?php echo $_GET['date'] ?? date('Y-m-d') ?>'
        };

        $.ajax({
            url: '/calendar/worker/change-time-worker',
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function (res) {
                if (res.success) {
                    toastr.success(res.message);
                    $('#changeTimeWork').modal('hide');
                } else {
                    toastr.error(res.message);
                }
            }
        })
    }

    function convertTimeToArray(time) {
        return time.split(':');
    }
</script>
