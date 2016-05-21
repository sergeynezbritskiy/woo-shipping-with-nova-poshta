<?php
namespace plugins\NovaPoshta\classes\base;

class Base
{
    /**
     * @param $property
     * @return mixed
     */
    public function __get($property)
    {
        $method = 'get' . ucwords($property);
        return $this->$method();
    }
}