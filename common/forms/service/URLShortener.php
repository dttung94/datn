<?php
namespace common\forms\service;


use common\entities\system\SystemConfig;
use common\entities\system\SystemSortURL;
use common\helper\ArrayHelper;
use common\models\base\AbstractForm;
use linslin\yii2\curl\Curl;
use yii\base\InvalidArgumentException;
use yii\helpers\Json;

/**
 * Class URLShortener
 * @package common\forms\service
 */
class URLShortener extends AbstractForm
{
    public static function shortenLongUrl($url, $description = "")
    {
        return self::_shortenLongUrlViaSystem($url, $description);
    }

    private static function _shortenLongUrlViaSystem($url, $description = "")
    {
        $model = SystemSortURL::findOne([
            "url" => $url,
        ]);
        if ($model == null) {
            $model = new SystemSortURL();
            $model->id = SystemSortURL::genId();
            $model->url = $url;
            $model->description = $description;
            $model->setExpireDate(24 * 30);
            $model->status = SystemSortURL::STATUS_ACTIVE;
            $model->total_access = 0;
            if (!$model->save()) {
                throw new InvalidArgumentException();
            }
        } elseif (!$model->isValid()) {
            $model->setExpireDate(24 * 30);
            $model->status = SystemSortURL::STATUS_ACTIVE;
            $model->description = $description;
            if (!$model->save()) {
                throw new InvalidArgumentException();
            }
        }
        if (\App::$app->has("urlManagerFrontend")) {
            return \App::$app->urlManagerFrontend->createAbsoluteUrl([
                "$model->id",
            ]);
        } else {
            return \App::$app->urlManager->createAbsoluteUrl([
                "$model->id",
            ]);
        }
    }

    private static function _shortenLongUrlViaGoogle($url)
    {
        $apiURL = "https://www.googleapis.com/urlshortener/v1/url?key=AIzaSyBTQgwUQ7f6IS3HUqsT8_PYKY71RbNeNCw";
        $curl = new Curl();
        $response = $curl->setHeader("Content-Type", "application/json")->setRequestBody(Json::encode([
            "longUrl" => $url,
        ]))->post($apiURL);
        if ($curl->errorCode === null) {
            $data = Json::decode($response);
            return ArrayHelper::getValue($data, "id");
        } else {
            return false;
        }
    }
}