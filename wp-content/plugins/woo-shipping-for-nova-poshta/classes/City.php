<?php

namespace plugins\NovaPoshta\classes;

/**
 * Class City
 * @package plugins\NovaPoshta\classes
 */
class City extends Area
{

    /**
     * @return string
     */
    public static function table()
    {
        return NP()->db->prefix . self::KEY_CITY;
    }

    /**
     * @param string $type
     * @return string
     */
    public static function key($type = '')
    {
        return parent::_key(self::KEY_CITY, $type);
    }
}