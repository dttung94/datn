<?php
namespace backend\modules\calendar\controllers;


use backend\filters\BackendAccessRule;
use backend\models\BackendController;
use backend\modules\calendar\CalendarModule;
use backend\modules\calendar\forms\sms\SmsTemplateForm;
use backend\modules\service\forms\mail\MailTemplateForm;
use common\entities\calendar\BookingInfo;
use common\entities\customer\CustomerData;
use common\entities\service\TemplateMail;
use common\forms\service\SendSMSForm;
use common\helper\ArrayHelper;
use common\mail\forms\Mail;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * Class SmsController
 * @package backend\modules\calendar\controllers
 */
class SmsController extends BackendController
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
                            'load-template',
                            'send-booking-sms',
                            'load-mail-template',
                            'send-booking-email',
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

    public function actionLoadTemplate($type)
    {
        if (!$this->request->isAjax) {
            return $this->redirect([
                "calendar/booking/index"
            ]);
        }
        $this->response->format = Response::FORMAT_JSON;
        $form = SmsTemplateForm::findOne([
            "type" => $type,
        ]);
        if (!$form) {
            $form = new SmsTemplateForm();
            $form->type = $type;
        }
        return [
            "success" => true,
            "data" => $form,
        ];
    }

    public function actionLoadMailTemplate($type)
    {
        if (!$this->request->isAjax) {
            return $this->redirect([
                "calendar/booking/index"
            ]);
        }
        $this->response->format = Response::FORMAT_JSON;
        $form = MailTemplateForm::findOne([
            "type" => $type,
        ]);
        if (!$form) {
            $form = new MailTemplateForm();
            $form->type = $type;
        }
        return [
            "success" => true,
            "data" => $form,
        ];
    }

    public function actionSendBookingSms()
    {
        if (!$this->request->isAjax) {
            return $this->redirect([
                "calendar/booking/index"
            ]);
        }
        $this->response->format = Response::FORMAT_JSON;
        $booking_id = $this->request->post("booking_id");
        $sms_content = $this->request->post("sms_content");
        $type = $this->request->post("type");

        $bookingInfo = BookingInfo::findOne($booking_id);
        if ($bookingInfo && $bookingInfo->memberInfo) {
            /**
             * @var $bookingInfo BookingInfo
             */
            $phoneNumber = '84' . $bookingInfo->memberInfo->phone_number;
            $smsParams = [
                "shop_address" => $bookingInfo->slotInfo->shopInfo->shop_address,
                "worker_name" => $bookingInfo->slotInfo->workerInfo->worker_name,
                "shop_name" => $bookingInfo->slotInfo->shopInfo->shop_name,
                "phone_number" => $bookingInfo->slotInfo->shopInfo->phone_number,
                "booking_date" => \App::$app->formatter->asDate($bookingInfo->slotInfo->date),
                "booking_time" => \App::$app->formatter->asTime($bookingInfo->slotInfo->start_time),
                "course_id" => ArrayHelper::getValue($bookingInfo::getListCourseType(), $bookingInfo->course_id),
                "cost" => \App::$app->formatter->asCurrency($bookingInfo->cost),
            ];
            if (SendSMSForm::toSend(
                $phoneNumber,
                $sms_content,
                $smsParams,
                [
                    SendSMSForm::TYPE_BOOKING_FREE_SMS
                ]
            )) {
                return [
                    "success" => true,
                    "message" => \App::t("backend.booking.message", "Gửi tin nhắn thông báo"),
                    "booking" => $bookingInfo,
                ];
            }
        }
        return [
            "success" => false,
            "message" => \App::t("backend.booking.message", "Có lỗi xảy ra"),
        ];
    }

    public function actionSendBookingEmail()
    {
        if (!$this->request->isAjax) {
            return $this->redirect([
                "calendar/booking/index"
            ]);
        }
        $this->response->format = Response::FORMAT_JSON;
        $bookingId = $this->request->post("booking_id");
        $mailContent = $this->request->post("sms_content");
        $type = $this->request->post("type");
        $titleEmail = $this->request->post("title_email", "");

        $bookingInfo = BookingInfo::findOne($bookingId);
        if ($bookingInfo && $bookingInfo->customerInfo) {
            $mail = new Mail();
            $template = TemplateMail::getMailTemplate(TemplateMail::TYPE_BOOKING_FREE_MAIL);
            $mailParams = [
                "worker_name" => $bookingInfo->slotInfo->workerInfo->worker_name,
                "shop_name" => $bookingInfo->slotInfo->shopInfo->shop_name,
                "phone_number" => $bookingInfo->slotInfo->shopInfo->phone_number,
                "booking_date" => \App::$app->formatter->asDate($bookingInfo->slotInfo->date),
                "booking_time" => \App::$app->formatter->asTime($bookingInfo->slotInfo->start_time),
                "course_id" => ArrayHelper::getValue($bookingInfo::getListCourseType(), $bookingInfo->course_id),
                "cost" => \App::$app->formatter->asCurrency($bookingInfo->cost),
            ];
            $data = [
                'email' => $bookingInfo->memberInfo->email,
                'name' => $bookingInfo->memberInfo->username,
                'subject' => $titleEmail != "" ? $titleEmail : $template->title,
                'content' => $mailContent,
                'params' => $mailParams
            ];
            if ($mail->toSend($data)) {
                return [
                    "success" => true,
                    "message" => \App::t("backend.booking.message", "Email送信成功。"),
                    "booking" => $bookingInfo,
                ];
            }
        }
        return [
            "success" => false,
            "message" => \App::t("backend.booking.message", "Email送信時にエラーがでました。"),
        ];
    }
}
