<?php
namespace backend\modules\data\controllers;

use backend\filters\BackendAccessRule;
use backend\models\BackendController;
use backend\modules\data\DataModule;
use backend\modules\data\forms\price\CourseForm;
use backend\modules\data\forms\price\CoursePriceCreateForm;
use backend\modules\data\forms\price\CoursePriceForm;
use backend\modules\data\forms\price\OptionFeeForm;
use common\entities\calendar\OptionFee;
use common\entities\user\UserInfo;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;

class PriceController extends BackendController
{
    public $layout = "layout_admin";

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
                            'course',
                            'fee',
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => [
                            'save-course-price',
                            'delete-course-price',
                            'create-course-price',
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

    public function actionCourse()
    {
        $model = new CoursePriceForm();
        return $this->render("course/index", [
            "model" => $model,
        ]);
    }

    public function actionSaveCoursePrice()
    {
        $this->response->format = Response::FORMAT_JSON;
        $model = CoursePriceForm::findOne([
            "course_id" => $this->request->post("course_id"),
            "worker_rank" => $this->request->post("worker_rank"),
            "duration_minute" => $this->request->post("duration_minute"),
        ]);
        if ($model) {
            $model->price = $this->request->post("temp_price", null);
            if ($model->save()) {
                return [
                    "success" => true,
                    "data" => $model,
                ];
            }
        }
        return [
            "success" => false,
        ];
    }

    public function actionDeleteCoursePrice()
    {
        $this->response->format = Response::FORMAT_JSON;
        if (CoursePriceForm::toDeleteCoursePrice($this->request->post("data"))) {
            return [
                "success" => true,
                "message" => \App::t("backend.course.message", "???? x??a"),
            ];
        } else {
            return [
                "success" => false,
                "message" => \App::t("backend.course.message", "C?? l???i khi x??a"),
            ];
        }
    }

    public function actionCreateCoursePrice($course_id)
    {
        $this->response->format = Response::FORMAT_JSON;
        $model = new CoursePriceCreateForm();
        $model->course_id = $course_id;
        if ($model->load($this->request->post(), "") && $model->toSave()) {
            return [
                "success" => true,
                "message" => \App::t("backend.course.message", "???? t???o th??nh c??ng"),
            ];
        } else {
            return [
                "success" => false,
                "message" => \App::t("backend.course.message", "C?? l???i khi t???o"),
                "error" => $model->getErrors(),
            ];
        }
    }
}
