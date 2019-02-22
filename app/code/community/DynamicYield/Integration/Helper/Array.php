<?php
/**
 * DynamicYield_Integration
 *
 * @category     DynamicYield
 * @package      DynamicYield_Integration
 * @author       Dynamic Yield Ltd <support@dynamicyield.com.com>
 * @copyright    Copyright (c) 2017 Dynamic Yield (https://www.dynamicyield.com)
 **/

/**
 * Class DynamicYield_Integration_Helper_Array
 */
class DynamicYield_Integration_Helper_Array
{
    /**
     * @param $array
     * @param $value
     * @param null $key
     * @return array
     */
    public function pluck($array, $value, $key = NULL) {
        $out = array();

        foreach ((array)$array as $k => $v) {
            if (!is_null($key)) {
                $out[$v[$key]] = $v[$value];
            } else {
                $out[] = $v[$value];
            }
        }

        return $out;
    }
}
