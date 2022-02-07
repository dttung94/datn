<?php
namespace backend\modules\service\forms\mail;

use common\entities\international\LanguageInfo;
use common\entities\international\LanguageSource;
use common\entities\international\LanguageTranslate;
use common\forms\service\SendMailForm;
use common\models\base\AbstractForm;
use yii\helpers\ArrayHelper;

/**
 * Class MailTemplateSearchForm
 * @package backend\modules\service\forms\mail
 *
 * @var string $key
 * @var string $language
 * @var string $title
 * @var string $content
 */
class MailTemplateSearchForm extends AbstractForm
{
    const
        MAIL_TEMPLATE_CATEGORY = "template.mail";
    public $key, $language, $title, $content;

    public function __construct($key, $config = [])
    {
        parent::__construct($config);
        $this->key = $key;
        $this->title = $this->getTitle();
        $this->content = $this->getContent();
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['key', "language", "title", "content"], 'required'],
            [['key', "language", "title", "content"], 'safe'],
            [['key'], 'validateKey'],
        ]);
    }

    /**
     * @return bool
     */
    public function validateKey()
    {
        if ($this->getTemplateTitleSource() != null && $this->getTemplateContentSource() != null) {
            return true;
        }
        return false;
    }

    public function getTitle()
    {
        if (($source = $this->getTemplateTitleSource()) !== null) {
            $trans = LanguageTranslate::find()
                ->where("id = :id", [
                    "id" => $source->id
                ])
                ->andWhere("language = :language", [
                    ":language" => $this->language
                ])
                ->one();
            if ($trans != null) {
                /**
                 * @var $trans LanguageTranslate
                 */
                return $trans->translation;
            } else {
                return $source->message;
            }

        }
        return "";
    }

    public function getContent()
    {
        if (($source = $this->getTemplateContentSource()) !== null) {
            $trans = LanguageTranslate::find()
                ->where("id = :id", [
                    "id" => $source->id
                ])
                ->andWhere("language = :language", [
                    ":language" => $this->language
                ])
                ->one();
            if ($trans != null) {
                /**
                 * @var $trans LanguageTranslate
                 */
                return $trans->translation;
            } else {
                return $source->message;
            }

        }
        return "";
    }

    public function getListLanguage()
    {
        $languages = LanguageInfo::findAll([
            "status" => LanguageInfo::STATUS_ACTIVE
        ]);
        return ArrayHelper::map($languages, "language_id", "name");
    }

    public function toSave()
    {
        if ($this->validate()) {
            $trans = $this->getDb()->beginTransaction();
            //TODO save title
            $titleSource = $this->getTemplateTitleSource();
            $transTitle = LanguageTranslate::find()
                ->where("id = :id", [
                    "id" => $titleSource->id
                ])
                ->andWhere("language = :language", [
                    ":language" => $this->language
                ])
                ->one();
            if ($transTitle == null) {
                $transTitle = new LanguageTranslate();
                $transTitle->id = $titleSource->id;
                $transTitle->language = $this->language;
            }
            $transTitle->translation = $this->title;
            if (!$transTitle->save()) {
                $this->addErrors($transTitle->getErrors());
            }
            //TODO save content
            $contentSource = $this->getTemplateContentSource();
            $transContent = LanguageTranslate::find()
                ->where("id = :id", [
                    "id" => $contentSource->id
                ])
                ->andWhere("language = :language", [
                    ":language" => $this->language
                ])
                ->one();
            if ($transContent == null) {
                $transContent = new LanguageTranslate();
                $transContent->id = $contentSource->id;
                $transContent->language = $this->language;
            }
            $transContent->translation = $this->content;
            if (!$transContent->save()) {
                $this->addErrors($transContent->getErrors());
            }
            if (!$this->hasErrors()) {
                $trans->commit();
                return true;
            } else {
                $trans->rollBack();
            }
        }
        return false;
    }

    /**
     * @return null|LanguageSource
     */
    private function getTemplateTitleSource()
    {
        return LanguageSource::find()
            ->where("category = :category", [
                ":category" => self::MAIL_TEMPLATE_CATEGORY . "." . $this->key . ".title"
            ])
            ->one();
    }

    /**
     * @return null|LanguageSource
     */
    private function getTemplateContentSource()
    {
        return LanguageSource::find()
            ->where("category = :category", [
                ":category" => self::MAIL_TEMPLATE_CATEGORY . "." . $this->key . ".content"
            ])
            ->one();
    }

    public function getTemplateParams()
    {
        $templates = SendMailForm::$defaultVal;
        $template = isset($templates[$this->key]) ? $templates[$this->key] : [];
        return ArrayHelper::merge(SendMailForm::$defaultParams, (isset($template['params'])) ? $template['params'] : []);
    }


    public static function initEmailTemplate()
    {
        //todo delete all email template
        $templates = LanguageSource::find()
            ->where(['like', 'category', self::MAIL_TEMPLATE_CATEGORY])
            ->all();
        foreach ($templates as $index => $template) {
            /**
             * @var $template LanguageSource
             */
            //todo delete all template translate
            LanguageTranslate::deleteAll([
                "id" => $template->id
            ]);
            //todo delete template source
            $template->delete();
        }
        //todo init all email template
        $defaultTemplates = SendMailForm::$defaultVal;
        foreach ($defaultTemplates as $key => $val) {
            SendMailForm::getEmailTemplate($key, "title");
            SendMailForm::getEmailTemplate($key, "content");
        }
    }

    public static function getListTemplates()
    {
        return SendMailForm::$defaultVal;
    }
}