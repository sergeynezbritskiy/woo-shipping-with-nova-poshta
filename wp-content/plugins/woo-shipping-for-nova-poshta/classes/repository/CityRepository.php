<?php

namespace plugins\NovaPoshta\classes\repository;

use plugins\NovaPoshta\classes\City;

/**
 * Class CityRepository
 * @package plugins\NovaPoshta\classes\repository
 */
class CityRepository extends AbstractAreaRepository
{

    /**
     * @return string
     */
    public function table()
    {
        return NP()->db->prefix . 'nova_poshta_city';
    }

    /**
     * @return string
     */
    protected function getAreaClass()
    {
        return City::getClass();
    }

}