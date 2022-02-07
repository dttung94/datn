<?php
return [
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'webServiceClient' => [
            'class' => \common\components\WebSocketClient::className(),
            'host' => '127.0.0.1',
            'post' => '8080',
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=datn',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
            'enableSchemaCache' => true,
            // Duration of schema cache.
            'schemaCacheDuration' => 3600,
            // Name of the cache component used to store schema information
            'schemaCache' => 'cache',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            "enableSwiftMailerLogging" => true,
//            'useFileTransport' => true,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.gmail.com',
                'username' => 'namdt.98@gmail.com',
                'password' => 'ynmrfzsrowodfrbu',
                'port' => '465',
                'encryption' => 'ssl',
            ],
        ],
        'formatter' => [
            'class' => \common\helper\Formatter::className(),
            'timeZone' => \common\helper\DatetimeHelper::DEFAULT_TIMEZONE,
            'decimalSeparator' => '.',
            'thousandSeparator' => ',',
            'currencyCode' => ' VNĐ',
        ],
    ],
];
