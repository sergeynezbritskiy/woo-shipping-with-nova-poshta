<?php

namespace plugins\NovaPoshta\classes;

/**
 * Class Area
 * @package plugins\NovaPoshta\classes
 */
class Region extends Area
{

    /**
     * @return string
     */
    public static function table()
    {
        return NP()->db->prefix . self::KEY_REGION;
    }

    /**
     * @param string $type
     * @return string
     */
    public static function key($type = '')
    {
        return parent::_key(self::KEY_REGION, $type);
    }
}