<?php
namespace backend\modules\calendar\controllers;

use backend\models\BackendController;
use backend\modules\calendar\forms\schedule\ShopForm;
use common\components\WebSocketClient;
use common\entities\shop\ShopCalendar;
use common\entities\shop\ShopInfo;
use common\entities\user\UserConfig;
use common\entities\user\UserInfo;
use common\entities\worker\WorkerInfo;
use common\helper\ArrayHelper;
use common\helper\DatetimeHelper;
use common\helper\StringHelper;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use App;
use yii\helpers\Url;

class ScheduleController extends BackendController
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
                'rules' => [
                    [
                        'actions' => [
                            'config',
                            'save-config',

                            'check-worker-schedule',
                            'get-worker-schedule',
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'switch-status' => ['post'],
                ],
            ],
        ];
    }

    public function actionConfig($shop_id, $date = null, $page = 1)
    {
        $users = App::$app->user->identity;
        if ($users->role == UserInfo::ROLE_MANAGER || $users->role == UserInfo::ROLE_OPERATOR) {
            $configs = UserConfig::find()
                ->where([
                    "user_id" => $users->user_id,
                    "key" => UserConfig::KEY_MANAGE_SHOP_IDS
                ])->one();
            $shopIds = Json::decode($configs->value);
            if (!in_array($shop_id, $shopIds)) {
                return $this->redirect('/site/index');
            }
        }
        if ($this->request->post()) {
            $name = empty($this->request->post()['keyword']) ? "" : $this->request->post()['keyword'];
            $redirect = [
                "config",
                "shop_id" => $shop_id,
                "date" =>$date,
                "page" => 1,
            ];
            if ($name != "") {
                $redirect["name"] = $name;
            }
            return $this->redirect($redirect);
        } else {
            $name = empty($this->request->get()['name']) ? "" : $this->request->get()['name'];
        }

        $shop = $this->_findModel($shop_id);
        if ($date == null) {
            return $this->redirect([
                "config",
                "shop_id" => $shop_id,
                "date" => App::$app->formatter->asDate(date(time()) + 60 * 60 * 24 * 0, "yyyy-MM-dd"),
                "page" => $page
            ]);
        }
        $shop->date = $date;
        $shop->toPrepare($page, $name);
        $pages = $shop->totalPage;
        $parsed = parse_url($_SERVER['REQUEST_URI']);
        parse_str($parsed['query'], $params);
        unset($params['page']);
        unset($params['name']);
        $string = http_build_query($params);
        $parsed['query'] = $string;
        $url = Url::base(true).$parsed['path'].'?'.$string;
        return $this->render("config", [
            "model" => $shop,
            "pages" => $pages,
            "url" => $url,
            "name" => $name
        ]);
    }

    public function actionSaveConfig($shop_id, $date)
    {
        $shop = $this->_findModel($shop_id);
        if ($date == null) {
            $date = App::$app->formatter->asDate(date(time()) + 60 * 60 * 24 * 0, "yyyy-MM-dd");
        }
        $shop->date = $date;
        $shop->toPrepare();
        if ($shop->load($this->request->post()) && $shop->toSave()) {
            \App::$app->webServiceClient->send(
                WebSocketClient::EVENT_SHOP_SCHEDULE_CHANGED,
                \App::t("common.notice.message", "Shop schedule updated."), [
                    "shop_id" => $shop_id,
                    "date" => $date,
                ]
            );//todo send notification
            \App::$app->session->setFlash("ALERT_MESSAGE", \App::t("backend.schedule.message", "Đã lưu lịch làm việc"));
            return $this->redirect([
                "config",
                "shop_id" => $shop_id,
                "date" => $date,
            ]);
        }
        $this->response->format = Response::FORMAT_JSON;
        return [
            "success" => false,
            "message" => StringHelper::errorToString($shop->getErrors()),
            "data" => $shop,
            "error" => $shop->getErrors(),
        ];
    }

    public function actionCheckWorkerSchedule($shop_id, $worker_id, $date, $work_start_hour, $work_start_minute, $work_end_hour, $work_end_minute)
    {
        $this->response->format = Response::FORMAT_JSON;
        $shop = $this->_findModel($shop_id);
        $model = $shop->toCheckWorkerTimeIsValid($worker_id, $date, $work_start_hour, $work_start_minute, $work_end_hour, $work_end_minute);
        if (!$model->hasErrors()) {
            return [
                "success" => true,
                "params" => [
                    "shop_id" => $shop_id,
                    "worker_id" => $worker_id,
                    "date" => $date,
                ],
                "model" => $model,
            ];
        } else {
            return [
                "success" => false,
                "error" => $model->getErrors("date"),
                "errors" => $model->getErrors(),
            ];
        }
    }

    public function actionGetWorkerSchedule($worker_id, $date)
    {
        $this->response->format = Response::FORMAT_JSON;
        $workerInfo = WorkerInfo::findOne($worker_id);
        if ($workerInfo) {
            $data = [
                "title" => App::t("backend.schedule.message", "Ca làm việc: {worker-name} - {date}", [
                    "worker-name" => $workerInfo->worker_name,
                    "date" => App::$app->formatter->asDate($date),
                ]),
                "calendars" => Json::decode(file_get_contents(\App::getAlias("@common/data/day-data.json"))),
            ];
            $calendars = ShopCalendar::find()
                ->innerJoin(ShopInfo::tableName(), ShopInfo::tableName() . ".shop_id = " . ShopCalendar::tableName() . ".shop_id")
                ->where([
                    ShopCalendar::tableName() . ".status" => ShopCalendar::STATUS_ACTIVE,
                    ShopCalendar::tableName() . ".type" => ShopCalendar::TYPE_WORKING_DAY,
                    ShopCalendar::tableName() . ".worker_id" => $worker_id,
                    ShopCalendar::tableName() . ".date" => $date,
                    ShopInfo::tableName() . ".status" => ShopInfo::STATUS_ACTIVE,
                ])
                ->all();
            foreach ($calendars as $calendar) {
                /**
                 * @var $calendar ShopCalendar
                 */
                $work_start_hour = DatetimeHelper::getHourFromTimeFormat($calendar->work_start_time);
                $work_start_time = DatetimeHelper::getMinuteFromTimeFormat($calendar->work_start_time);
                $work_end_hour = DatetimeHelper::getHourFromTimeFormat($calendar->work_end_time);
                $work_end_time = DatetimeHelper::getMinuteFromTimeFormat($calendar->work_end_time);
                $startMinute = $work_start_hour * 60 + $work_start_time;
                $endMinute = $work_end_hour * 60 + $work_end_time;
                $data["calendars"][$startMinute] = ArrayHelper::merge($data["calendars"][$startMinute], [
                    "colspan" => ($endMinute - $startMinute) / 5,
                    "isWorkingTime" => true,
                    "message" => App::t("backend.schedule.message", "{shop-name} [{from} - {to}]", [
                        "shop-name" => $calendar->shopInfo->shop_name,
                        "from" => App::$app->formatter->asTime($calendar->work_start_time),
                        "to" => $calendar->work_end_time,
                    ]),
                ]);
                for ($time = $startMinute + 5; $time < $endMinute; $time += 5) {
                    if (isset($data["calendars"][$time])) {
                        $data["calendars"][$time] = ArrayHelper::merge($data["calendars"][$time], [
                            "isInvisible" => true,
                        ]);
                    }
                }
            }
            return [
                "success" => true,
                "data" => $data,
            ];
        }
        return [
            "success" => false,
            "message" => App::t("backend.schedule.message", "Worker calendar is invalid"),
        ];
    }

    private function _findModel($id)
    {
        $model = ShopForm::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException(\Yii::t('common.message', 'The requested page does not exist.'));
        }
        return $model;
    }
}