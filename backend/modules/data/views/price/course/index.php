<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model CoursePriceForm
 */
use backend\modules\data\forms\price\CoursePriceForm;
use common\helper\HtmlHelper;
use backend\assets\AppAsset;

$bundle = App::$app->assetManager->getBundle(AppAsset::className());
$this->registerJsFile($bundle->baseUrl . "/js/controllers/price.js", [
    "depends" => [
        AppAsset::className(),
    ]
]);

$this->title = Yii::t('backend.price.title', "Quản lý dịch vụ", [
]);
$this->subTitle = Yii::t('backend.price.title', "Phí dịch vụ");

$this->breadcrumbs = [
    [
        "label" => $this->title
    ]
];

$this->registerJs(
    <<< JS
    jQuery(document).ready(function(){
    });
JS
    , \yii\web\View::POS_END, 'register-js-course-unit-price');
?>
<?php echo HtmlHelper::beginTag("div", [
    "ng-controller" => "PriceController",
    "ng-init" => "init()"
]); ?>
<?php echo $this->render("_modal_course_form", []) ?>
<?php echo $this->render("_modal_course_price_form", []) ?>
    <div class="portlet light">
        <div class="portlet-title">
            <div class="caption">
                <i class="fa fa-cogs"></i>Định giá từng dịch vụ
            </div>
            <div class="tools">
            </div>
            <div class="actions">
                <a href="javascript:;" class="btn blue btn-sm"
                   ng-click="course.toOpenCourseModal()">
                    <i class="fa fa-plus"></i>Thêm dịch vụ</a>
            </div>
        </div>
        <div class="portlet-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="col-md-3">
                        <h3>Dịch vụ</h3>
                    </div>
                    <div class="col-md-7">
                        <h3>Chi phí</h3>
                    </div>
                    <div class="col-md-2">
                        <h3>Tùy chỉnh</h3>
                    </div>
                </div>
            </div>
            <div class="row"
                 ng-repeat="(courseTypeId, courseType) in data['course-type']">
                <div class="col-md-12">
                    <div class="col-md-3">
                        <h3 style="color: #3598dc">

                                {{$index + 1}}. {{courseType.course_name}}
                        </h3>
                    </div>
                    <div class="col-md-7">
                        <h3>
                            <div>
                                <div class="list-price-course">{{courseType.price}}</div><span>VNĐ</span>
                            </div>
                        </h3>
                    </div>

                    <div class="col-md-2">
                        <h3>
                            <a class="btn btn-xs green margin-right-10"
                               ng-click="course.toOpenCourseModal(courseTypeId)">
                                <i class="fa fa-pencil"></i>
                            </a>
                            <a class="btn btn-xs btn-danger"
                               ng-click="course.toDeleteCourse(courseTypeId)">
                                <i class="fa fa-times"></i>
                            </a>
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php echo HtmlHelper::endTag("div"); ?>