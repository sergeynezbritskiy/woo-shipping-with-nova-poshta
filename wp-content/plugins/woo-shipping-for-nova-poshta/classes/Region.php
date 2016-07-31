<?php

namespace plugins\NovaPoshta\classes;

/**
 * Class Area
 * @package plugins\NovaPoshta\classes
 */
class Region extends Area
{
    /**
     * @param string $type
     * @return string
     */
    public static function key($type = '')
    {
        return parent::_key(self::KEY_REGION, $type);
    }
}