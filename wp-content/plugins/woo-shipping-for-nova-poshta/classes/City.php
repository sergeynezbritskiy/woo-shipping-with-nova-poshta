<?php

namespace plugins\NovaPoshta\classes;

use plugins\NovaPoshta\classes\repository\AbstractAreaRepository;
use plugins\NovaPoshta\classes\repository\AreaRepositoryFactory;

/**
 * Class City
 * @package plugins\NovaPoshta\classes
 */
class City extends Area
{

    /**
     * @return string
     */
    protected static function _key()
    {
        return 'nova_poshta_city';
    }

    /**
     * @return AbstractAreaRepository
     */
    protected function getRepository()
    {
        return AreaRepositoryFactory::instance()->cityRepo();
    }

}