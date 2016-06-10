<?php

namespace plugins\NovaPoshta\classes;

/**
 * Class Area
 * @package plugins\NovaPoshta\classes
 */
class Region extends Area
{
    /**
     * @var string
     */
    public static $key = 'nova_poshta_region';

    /**
     * @return string
     */
    public static function table()
    {
        return NP()->db->prefix . self::$key;
    }

    /**
     * Get areas by area name suggestion
     */
    public static function ajaxGetAreasBySuggestion()
    {
        $name = $_POST['name'];
        $areas = Region::findByNameSuggestion($name);
        foreach ($areas as & $area) {
            $area->getDescription();
            $area->getRef();
        }
        echo json_encode($areas);
        exit;
    }
}