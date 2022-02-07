<?php
namespace console\models;

use common\helper\ArrayHelper;
use consik\yii2websocket\events\WSClientMessageEvent;
use consik\yii2websocket\WebSocketServer;
use Ratchet\ConnectionInterface;
use yii\helpers\Json;

/**
 * Class RealtimeServer
 * @package console\models
 */
class RealtimeServer extends WebSocketServer
{
    public function init()
    {
        parent::init();
        $this->on(self::EVENT_CLIENT_MESSAGE, function (WSClientMessageEvent $e) {
            echo "Request: $e->message\n";
//            $e->client->send($e->message);
            $this->toSendBroadcastClient($e->message);
        });
    }

    /**
     * override method getCommand( ... )
     * For example, we think that all user's message is a command
     */
    protected function getCommand(ConnectionInterface $from, $msg)
    {
        try {
            $msgData = Json::decode($msg);
            return ArrayHelper::getValue($msgData, "type");
        } catch (\Exception $ex) {
            return null;
        }
    }

    protected function toSendBroadcastClient($msg)
    {
        foreach ($this->clients as $chatClient) {
            $chatClient->send($msg);
        }
    }
}