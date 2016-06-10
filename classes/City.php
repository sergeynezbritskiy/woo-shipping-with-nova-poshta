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
     * @return void
     */
    public static function ajaxGetCitiesListByAreaRef()
    {
        $areaRef = $_POST['area_ref'];
        $cities = City::findByParentAreaRef($areaRef);
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
        $cities = self::findByNameSuggestion($name, $areaRef);
        foreach ($cities as $city) {
            $city->getRef();
            $city->getDescription();
        }
        echo json_encode($cities);
        exit;
    }
}