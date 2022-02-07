<?php
/**
 * @var $this \backend\models\BackendView
 */
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;

?>
<script type="text/ng-template" id="modal-write-sms-message.html">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true" ng-click="cancel()"></button>
        <h4 class="modal-title">
            <?php echo App::t("backend.booking.title", "Chỉnh sửa tin nhắn") ?>
        </h4>
    </div>
    <div class="modal-body">
        <?php $form = ActiveForm::begin([
            'id' => 'write-sms-message-form',
            'fieldConfig' => [
                'horizontalCssClasses' => [
                    'label' => 'col-sm-4',
                    'offset' => '',
                    'wrapper' => 'col-sm-8',
                    'error' => '',
                    'hint' => '',
                ],
            ]
        ]); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <?php echo Html::a("", "javascript:;", [
                        "class" => "btn btn-default btn-xs margin-top-10",
                        "data-clipboard" => "copy",
                        "ng-repeat" => "param in smsForm.params",
                        "ng-bind" => "param",
                        "ng-click" => "smsForm.content = smsForm.content + '{' + param + '}'",
                    ]); ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <?= Html::textarea("worker_id", "", [
                        "class" => "form-control autosizeme maxlength-validate",
                        "maxlength" => 500,
                        "rows" => "8",
                        "style" => [
                            "resize" => "vertical"
                        ],
                        "ng-model" => "smsForm.content",
                        "placeholder" => "Nội dung tin nhắn",
                    ]) ?>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
    <div class="modal-footer">
        <?php echo Html::button(App::t("backend.booking.button", "Hủy bỏ"), [
            "class" => "btn default",
            "ng-click" => "cancel()",
        ]) ?>
        <?php echo Html::button('<i class="fa fa-send"></i>&nbsp;&nbsp;' . App::t("backend.booking.button", "Gửi"), [
            "class" => "btn blue",
            "ng-class" => "{'disabled':!smsForm.content}",
            "ng-click" => "sendSMS()",
            "ng-disabled" => "!smsForm.content",
        ]) ?>
    </div>
</script>
