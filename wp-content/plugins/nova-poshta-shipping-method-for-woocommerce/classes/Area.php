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
abstract class Area extends Base
{
    const KEY_REGION = 'nova_poshta_region';
    const KEY_CITY = 'nova_poshta_city';
    const KEY_WAREHOUSE = 'nova_poshta_warehouse';

    const BILLING = 'billing';
    const SHIPPING = 'shipping';

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
    public static function key()
    {
        _doing_it_wrong("Area Type", "You should not call this method from abstract class", "1.0.0");
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
        $query = NP()->db->prepare("SELECT * FROM $table WHERE `area_type` = %s", static::key());
        return self::findByQuery($query);
    }

    /**
     * @param string $areaRef
     * @return Area[]
     */
    public static function findByParentAreaRef($areaRef)
    {
        $query = NP()->db->prepare("SELECT * FROM " . static::table() . " WHERE `parent_area_ref` = '%s' AND `area_type`='%s'", $areaRef, static::key());
        return self::findByQuery($query);
    }

    /**
     * @param string $name
     * @return Area[]
     */
    public static function findByNameSuggestion($name)
    {
        $table = static::table();
        $a = '%';
        $query = NP()->db->prepare(
            "SELECT * FROM $table WHERE (`description` LIKE CONCAT(%s, %s, %s) OR `description_ru` LIKE CONCAT(%s, %s, %s)) AND (`area_type`=%s)",
            $a, $name, $a,
            $a, $name, $a,
            static::key()
        );
        return self::findByQuery($query);
    }

    /**
     * @param string $name
     * @param string $parentRef
     * @return Area[]
     */
    public static function findByNameSuggestionAndParentArea($name, $parentRef = null)
    {
        $table = static::table();
        $a = '%';
        $parentAreaCond = (is_null($parentRef)) ? " AND (`parent_area_ref` IS NULL)" : " AND (`parent_area_ref` = '$parentRef')";
        $query = NP()->db->prepare(
            "SELECT * FROM $table WHERE (`description` LIKE CONCAT(%s, %s, %s) OR `description_ru` LIKE CONCAT(%s, %s, %s)) AND (`area_type`=%s) 
            $parentAreaCond",
            $a, $name, $a,
            $a, $name, $a,
            static::key()
        );
        return self::findByQuery($query);
    }

    /**
     * @param string $ref
     * @return Area
     */
    public static function findByRef($ref)
    {
        $table = static::table();
        $query = NP()->db->prepare("SELECT * FROM $table WHERE Ref = %s AND `area_type` = %s", $ref, static::key());
        $result = NP()->db->get_row($query);
        return new static($result);
    }

    /**
     * @return void
     */
    public static function ajaxGetAreasListByParentAreaRef()
    {
        $areaRef = $_POST['parent_area_ref'];
        $cities = static::findByParentAreaRef($areaRef);
        $optionsList = OptionsHelper::getList($cities);
        echo json_encode($optionsList);
        exit;
    }

    /**
     * @return void
     */
    public static function ajaxGetAreasByNameSuggestion()
    {
        $areaRef = ArrayHelper::getValue($_POST, 'parent_area_ref', null);
        $name = $_POST['name'];
        $areas = self::findByNameSuggestionAndParentArea($name, $areaRef);
        foreach ($areas as $area) {
            $area->getRef();
            $area->getDescription();
        }
        echo json_encode($areas);
        exit;
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
        $query = NP()->db->prepare("SELECT * FROM $table WHERE Ref = %s AND `area_type` = %s", $this->ref, static::key());
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