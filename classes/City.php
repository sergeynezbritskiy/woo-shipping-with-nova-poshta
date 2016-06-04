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
        return self::findByQuery($query);
    }

    /**
     * @param string $areaRef
     * @return array
     */
    public static function getCitiesListByAreaRef($areaRef)
    {
        $result = array('' => __('Choose an option', NOVA_POSHTA_DOMAIN));
        $cities = City::findByAreaRef($areaRef);
        /** @var City $city */
        foreach ($cities as $city) {
            $result[$city->ref] = $city->description;
        }
        return $result;
    }

    /**
     * @return array
     */
    public static function ajaxGetCitiesListByAreaRef()
    {
        $areaRef = $_POST['area_ref'];
        echo json_encode(self::getCitiesListByAreaRef($areaRef));
        exit;
    }

    /**
     * @param string $name
     * @param string $areaRef
     * @return Location[]
     */
    private static function findByAreaRefAndName($name, $areaRef)
    {
        $query = "SELECT * FROM " . self::table() . " WHERE `area_ref` = '" . $areaRef . "' AND (`description` LIKE CONCAT('%', '" . $name . "', '%') OR `description_ru` LIKE CONCAT('%', '" . $name . "', '%'))";
        return self::findByQuery($query);
    }

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