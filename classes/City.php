<?php

namespace plugins\NovaPoshta\classes;

/**
 * Class City
 * @package plugins\NovaPoshta\classes
 */
class City extends Location
{
    /**
     * @return string
     */
    public static function table()
    {
        return NP()->db->prefix . 'nova_poshta_city';
    }

}