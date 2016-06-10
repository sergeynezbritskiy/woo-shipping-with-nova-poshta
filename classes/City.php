<?php

namespace plugins\NovaPoshta\classes;

use plugins\NovaPoshta\classes\base\OptionsHelper;

/**
 * Class City
 * @package plugins\NovaPoshta\classes
 */
class City extends Area
{
    public static function areaType()
    {
        return City::CITY_KEY;
    }

    /**
     * @param string $area
     * @return City[]
     */
    public static function findByAreaRef($area)
    {
        $query = NP()->db->prepare("SELECT * FROM " . static::table() . " WHERE `parent_area_ref` = '%s' AND `area_type`='%s'", $area, static::areaType());
        return self::findByQuery($query);
    }

    /**
     * @param string $name
     * @param string $areaRef
     * @return City[]
     */
    public static function findByAreaRefAndName($name, $areaRef)
    {
        $query = "SELECT * FROM " . static::table() . " WHERE `parent_area_ref` = '" . $areaRef . "' AND (`description` LIKE CONCAT('%', '" . $name . "', '%') OR `description_ru` LIKE CONCAT('%', '" . $name . "', '%')) AND `area_type`='" . static::areaType() . "'";
        return self::findByQuery($query);
    }

    /**
     * @return void
     */
    public static function ajaxGetCitiesListByAreaRef()
    {
        $areaRef = $_POST['area_ref'];
        $cities = City::findByAreaRef($areaRef);
        $optionsList = OptionsHelper::getList($cities);
        echo json_encode($optionsList);
        exit;
    }

    /**
     * @return void
     */
    public static function ajaxGetCitiesByNameSuggestion()
    {
        $areaRef = $_POST['area_ref'];
        $name = $_POST['name'];
        $cities = self::findByAreaRefAndName($name, $areaRef);
        foreach ($cities as $city) {
            $city->getRef();
            $city->getDescription();
        }
        echo json_encode($cities);
        exit;
    }
}