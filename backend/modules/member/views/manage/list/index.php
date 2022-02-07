<?php
/**
 * @var $this \backend\datas\BackendView
 * @var $data MemberForm
 * @var $pages MemberForm
 * @var $pageNow MemberForm
 * @var $perPage MemberForm
 * @var $totalNow MemberForm
 */
use backend\modules\member\forms\MemberForm;
use common\entities\user\UserConfig;
use common\entities\user\UserInfo;
use common\helper\ArrayHelper;
use yii\helpers\Html;
use backend\assets\AppAsset;
use common\entities\calendar\CouponInfo;

$bundle = App::$app->assetManager->getBundle(AppAsset::className());
$this->registerJsFile($bundle->baseUrl . "/js/controllers/member.js", [
    "depends" => [
        AppAsset::className(),
    ]
]);
$users = App::$app->user->identity;
$flag = 0;
if ($users->role == UserInfo::ROLE_OPERATOR) {
    $flag = 1;
}
$this->title = Yii::t('backend.member.title', "Quản lý thành viên", [
]);
$this->subTitle = Yii::t('common.label', "");

$this->breadcrumbs = [
    [
        "label" => $this->title
    ]
];

// bắt sự kiện khi bấm vào nút tải danh sách thành viên xuống

$this->actions = $users->role == UserInfo::ROLE_ADMIN ? [
    Html::a("<btn class=\"btn btn-default btn-export-excel\"><i class=\"fa fa-download\"></i> 会員情報をダウンロード</btn>")
] : [];

$this->registerJs(
    <<< JS
    jQuery(document).ready(function(){
    });
JS
    , \yii\web\View::POS_END, 'register-js-member-management');
