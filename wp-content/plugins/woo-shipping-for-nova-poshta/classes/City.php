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
     * @return string
     */
    public static function table()
    {
        return NP()->db->prefix . 'nova_poshta_city';
    }

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
        $result = [];
        foreach ($areas as $area) {
            $result[] = [
                'ref' => $area->ref,
                'description' => $area->description,
            ];
        }
        echo json_encode($result);
        exit;
    }
}