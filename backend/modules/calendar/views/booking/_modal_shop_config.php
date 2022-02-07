<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model CalendarForm
 */
use backend\modules\calendar\forms\booking\CalendarForm;
use common\entities\shop\ShopInfo;
use yii\bootstrap\Html;

$minutes  = ShopInfo::getFreeBookingMinutes();
?>
<script type="text/ng-template" id="modal-shop-config.html">
    <div class="modal-header">
        <button type="button" class="close" aria-hidden="true"
                ng-click="closeModal()"></button>
        <h4 class="modal-title">フリー予約店舗設定</h4>
    </div>
    <div class="modal-body form">
        <form action="#" class="form-horizontal form-bordered">
            <div class="form-body">
                <div class="form-group"
                     ng-repeat="shop in shops"
                     ng-class="{'last':$last}">
                    <label class="control-label col-md-5"
                           ng-bind="shop.shop_name"></label>
                    <div class="col-md-7">
                        <?php echo Html::dropDownList("shop-config-free-booking", null, $minutes, [
                            'convert-to-number' => 'convert-to-number',
                            "ng-model" => "shop.isAllowFreeBooking",
                            'class' => 'form-control'
                        ]) ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn default"
                ng-click="closeModal()">Hủy bỏ
        </button>
        <button type="button" class="btn blue"
                ng-click="saveConfig()">
            <i class="fa fa-save"></i>&nbsp;&nbsp;Lưu
        </button>
    </div>
</script>