<?php
namespace console\controllers;

use consik\yii2websocket\events\WSClientMessageEvent;
use consik\yii2websocket\WebSocketServer;
use console\models\ConsoleController;
use console\models\RealtimeServer;

class RealtimeServerController extends ConsoleController
{
    public function actionStart($port = null)
    {
        $server = new RealtimeServer();
        if ($port) {
            $server->port = $port;
        }
        $server->on(WebSocketServer::EVENT_WEBSOCKET_OPEN_ERROR, function ($e) use ($server) {
            echo "Error opening port " . $server->port . "\n";
            $server->port += 1; //Try next port to open
            $server->start();
        });

        $server->on(WebSocketServer::EVENT_WEBSOCKET_OPEN, function ($e) use ($server) {
            echo "Server started at port " . $server->port . "\n";
        });

        $server->on(WebSocketServer::EVENT_CLIENT_MESSAGE, function (WSClientMessageEvent $e) {
//            echo "Request: $e->message\n";
        });

        $server->start();
    }
}