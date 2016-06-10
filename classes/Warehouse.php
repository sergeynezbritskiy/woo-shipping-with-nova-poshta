<?php

namespace plugins\NovaPoshta\classes;

use plugins\NovaPoshta\classes\base\OptionsHelper;

/**
 * Class Warehouse
 * @package plugins\NovaPoshta\classes
 */
class Warehouse extends Area
{
    /**
     * @return string
     */
    public static function areaType()
    {
        return self::WAREHOUSE_KEY;
    }

    /**
     * @return array
     */
    public static function ajaxGetWarehousesListByCityRef()
    {
        $cityRef = $_POST['city_ref'];
        $warehouses = Warehouse::findByParentAreaRef($cityRef);
        $options = OptionsHelper::getList($warehouses);
        echo json_encode($options);
        exit;
    }

    /**
     * Get  warehouses list by name suggestion for current city
     */
    public static function ajaxGetWarehousesByNameSuggestion()
    {
        $cityRef = $_POST['city_ref'];
        $name = $_POST['name'];
        $warehouses = self::findByNameSuggestion($name, $cityRef);
        foreach ($warehouses as $warehouse) {
            $warehouse->getRef();
            $warehouse->getDescription();
        }
        echo json_encode($warehouses);
        exit;
    }
}