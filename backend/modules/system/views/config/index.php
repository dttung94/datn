<?php
/**
 * @var $this \backend\models\BackendView
 * @var $defaultVal []
 */
use common\entities\system\SystemConfig;
use common\helper\HtmlHelper;
use yii\widgets\ActiveForm;
use backend\assets\AppAsset;
use common\components\WebSocketClient;
use common\helper\ArrayHelper;

$assetBundle = AppAsset::register($this);
$this->title = App::t("backend.system_config.title", "Thiết lập hệ thống");
$this->subTitle = App::t("backend.system_config.title", "");
$this->actions = [
    '<button class="btn btn-primary" onclick="toSaveConfig()"><i class="fa fa-save"></i> Lưu</button>',
];
$this->breadcrumbs = [
    [
        "label" => $this->title
    ]
];
$optionSound = "";
$listSound = "";

$isBookSortTime = SystemConfig::getValue(SystemConfig::CATEGORY_BOOKING, SystemConfig::BOOKING_IS_BOOKING_SORT_TIME) == 1 ? 'checked' : '';
//foreach ($sounds as $key => $sound) {
//    $nameSound = $sound['name_sound'];
//    $idSound = $sound['id'];
//    $optionSound .= "<option value='$idSound'>$nameSound</option>";
//    $listSound .= "<p><i class='fa fa-remove remove-sound text-danger' data-sound='$idSound' data-name='$nameSound'></i>|<i class='fa fa-file-audio-o sound' data-sound='$idSound'> $nameSound</i></p>";
//}




$optionEvent = "";

$this->registerJs(
    <<< JS
    var datas = [];
    function saveConfig(category,id,el){
        var params = {
            'category': category,
            'id': id,
            'value': $(el).val()
        };
        datas.push(params);
    }
    $('input[name="CONFIG_SITE_MEMBER-NUMBER_LIMIT_WORKER_REMINDER"]').keypress(function(evt) {
      if (evt.which != 8 && evt.which != 0 && evt.which < 48 || evt.which > 57)
        {
            evt.preventDefault();
        }
    })
    
    function toSaveConfig() {
        if (datas.length > 0) {
            $.ajax({
                url: '/system/config/save',
                method: 'POST',
                data: {datas: datas},
                success: function () {
                    toastr.success('Cập nhật thành công');
                    setTimeout(function() {
						window.location.reload();	
                    }, 500);
                }
            });
        } else {
            toastr.warning('Không có thay đổi');
        }
    }
JS
    , \yii\web\View::POS_END, 'register-js-system-config');
?>
<div class="row">
    <?php
        $categories = [
            'SYSTEM' => 'Hệ thống',

            'BOOKING' => 'Quá trình đặt lịch',
            'TWILIO_APP' => 'Twilio',
            'DURATION_TIME_COURSE' => 'Thời gian của một khung làm việc',
        ];
        foreach ($defaultVal as $category => $ids) {
            if ($category != 'COLOR') {
    ?>
        <div class="col-md-offset-2 col-md-8">
            <div class="portlet light bordered">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-cog font-green-sharp"></i>
                        <span class="">
                            <?php echo isset($categories[$category]) ? $categories[$category] : $category ?>
                        </span>
                        <span class="caption-helper">
                        </span>
                    </div>
                    <div class="tools">
                        <a title="" data-original-title="" href="javascript:;" class="collapse">
                        </a>
                    </div>
                    <div class="actions">
                    </div>
                </div>
                <div class="portlet-body">
                    <form>
                        <?php foreach ($ids as $id => $val) {
                            if ($id != 'SITE_LOGO' && $id != 'HOME_URL') {

                            $config = SystemConfig::getConfig($category, $id); ?>
                                <label class="control-label"><?php echo $config->getAttributeLabel($id); ?></label>

                                <?php
                                    echo HtmlHelper::textInput("$category-$id", $config->value, ["class" => "form-control",
                                        "onchange" => "saveConfig('$category','$id',this)"
                                    ]);

                                ?>
                                <div class="help-block">
                                    <?php echo App::t('backend.system_config.message', 'Lần cập nhật cuối: {datetime}', [
                                        'datetime' => Yii::$app->getFormatter()->asDatetime($config->modified_at),
                                    ]) ?>
                                </div>
                        <?php }
                        }?>
                    </form>
                </div>
            </div>
        </div>
    <?php }
        }?>
</div>

<div class="modal fade" id="listSound" tabindex="-1" role="dialog" aria-labelledby="listSound" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="listSound">サウンドリスト</h3>
            </div>
            <div class="modal-body" style="cursor: pointer; font-size: 15px;">
                <?php echo  $listSound; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary close-list" data-dismiss="modal">確認</button>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="/resource/css/system-config.css" type="text/css">
<script src="/resource/js/croppie.js"></script>




<script src="/resource/js/system-config.js"></script>

<script>
    $(document).ready(function () {
        $('#select-file').on('change', function () {
            document.getElementById('audio_'+$(this).val()).play();
        });

        $('.sound').on('click', function () {
            document.getElementById('audio_'+$(this).attr('data-sound')).play();
        });

        $('#select-event').on('change', function () {
            $.ajax({
                url: '/system/config/get-selected-sound',
                type: "POST",
                data: {event: $(this).val()},
                success: function (res) {
                    $('#select-file').val(res);
                }
            });
        });

        $("#update-event").on('click', function () {
            var sound, event;
            event = $('#select-event').val();
            sound = $('#select-file').val();
            if (event == 0) {
                toastr.error('イベントを選択してください。');
            } else {
                if (confirm('更新はよろしいでしょうか。')) {
                    $.ajax({
                        url: '/system/config/update-event',
                        type: "POST",
                        data: {event: event, sound: sound},
                        success: function () {
                            toastr.success('更新は正常に終了しました。');
                        }
                    });
                }
            }
        });

        $('.remove-sound').on('click', function () {
            var p = $(this).closest('p');
            if (confirm("そのサウンドが削除しますでしょうか。")) {
                $.ajax({
                    url: '/system/config/delete-sound',
                    type: 'POST',
                    data: {id: $(this).attr('data-sound'), name: $(this).attr('data-name')},
                    success: function (res) {
                        toastr.success(res + ' のファイルが削除しました。');
                        p.hide();
                    }
                });
            }
        });

        $('.close-list').on('click', function () {
            window.location.reload();
        });

        $('.make-switch').on('switchChange.bootstrapSwitch', function (event, state) {
            var category, id, value;
            category = $(this).attr('data-category');
            id = $(this).attr('data-id');
            value = state ? 1 : 0;
            $.ajax({
                url: '/system/config/change-status',
                type: 'POST',
                data: {
                    category: category,
                    id: id,
                    value: value
                },
                success: function (res) {
                    toastr.success('保存しました。');
                }
            })
        });
    });

</script>
