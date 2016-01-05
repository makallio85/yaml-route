<?php

namespace makallio85\YamlRoute;

/**
 * Class ConversionTrait
 *
 * @package makallio85\YamlRoute
 */
trait ConversionTrait
{
    /**
     * Transfer array into string in format ['key' => 'value']
     *
     * @param $array
     *
     * @return string
     */
    private function _arrToStr($array)
    {
        $str = '[';
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = self::_arrToStr($value);
                $str .= "'$key' => $value, ";
            } else {
                $str .= "'$key' => '$value', ";
            }
        }
        if (strlen($str) > 1) {
            $str = substr(trim($str), 0, -1);
        }

        return $str . ']';
    }

    /**
     * @param $string
     *
     * @return mixed
     */
    private function _varsToString($string)
    {
        $string = str_replace('{', ':', $string);

        return str_replace('}', '', $string);
    }
}