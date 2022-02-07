<?php
namespace common\components;

use WebSocket\Client;
use yii\base\Component;
use yii\helpers\Json;

/**
 * Class WebserviceClient
 * @package common\components
 *
 * @property string $host
 * @property integer $post
 */
class WebSocketClient extends Component
{
    const
        EVENT_PING = "ping", //just for test
        //todo free booking request & booking slot changed event
        EVENT_SHOP_CALENDAR_SLOT_CHANGED = "shopCalendarSlotChanged",
        EVENT_BOOKING_FREE_REQUEST_CHANGED = "bookingFreeRequestChanged",
        //todo shop event
        EVENT_SHOP_CONFIG_CHANGED = "shopConfigChanged",//shop config saved (allow/not-allow free booking)
        EVENT_SHOP_SCHEDULE_CHANGED = "shopScheduleChanged",//shop schedule saved
        //todo booking slot event
        EVENT_SHOP_SLOT_UPDATED = "shopSlotUpdated",//booking slot created
        EVENT_SHOP_SLOT_REMOVED = "shopSlotRemoved",//booking slot removed
        EVENT_SHOP_SLOT_EXPIRED = "shopSlotExpired",//booking slot expired
        //todo booking event
        EVENT_BOOKING_CANCELED = "bookingCanceled",//booking was canceled by Member
        EVENT_BOOKING_CANCELED_BY_MANAGER = "bookingCanceledByManager",//booking was canceled by Manager
        EVENT_BOOKING_DELETED = "bookingDeleted",//booking was deleted by Manager
        EVENT_BOOKING_CHANGE_DURATION_MINUTE = "bookingChangeDurationMinute", //booking was change duration minute by Member
        //todo booking online event
        EVENT_BOOKING_ONLINE_CREATED = "bookingOnlineCreated", //booking online created by Member
        EVENT_BOOKING_ONLINE_UPDATE_REQUEST = "bookingOnlineUpdating", //booking online updated by Member
        EVENT_BOOKING_ONLINE_CONFIRM_EXPIRED = "bookingOnlineConfirmExpired", //booking online confirm expired by Member
        EVENT_BOOKING_ONLINE_ACCEPTED = "bookingOnlineAccepted", //booking online accepted by Manager
        EVENT_BOOKING_ONLINE_REJECTED = "bookingOnlineRejected", //booking online rejected by Manager
        EVENT_BOOKING_ONLINE_UPDATED = "bookingOnlineUpdated", //booking online updated by Manager
        //todo booking free event
        EVENT_BOOKING_FREE_CREATED = "bookingFreeCreated", //booking free created request by Member
        EVENT_BOOKING_FREE_EXPIRED = "bookingFreeExpired", //booking free request expired by Console
        EVENT_BOOKING_FREE_REJECT = "bookingFreeReject", //booking free rejected by Manager
        EVENT_BOOKING_FREE_ACCEPT = "bookingFreeAccept", //booking free accepted by Manager
        EVENT_BOOKING_FREE_UPDATED = "bookingFreeUpdated", //booking free updated by Manager
        EVENT_BOOKING_FREE_SEND_CONFIRM = "bookingFreeSendConfirm", //send confirm booking free by Manager
        EVENT_BOOKING_FREE_CONFIRM_EXPIRED = "bookingFreeConfirmExpired", //confirm booking expired by Console
        EVENT_BOOKING_FREE_CONFIRM_ACCEPT = "bookingFreeConfirmAccept", //booking free confirm accept by Member
        EVENT_BOOKING_FREE_CONFIRM_REJECT = "bookingFreeConfirmReject", //booking free confirm reject by Member
        //todo booking offline event
        EVENT_BOOKING_OFFLINE_UPDATED = "bookingOfflineUpdated", //booking offline updated by Manager
        EVENT_BOOKING_OFFLINE_REMOVED = "bookingOfflineRemoved", //booking offline removed by Manager
        //todo worker event
        EVENT_WORKER_SCHEDULE_CHANGED = "workerScheduleChanged", //worker work break
        EVENT_WORKER_SLOT_CHANGED = "workerSlotChanged", //worker slot created
        EVENT_WORKER_CONFIG_CHANGED = "workerConfigChanged", //worker note change
        EVENT_CONFIG_WORKER_TIME = "configWorkerTime", //worker change time
        //todo member event
        EVENT_NEW_MEMBER_SIGN_UP = "newMemberSignUp", //new member sign up
        EVENT_TIME_CONFIRM_EXPIRED = "timeConfirmExpired";

    public $host;
    public $post;

    /**
     * @var Client
     */
    protected $_client;

    public function init()
    {
        parent::init();
        try {
            $env_ws = 'ws';
            $this->_client = new Client("$env_ws://$this->host:$this->post/");
        } catch (\Exception $ex) {
            $this->_client = null;
        }
    }

    public function send($type, $message, $data = [], $senderId = null, $isTest = false)
    {
        if ($senderId == null && \App::$app->has("user")) {
            $senderId = \App::$app->user->id;
        }
        if ($this->_client) {
            try {
                $this->_client->send(Json::encode([
                    "type" => $type,
                    "message" => $message,
                    "senderId" => $senderId,
                    "data" => $data
                ]));
            } catch (\Exception $ex) {
                if ($isTest) {
                    throw $ex;
                }
            }
        } else {
            if ($isTest) {
                throw new \Exception("Can not connect to WebSocket");
            }
        }
    }

    public function ping($isTest = false)
    {
        $this->send(self::EVENT_PING, "Ping", [], $isTest);
    }
}