<?php
namespace backend\modules\worker\controllers;

use backend\filters\BackendAccessRule;
use backend\models\BackendController;
use backend\modules\coupon\forms\CouponForm;
use backend\modules\member\forms\MemberFollowWorkerForm;
use backend\modules\worker\forms\WorkerRatingHistoryForm;
use backend\modules\worker\WorkerModule;
use common\entities\calendar\Rating;
use common\entities\shop\ShopCalendar;
use common\entities\shop\ShopInfo;
use common\entities\user\UserConfig;
use common\entities\user\UserInfo;
use common\entities\worker\WorkerMappingShop;
use common\helper\StringHelper;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use backend\modules\worker\forms\WorkerForm;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use yii\db\Query;

class ManageController extends BackendController
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
                    "module" => WorkerModule::MODULE_ID
                ],
                'rules' => [
                    [
                        'actions' => [
                            'index',
                            'view',
                            'switch-status',
                            'recent-rating',
                            'rating',
                            'user-follow',
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => [
                            'update',
                            'create',
                            'delete',
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'index' => ['post', 'get'],
                    'switch-status' => ['post'],
                    'user-follow' => ['post', 'get'],
                ],
            ],
        ];
    }

    public function actionRecentRating($id)
    {
        $this->layout = 'layout_base';
        $query = WorkerForm::getRecentRating($id);
        $data = $query->all();
        return $this->render("rating/recent-rating", [
            'data' => $data,
            'id' => $id
        ]);
    }

    public function actionRating()
    {
        $model = new WorkerRatingHistoryForm();
        $model->load($this->request->post());
        $model->load($this->request->get());
        return $this->render("rating/index", [
            "model" => $model,
        ]);
    }

    public function actionIndex()
    {
        $form = new WorkerForm();
        $form->load($this->request->get());
        $form->load($this->request->post());
        $shops_name = array_column(
            (new Query())
                ->from(ShopInfo::tableName())
                ->select(['shop_id', 'shop_name'])
                ->all(),
            'shop_name', 'shop_id'
        );
        return $this->render('list/index', [
            'model' => $form,
            "shops_name" => $shops_name,
        ]);
    }

    public function actionView($id)
    {
        $form = $this->findModel($id);
        $rating = Rating::find()->where(['worker_id' => $id])->all();
        $ratingCount = count($rating);
        $behavior = 0; $technique = 0; $service = 0; $price = 0; $satisfaction = 0;
        $dataChart = [];
        foreach ($rating as $item) {
            $behavior += $item->behavior;
            $technique += $item->technique;
            $service += $item->service;
            $price += $item->price;
            $satisfaction += $item->satisfaction;
        }
        $dataChart = [
            [
                'category' => 'Thái độ phục vụ',
                'rating' => $ratingCount > 0 ? $behavior/$ratingCount : $behavior,
            ],
            [
                'category' => 'Kỹ thuật',
                'rating' => $ratingCount > 0 ? $technique/$ratingCount : $technique,
            ],
            [
                'category' => 'Dịch vụ',
                'rating' => $ratingCount > 0 ? $service/$ratingCount : $service,
            ],
            [
                'category' => 'Chi phí dịch vụ',
                'rating' => $ratingCount > 0 ? $price/$ratingCount : $price,
            ],
            [
                'category' => 'Mức độ hài lòng',
                'rating' => $ratingCount > 0 ? $satisfaction/$ratingCount : $satisfaction,
            ],
        ];
        return $this->render("view/index", [
            "model" => $form,
            "dataChart" => $dataChart,
        ]);
    }

    public function actionUserFollow($id)
    {
        $listUserFollow = new MemberFollowWorkerForm();
        $listUserFollow->load($this->request->get());
        $listUserFollow->load($this->request->post());
        return $this->render('view/user_follow', [
            'model' => $listUserFollow,
            'worker_id' => $id,
        ]);
    }

    public function actionCreate()
    {
        $modelShop = new ShopInfo();
        $shopIds = array_keys($modelShop->getListShop());
        $form = new WorkerForm();
        $form->status = WorkerForm::STATUS_ACTIVE;
        $form->prepareData();
        $dataShops = [];
        foreach ($form->shops as $key => $value) {
            if (in_array($key, $shopIds)) {
                $dataShops[$key] = $value;
            }
        }
        $form->shops = $dataShops;
        if ($form->load(\Yii::$app->request->post())) {
            if ($form->toSave()) {
                return $this->redirect([
                    'index',
                ]);
            }
        }
        return $this->render('create', [
            'model' => $form
        ]);
    }

    public function actionUpdate($id)
    {
        $modelShop = new ShopInfo();
        $shopIds = array_keys($modelShop->getListShop());
        $form = $this->findModel($id);
        $form->prepareData();
        $dataShops = [];
        foreach ($form->shops as $key => $value) {
            if (in_array($key, $shopIds)) {
                $dataShops[$key] = $value;
            }
        }
        $form->shops = $dataShops;
        if ($form->load(\Yii::$app->request->post())) {
            if ($form->toSave()) {
                \App::$app->session->setFlash("ALERT_MESSAGE", \App::t("backend.worker.message", "Nhân viên đã được cập nhật"));
            }
        }
        return $this->render('update', [
            'model' => $form
        ]);
    }

    public function actionDelete($id)
    {
        $form = $this->findModel($id);
        $form->status = WorkerForm::STATUS_DELETED;
        if ($form->toSave()) {
            \App::$app->session->setFlash("ALERT_MESSAGE", \App::t("backend.worker.message", "女の子削除成功"));
        } else {
            \App::$app->session->setFlash("ERROR_MESSAGE", \App::t("backend.worker.message", "女の子削除にエラーがあります"));
        }
        return $this->redirect(['index']);
    }

    public function actionSwitchStatus($id)
    {
        if (!$this->request->isAjax) {
            return $this->redirect("index");
        }
        $this->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        if ($model->toToggleStatus()) {
            return [
                "success" => true,
                "message" => \App::t("backend.worker.message", "Đã thay đổi trạng thái hoạt động"),
            ];
        }
        return [
            "success" => false,
            "message" => \App::t("backend.worker.message", "Có lỗi xảy ra khi thay đổi trạng trái"),
        ];
    }

    /**
     * @param $id
     * @return WorkerForm
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = WorkerForm::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(\Yii::t('common.message', 'The requested page does not exist.'));
        }
    }
}
