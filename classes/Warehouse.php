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
    public static function key()
    {
        return self::KEY_WAREHOUSE;
    }
}