$keywordSearch = !empty($_GET['keyword']) ? $_GET['keyword'] : '';
$dateSearch = !empty($_GET['last_booking']) ? $_GET['last_booking'] : '';
$statusSearch = !empty($_GET['status']) ? $_GET['status'] : '';
$statusMembers = [
    '' => '',
    MemberForm::STATUS_ACTIVE => Yii::t("common.label", "Hoạt động"),
//    MemberForm::STATUS_SHOP_BLACK_LIST => App::t("backend.member.label", "店舗BL"),
//    MemberForm::STATUS_WORKER_BLACK_LIST => App::t("backend.member.label", "NGリスト"),
    MemberForm::STATUS_VERIFYING => App::t("backend.member.label", "Đang xác thực"),
];
?>
<div class="portlet light"
     ng-controller="MemberController">
    <div class="portlet-body">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <input class="form-control" placeholder="Từ khóa" id="keyword_search_member" value="<?php echo $keywordSearch?>">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <input class="form-control date-picker" style="cursor: pointer" placeholder="Lần đặt lịch cuối cùng" id="date_search_member" value="<?php echo $dateSearch?>">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <select class="form-control select2me" id = 'status_search_member' placeholder="Trạng thái">
                        <?php
                        foreach ($statusMembers as $key => $statusMember) {
                            echo $key == $statusSearch ? '<option value="'.$key.'" selected>'.$statusMember.'</option>' : '<option value="'.$key.'">'.$statusMember.'</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <button class="btn btn-primary" onclick="searchMember()">Tìm kiếm</button>
                </div>
            </div>
            <div class="col-md-12">
                <table class="table table-striped table-bordered" style="color: #666 !important;">
                    <thead style="background: #DDD;">
                    <th style="width: 5px;  text-align: center">#</th>
                    <th class="col-md-1" style="text-align: center"><a class="sort-by" data-sort="full_name">Tên</a></th>
                    <th class="col-md-2" style="text-align: center;"><a class="sort-by" data-sort="last_booking">Lần đặt lịch cuối cùng</a></th>
                    <th style="width: 60px; text-align: center;"><a class="sort-by" data-sort="total_booking">Tổng số lượt đã đặt lịch </a></th>

                    <th style="width: 40px; text-align: center;"><a class="sort-by" data-sort="total_money">Tổng tiền đã chi</a></th>
                    <th style="width: 100px;text-align: center;"><a class="sort-by" data-sort="status">Trạng thái</a></th>
                    <th style="width: 95px;text-align: center;"><a class="sort-by" data-sort="created_at">Ngày khởi tạo</a></th>
                    </thead>
                    <tbody>
                    <?php if ($pages->totalCount == 0): ?>
                        <tr>
                            <td colspan="12">Không tìm thấy kết quả</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data as $key => $user): ?>
                            <tr>
                                <td style="width: 5px; text-align: center"><?php echo ($offset + 1) + $key?></td>
                                <td class="col-md-2" style="text-align: center">
                                    <?php
                                    $userId = $user['user_id'];
                                    $phone_number = $user['phone_number'];
                                    $email = $user['email'];
                                    $html = Html::a($user['full_name'], [
                                        "view",
                                        "id" => $userId
                                    ]);
                                    $html .= "<br/><span copy-value='$phone_number'
                                                        copy-clipboard='copy'
                                                        data-clipboard-text='{$phone_number}'>
                                                        <i class='fa fa-phone'></i> $phone_number</span>";
                                    if (!empty($email)) {
                                        $html .= "<br/><i class='fa fa-envelope'></i> ".$email;
                                    }
                                    echo $html;
                                    ?>
                                </td>
                                <td class="col-md-1" style="text-align: center">
                                    <?php echo $user['last_booking'] == 0 ? 'N/A' : $user['last_booking'] ?>
                                </td>
                                <td style="width: 60px; text-align: center">
                                    <?php
                                    if ($user['total_booking'] > 0) {
                                        $html = Html::a(
                                            App::t("backend.member.label", "{totalBooking} lượt", [
                                                "totalBooking" => $user['total_booking'],
                                            ]),
                                            App::$app->urlManager->createUrl([
                                                "calendar/booking/history",
                                                "BookingHistorySearchForm[filter_user_id]" => $user['user_id'],
                                            ])
                                        );
                                    } else {
                                        $html = 0;
                                    }
                                    echo $html;
                                    ?>
                                </td>
                                <td style="width: 40px; text-align: center">
                                    <?php if($user['total_money'] > 0) {
                                        echo $user['total_money'].' VNĐ';
                                    }else{
                                        echo "0 VNĐ";
                                    }?>
                                </td>
                                <td style="width: 100px; text-align: center">
                                    <?php
                                    $html = "";
                                    switch ($user['status']) {
                                        case MemberForm::STATUS_VERIFYING:
//                                            if ($user['verify_email'] == MemberForm::NOT_VERIFIED && $user['verify_phone'] == MemberForm::NOT_VERIFIED) {
//                                                $html = Html::label(App::t("backend.member.label", "Đang xác thực email & SMS"), null, [
//                                                    "class" => "label label-default"
//                                                ]);
//                                            } elseif ($user['verify_email'] == MemberForm::NOT_VERIFIED && $user['verify_phone'] == MemberForm::VERIFIED) {
//                                                $html = Html::label(App::t("backend.member.label", "Đang xác thực "), null, [
//                                                    "class" => "label label-default"
//                                                ]);
//                                            } else {
//                                                $html = Html::label(App::t("backend.member.label", "SMS認証途中"), null, [
//                                                    "class" => "label label-default"
//                                                ]);
//                                            }
                                                $html = Html::label(App::t("backend.member.label", "Đang xác thực SMS"), null, [
                                                    "class" => "label label-default"
                                                ]);
                                            break;
                                        case MemberForm::STATUS_ACTIVE:
                                            $html = Html::label(App::t("backend.member.label", "Hoạt động"), null, [
                                                "class" => "label label-primary"
                                            ]);
                                            break;
                                    }
                                    echo $html;
                                    ?>
                                </td>
                                <td style="width: 95px; text-align: center">
                                    <?php
                                    echo date('Y-m-d', strtotime($user['created_at']));
                                    echo date('H:i:s', strtotime($user['created_at']));
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
                <?php
                if ($pages->totalCount > 0) {
                    echo '<p>Hiển thị từ <b>'.($offset + 1).'</b> đến <b>'.($offset + count($data)).'</b> trong tổng số <b>'.$pages->totalCount.'</b> thành viên</p>';
                }
                ?>
                <?php
                echo \yii\widgets\LinkPager::widget([
                    'pagination' => $pages,
                ]);
                ?>
            </div>
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
        var flag = <?php echo $flag?>;
        if (flag === 1) {
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
        var tag, tags, str = [], flag;
        flag = <?php echo $flag?>;
        if (flag === 1) {
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

    function searchMember() {
        var searchParams = new URLSearchParams(window.location.search);
        searchParams.set('keyword', $('#keyword_search_member').val().trim());
        searchParams.set('last_booking', $('#date_search_member').val().trim());
        searchParams.set('status', $('#status_search_member').val().trim());
        searchParams.delete('page');
        searchParams.delete('sort');
        searchParams.delete('sort_type');
        window.location = window.location.origin + window.location.pathname + '?' + searchParams.toString();
    }

    /*
    *
    * onclick event download excel file
    * ***/

    $('.btn-export-excel').click(function () {
        if($(this).is('[disabled=disabled]') == false){
            $(this).attr('disabled', true);
            $(this).attr('title', 'ダウンロード中からお待ちください。');
            $("body").css("cursor", "progress");
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "/member/manage/download-template");
            xhr.responseType = "arraybuffer";
            xhr.onload = function () {
                if (this.status === 200) {
                    var blob = new Blob([xhr.response], {type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"}, "imageFilename.xlsx");
                    var objectUrl = URL.createObjectURL(blob);
                    var link = document.createElement("a");
                    link.setAttribute("target", "_blank");
                    link.href = objectUrl;
                    link.download = 'member-list.xlsx';
                    $('.btn-export-excel').removeAttr("disabled");
                    $('.btn-export-excel').removeAttr("title");
                    $("body").css("cursor", "default");
                    link.click();
                }
            };
            xhr.send();
        }
    })

    function getParam(key, data) {
        return data === '' ? '' : key+'='+data+'&';
    }

    $(document).ready(function () {
        $('.permission').on('click', function () {
            var flag = <?php echo $flag?>;
            if (flag === 1) {
                toastr.error('このアクションの実行は許可されていません');
                return false;
            }
        });

        $('.sort-by').on('click', function () {
            sortParam = $(this).attr('data-sort');
            var searchParams = new URLSearchParams(window.location.search);
            searchParams.set('keyword', $('#keyword_search_member').val().trim());
            searchParams.set('coupon_type', $('#date_search_member').val().trim());
            searchParams.set('status', $('#status_search_member').val().trim());

            if (searchParams.has('sort') && searchParams.get('sort') === sortParam) {
                searchParams.set(
                    'sort_type',
                    searchParams.get('sort_type') === 'asc' ? 'desc' : 'asc',
                );
            } else {
                searchParams.set('sort_type', 'asc');
            }
            searchParams.set('sort', sortParam);
            window.location = window.location.origin + window.location.pathname + '?' + searchParams.toString();
        })
    });
</script>
