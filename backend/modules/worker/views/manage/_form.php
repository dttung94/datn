<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model WorkerForm
 */
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\helper\ArrayHelper;
use backend\modules\worker\forms\WorkerForm;

$this->registerJs(
    <<< JS
    jQuery(document).ready(function(){
    });
JS
    , \yii\web\View::POS_END, 'register-js-worker-form');
?>
<?php $form = ActiveForm::begin([
    'id' => 'worker-form',
    'layout' => 'horizontal',
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
<?php if ($model->hasErrors()) {
    echo Html::tag("div", $form->errorSummary($model), [
        "class" => "note note-danger"
    ]);
} ?>
    <style>
        input[type=number]::-webkit-inner-spin-button {
            -webkit-appearance: none;
        }
    </style>
    <div class="row">
        <div class="col-md-8">
            <?= $form->field($model, 'worker_name', [])->textInput([
                'maxlength' => 255,
                'class' => 'form-control',
            ]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <?= $form->field($model, 'avatar_file', [])->fileInput([
                'class' => 'form-control',
            ]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <?= $form->field($model, 'description', [])->textarea([
                'class' => 'form-control',
                "rows" => 5
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-offset-3 col-md-8">
            <h3>Làm việc tại:</h3>
        </div>
    </div>
    <div class="row">
        <div class="col-md-offset-2 col-md-8">
            <div class="checker"><span data-check="uncheck" id="select_all_shop"></span></div>
            <label>Tất cả các salon</label>
        </div>
    </div>
<?php foreach ($model->shops as $shop_id => $data) { ?>
    <?php
//        $dataUrl = rtrim($data['shopInfo']['shop_address'], '/').'/sp/profile.php';
//        $urlView = !empty($data['worker_url']) ? $data['worker_url'] : $dataUrl;
        $hidden = $data['isEnable'] ? '' : 'hidden';
        $checked = $data['isEnable'] ? 'checked' : '';
        $dataCheck = $data['isEnable'] ? 'checked' : 'uncheck';
        $valueIsEnable = $data['isEnable'] ? 1 : 0;
    ?>
    <div class="row margin-top-10">
        <div class="col-md-offset-2 col-md-7">
            <div class="row">
                <div class="col-md-3">
                    <?php
                    if ($data["isSwitchable"]) {
                        echo '<div class="checker">';
                        echo '<span data-check="'.$dataCheck.'" class="select-shop-address '.$checked.'" data-address="'.$shop_id.'"></span>';
                        echo '<input type="hidden" value="'.$valueIsEnable.'" class="is-enable-all" name="WorkerForm[shops]['.$shop_id.'][isEnable]" id="is_enable_shop_'.$shop_id.'">';
                        echo '</div>';
                        echo '<label>'.$data['shopInfo']['shop_name'].'</label>';
                    } else {
                        echo Html::activeHiddenInput($model, "shops[$shop_id][isEnable]", [
                            "value" => 1
                        ]);
                        echo Html::checkbox("shops[$shop_id][isEnable]", true, [
                            "class" => "form-control disabled",
                            "ng-init" => "formData.shops[$shop_id]['isEnable']=" . ($data["isEnable"] ? 1 : 0),
                            "label" => ArrayHelper::getValue($data, ["shopInfo", "shop_name"]),
                            "disabled" => "disabled",
                        ]);
                    }
                    ?>
                </div>
                <div class="col-md-2 address-worker shop-<?php echo $shop_id?>" <?php echo $hidden; ?>>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

    <div class="row margin-top-20">
        <div class="col-md-9 col-md-offset-3">
            <?php echo Html::submitButton("<i class='fa fa-save'></i> " . ($model->isNewRecord ? Yii::t('common.button', 'Lưu') : Yii::t('common.button', 'Lưu')), [
                'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary'
            ]) ?>
            <?php echo Html::a(\Yii::t('common.label', 'Hủy bỏ'), Yii::$app->urlManager->createUrl([
                "worker/manage",
            ]), [
                    "class" => "btn btn-default"
                ]
            ); ?>
        </div>
    </div>

<?php ActiveForm::end(); ?>
<script>
    $(document).ready(function () {
        $('.ref-id-change').on('input', function () {
            var workerUrl, refId, dataWorkerUrl, html;
            refId = $(this).val();
            dataWorkerUrl = $(this).attr('data-worker-url');
            workerUrl = $('#'+dataWorkerUrl);
            html = refId === '' ? '' : '?id='+refId;
            workerUrl.val(workerUrl.attr('data-url')+html);
        });

        $('#select_all_shop').on('click', function () {
            if ($(this).attr('data-check') === 'uncheck') {
                $(this).addClass('checked').attr('data-check', 'checked');
                $('.select-shop-address').addClass('checked').attr('data-check', 'checked');
                $('.is-enable-all').val(1);
                $('.address-worker').show();
            } else {
                $(this).removeClass('checked').attr('data-check', 'uncheck');
                $('.select-shop-address').removeClass('checked').attr('data-check', 'uncheck');
                $('.is-enable-all').val(0);
                $('.address-worker').hide();
            }
        });

        $('.select-shop-address').on('click', function () {
            if ($(this).attr('data-check') === 'uncheck') {
                $(this).addClass('checked').attr('data-check', 'checked');
                $('.shop-'+$(this).attr('data-address')).show();
                $('#is_enable_shop_'+$(this).attr('data-address')).val(1);
            } else {
                $(this).removeClass('checked').attr('data-check', 'uncheck');
                $('.shop-'+$(this).attr('data-address')).hide();
                $('#is_enable_shop_'+$(this).attr('data-address')).val(0);
            }
        })
    })
</script>
