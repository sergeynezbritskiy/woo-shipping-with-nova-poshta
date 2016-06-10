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

    public static function areaType()
    {
        _doing_it_wrong("Area Type", "You should not call this method from abstract class", "1.0.0");
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
        $table = static::table();
        $query = NP()->db->prepare("SELECT * FROM $table WHERE `area_type` = %s", static::areaType());
        return self::findByQuery($query);
    }

    /**
     * @param string $areaRef
     * @return Area[]
     */
    public static function findByParentAreaRef($areaRef)
    {
        $query = NP()->db->prepare("SELECT * FROM " . static::table() . " WHERE `parent_area_ref` = '%s' AND `area_type`='%s'", $areaRef, static::areaType());
        return self::findByQuery($query);
    }

    /**
     * @param string $name
     * @param string $parentRef
     * @return Area[]
     */
    public static function findByNameSuggestion($name, $parentRef = null)
    {
        $table = static::table();
        $type = static::areaType();
        $query = "SELECT * FROM $table WHERE "
            . "(`description` LIKE CONCAT('%', '$name', '%') OR `description_ru` LIKE CONCAT('%', '$name', '%'))"
            . " AND (`area_type`='$type')";
        if (is_null($parentRef)) {
            $query .= " AND (`parent_area_ref` IS NULL)";
        } else {
            $query .= " AND (`parent_area_ref` = '$parentRef')";
        }
        return self::findByQuery($query);
    }

    /**
     * @param string $ref
     * @return Area
     */
    public static function findByRef($ref)
    {
        $table = static::table();
        $query = NP()->db->prepare("SELECT * FROM $table WHERE Ref = %s AND `area_type` = %s", $ref, static::areaType());
        $result = NP()->db->get_row($query);
        return new static($result);
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
        $table = static::table();
        $query = NP()->db->prepare("SELECT * FROM $table WHERE Ref = %s AND `area_type` = %s", $this->ref, static::areaType());
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