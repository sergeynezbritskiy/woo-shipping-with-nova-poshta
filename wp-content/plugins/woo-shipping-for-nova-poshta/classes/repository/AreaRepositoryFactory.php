<?php

namespace plugins\NovaPoshta\classes\repository;

use plugins\NovaPoshta\classes\base\Base;
use plugins\NovaPoshta\classes\City;
use plugins\NovaPoshta\classes\Region;
use plugins\NovaPoshta\classes\Warehouse;

/**
 * Class AreaRepositoryFactory
 * @package plugins\NovaPoshta\classes
 */
class AreaRepositoryFactory extends Base
{

    /**
     * @var self
     */
    private static $_instance;

    /**
     * @var AbstractAreaRepository[]
     */
    private $repositories = [];

    /**
     * @return self
     */
    public static function instance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * @return AbstractAreaRepository
     */
    public function regionRepo()
    {
        return $this->ensureRepository(Region::key(), RegionRepository::getClass());
    }

    /**
     * @return AbstractAreaRepository
     */
    public function cityRepo()
    {
        return $this->ensureRepository(City::key(), CityRepository::getClass());
    }

    /**
     * @return AbstractAreaRepository
     */
    public function warehouseRepo()
    {
        return $this->ensureRepository(Warehouse::key(), WarehouseRepository::getClass());
    }

    /**
     * @param string $key
     * @param string $class
     * @return AbstractAreaRepository
     */
    private function ensureRepository($key, $class)
    {
        if (!isset($this->repositories[$key])) {
            $this->repositories[$key] = new $class();
        }
        return $this->repositories[$key];
    }

    /**
     * AreaRepositoryFactory constructor.
     * @access private
     */
    private function __construct()
    {
    }

    /**
     * @access private
     */
    private function __clone()
    {
    }

}