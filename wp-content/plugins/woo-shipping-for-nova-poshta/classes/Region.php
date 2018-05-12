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
     * @return string
     */
    protected static function _key()
    {
        return 'nova_poshta_region';
    }

    /**
     * @return AbstractAreaRepository
     */
    protected function getRepository()
    {
        return AreaRepositoryFactory::instance()->regionRepo();
    }
}