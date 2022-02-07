<?php
namespace backend\models;

use common\entities\international\LanguageInfo;
use common\entities\user\UserConfig;
use common\helper\ArrayHelper;
use common\models\base\AbstractController;

/**
 * Class BackendController
 * @package backend\models
 */
class BackendController extends AbstractController
{
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            date_default_timezone_set(ArrayHelper::getValue(\App::$app->params, "timezone"));
//            $languageId = LanguageInfo::getDefaultLanguageId();
//            if (!\Yii::$app->user->isGuest) {
//                $languageId = UserConfig::getValue(UserConfig::KEY_LANGUAGE, $this->userInfo->user_id, $languageId);
//            }
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