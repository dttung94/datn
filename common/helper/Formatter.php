<?php
namespace common\helper;

use NumberFormatter;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;

class Formatter extends \yii\i18n\Formatter
{
    protected $_intlLoaded;

    public function init()
    {
        parent::init();
        $this->_intlLoaded = extension_loaded('intl');
    }

    function asDayOfWeek($value, $languageId = null, $isFull = true)
    {
        $dfw = date("N", $value);
        if (($day = DatetimeHelper::getDayOfWeek($dfw, $languageId, $isFull)) != null) {
            return $day;
        }
        return date("l", $value);
    }

    public function asCurrency($value, $currency = null, $options = [], $textOptions = [])
    {
        return number_format($value, 0, '.', ',');
    }

    public function addCurrency($value, $currency = null, $options = [], $textOptions = [])
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        $value = $this->normalizeNumericValue($value);

        if ($this->_intlLoaded) {
            $currency = $currency ?: $this->currencyCode;
            // currency code must be set before fraction digits
            // http://php.net/manual/en/numberformatter.formatcurrency.php#114376
            if ($currency && !isset($textOptions[NumberFormatter::CURRENCY_CODE])) {
                $textOptions[NumberFormatter::CURRENCY_CODE] = $currency;
            }
            $formatter = $this->createNumberFormatter(NumberFormatter::CURRENCY, null, $options, $textOptions);
            if ($currency === null) {
                //$result = $formatter->format($value);
                $result = number_format($value, 0, '.', ',');
            } else {
                //$result = $formatter->formatCurrency($value, $currency);
                $result = number_format($value, 0, '.', ',') . $currency;
            }
            if ($result === false) {
                throw new InvalidArgumentException('Formatting currency value failed: ' . $formatter->getErrorCode() . ' ' . $formatter->getErrorMessage());
            }

            return $result;
        }

        if ($currency === null) {
            if ($this->currencyCode === null) {
                throw new InvalidConfigException('The default currency code for the formatter is not defined and the php intl extension is not installed which could take the default currency from the locale.');
            }
            $currency = $this->currencyCode;
        }

        return $this->asDecimal($value, 0, $options, $textOptions) . $currency;
    }
}