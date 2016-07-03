<?php
namespace plugins\NovaPoshta\classes\base;

use plugins\NovaPoshta\classes\Area;

/**
 * Class OptionsHelper
 * @package plugins\NovaPoshta\classes\base
 */
class OptionsHelper
{
    /**
     * @param Area[] $locations
     * @param bool $enableEmpty
     * @return array
     */
    public static function getList($locations, $enableEmpty = true)
    {
        $result = array();
        if ($enableEmpty) {
            $result[''] = __('Choose an option', NOVA_POSHTA_DOMAIN);
        }
        foreach ($locations as $location) {
            $result[$location->ref] = $location->description;
        }
        return $result;
    }
}
