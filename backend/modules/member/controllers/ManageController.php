<?php
namespace backend\modules\member\controllers;

use backend\filters\BackendAccessRule;
use backend\models\BackendController;
use backend\modules\member\forms\AddPrivateCouponForm;
use backend\modules\member\forms\MemberBlackListConfigForm;
use backend\modules\member\forms\MemberExportForm;
use backend\modules\member\forms\MemberForm;
use backend\modules\member\MemberModule;
use common\entities\calendar\BookingInfo;
use common\entities\calendar\FreeBookingRequest;
use common\entities\calendar\FreeBookingRequestCoupon;
use common\entities\calendar\CourseInfo;
use common\entities\service\ServiceMail;
use common\entities\service\ServiceSms;
use common\entities\shop\ShopCalendarSlot;
use common\entities\shop\ShopInfo;
use common\entities\system\CronJobLog;
use common\entities\user\UserConfig;
use common\entities\user\UserInfo;
use common\entities\customer\CustomerInfo;
use common\entities\worker\WorkerInfo;
use common\entities\worker\WorkerMappingShop;
use common\helper\StringHelper;
use common\helper\DatetimeHelper;
use Yii;
use yii\data\Pagination;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\db\Query;
use yii\helpers\Json;

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
                    "module" => MemberModule::MODULE_ID
                ],
                'rules' => [
                    [
                        'actions' => [
                            'index',
                            'view',
                            "load-black-list-form",
                            "load-private-coupon-form",
                            "load-booking-history",
                            "load-coupon-log",
                            'get',
                            "load-list-worker-remind",
                            "load-sms-email-log",
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => [
                            'update',
                            'delete',
                            'approve',
                            'reject',
                            "save-black-list",
                            "save-private-coupon",
                            "update-tag",
                            "booking-days"
                        ],
                        'allow' => true,
                        'roles' => [UserInfo::ROLE_ADMIN, UserInfo::ROLE_MANAGER, UserInfo::ROLE_OPERATOR],
                    ],
                    [
                        'actions' => [
                            'download-template',
                            'switch-status',
                        ],
                        'allow' => true,
                        'roles' => [UserInfo::ROLE_ADMIN],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'index' => ['post', 'get'],
                    'delete' => ['post'],
                    'approve' => ['post'],
                    'reject' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $page = !empty($this->request->get('page')) ? $this->request->get('page') : 1;
        $model = new MemberForm();
        $result = $this->getIndex($page);
        $dataUser = $result['data'];
        return $this->render('list/index', [
            'pages' => $result['pages'],
            'model' => $model,
            'data' => $dataUser,
            'pageNow' => (int)$result['pageNow'],
            'perPage' => (int)$result['perPage'],
            'offset' => $result['offset'],
            'totalCount' => (int)$result['totalCount'],
        ]);
    }

    protected function getIndex($page)
    {
            $params = $this->request->get();
            $perPage = 30;
            $offset = ((int)$page - 1)*$perPage;
            $query = MemberForm::getMemberDetailQuery();
            $totalCount = UserInfo::find()->where(['role' => UserInfo::ROLE_USER])->andWhere(['!=', 'status', UserInfo::STATUS_DELETED]);
            if (!empty($params['status'])) {
                if($params['status'] == MemberForm::STATUS_CONFIRMING_INVITE){
                    $params['status'] = MemberForm::STATUS_CONFIRMING;
                    $query->andWhere(['not', ['ui.referrer_id' => null]]);
                    $totalCount->andWhere(['not', ['referrer_id' => null]]);
                }else if($params['status'] == MemberForm::STATUS_CONFIRMING){
                    $query->andWhere([ 'is', 'ui.referrer_id', null ]);
                    $totalCount->andWhere([ 'is', 'referrer_id', null ]);
                }
                $query->andWhere([
                    "ui.status" => $params['status']
                ]);
                $totalCount->andWhere(['status' => $params['status']]);
            }

            if (!empty($params['keyword'])) {
                $keyword = str_replace('-', '', $params['keyword']);
                $userIdFromUsedShop = $this->getUsedShopsFromKeyword($keyword);
                $query->andFilterWhere([
                    'or',
                    ['LIKE', 'ui.full_name', $keyword],
                    ['LIKE', 'ui.phone_number', $keyword],
                    ['LIKE', 'ui.tag', $keyword],
                    ['IN', 'ui.user_id', $userIdFromUsedShop],
                ]);

                $totalCount->andFilterWhere([
                    'or',
                    ['LIKE', 'full_name', $keyword],
                    ['LIKE', 'phone_number', $keyword],
                    ['LIKE', 'tag', $keyword],
                    ['IN', 'user_id', $userIdFromUsedShop],
                ]);
            }

            if (!empty($params['last_booking'])) {
                $queryBooking = BookingInfo::find()
                    ->select('member_id')
                    ->distinct()
                    ->where(['status' => BookingInfo::STATUS_ACCEPTED])
                    ->andWhere(['>=', 'created_at', $params['last_booking']])
                    ->distinct('member_id')
                    ->asArray()
                    ->all();
                $userIds = ArrayHelper::getColumn($queryBooking, 'member_id');
                $query->andWhere([
                    'IN', 'ui.user_id', $userIds
                ]);
                $totalCount->andWhere([
                    'IN', 'user_id', $userIds
                ]);
            }

            //        $query->distinct('user_id');
            //        $totalCount = (clone $query)->count();
            $totalCount = $totalCount->count();
            $pages = new Pagination([
                'totalCount' => (int)$totalCount,
                'pageSize' => $perPage,
            ]);
            $pages->setPageSize($perPage);

            $query->orderBy([
                'ui.status' => SORT_ASC,
                'ui.created_at' => SORT_DESC,
            ]);
            if (!empty($params['sort'])) {
                if ($params['sort'] == 'status') {
                    $query->orderBy([
                        $params['sort'] => $params['sort_type'] === 'asc' ? SORT_ASC : SORT_DESC,
                        'ui.referrer_id' => SORT_DESC,
                    ]);
                } else {
                    $query->orderBy([$params['sort'] => $params['sort_type'] === 'asc' ? SORT_ASC : SORT_DESC]);
                }
            }
            $data = $query
                ->limit($perPage)
                ->offset($offset)
                ->all();

        return [
            'pages' => $pages,
            'data' => $data,
            'pageNow' => $page,
            'perPage' => $perPage,
            'offset' => $offset,
            'totalCount' => $totalCount,
        ];
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        $query = MemberForm::getMemberDetailQuery($id);
        $query2 = MemberForm::getWorkerRating($id);
        $ratingData = $query2->all();
        $data = $query->all();
        return $this->render("view/index", [
            "model" => $model,
            "data" => $data[0],
            "member_id" => $id,
            'ratingData' => $ratingData,
        ]);
    }

    public function actionLoadBookingHistory($member_id, $page = 1, $per_page = 10, $filter_coupon = 0, $keyword = '') {
        $this->response->format = Response::FORMAT_JSON;
        $member = UserInfo::findOne($member_id);
        $offset = (int)$per_page * ((int)$page - 1);
        $bookingInfoQuery = (new Query())
            ->from(["bi" => BookingInfo::tableName()])
            ->innerJoin(["ui" => UserInfo::tableName()], "bi.member_id = ui.user_id")
            ->innerJoin(["scs" => ShopCalendarSlot::tableName()], "bi.slot_id = scs.slot_id")
            ->where([
                'AND',
                'bi.status='.BookingInfo::STATUS_ACCEPTED,
                [
                    'OR',
                    'bi.member_id='.$member_id,
                    'ui.phone_number='.$member->phone_number,
                ]
            ])
            ->select([
                "bi.booking_id",
                "bi.course_id",
                "bi.cost",
                "scs.start_time",
                "scs.end_time",
                "scs.duration_minute",
                "bi.created_at as booking_created_at",
                "bi.modified_at as booking_modified_at",
                "scs.shop_id",
            ]);

        $totalCount = (clone $bookingInfoQuery)->count();
        $result = $bookingInfoQuery
            ->orderBy(['booking_created_at' => SORT_DESC])
            ->limit($per_page)
            ->offset($offset)
            ->all();
        $courses = array_column(
            CourseInfo::find()
                ->select(['course_id', 'course_name'])
                ->where(['status' => CourseInfo::STATUS_ACTIVE])
                ->all(),
            'course_name', 'course_id'
        );
        $shops = array_column(
            ShopInfo::find()
            ->select(['shop_id', 'shop_name'])
            ->where(['status' => ShopInfo::STATUS_ACTIVE])
            ->all(),
            'shop_name', 'shop_id'
        );
        return [
            "success" => true,
            "totalCount" => $totalCount,
            "page" => $page,
            "perPage" => $per_page,
            "offset" => $offset,
            "data" => $result,
            "courses" => $courses,
            "shops" => $shops,
        ];
    }


    public function actionDelete($id)
    {
        $form = $this->findModel($id);
        if ($form->toDelete()) {
            \App::$app->session->setFlash("ALERT_MESSAGE", \App::t("backend.member.message", "Đã xóa thành công"));
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            return $this->redirect(['index']);
        }
    }

    public function actionApprove($id)
    {
        $form = $this->findModel($id);
        if ($form->toApprove()) {
            \App::$app->session->setFlash("ALERT_MESSAGE", \App::t("backend.member.message", "Chờ xác nhận"));
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            \App::$app->session->setFlash("ERROR_MESSAGE", $form->getFirstError("user_id"));
            return $this->redirect(['index']);
        }
    }

    public function actionReject($id)
    {
        $form = $this->findModel($id);
        if ($form->toReject()) {
            \App::$app->session->setFlash("ALERT_MESSAGE", \App::t("backend.member.message", "Đã từ chối"));
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            \App::$app->session->setFlash("ERROR_MESSAGE", $form->getFirstError("user_id"));
            return $this->redirect(['index']);
        }
    }

    public function actionLoadBlackListForm($id)
    {
        $this->response->format = Response::FORMAT_JSON;
        $model = MemberBlackListConfigForm::findOne($id);
        if ($model) {
            return [
                "success" => true,
                "data" => $model,
            ];
        } else {
            return [
                "success" => false,
                "message" => \App::t("backend.member.message", "Error"),
            ];
        }
    }

    public function actionSaveBlackList($id)
    {
        $this->response->format = Response::FORMAT_JSON;
        $model = MemberBlackListConfigForm::findOne($id);
        if ($model && $model->toSaveBlackList(
                $this->request->post("isAddedBlackList"),
                $this->request->post("isAddedWorkerBlackList"),
                $this->request->post("blackListWorkerIds")
            )
        ) {
            return [
                "success" => true,
                "message" => \App::t("backend.member.message", "Cập nhật thành công"),
                "data" => $model,
            ];
        }
        return [
            "success" => false,
            "message" => \App::t("backend.member.message", "Have error when save member config"),
            "error" => $model ? $model->getErrors() : null,
        ];
    }

    public function actionLoadPrivateCouponForm($id)
    {
        $this->response->format = Response::FORMAT_JSON;
        $form = new AddPrivateCouponForm();
        $form->prepare();
        $form->member_id = $id;
        return [
            "success" => true,
            "data" => $form->toArray([], [
                'isSendSMS',
                'smsContent',
                "memberInfo",
                'type_expire_date'
            ]),
        ];
    }


    /**
     * @param $id
     * @return MemberForm
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = MemberForm::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(\Yii::t('common.message', 'The requested page does not exist.'));
        }
    }

    public function actionUpdateTag()
    {
        $userId = $this->request->post('user_id');
        $tags = $this->request->post('tags');
        $userInfo = UserInfo::findOne($userId);
        $userInfo->tag = $tags;
        $userInfo->save();
    }

    public function actionDownloadTemplate()
    {
        $model = new MemberExportForm();
        if ($model) {
            ini_set('memory_limit', '-1');
            $filePath = $model->downloadTemplate();
            return $this->response->sendContentAsFile(file_get_contents($filePath), "member-list-" . DatetimeHelper::now("Y-m-d_H_i_s") . ".xlsx");
        }
        throw new NotFoundHttpException(\Yii::t('common.message', 'The requested page does not exist.'));
    }

    protected function getReferrerFromKeyword($keyword)
    {
        $query = UserInfo::find()
            ->where([
                'LIKE', 'full_name', $keyword
            ])->all();
        $userId = ArrayHelper::getColumn($query, 'user_id');
        return $userId;
    }

    public function actionSwitchStatus($id)
    {
        if (!$this->request->isAjax) {
            return $this->redirect("forum/manage");
        }
        $this->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        if ($model->status_forum == UserInfo::STATUS_USER_FORUM_ACTIVE) {
            $model->status_forum = UserInfo::STATUS_USER_FORUM_BLOCK;
        } else {
            $model->status_forum = UserInfo::STATUS_USER_FORUM_ACTIVE;
        }
        if ($model->save()) {
//            var_dump();
            return [
                "success" => true,
                "message" => \App::t("backend.forum.message", "Success"),
            ];
        }
        return [
            "success" => false,
            "message" => StringHelper::errorToString($model->getErrors()),
        ];
    }

    public function actionBookingDays($id, $worker_id)
    {
        $this->layout = 'layout_base';
        $query = MemberForm::getTotalRatingDays($id, $worker_id);
        $data = $query->all();
        return $this->render("rating/rating-days", [
            'data' => $data,
            'id' => $id
        ]);
    }
}
