<?php

namespace plugins\NovaPoshta\classes;

/**
 * Class Area
 * @package plugins\NovaPoshta\classes
 */
class Area extends Location
{

    /**
     * @return string
     */
    public static function table()
    {
        return NP()->db->prefix . 'nova_poshta_area';
    }
}