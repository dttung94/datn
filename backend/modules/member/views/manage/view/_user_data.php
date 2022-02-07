<?php

use yii\helpers\Html;
use common\entities\calendar\CouponInfo;
use common\entities\user\UserInfo;


$user_id = $data['user_id'];
$phone_number = $data['phone_number'];
$email = $data['email'];
$isOperator = 0;
$users = App::$app->user->identity;
$isOperator = 0;
if ($users->role == UserInfo::ROLE_OPERATOR) {
    $isOperator = 1;
}
?>
<style>
    .flex-container {
        display: flex;
        flex-direction: column;
        width: 300px;
    }
</style>
<div id="user-data">
    <div class="portlet light">
        <div class="portlet-title">
            <div class="caption">Thông tin thành viên</div>
        </div>
        <div class="portlet-body">
            <table class="table table-striped">
                <tbody>
                    <tr>
                        <td>Tên</td>
                        <td><?php echo $data['full_name'] ?></td>
                    </tr>
                    <tr>
                        <td>Số điện thoại</td>
                        <td><?php
                            echo "
                                <span copy-value='$phone_number'
                                copy-clipboard='copy'
                                data-clipboard-text='{$phone_number}'>
                                <i class='fa fa-phone'></i> $phone_number</span>
                            ";
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td><?php
                            echo "
                                <span copy-value='$email'
                                copy-clipboard='copy'
                                data-clipboard-text='{$email}'>
                                <i class='fa fa-envelope'></i> $email</span>
                            ";
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Lần cuối cùng đặt lịch</td>
                        <td><?php echo $data['last_booking'] == 0 ? 'N/A' : $data['last_booking'] ?></td>
                    </tr>
                    <tr>
                        <td>Trạng thái</td>
                        <td>
                        <?php
                        $html = "";
                        switch ($data['status']) {
                            case UserInfo::STATUS_CONFIRMING:
                                $html = Html::label(App::t("backend.member.label", "Trong quá trình xác thực"), null, [
                                    "class" => "label label-default"
                                ]);
                                break;
                            case UserInfo::STATUS_ACTIVE:
                                $html = Html::label(App::t("backend.member.label", "Đang hoạt động"), null, [
                                    "class" => "label label-primary"
                                ]);
                                break;
                            default:
                                $html = "";
                                break;
                        }
                        echo $html;
                        ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Tổng số lượt đặt lịch</td>
                        <td>
                        <?php
                            if ($data['total_booking'] > 0) {
                                $html = Html::a(
                                    App::t("backend.member.label", "{totalBooking} lượt", [
                                        "totalBooking" => $data['total_booking'],
                                    ]),
                                    App::$app->urlManager->createUrl([
                                        "calendar/booking/history",
                                        "BookingHistorySearchForm[filter_user_id]" => $user_id,
                                        "target" => "_blank",
                                    ])
                                );
                            } else {
                                $html = 0;
                            }
                            echo $html;
                        ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Tổng tiền phí dịch vụ</td>
                        <td><?php if ($data['total_money'] > 0) {
                                echo $data['total_money']. ' VNĐ';
                            }else{
                                echo '0 VNĐ';
                            }?></td>
                    </tr>
                    <tr>
                        <td>Ngày khởi tạo</td>
                        <td><?php echo date('Y-m-d H:i:s', strtotime($data['created_at'])) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">タグ追加</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="label-control">会員</label>
                            <input type="text" readonly id="name_user_tag" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="label-control">タグ</label>
                            <input type="text" id="your_tag" class="form-control" autofocus>
                            <span id="error_add" style="color: red;"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">閉じる</button>
                <button type="button" class="btn btn-primary" id="confirm_save"><i class="fa fa-save"></i>　セープ</button>
            </div>
        </div>
    </div>
</div>
<script>
    var listTag = [];
    function removeTagUser(userId, span) {
        var isOperator = <?php echo $isOperator?>;
        if (isOperator) {
            toastr.error("このアクションの実行は許可されていません");
            return false;
        } else{
            if (confirm('タグを削除しますか。')) {
                var td, tags, str = [], data;
                td = $(span).closest('td');
                $(span).closest('label').remove();
                tags = $(td).find('.text-tag-user');
                $.each(tags, function (key, value) {
                    str.push(value.innerHTML);
                });
                data = str.join(",");
                $.ajax({
                    url: "/member/manage/update-tag",
                    type: "POST",
                    data: {
                        tags: data,
                        user_id: userId
                    }
                });
            }
        }
    }

    function openModalTagUser(name, userId, addTag) {
        listTag = $(addTag).closest('td').find('.text-tag-user');
        $('#name_user_tag').val(name);
        $('#confirm_save').attr('onclick', 'addTagUser(\''+userId+'\')');
    }

    function addTagUser(userId) {
        var tag, tags, str = [];
        var isOperator = <?php echo $isOperator?>;
        if (isOperator) {
            toastr.error("このアクションの実行は許可されていません");
            return false;
        }
        tag = $('#your_tag').val();
        if (tag.trim() !== "") {
            $.each(listTag, function (key, value) {
                str.push(value.innerHTML);
            });
            str.push(tag);
            tags = str.join(",");
            $.ajax({
                url: "/member/manage/update-tag",
                type: "POST",
                data: {
                    tags: tags,
                    user_id: userId
                },
                success: function () {
                    window.location.reload();
                }
            });
        } else {
            $('#your_tag').val("");
            $("#error_add").css('display', 'block').html('タグを入力してください。').fadeOut(2500);
        }
    }
</script>