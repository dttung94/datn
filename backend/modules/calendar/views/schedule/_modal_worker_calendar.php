<?php
/**
 * @var $this \backend\models\BackendView
 */
use backend\assets\AppAsset;

$bundle = App::$app->assetManager->getBundle(AppAsset::className());
$this->registerCssFile($bundle->baseUrl . "/pages/css/booking.css?v=7", [
    "depends" => [
        AppAsset::className(),
    ]
]);
$this->registerCss(<<<CSS
.table-scrollable table thead tr td.minute-col, 
.table-scrollable table tbody tr td.minute-col {
    padding: 1px 3px !important;
    font-size: 20px;
    font-weight: 700;
    vertical-align: middle;
    text-align: center;
    color: #EAA95E;
}
CSS
);
?>
<script type="text/ng-template" id="modal-worker-calendar.html">
    <div class="modal-header">
        <button type="button" class="close" aria-hidden="true"
                ng-click="closeModal()"></button>
        <h4 class="modal-title"
            ng-bind="workerScheduleData.title"></h4>
    </div>
    <div class="modal-body">
        <div class="table-scrollable">
            <table class="table">
                <?php echo $this->render("_table_timeline", [
                ]) ?>
                <tbody>
                <tr class="worker-row mix-grid">
                    <td class="minute-col mix"
                        ng-repeat="colData in workerScheduleData.calendars"
                        ng-class="{'hide':colData.isInvisible, 'holiday-time': !colData.isWorkingTime,'working-time': colData.isWorkingTime}"
                        colspan="{{colData.colspan?colData.colspan:1}}"
                        ng-bind="colData.message">
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</script>