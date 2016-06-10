<?php

namespace plugins\NovaPoshta\classes;

use NovaPoshta;
use plugins\NovaPoshta\classes\base\Base;
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
abstract class Area extends Base
{

    const REGION_KEY = 'nova_poshta_region';
    const CITY_KEY = 'nova_poshta_city';
    const WAREHOUSE_KEY = 'nova_poshta_warehouse';

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
     * @return string
     */
    public static function table()
    {
        return NP()->db->prefix . 'nova_poshta_area';
    }

    /**
     * @return Area[]
     */
    public static function findAll()
    {
        $query = "SELECT * FROM " . static::table();
        return self::findByQuery($query);
    }

    /**
     * @param string $ref
     * @return Area
     */
    public static function findByRef($ref)
    {
        $query = NP()->db->prepare("SELECT * FROM " . static::table() . " WHERE Ref = %s", $ref);
        $result = NP()->db->get_row($query);
        return new static($result);
    }

    /**
     * @param $name
     * @return Area[]
     */
    public static function findByNameSuggestion($name)
    {
        $query = "SELECT * FROM " . static::table() . " WHERE "
            . "(`description` LIKE CONCAT('%', '" . $name . "', '%') "
            . "OR `description_ru` LIKE CONCAT('%', '" . $name . "', '%'))"
            . " AND (`area_type`='" . static::areaType() . "')";
        return self::findByQuery($query);
    }

    /**
     * @param string $query
     * @return Area[]
     */
    public static function findByQuery($query)
    {
        $result = NP()->db->get_results($query);
        $locations = array();
        foreach ($result as $item) {
            $locations[] = new static($item);
        }
        return $locations;
    }

    public static function areaType()
    {
        _doing_it_wrong("Area Type", "You should not call this method from abstract class", "1.0.0");
    }

    /**
     * @return mixed
     */
    protected function getLocale()
    {
        $this->locale = get_locale();
        return $this->locale;
    }

    /**
     * @return string
     */
    protected function getContent()
    {
        $query = NP()->db->prepare("SELECT * FROM " . static::table() . " WHERE `ref`='%s'", $this->ref);
        $this->content = NP()->db->get_row($query);
        return $this->content;
    }

    /**
     * @return string
     */
    protected function getDescription()
    {
        $this->description = (($this->locale == NovaPoshta::LOCALE_RU) && $this->content->description_ru)
            ? $this->content->description_ru
            : $this->content->description;
        return $this->description;
    }

    /**
     * @return string
     */
    protected function getRef()
    {
        $this->ref = $this->content->ref;
        return $this->ref;
    }
}