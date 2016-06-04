<?php
namespace plugins\NovaPoshta\classes\base;
use plugins\NovaPoshta\classes\Location;

/**
 * Class OptionsHelper
 * @package plugins\NovaPoshta\classes\base
 */
class OptionsHelper
{
    /**
     * @param Location[] $locations
     * @return array
     */
    public static function getList($locations)
    {
        $result = array('' => __('Choose an option', NOVA_POSHTA_DOMAIN));
        foreach ($locations as $location) {
            $result[$location->ref] = $location->description;
        }
        return $result;
    }
}
