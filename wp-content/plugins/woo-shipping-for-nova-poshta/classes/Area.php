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
     * @return string
     */
    public static function table()
    {
        _doing_it_wrong("Area table", "You have to override this method in child classes", "2.1.0");
        return '';
    }

    /**
     * @return Area[]
     */
    public static function findAll()
    {
        $table = static::table();
        $query = "SELECT * FROM $table";
        return self::findByQuery($query);
    }

    /**
     * @param string $areaRef
     * @return Area[]
     */
    public static function findByParentAreaRef($areaRef)
    {
        $query = NP()->db->prepare("SELECT * FROM " . static::table() . " WHERE `parent_ref` = '%s' ", $areaRef);
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
            "SELECT * FROM $table WHERE (`description` LIKE CONCAT(%s, %s, %s) OR `description_ru` LIKE CONCAT(%s, %s, %s))",
            $a, $name, $a,
            $a, $name, $a
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
        $parentAreaCond = $parentRef !== null ? " AND (`parent_ref` = '$parentRef')" : "";
        $query = NP()->db->prepare(
            "SELECT * FROM $table WHERE (`description` LIKE CONCAT(%s, %s, %s) OR `description_ru` LIKE CONCAT(%s, %s, %s))
            $parentAreaCond",
            $a, $name, $a,
            $a, $name, $a
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
        $query = NP()->db->prepare("SELECT * FROM $table WHERE ref = %s", $ref);
        $result = NP()->db->get_row($query);
        return new static($result);
    }

    /**
     * @return void
     */
    public static function ajaxGetAreasListByParentAreaRef()
    {
        $areaRef = $_POST['parent_ref'];
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
        $areaRef = ArrayHelper::getValue($_POST, 'parent_ref', null);
        $name = $_POST['name'];
        $areas = self::findByNameSuggestionAndParentArea($name, $areaRef);
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