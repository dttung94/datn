<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'frontend\controllers',
    'components' => [
        'view' => [
            'class' => \frontend\models\FrontendView::className(),
        ],
        'request' => [
            'class' => '\yii\web\Request',
            'enableCookieValidation' => false,
        ],
        'user' => [
            'identityClass' => \common\models\UserIdentity::className(),
            'enableAutoLogin' => true,
            'loginUrl' => ['site/login'],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '/' => 'site/index',
                'shop' => 'shop/view',

                '<url_id:[^/]+>' => 'url/view',
                'booking/worker/<worker_id:[^/]+>' => "site/booking",

                'shop/load-info' => 'shop/load-info',
                'shop/<shop_id:[^/]+>' => 'shop/view',
                'shop/<shop_id:[^/]+>/<worker_id:[^/]+>' => 'shop/view',

                'view/schedule' => 'worker/calendar',

                'file/<id:[^/]+>/<type:[^/]+>/view' => 'file/view',
                'file//<type:[^/]+>/view' => 'file/view',
                'file/<id:[^/]+>/down' => 'file/down',
                'forum/detail/<id:[^/]+>' => 'forum/detail',
                'forum/edit/<id:[^/]+>' => 'forum/edit',
                'booking/history/<page:[^/]+>' => 'booking/history',
                'site/invite/<inviteCode:[^/]+>' => 'site/invite',
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
            ],
        ],
        'i18n' => [
            'translations' => [
                'frontend.*' => [
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
