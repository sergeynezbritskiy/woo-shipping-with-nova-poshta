<?php

namespace plugins\NovaPoshta\classes\repository;

use plugins\NovaPoshta\classes\Warehouse;

/**
 * Class WarehouseRepository
 * @package plugins\NovaPoshta\classes\repository
 */
class WarehouseRepository extends AbstractAreaRepository
{

    /**
     * @return string
     */
    public function table()
    {
        return NP()->db->prefix . 'nova_poshta_warehouse';
    }

    /**
     * @return string
     */
    protected function getAreaClass()
    {
        return Warehouse::getClass();
    }

    /**
     * @param string $name
     * @return string
     * @override
     */
    protected function getNameSearchCriteria($name)
    {
        NP()->db->escape_by_ref($name);
        return sprintf("(`description` LIKE CONCAT('%%', '%s', '%%') OR `description_ru` LIKE CONCAT('%%', '%s', '%%'))", $name, $name);
    }

}