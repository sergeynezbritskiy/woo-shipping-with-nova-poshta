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
     * @param string $type
     * @return string
     */
    public static function key($type = '')
    {
        return parent::_key(self::KEY_WAREHOUSE, $type);
    }

    /**
     * @return AbstractAreaRepository
     */
    protected function getRepository()
    {
        return AreaRepositoryFactory::instance()->warehouseRepo();
    }
}