<?php

class App extends \Yii
{
    public static function t($category, $message, $params = [], $language = null)
    {
        if (static::$app !== null) {
            $text = static::$app->getI18n()->translate($category, $message, $params, $language ?: static::$app->language);
        } else {
            $p = [];
            foreach ((array)$params as $name => $value) {
                $p['{' . $name . '}'] = $value;
            }

            $text = ($p === []) ? $message : strtr($message, $p);
        }
        if (isset($_GET['translate']) && $_GET['translate']) {
            return "<span class='text-translate' data-category='$category' data-message='$message' data-language='" . self::$app->language . "'>$text</span>";
        }
        return $text;
    }
}