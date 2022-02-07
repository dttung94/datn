<?php
namespace frontend\models;


use common\entities\international\LanguageInfo;
use common\entities\user\UserInfo;
use common\helper\ArrayHelper;
use common\models\base\AbstractController;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

/**
 * Class FrontendController
 * @package frontend\models
 */
abstract class FrontendController extends AbstractController
{
    public $layout = "layout_member";

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
            if ($this->userInfo) {
                if (//todo check user is valid
                    $this->userInfo->role != UserInfo::ROLE_USER ||
                    !in_array($this->userInfo->status, [
                        UserInfo::STATUS_ACTIVE,
                    ])
                ) {
                    \App::$app->user->logout();
                    return $this->redirect("/site/login");
                }
            }
            return true;
        }
        return false;
    }

    public function afterAction($action, $result)
    {
        if (!\App::$app->user->isGuest && $this->userInfo) {
            if (
                $this->userInfo->status == UserInfo::STATUS_SHOP_BLACK_LIST &&
                $action->uniqueId != "site/index" && $action->uniqueId != "site/error"
            ) {
                return $this->redirect([
                    "/site/index"
                ]);
            }
        }
        return parent::afterAction($action, $result);
    }
}