<?php

namespace plugins\NovaPoshta\classes;
use plugins\NovaPoshta\classes\base\OptionsHelper;

/**
 * Class City
 * @package plugins\NovaPoshta\classes
 */
class City extends Location
{
    /**
     * @var string
     */
    public static $key = 'nova_poshta_city';

    /**
     * @return string
     */
    public static function table()
    {
        return NP()->db->prefix . self::$key;
    }

    /**
     * @param string $area
     * @return City[]
     */
    public static function findByAreaRef($area)
    {
        $query = NP()->db->prepare("SELECT * FROM " . self::table() . " WHERE `area_ref` = '%s'", $area);
        return self::findByQuery($query);
    }

    /**
     * @param string $name
     * @param string $areaRef
     * @return City[]
     */
    public static function findByAreaRefAndName($name, $areaRef)
    {
        $query = "SELECT * FROM " . self::table() . " WHERE `area_ref` = '" . $areaRef . "' AND (`description` LIKE CONCAT('%', '" . $name . "', '%') OR `description_ru` LIKE CONCAT('%', '" . $name . "', '%'))";
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
    public static function ajaxGetCitiesBySuggestion()
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