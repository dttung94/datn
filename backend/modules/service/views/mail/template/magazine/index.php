<?php
use backend\assets\AppAsset;
use backend\modules\service\forms\mail\MailTemplateForm;
use common\entities\service\TemplateMail;

/**
 * @var $this \backend\models\BackendView
 * @var $model MailTemplateForm
 */
$this->title = App::t("backend.service_sms.title", "メルマガ送信");
$this->subTitle = App::t("backend.service_sms.title", "");

$this->registerCssFile(
    Yii::$app->assetManager->getBundle(AppAsset::className())->baseUrl . "/pages/css/inbox.css", [
    'depends' => [AppAsset::className()]
]);
$this->breadcrumbs = [
    [
        "label" => $this->title
    ]
];
$this->actions = [];
?>
<script src="/resource/ckeditor/ckeditor.js"></script>
<style>
    #process {
        width: 0;
        background: yellowgreen;
        height: 25px;
        text-align: center;
        font-size: 19px;
    }
</style>
<div class="portlet light">
    <div class="portlet-body">
        <div class="row inbox">
            <form id="data" method="post" enctype="multipart/form-data" style="display: none">
                <input type="file" id="file" name="file" accept="image/*" onchange="read(this)">
                <button type="submit" id="submit_img"></button>
            </form>

            <div class="form-group">
                <div>
                    ※送信人数: <b id="count"><?php echo count($users) ?></b> 人.
                </div>
                <div class="row">
                    <div class="col-md-5">
                        <select class="form-control" id="type_user">
                            <?php foreach ($send as $key => $value): ?>
                                <option value="<?php echo $key?>"><?php echo $value?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3" style="display: none">
                        <input class="form-control date-picker" id="date" type="text" value="<?php echo date('Y-m-d')?>">
                    </div>
                    <div class="col-md-6" style="display: none">
                        <select class="form-control select2me" id="tag" name="tags[]" multiple>
                            <?php foreach ($tags as $tag): ?>
                                <option value="<?php echo $tag?>"><?php echo $tag?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row" style="margin-bottom: 15px; display: none" id="hobbies">
                <?php foreach ($hobbies as $hobby) { ?>
                <div class="col-md-1 item-hobby">
                    <a class="btn btn-default hobby" style="width: 100%" data-id="<?php echo $hobby->data_id ?>" data-value="<?php echo $hobby->value ?>"><?php echo $hobby->value ?></a>
                </div>
                <?php } ?>
            </div>
            <div class="form-group">
                <label>タイトル</label>
                <input type="text" placeholder="タイトル" class="form-control" id="subject">
            </div>
            <div class="form-group">
                <label>本文</label>
                <textarea id="content" rows="20" class="form-control"></textarea>
            </div>
            <div class="form-group">
                <button type="button" id="send" class="btn btn-primary">
                    <i class="fa fa-paper-plane"></i>
                    送信
                </button>
            </div>
            <div class="form-group" style="display: none" id="sending">
                <label id="text_sending"></label>
                <div id="process"></div>
            </div>
        </div>
    </div>
