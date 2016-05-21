<?php

namespace plugins\NovaPoshta\classes;


use plugins\NovaPoshta\classes\base\Base;

/**
 * Class Location
 * @package plugins\NovaPoshta\classes
 * 
 * @property string content
 * @property string description
 * @property string ref
 * @property string content
 */
abstract class Location extends Base
{
    /**
     * @return string
     */
    protected function getContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    protected function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    protected function getRef()
    {
        return $this->ref;
    }
}