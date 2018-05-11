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

    /**
     * @param string $name
     * @return string
     */
    protected static function getNameSearchCriteria($name)
    {
        NP()->db->escape_by_ref($name);
        return sprintf("(`description` LIKE CONCAT('%%', '%s', '%%') OR `description_ru` LIKE CONCAT('%%', '%s', '%%'))", $name, $name);
    }

}