<?php

namespace plugins\NovaPoshta\classes;

use plugins\NovaPoshta\classes\base\OptionsHelper;

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
        return self::findByQuery($query);
    }

    /**
     * @param $name
     * @param $cityRef
     * @return Warehouse[]
     */
    public static function findByCityRefAndName($name, $cityRef)
    {
        $query = "SELECT * FROM " . self::table() . " WHERE `city_ref` = '" . $cityRef . "' AND (`description` LIKE CONCAT('%', '" . $name . "', '%') OR `description_ru` LIKE CONCAT('%', '" . $name . "', '%'))";
        return self::findByQuery($query);
    }

    /**
     * @return array
     */
    public static function ajaxGetWarehousesListByCityRef()
    {
        $cityRef = $_POST['city_ref'];
        $warehouses = Warehouse::findByCityRef($cityRef);
        $options = OptionsHelper::getList($warehouses);
        echo json_encode($options);
        exit;
    }

    /**
     * Get  warehouses list by name suggestion for current city
     */
    public static function ajaxGetWarehousesBySuggestion()
    {
        $cityRef = $_POST['city_ref'];
        $name = $_POST['name'];
        $warehouses = self::findByCityRefAndName($name, $cityRef);
        foreach ($warehouses as $warehouse) {
            $warehouse->getRef();
            $warehouse->getDescription();
        }
        echo json_encode($warehouses);
        exit;
    }
}