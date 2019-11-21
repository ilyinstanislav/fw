<?php

/**
 * Class for working with array
 * Class ArrayHelper
 */
class ArrayHelper
{
    /**
     * @param array $array
     * @param string $value
     * @param null $defaultValue
     * @return mixed|null
     */
    public static function getValue($array, $value, $defaultValue = null)
    {
        if (isset($array[$value])) {
            return $array[$value];
        }
        return $defaultValue;
    }
}