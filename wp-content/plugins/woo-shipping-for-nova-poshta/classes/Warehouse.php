<?php

namespace plugins\NovaPoshta\classes;

use plugins\NovaPoshta\classes\repository\AbstractAreaRepository;
use plugins\NovaPoshta\classes\repository\AreaRepositoryFactory;

/**
 * Class Warehouse
 * @package plugins\NovaPoshta\classes
 */
class Warehouse extends Area
{

    /**
     * @return string
     */
    protected static function _key()
    {
        return 'nova_poshta_warehouse';
    }

    /**
     * @return AbstractAreaRepository
     */
    protected function getRepository()
    {
        return AreaRepositoryFactory::instance()->warehouseRepo();
    }
}