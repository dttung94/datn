<?php
namespace common\models\base;


use common\entities\calendar\BookingInfo;
use common\entities\system\SystemConfig;
use common\forms\service\SendMailForm;
use common\models\UserIdentity;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\Request;
use yii\web\Response;

/**
 * Class AbstractController
 * @package app\common\models\base
 *
 * @property Request $request
 * @property Response $response
 *
 * @property UserIdentity $userInfo
 */
abstract class AbstractController extends Controller
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var UserIdentity
     */
    protected $userInfo;

    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $this->request = \Yii::$app->request;
            $this->response = \Yii::$app->response;
            if (!\Yii::$app->user->isGuest) {
                $this->userInfo = \Yii::$app->user->identity;
            }
            return true;
        }
        return false;
    }

    public function afterAction($action, $result)
    {
        $result = parent::afterAction($action, $result);
        return $result;
    }

    public function toSendMail($datas, $email, $type)
    {
        $members = $datas->memberInfo;
        switch ($type) {
            case BookingInfo::BOOKING_ONLINE:
            case BookingInfo::BOOKING_CANCEL_SLOT:
            case BookingInfo::BOOKING_TIME_EXPIRED_REJECT:
            case BookingInfo::BOOKING_REJECT:
            case BookingInfo::BOOKING_ONLINE_UPDATE:
                $slot = $datas->slotInfo;
                $times = $this->convertTimeToArray($slot->start_time);
                $text = $type.'<br>'.'- '.$times[0]. ' giờ ' .$times[1]. ' phút: Nhân viên '.$slot->workerInfo->worker_name.' với số điện thoại của khách hàng đặt chỗ là '.$members->phone_number;

                break;
            case BookingInfo::BOOKING_CANCEL_REQUEST:
                $text = '['.$type.']. Số điện thoại khách hàng '.$datas->memberInfo->phone_number.', khách hàng'.$datas->memberInfo->full_name.' đã đặt lịch tại cửa hàng '.$datas->shopInfo->shop_name;
                break;
            default:
                $text = '';
        }
        $mail = new SendMailForm();
        $mail->from_name = SystemConfig::getValue(SystemConfig::CATEGORY_SYSTEM, SystemConfig::SYSTEM_SITE_NAME);
        $mail->from_email = SystemConfig::getValue(SystemConfig::CATEGORY_SYSTEM, SystemConfig::SYSTEM_NO_REPLY_EMAIL);
        $mail->subject = $type;
        $mail->content = $text;
        $mail->to = $email;
        return $mail->toSend();
    }

    protected function convertTimeToArray($time)
    {
        $times = explode(':', $time);
        if ((int)$times[1] < 10) {
            $times[1] = "0".$times[1];
        }
        return $times;
    }

    protected function convertTimeToString($time)
    {
        $times = explode(':', $time);
        if ((int)$times[1] < 10) {
            $times[1] = "0".$times[1];
        }
        return $times[0].':'.$times[1];
    }


}