<?php

namespace plugins\NovaPoshta\classes;

use plugins\NovaPoshta\classes\repository\AbstractAreaRepository;
use plugins\NovaPoshta\classes\repository\AreaRepositoryFactory;

/**
 * Class Area
 * @package plugins\NovaPoshta\classes
 */
class Region extends Area
{

    /**
     * @param string $type
     * @return string
     */
    public static function key($type = '')
    {
        return parent::_key(self::KEY_REGION, $type);
    }

    /**
     * @return AbstractAreaRepository
     */
    protected function getRepository()
    {
        return AreaRepositoryFactory::instance()->regionRepo();
    }
}