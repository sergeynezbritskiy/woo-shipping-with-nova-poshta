<?php

namespace plugins\NovaPoshta\classes;

/**
 * Class Warehouse
 * @package plugins\NovaPoshta\classes
 */
class Warehouse extends Location
{

    /**
     * @return string
     */
    public static function table()
    {
        return NP()->db->prefix . 'nova_poshta_warehouse';
    }

    /**
     * @param string $cityRef
     * @return Warehouse[]
     */
    public static function findByCityRef($cityRef)
    {
        $query = NP()->db->prepare("SELECT * FROM " . self::table() . " WHERE `city_ref` = '%s'", $cityRef);
        $result = NP()->db->get_results($query);
        $cities = array();
        foreach ($result as $items) {
            $cities[] = new Warehouse($items);
        }
        return $cities;
    }
}