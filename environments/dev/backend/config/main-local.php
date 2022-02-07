<?php

$config = [
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '',
        ],
        'urlManagerFrontend' => [
            "class" => \yii\web\UrlManager::className(),
            'baseUrl' => "http://datn.lc",
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '/' => 'site/index',
                '<controller>/<action:\d+>' => '/<controller>/<action:\d+>',
            ]
        ],
    ],
];

//if (!YII_ENV_TEST) {
//    // configuration adjustments for 'dev' environment
//    $config['bootstrap'][] = 'debug';
//    $config['modules']['debug'] = [
//        'class' => 'yii\debug\Module',
//        'allowedIPs' => ['*'],
//    ];
//
//    $config['bootstrap'][] = 'gii';
//    $config['modules']['gii'] = [
//        'class' => 'yii\gii\Module',
//        'allowedIPs' => ['*'],
//    ];
//}

return $config;
