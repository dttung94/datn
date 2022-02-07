<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model OptionFeeForm
 */
use yii\helpers\Json;
use common\helper\HtmlHelper;
use backend\modules\data\forms\price\OptionFeeForm;
use backend\assets\AppAsset;

$bundle = App::$app->assetManager->getBundle(AppAsset::className());
$this->registerJsFile($bundle->baseUrl . "/js/controllers/fee.js", [
    "depends" => [
        AppAsset::className(),
    ]
]);

$this->title = Yii::t('common.label', "料金管理", [
]);
$this->subTitle = Yii::t('backend.fee.title', "指名料金 延長料金");

$this->breadcrumbs = [
    [
        "label" => $this->title
    ]
];

$unitPriceTableJson = Json::encode($model->getUnitPriceTable());
$this->registerJs(
    <<< JS
    jQuery(document).ready(function(){
    });
JS
    , \yii\web\View::POS_END, 'register-js-option-fee');
?>
<?php echo HtmlHelper::beginTag("div", [
    "ng-controller" => "FeeController",
    "ng-init" => "init($unitPriceTableJson)"
]); ?>
    <div class="portlet light">
        <div class="portlet-body">
            <div class="row">
                <div class="col-md-12">
                    <h3>指名料金（編集不可）</h3>
                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                        <tr>
                            <th style="width: 10%">&nbsp;</th>
                            <th style="width: 30%;"
                                ng-repeat="(workerRankId, workerRankText) in data['worker-rank']">
                                {{workerRankText}}
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr ng-repeat="(key,systemOption) in data['systemOptions']">
                            <td>
                                {{data['option-key'][key]}}
                            </td>
                            <td ng-repeat="(workerRankId, workerRankText) in data['worker-rank']">
                                <span ng-if="systemOption[workerRankId].action != 'edit'">
                                        <a class="edit" href="javascript:;"
                                           ng-click="systemOption[workerRankId].action = 'edit';systemOption[workerRankId].temp_price = systemOption[workerRankId].price;">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        {{systemOption[workerRankId].price|currency:'円':0}}
                                </span>
                                <div class="input-group"
                                     ng-if="systemOption[workerRankId].action == 'edit'">
                                    <input class="form-control" type="text"
                                           ng-model="systemOption[workerRankId].temp_price">
                                    <span class="input-group-btn">
                                        <a class="btn btn-success"
                                           ng-click="toSaveOptionFee(systemOption[workerRankId])">
                                            <i class="fa fa-save"></i>
                                        </a>
                                    </span>
                                    <span class="input-group-btn">
                                        <a class="btn btn-default"
                                           ng-click="systemOption[workerRankId].action = null">
                                            <i class="fa fa-times"></i>
                                        </a>
                                    </span>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php echo HtmlHelper::endTag("div"); ?>