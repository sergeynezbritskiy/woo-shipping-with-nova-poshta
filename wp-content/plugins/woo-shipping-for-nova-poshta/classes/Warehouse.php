<?php

namespace plugins\NovaPoshta\classes;

/**
 * Class Warehouse
 * @package plugins\NovaPoshta\classes
 */
class Warehouse extends Area
{

    /**
     * @return string
     */
    public static function table()
    {
        return NP()->db->prefix . self::KEY_WAREHOUSE;
    }

    /**
     * @param string $type
     * @return string
     */
    public static function key($type = '')
    {
        return parent::_key(self::KEY_WAREHOUSE, $type);
    }

}