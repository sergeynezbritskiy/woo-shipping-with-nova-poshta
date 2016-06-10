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
    public static function key()
    {
        return City::KEY_CITY;
    }
}