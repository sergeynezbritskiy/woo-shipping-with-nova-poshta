<?php

namespace plugins\NovaPoshta\classes;

/**
 * Class Area
 * @package plugins\NovaPoshta\classes
 */
class Region extends Area
{

    public static function areaType()
    {
        return Region::REGION_KEY;
    }

    /**
     * Get areas by area name suggestion
     */
    public static function ajaxGetRegionsByNameSuggestion()
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