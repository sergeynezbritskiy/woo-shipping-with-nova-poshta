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
    public static function key()
    {
        return Region::KEY_REGION;
    }
}