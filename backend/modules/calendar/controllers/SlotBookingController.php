<?php
namespace backend\modules\calendar\controllers;

use backend\filters\BackendAccessRule;
use backend\models\BackendController;
use backend\modules\calendar\CalendarModule;
use backend\modules\calendar\forms\booking\CalendarForm;
use backend\modules\calendar\forms\booking\WorkerCalendarSlotForm;
use common\components\WebSocketClient;
use common\helper\StringHelper;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * Class SlotBookingController
 * @package backend\modules\calendar\controllers
 */
class SlotBookingController extends BackendController
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
                            "load-schedule-slot",
                            "save-schedule-slot",
                            "delete-schedule-slot",
                        ],
                        'allow' => true,
                        'roles' => ['@'],
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

    public function actionLoadScheduleSlot($worker_id, $shop_id, $date, $start_time)
    {
        if (!$this->request->isAjax) {
            return $this->redirect("index");
        }
        $this->response->format = Response::FORMAT_JSON;
        return [
            "success" => true,
            "data" => WorkerCalendarSlotForm::getInstance($worker_id, $shop_id, $date, $start_time),
        ];
    }

    public function actionSaveScheduleSlot()
    {
        if (!$this->request->isAjax) {
            return $this->redirect("index");
        }
        $this->response->format = Response::FORMAT_JSON;
        $form = WorkerCalendarSlotForm::getInstance(
            $this->request->post("worker_id"),
            $this->request->post("shop_id"),
            $this->request->post("date"),
            $this->request->post("start_time")
        );
        if ($form->toSave($this->request->post("start_hour"), $this->request->post("start_minute"), $this->request->post("duration_minute"))) {
            \App::$app->webServiceClient->send(
                WebSocketClient::EVENT_SHOP_SLOT_UPDATED,
                \App::t("common.notice.message", "Shop booking slot updated."), [
                    "shop_id" => $form->shop_id,
                    "worker_id" => $form->worker_id,
                    "date" => $form->date,
                ]
            );//todo send notification
            return [
                "success" => true,
                "message" => \App::t("backend.booking.message", "Khung làm việc đã sẵn sàng"),
                "data" => $form,
            ];
        }
        return [
            "success" => false,
            "message" => StringHelper::errorToString($form->getErrors()),
            "data" => $form,
            "error" => $form->getErrors(),
        ];
    }

    public function actionDeleteScheduleSlot($slot_id)
    {
        if (!$this->request->isAjax) {
            return $this->redirect("index");
        }
        $this->response->format = Response::FORMAT_JSON;
        $form = WorkerCalendarSlotForm::findOne([
            "slot_id" => $slot_id,
        ]);
        if ($form->delete()) {
            \App::$app->webServiceClient->send(
                WebSocketClient::EVENT_SHOP_SLOT_REMOVED,
                \App::t("common.notice.message", "Shop booking slot deleted."), [
                    "shop_id" => $form->shop_id,
                    "worker_id" => $form->worker_id,
                    "date" => $form->date,
                ]
            );//todo send notification
            return [
                "success" => true,
                "message" => \App::t("backend.booking.message", "Đã xóa khung"),
                "data" => $form,
            ];
        }
        return [
            "success" => false,
            "message" => StringHelper::errorToString($form->getErrors()),
            "data" => $form,
            "error" => $form->getErrors(),
        ];
    }
}