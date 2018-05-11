<?php

namespace plugins\NovaPoshta\classes;

use NovaPoshta;
use plugins\NovaPoshta\classes\base\ArrayHelper;
use plugins\NovaPoshta\classes\base\Base;
use plugins\NovaPoshta\classes\base\OptionsHelper;
use stdClass;

/**
 * Class Location
 * @package plugins\NovaPoshta\classes
 *
 * @property stdClass content
 * @property string description
 * @property string ref
 * @property string locale
 */
class Area extends Base
{
    const KEY_REGION = 'nova_poshta_region';
    const KEY_CITY = 'nova_poshta_city';
    const KEY_WAREHOUSE = 'nova_poshta_warehouse';

    const BILLING = 'billing';
    const SHIPPING = 'shipping';

    /**
     * @return string
     */
    public static function table()
    {
        _doing_it_wrong("Area table", "You have to override this method in child classes", "2.1.0");
        return '';
    }

    /**
     * Ajax requests entry point
     */
    public static function ajaxGetAreasByNameSuggestion()
    {
        $areaRef = ArrayHelper::getValue($_POST, 'parent_ref', null);
        $name = ArrayHelper::getValue($_POST, 'name', null);

        $areas = self::findByParentRefAndNameSuggestion($areaRef, $name);
        $result = OptionsHelper::getList($areas, false);
        echo json_encode($result);
        exit;

    }

    /**
     * @return Area[]
     */
    public static function findAll()
    {
        return static::findByParentRefAndNameSuggestion();
    }

    /**
     * @param string|null $parentRef
     * @param string|null $name
     * @return Area[]
     */
    public static function findByParentRefAndNameSuggestion($parentRef = null, $name = null)
    {
        $searchCriteria = [];
        $searchCriteria[] = '(1=1)';
        if ($parentRef !== null) {
            $searchCriteria[] = static::getParentRefSearchCriteria($parentRef);
        }
        if ($name !== null) {
            $searchCriteria[] = static::getNameSearchCriteria($name);
        }
        $table = static::table();
        $query = "SELECT * FROM $table WHERE " . implode(' AND ', $searchCriteria);
        return self::findByQuery($query);
    }

    /**
     * @param string $query
     * @return Area[]
     */
    public static function findByQuery($query)
    {
        $result = NP()->db->get_results($query);
        return array_map(function ($location) {
            return new static($location);
        }, $result);
    }

    /**
     * @return string
     */
    public static function key()
    {
        _doing_it_wrong("Area Type", "You should not call this method from abstract class", "1.0.0");
        return '';
    }

    /**
     * @param string $name
     * @param string $type
     * @return string
     */
    protected static function _key($name, $type)
    {
        return $type ? $type . '_' . $name : $name;
    }

    /**
     * @param string $parentRef
     * @return string
     */
    protected static function getParentRefSearchCriteria($parentRef)
    {
        NP()->db->escape_by_ref($parentRef);
        return sprintf("(`parent_ref` = '%s')", $parentRef);
    }

    /**
     * @param string $name
     * @return string
     */
    protected static function getNameSearchCriteria($name)
    {
        NP()->db->escape_by_ref($name);
        return sprintf("(`description` LIKE CONCAT('%s', '%%') OR `description_ru` LIKE CONCAT('%s', '%%'))", $name, $name);
    }

    /**
     * Location constructor.
     * @param $ref
     */
    public function __construct($ref)
    {
        if (is_string($ref)) {
            $this->ref = $ref;
        } else {
            $this->content = json_decode(json_encode($ref));
        }
    }

    /**
     * @return mixed
     */
    protected function getLocale()
    {
        return get_locale();
    }

    /**
     * @return string
     */
    protected function getContent()
    {
        $table = static::table();
        $query = NP()->db->prepare("SELECT * FROM $table WHERE ref = %s", $this->ref);
        return NP()->db->get_row($query);
    }

    /**
     * @return string
     */
    protected function getDescription()
    {
        return (($this->locale == NovaPoshta::LOCALE_RU) && $this->content->description_ru)
            ? $this->content->description_ru
            : $this->content->description;
    }

    /**
     * @return string
     */
    protected function getRef()
    {
        return $this->content->ref;
    }

}