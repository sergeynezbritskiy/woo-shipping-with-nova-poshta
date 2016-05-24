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

    /**
     * @param string $area
     * @return City[]
     */
    public static function findByAreaRef($area)
    {
        $query = NP()->db->prepare("SELECT * FROM " . self::table() . " WHERE `area_ref` = '%s'", $area);
        $result = NP()->db->get_results($query);
        $cities = array();
        foreach ($result as $items) {
            $cities[] = new City($items);
        }
        return $cities;
    }

}