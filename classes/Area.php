<?php

namespace plugins\NovaPoshta\classes;

/**
 * Class Area
 * @package plugins\NovaPoshta\classes
 */
class Area extends Location
{

    /**
     * @return string
     */
    public static function table()
    {
        return NP()->db->prefix . 'nova_poshta_area';
    }

    /**
     * Get areas by area name suggestion
     */
    public static function ajaxGetAreasBySuggestion()
    {
        $name = $_POST['name'];
        $areas = Area::findByNameSuggestion($name);
        foreach ($areas as & $area) {
            $area->getDescription();
            $area->getRef();
        }
        echo json_encode($areas);
        exit;
    }
}