</div>
<script>
    var users = <?php echo json_encode($users)?>;
    CKEDITOR.replace('content',
        {
            toolbar:[
                { name: 'document', items: [ 'Source', '-', 'Save', 'NewPage', 'Preview', 'Print', '-', 'Templates' ] },
                { name: 'clipboard', items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
                { name: 'editing', items: [ 'Find', 'Replace', '-', 'SelectAll', '-', 'Scayt' ] },
                { name: 'forms', items: [ 'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField' ] },
                '/',
                { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'CopyFormatting', 'RemoveFormat' ] },
                { name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl', 'Language' ] },
                { name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
                { name: 'insert', items: [ 'Image', 'Flash', 'HorizontalRule', 'Smiley', 'PageBreak', 'Iframe' ] },
                '/',
                { name: 'styles', items: [ 'Styles', 'Format', 'Font', 'FontSize' ] },
                { name: 'colors', items: [ 'TextColor', 'BGColor' ] },
                { name: 'tools', items: [ 'Maximize', 'ShowBlocks' ] },
                { name: 'about', items: [ 'About' ] }
            ],
            // extraPlugins : 'wordcount',
            height:400,
            resize_enabled:true
            // wordcount: {
            //     showParagraphs: false,
            //     showWordCount: true,
            //     showCharCount: true,
            //     countSpacesAsChars: false,
            //     countHTML: false,
            //     maxWordCount: -1,
            //     maxCharCount: 340
            // }
        }
    );
    $(document).ready(function() {
        setTimeout(function () {
            $('#cke_39').attr('onclick', 'selectImg()');
        }, 1000);

        $("form#data").submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: '/service/mail/import-file',
                type: 'POST',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function (res) {
                    if (res) {
                        var text, content;
                        content = CKEDITOR.instances['content'];
                        text = content.getData() + '<img id="img" src="'+res+'" width="150"/>';
                        content.setData(text);
                    }
                }
            });
        });

        $('#send').on('click', function () {
            var subject, content;
            content = CKEDITOR.instances['content'].getData();
            subject = $('#subject').val();

            if (subject.trim() == '') {
                toastr.error('タイトルは空白ではいけません。');
                return false;
            }

            if (content.replace(/&nbsp;|<p>|<\/p>/g,'').trim() == '') {
                toastr.error('本文は空白ではいけません。');
                return false;
            }

            if (users.length == 0) {
                toastr.error('メールは空白ではいけません。');
                return false;
            }
            $(this).attr('disabled', 'disabled');
            $('#sending').show();
            $('#text_sending').text('Sending...1/'+users.length+' email');
            toSendMagazine(subject, content, 0, users);
        });

        var date, tag;
        date = '<?php echo date('Y-m-d') ?>';
        tag = [];
        $('#type_user').on('change', function () {
            if ($(this).val() == '<?php echo TemplateMail::USER_TIME ;?>') {
                $('#date').closest('div').show();
                $('#tag').closest('div').hide();
                $('#hobbies').closest('div').hide();
                date = $('#date').val();
            } else if ($(this).val() == '<?php echo TemplateMail::USER_TAG ;?>') {
                $('#date').closest('div').hide();
                $('#tag').closest('div').show();
                $('#hobbies').closest('div').hide();
                tag = $('#tag').val();
            } else if ($(this).val() == '<?php echo TemplateMail::USER_FAVORITE ?>') {
                $('#tag').closest('div').hide();
                $('#date').closest('div').hide();
                $('#hobbies').closest('div').show();
            } else {
                $('#tag').closest('div').hide();
                $('#date').closest('div').hide();
                $('#hobbies').closest('div').hide();
            }
            callAjax($(this).val(), date, tag);
        });

        $('#date').on('change', function () {
            var type = $('#type_user').val();
            callAjax(type, $(this).val(), tag);
        });

        $('#tag').on('change', function () {
            var type = $('#type_user').val();
            callAjax(type, date, $(this).val());
        });

        let hobbies = [];
        $('.hobby').on('click', function () {
            var type = $('#type_user').val();
            if (!$(this).hasClass('hb-selected') && $('.hb-selected').length >= 3) {
                toastr.error('好みの女の子を３つまで選択してお願いします。!!!');
                return false;
            }
            var className = $(this).hasClass('hb-selected') ? 'btn btn-default hobby' : 'btn btn-primary hobby hb-selected';
            $(this).attr('class', className);
            if ($(this).hasClass('hb-selected')) {
                hobbies.push($(this).attr('data-id'));
            } else {
                let index = hobbies.indexOf($(this).attr('data-id'));
                if (index > -1) {
                    hobbies.splice(index, 1);
                }
            }

            if (hobbies.length > 0) {
                $.ajax({
                    url: '/service/mail/get-user-follow-hobbies',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        users: '<?php echo json_encode($users)?>',
                        data: hobbies,
                    },
                    success: function (res) {
                        $('#count').text(res.length);
                        $('#type_user_input').val(type);
                        $('#date_input').val(date);
                        users = res;
                    }
                });
            } else {
                callAjax($('#type_user').val(), date, tag);
            }
        });
    });

    function callAjax(type, date, tag) {
        $.ajax({
            url: '/service/mail/get-user-send',
            type: 'get',
            dataType: 'json',
            data: {
                type: type,
                date: date,
                tag: tag.toString(),
                isCheckReceiveEmail: 'mail_coupon_mega',
                typeMail: "<?php echo TemplateMail::TYPE_MAIL_MAGAZINE?>",
            },
            success: function (res) {
                $('#count').text(res.length);
                $('#type_user_input').val(type);
                $('#date_input').val(date);
                users = res;
            }
        })
    }

    function toSendMagazine(subject, content, location, users) {
        var data = {
            subject: subject,
            content: content,
            email: users[location].email,
            name: users[location].name
        };
        $.ajax({
            url: '/service/mail/to-send-magazine',
            type: 'post',
            dataType: 'json',
            data: data,
            success: function () {
                var width, text;
                location += 1;
                width = location/users.length*100+'%';
                text = location == users.length ? 'メールが全て正常に送信しました。' :users.length+'の中で'+location+'を送信しています。';
                $('#process').css('width', width);
                $('#text_sending').text(text);
                if (location == users.length) {
                    $('#send').removeAttr('disabled');
                } else {
                    toSendMagazine(subject, content, location, users);
                }
            }
        })
    }

    function selectImg() {
        $('#file').click();
    }

    function read() {
        $('#submit_img').click();
    }
</script>
