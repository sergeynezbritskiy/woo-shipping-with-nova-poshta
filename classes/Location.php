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
abstract class Location extends Base
{

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
        _doing_it_wrong("Location::table()", "You should not call this method from abstract class", "3.1.0");
    }

    /**
     * @return array
     */
    public static function findAll()
    {
        $query = "SELECT * FROM " . static::table();
        $result = NP()->db->get_results($query);
        $locations = array();
        foreach ($result as $items) {
            $locations[] = new static($items);
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