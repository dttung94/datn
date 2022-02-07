<?php
namespace common\components;

use common\entities\international\LanguageSource;
use common\entities\international\LanguageTranslate;
use yii\base\Component;
use yii\i18n\MissingTranslationEvent;

class TranslationEventHandler extends Component
{
    public static function handleMissingTranslation(MissingTranslationEvent $event)
    {
        $langSource = LanguageSource::find()->where("category = :category AND message = :message", [
            ":category" => $event->category,
            ":message" => $event->message
        ])->one();
        if ($langSource == null) {
            //TODO add category
            $langSource = new LanguageSource();
            $langSource->category = $event->category;
            $langSource->message = $event->message;
            if (!$langSource->save()) {
            }
        }
        //TODO add translate
        $langTranslate = LanguageTranslate::getTranslate($langSource->id, $event->language);
        if ($langTranslate == null) {
            $langTranslate = new LanguageTranslate();
            $langTranslate->id = $langSource->id;
            $langTranslate->language = $event->language;
        }
        $langTranslate->translation = $event->message;
        $langTranslate->status = LanguageTranslate::STATUS_AUTO;
        $langTranslate->save();
        $event->translatedMessage = $langTranslate->translation;
    }
}