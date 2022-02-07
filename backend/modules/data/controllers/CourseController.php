<?php
namespace backend\modules\data\controllers;

use backend\filters\BackendAccessRule;
use backend\models\BackendController;
use backend\modules\data\DataModule;
use backend\modules\data\forms\price\CourseForm;
use backend\modules\data\forms\price\CoursePriceForm;
use common\entities\calendar\BookingInfo;
use common\entities\calendar\CourseInfo;
use backend\modules\coupon\forms\CouponForm;
use common\entities\calendar\FreeBookingRequest;
use common\entities\user\UserInfo;
use common\helper\DatetimeHelper;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class CourseController extends BackendController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => BackendAccessRule::className(),
                    "module" => DataModule::MODULE_ID
                ],
                'rules' => [
                    [
                        'actions' => [
                            'load-course-data',
                            'load-course-form',
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => [
                            'save-course-info',
                            'delete-course-info',
                        ],
                        'allow' => true,
                        'roles' => [UserInfo::ROLE_ADMIN],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                ],
            ],
        ];
    }

    public function actionLoadCourseData()
    {
        $this->response->format = Response::FORMAT_JSON;
        return [
            "success" => true,
            "data" => CoursePriceForm::getUnitPriceTable(),
        ];
    }

    public function actionLoadCourseForm($course_id = null)
    {
//        $listShop = new CouponForm();
//        $shops = !empty($listShop->getListShop()) ? array_keys($listShop->getListShop()) : [];
        $this->response->format = Response::FORMAT_JSON;
        $model = CourseForm::findOne($course_id);
        if ($model == null) {
            $model = new CourseForm();
            $model->course_name = "";
            $model->description = "";
//            $model->shop_ids = implode('-', $shops);
        }
//        $model->shop_ids = (array)explode('-', $model->shop_ids);
        return [
            "success" => true,
            "data" => $model->toArray(),
        ];
    }

    public function actionSaveCourseInfo($course_id = null)
    {
        $this->response->format = Response::FORMAT_JSON;
        $model = CourseForm::findOne($course_id);
        $datas = $this->request->post();
        if ($model == null) {
            $model = new CourseForm();
            $model->course_name = "";
            $model->description = "";
        }
        if ($model->load($this->request->post(), '') && $model->toSave()) {
            return [
                "success" => true,
                "data" => $model->toArray(),
                "message" => \App::t("backend.course.message", "Đã tạo dịch vụ mới"),
            ];
        } else {
            return [
                "success" => false,
                "message" => \App::t("backend.course.message", "Lưu không thành công"),
                "error" => $model->getErrors(),
            ];
        }
    }

    public function actionDeleteCourseInfo($course_id)
    {
        $now = DatetimeHelper::now(DatetimeHelper::FULL_DATE).' 00:00:00';
        $this->response->format = Response::FORMAT_JSON;
        $model = CourseForm::findOne($course_id);

        $checkBookingInfo = BookingInfo::find()
            ->where(['course_id' => $course_id])
            ->andWhere(['!=', 'status', BookingInfo::STATUS_CANCELED])
            ->andWhere(['!=', 'status', CourseInfo::STATUS_DELETED])
            ->andWhere(['>=', 'created_at', $now])
            ->exists();

        if ($model != null) {
            if (!$checkBookingInfo && $model->toDelete()) {
                return [
                    "success" => true,
                    "data" => $model->toArray(),
                    "message" => \App::t("backend.course.message", "Đã xóa dịch vụ"),
                ];
            } else {
                return [
                    "success" => false,
                    "message" => \App::t("backend.course.message", "Không thể xóa do đang được khách sử dụng"),
                    "error" => $model->getErrors(),
                ];
            }
        }
        throw new NotFoundHttpException(\App::t("backend.course.message", "The course is not found."));
    }
}