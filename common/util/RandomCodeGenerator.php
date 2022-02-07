<?php
namespace common\util;


use common\entities\calendar\CouponInfo;
use common\entities\calendar\CouponCode;
use common\entities\calendar\CampaignCouponCode;
//ini_set('memory_limit','255M');

class RandomCodeGenerator {

    private $length = 16;
    private $map = [
        "0" => 0,
        "1" => 1,
        "2" => 2,
        "3" => 3,
        "4" => 4,
        "5" => 5,
        "6" => 6,
        "7" => 7,
        "8" => 8,
        "9" => 9,
        "A" => 10,
        "B" => 11,
        "C" => 12,
        "D" => 13,
        "E" => 14,
        "F" => 15,
        "G" => 16,
        "H" => 17,
        "I" => 18,
        "J" => 19,
        "K" => 20,
        "L" => 21,
        "M" => 22,
        "N" => 23,
        "O" => 24,
        "P" => 25,
        "Q" => 26,
        "R" => 27,
        "S" => 28,
        "T" => 29,
        "U" => 30,
        "V" => 31,
        "W" => 32,
        "X" => 33,
        "Y" => 34,
        "Z" => 35,
    ];

    /**
     * @param integer $amount
     * @return array
     */
    function generate($amount)
    {
        if ($amount <= 0) {
            return [];
        }
        $codes = [];
        $number = 0;
        $string = "";
        $completeCodes = new \SplFixedArray($amount);
        $index = 0;
        $finalCode = str_repeat('-', $this->length);

        do
        {
            $number = bin2hex(openssl_random_pseudo_bytes($this->length));
            $string = base_convert($number, 16, 36);

            $string = strtoupper(substr($string, 0, $this->length));
            $chars = str_split($string);

            if (!$this->isInArray($chars, $codes, 0) && !$this->isCodeExist($string))
            {
                $this->addToArray($chars, $codes, 0, $finalCode);
                $completeCodes[$index] = $finalCode;
                $index++;
            }

        } while($index < $amount);

        unset($codes);
        return $completeCodes;
    }

    /**
     * @param string $code
     * @return bool
     */
    private function isCodeExist($code) {
        if ($this->length == CouponCode::CODE_LENGTH) {
            return CouponCode::find()
                ->where(['coupon_code' => $code])
                ->exists();
        }
        $existInCampaignCode = CampaignCouponCode::find()
            ->andWhere(['coupon_code' => $code])
            ->exists();
        if ($existInCampaignCode) {
            return true;
        }
        return CouponInfo::find()
            ->where('LENGTH(coupon_code) = '.$this->length)
            ->andWhere(['coupon_code' => $code])
            ->exists();
    }

    /**
     * @param array $chars
     * @param array $codes
     * @param integer $index
     * @return bool
     */
    private function isInArray(&$chars, &$codes, $index)
    {
        if($index < $this->length - 2 && !is_array($codes))
        {
            if ($codes == implode("", array_slice($chars, $index+1))) {
                return true;
            }
            return false;
        }

        if (!isset($codes[$this->map[$chars[$index]]])) {
            return false;
        }
        else
        {
            if ($index == $this->length - 2) {
                return $codes[$this->map[$chars[$index]]] == $chars[$index+1];
            }

            if($index < $this->length - 2) {
                return $this->isInArray($chars, $codes[$this->map[$chars[$index]]], ++$index);
            }
        }
        return false;
    }

    /**
     * @param array $chars
     * @param array $codes
     * @param integer $index
     * @param string $finalCode
     */
    private function addToArray(&$chars, &$codes, $index, &$finalCode)
    {
        if($index < $this->length - 2)
        {
            if(!is_array($codes))
            {
                $code = $codes;
                $codes = [];
                $codes[$this->map[$code[0]]] = substr($code, 1);
                $finalCode = substr($finalCode, 0, $index).implode("", array_slice($chars, $index));
                return;
            }

            if(count($codes) == 1 && isset($codes[0]) && !is_array($codes[0]))
            {
                $code = $codes[0];
                unset($codes[0]);
                $codes[$this->map[$code[0]]] = substr($code, 1);
            }

            if(!isset($codes[$this->map[$chars[$index]]]))
            {
                $codes[$this->map[$chars[$index]]][] = implode("", array_slice($chars, $index+1));
                $finalCode = substr($finalCode, 0, $index).implode("", array_slice($chars, $index));
                return;

            }
            $finalCode[$index] = $chars[$index];

            $this->addToArray($chars, $codes[$this->map[$chars[$index]]], ++$index, $finalCode);
        }
        else{
            $codes[$this->map[$chars[$index]]][] = $chars[$index+1];
            $finalCode[$index] = $chars[$index];
            $finalCode[$index+1] = $chars[$index+1];
        }
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param int $length
     */
    public function setLength($length)
    {
        $this->length = $length;
    }
}