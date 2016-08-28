<?php

namespace plugins\NovaPoshta\classes;
use plugins\NovaPoshta\classes\base\ArrayHelper;

/**
 * Class City
 * @package plugins\NovaPoshta\classes
 */
class City extends Area
{

    /**
     * @param string $type
     * @return string
     */
    public static function key($type = '')
    {
        return parent::_key(self::KEY_CITY, $type);
    }

    /**
     * @return void
     */
    public static function ajaxGetCitiesByNameSuggestion()
    {
        $areaRef = ArrayHelper::getValue($_POST, 'parent_area_ref', null);
        $name = $_POST['name'];
        if(is_null($areaRef)){
            $areas = self::findByNameSuggestion($name);
        }else{
            $areas = self::findByNameSuggestionAndParentArea($name, $areaRef);
        }
        foreach ($areas as $area) {
            $area->getRef();
            $area->getDescription();
        }
        echo json_encode($areas);
        exit;
    }
}