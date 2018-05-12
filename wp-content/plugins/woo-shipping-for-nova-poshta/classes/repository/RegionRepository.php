<?php

namespace plugins\NovaPoshta\classes\repository;

use plugins\NovaPoshta\classes\Region;

/**
 * Class RegionRepository
 * @package plugins\NovaPoshta\classes\repository
 */
class RegionRepository extends AbstractAreaRepository
{

    /**
     * @return string
     */
    public function table()
    {
        return NP()->db->prefix . 'nova_poshta_region';
    }

    /**
     * @return string
     */
    protected function getAreaClass()
    {
        return Region::getClass();
    }

}