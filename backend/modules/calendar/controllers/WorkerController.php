<?php
namespace backend\modules\calendar\controllers;


use backend\filters\BackendAccessRule;
use backend\models\BackendController;
use backend\modules\calendar\CalendarModule;
use backend\modules\calendar\forms\worker\WorkerConfigForm;
use backend\modules\calendar\forms\worker\WorkerCreateCalendarSlotForm;
use common\components\WebSocketClient;
use common\entities\shop\ShopCalendar;
use common\entities\shop\ShopCalendarSlot;
use common\entities\shop\ShopConfig;
use common\entities\shop\ShopInfo;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;

class WorkerController extends BackendController
{
    public $enableCsrfValidation = false;

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
                    "module" => CalendarModule::MODULE_ID
                ],
                'rules' => [
                    [
                        'actions' => [
                            'save-config',
                            "work-break",

                            'load-slot-form',
                            'create-worker-slot',
                            'change-time-worker',
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'change-time-worker' => ['post'],
                ],
            ],
        ];
    }


    public function actionWorkBreak($worker_id)
    {
        if (!$this->request->isAjax) {
            return $this->redirect("/calendar/booking");
        }
        $this->response->format = Response::FORMAT_JSON;
        $model = WorkerConfigForm::findOne($worker_id);
        if ($model->toWorkBreak(
            $this->request->post("date"),
            $this->request->post("sms_content"),
            $this->request->post("mail_title")
        )) {
            \App::$app->webServiceClient->send(
                WebSocketClient::EVENT_WORKER_SCHEDULE_CHANGED,
                \App::t("common.notice.message", "Worker schedule updated."), [
                    "worker_id" => $model->worker_id,
                    "date" => $this->request->post("date"),
                ]
            );//todo send notification
            return [
                "success" => true,
                "message" => \App::t("backend.booking.message", "Cập nhật thành công"),
                "data" => $model,
            ];
        } else {
            return [
                "success" => false,
                "message" => \App::t("backend.booking.message", "Have error when process"),
                "error" => $model->getErrors(),
            ];
        }
    }

    public function actionLoadSlotForm($worker_id, $date)
    {
        if (!$this->request->isAjax) {
            return $this->redirect("/calendar/booking");
        }
        $this->response->format = Response::FORMAT_JSON;
        $model = WorkerCreateCalendarSlotForm::findOne($worker_id);
        if ($model) {
            $model->date = $date;
            if (($data = $model->loadForm()) !== false) {
                return [
                    "success" => true,
                    "data" => $data,
                ];
            }
            return [
                "success" => false,
                "error" => $model->getErrors(),
                "message" => \App::t("backend.booking.message", "Load slot form error"),
            ];
        }
        return [
            "success" => false,
            "message" => \App::t("backend.booking.message", "Load slot form error"),
        ];
    }

    public function actionCreateWorkerSlot($worker_id, $date)
    {
        if (!$this->request->isAjax) {
            return $this->redirect("/calendar/booking");
        }
        $this->response->format = Response::FORMAT_JSON;
        $model = WorkerCreateCalendarSlotForm::findOne($worker_id);
        if ($model) {
            $model->date = $date;
            if ($model->load($this->request->post(), "") && ($result = $model->toCreateSlot()) != false) {
                \App::$app->webServiceClient->send(
                    WebSocketClient::EVENT_WORKER_SLOT_CHANGED,
                    \App::t("common.notice.message", "Worker slot updated."), [
                        "worker_id" => $model->worker_id,
                        "date" => $date,
                    ]
                );//todo send notification
                return [
                    "success" => true,
                    "data" => $model,
                    "result" => $result,
                    "message" => \App::t("backend.booking.message", "Đã tạo khung đồng loạt"),
                ];
            } else {
                return [
                    "success" => false,
                    "error" => $model->getErrors(),
                    "message" => \App::t("backend.booking.message", "Lỗi khi tạo khung làm việc"),
                ];
            }
        }
        return [
            "success" => false,
            "message" => \App::t("backend.booking.message", "Lỗi khi tạo khung làm việc"),
        ];
    }

    public function actionChangeTimeWorker()
    {
        $shopInfo = new ShopInfo();
        $data = $this->request->post();
        $start = strtotime(self::convertTimeToString($data['start_time']));
        $end = strtotime(self::convertTimeToString($data['end_time']));

        if ($start >= $end || $end > strtotime('24:00')) {
            return json_encode([
                'success' => false,
                'message' => 'Thời gian không hợp lệ'
            ]);
        }
        $condition = [
            'shop_id' => $data['shop_id'],
            'worker_id' => $data['worker_id'],
            'date' => $data['date']
        ];

        $slots = ShopCalendarSlot::find()
            ->where($condition)
            ->andWhere(['!=', 'status', ShopCalendarSlot::STATUS_DELETE])
            ->all();
        foreach ($slots as $slot) {
            $slotStart = $slot->start_time;
            $slotEnd = $slot->end_time;
            if ($start > strtotime($slotStart) || $end < strtotime($slotEnd)) {
                return json_encode([
                    'success' => false,
                    'message' => 'Đang có khung làm việc ['.$slotStart.' - '.$slotEnd.']'
                ]);
            }
        }

        $schedules = ShopCalendar::find()
            ->where(['!=', 'shop_id', $data['shop_id']])
            ->andWhere([
                'worker_id' => $data['worker_id'],
                'date' => $data['date'],
                'type' => ShopCalendar::TYPE_WORKING_DAY
            ])
            ->all();
        foreach ($schedules as $schedule) {
            $startCheck = strtotime(self::convertTimeToString($schedule->work_start_time));
            $endCheck = strtotime(self::convertTimeToString($schedule->work_end_time));

            if (
                ($start >= $startCheck && $start <= $endCheck) ||
                ($end >= $startCheck && $end <= $endCheck) ||
                ($start <= $startCheck && $end >= $endCheck)
            ) {
                $time = self::convertTimeToString($schedule->work_start_time).'→'.self::convertTimeToString($schedule->work_end_time);
                return json_encode([
                    'success' => false,
                    'message' => '【'.$shopInfo->getListShop()[$schedule->shop_id].'】bị trùng giờ làm việc:'.$time.'',
                ]);
            }
        }

        $shopStartAt = ShopConfig::getValue(ShopConfig::KEY_SHOP_OPEN_DOOR_AT, $data['shop_id']);
        $shopStartAt = self::convertTimeToString($shopStartAt);

        $shopEndAt = ShopConfig::getValue(ShopConfig::KEY_SHOP_CLOSE_DOOR_AT, $data['shop_id']);
        $shopEndAt = self::convertTimeToString(($shopEndAt));

        if (
            ($start < strtotime($shopStartAt)) ||
            ($end > strtotime($shopEndAt))
        ) {
            $time = $shopStartAt.'→'.$shopEndAt;
            return json_encode([
                'success' => false,
                'message' => '【'.$shopInfo->getListShop()[$data['shop_id']].'】có thời gian làm việc'.$time.''
            ]);
        }

        $update = ShopCalendar::updateAll([
            'work_start_time' => $data['start_time'],
            'work_end_time' => $data['end_time']
        ], $condition);

        if (!$update) {
            return json_encode([
                'success' => false,
                'message' => 'Có lỗi xảy ra'
            ]);
        }

        \App::$app->webServiceClient->send(
            WebSocketClient::EVENT_CONFIG_WORKER_TIME,
            \App::t("common.notice.message", ""), [
                'shop_id' => $data['shop_id'],
                'worker_id' => $data['worker_id'],
                'start_time' => self::convertTimeToString($data['start_time']),
                'end_time' => self::convertTimeToString($data['end_time'])
            ]
        );

        return json_encode([
            'success' => true,
            'message' => 'Đã cập nhật'
        ]);
    }
}
