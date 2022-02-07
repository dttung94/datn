<?php
namespace console\models;


use common\entities\international\LanguageInfo;
use common\helper\ArrayHelper;
use yii\console\Controller;

class ConsoleController extends Controller
{
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            date_default_timezone_set(ArrayHelper::getValue(\App::$app->params, "timezone"));
//            $languageId = LanguageInfo::getDefaultLanguageId();
//            if (($languageInfo = LanguageInfo::findOne($languageId))) {//todo set formatter
//                \App::$app->language = $languageId;
//                \App::$app->formatter->timeFormat = $languageInfo->time_format;
//                \App::$app->formatter->dateFormat = $languageInfo->date_format;
//                \App::$app->formatter->datetimeFormat = $languageInfo->datetime_format;
//            }
            return true;
        }
        return false;
    }
}