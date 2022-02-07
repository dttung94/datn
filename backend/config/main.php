<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-backend',
    'language' => 'ja',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => [
        'system' => [
            "class" => backend\modules\system\SystemModule::className(),
            'layoutPath' => dirname(__DIR__) . '/views/layouts',
        ],
        'service' => [
            "class" => \backend\modules\service\ServiceModule::className(),
            'layoutPath' => dirname(__DIR__) . '/views/layouts',
        ],
        "data" => [
            "class" => \backend\modules\data\DataModule::className(),
            'layoutPath' => dirname(__DIR__) . '/views/layouts',
        ],
        "worker" => [
            "class" => \backend\modules\worker\WorkerModule::className(),
            'layoutPath' => dirname(__DIR__) . '/views/layouts',
        ],
        "shop" => [
            "class" => \backend\modules\shop\ShopModule::className(),
            'layoutPath' => dirname(__DIR__) . '/views/layouts',
        ],
        "member" => [
            "class" => \backend\modules\member\MemberModule::className(),
            'layoutPath' => dirname(__DIR__) . '/views/layouts',
        ],
        "calendar" => [
            "class" => \backend\modules\calendar\CalendarModule::className(),
            'layoutPath' => dirname(__DIR__) . '/views/layouts',
        ],
        "rating" => [
            "class" => \backend\modules\rating\RatingModule::className(),
            'layoutPath' => dirname(__DIR__) . '/views/layouts',
        ],
    ],
    'components' => [
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
//                'file' => [
//                    'class' => 'yii\log\FileTarget',
//                    'levels' => ['error', 'warning'],
//                ],
                'db' => [
                    'class' => common\forms\system\SystemLogForm::className(),
                    'levels' => ['error', 'warning'],
                    'logTable' => 'system_log'
                ]
            ],
        ],
        'view' => [
            'class' => backend\models\BackendView::className(),
        ],
        'user' => [
            'identityClass' => \common\models\UserIdentity::className(),
            'enableAutoLogin' => true,
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ],
        'i18n' => [
            'translations' => [
                'backend.*' => [
                    'class' => 'yii\i18n\DbMessageSource',
                    'db' => 'db',
                    'sourceMessageTable' => 'language_source',
                    'messageTable' => 'language_translate',
                    "on missingTranslation" => [\common\components\TranslationEventHandler::className(), 'handleMissingTranslation']
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
    ],
    'params' => $params,
];
