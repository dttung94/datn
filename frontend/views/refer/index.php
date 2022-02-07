<?php

use yii\bootstrap\Html;
use yii\helpers\Url;

?>
<style>
    .icon-user-refer {
        font-size: 30px;
        border: 1px solid #ccc;
        border-radius: 50%;
        padding: 5px;
    }

    label {
        text-shadow: none !important;
        font-size: 13px !important;
        font-weight: 300 !important;
        padding: 5px 10px !important;
        color: #fff;
        font-family: "Open Sans", sans-serif;
        border-radius: 0 !important;
    }

    .label-default {
        background-color: #c6c6c6;
    }
    body{
        display: none;
    }
    .panel-default.profile{
        max-width: 480px;
        margin: 0 auto;
        border: none;
    }
    .fw-900{
        font-weight: 900;
    }
    #link_invite {
        cursor: copy !important;
        background: inherit !important;
        display: block;
        width: 100%;
        height: 34px;
        padding: 6px 12px;
        font-size: 14px;
        line-height: 1.42857143;
        color: #555;
        background-color: #fff;
        background-image: none;
        border: 1px solid #ccc;
        border-radius: 4px;
        -webkit-box-shadow: inset 0 1px 1px rgb(0 0 0 / 8%);
        box-shadow: inset 0 1px 1px rgb(0 0 0 / 8%);
        -webkit-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
        -o-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
        -webkit-transition: border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s;
        transition: border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s;
        transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
        transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s,-webkit-box-shadow ease-in-out .15s;
    }
</style>
<?php

use yii\web\View;
use yii\widgets\ListView;

$this->registerCssFile("@web/resource/css/rating.css",
    [
        'position' => View::POS_HEAD,
    ]
);

?>
<div class="panel panel-default profile">
    <div class="panel-body">
        <div>
            <div style="margin-bottom: 20px; margin-top: 20px;">
                <p>こちらのリンクをオンラインに公開して不特定多数の人に見えるようにした場合アカウントを停止します。</p>
                <div class="form-group" onclick = "actionCopy()">
                    <br>
                    <p class="fw-900">Link Invite</p>
                    <?= Html::textInput("link_invite_code", Url::base(true) . '/site/invite/' . $users->invite_code, [
                        "id" => "link_invite",
                        "class" => "form-control disabled",
                        "disabled" => "disabled",
                        "copy-clipboard" => "copy",
                        "data-clipboard-text" => Url::base(true) . '/site/invite/' . $users->invite_code,
                    ]) ?>
                    <span id="message" style="color: cornflowerblue;"></span>
                </div>
            </div>
        </div>
    </div>
</div>
<br>
<div class="list-worker-new panel-group">
    <h2 class="text-center" style="margin-bottom: 20px">紹介した友達一覧</h2>
    <p class="text-center mb20">紹介した人数：<?php echo $model->getTotalCount() ?>人</p>
    <?php

    echo ListView::widget([
        'dataProvider' => $model,
        'itemView' => '_refer_item',
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
</div>
<script>
    window.onload = function () {
        let pw = prompt('password');
        if (pw != '0011' || pw === null) {
            document.location.href="/";
        }else{
            $('body').css('display', 'block');
        }
    };

    function actionCopy() {
        let $temp = $("<input>");
        let linkInvite = $('#link_invite').val();
        $("body").append($temp);
        $temp.val(linkInvite).select();
        document.execCommand("copy");
        $temp.remove();
        $('#message').css('display', 'block');
        $('#message').html('Đã sao chép '+ linkInvite);
        setTimeout(function(){
            $("#message").fadeOut(1000);
        },1000);
    }
</script>