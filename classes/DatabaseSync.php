<?php
namespace plugins\NovaPoshta\classes;

use plugins\NovaPoshta\classes\base\Base;
use wpdb;

/**
 * Class DatabaseSync
 * @package plugins\NovaPoshta\classes
 * @property int interval
 * @property wpdb db
 */
class DatabaseSync extends Base
{
    public static function table()
    {
        return NP()->db->prefix . 'nova_poshta_locations_synchronisation';
    }

    ///////////////////////////////////////////////////////
    /////////////////     OLD DATA    /////////////////////
    ///////////////////////////////////////////////////////

    const LOCATION_TYPE_AREA = 'Area';
    const LOCATION_TYPE_CITY = 'City';
    const LOCATION_TYPE_WAREHOUSE = 'Warehouse';

    public function checkLocations()
    {
        if ($this->requiresUpdate(self::LOCATION_TYPE_AREA)) {
            $this->updateAreas();
        }
        if ($this->requiresUpdate(self::LOCATION_TYPE_CITY)) {
            $this->updateCities();
        }
        if ($this->requiresUpdate(self::LOCATION_TYPE_WAREHOUSE)) {
            $this->updateWarehouses();
        }

    }

    /**
     * @return wpdb
     */
    protected function getDb()
    {
        $this->db = NP()->db;
        return $this->db;
    }

    /**
     * @param $location
     * @return bool
     */
    private function requiresUpdate($location)
    {
        $query = $this->db->wpdb->prepare("SELECT MAX(`time`) FROM {$this->db->tableLocationsUpdate} WHERE `type`=%s", $location);
        $result = $this->db->wpdb->get_var($query);
        return ($result + $this->interval) < time();
    }

    private function updateAreas()
    {
        $areas = NP()->api->getAreas();
        $this->db->wpdb->query('START TRANSACTION');

        $this->clearLocations(self::LOCATION_TYPE_AREA);
        foreach ($areas as $area) {
            $this->db->wpdb->insert($this->db->tableLocations, array(
                'type' => self::LOCATION_TYPE_AREA,
                'Ref' => $area['Ref'],
                'Description' => $area['Description']
            ));
        }
        $this->db->wpdb->insert($this->db->tableLocationsUpdate, array('type' => self::LOCATION_TYPE_AREA, 'time' => time()));

        if (!$this->db->wpdb->last_error) {
            $this->db->wpdb->query('COMMIT');
        } else {
            $this->db->wpdb->query('ROLLBACK');
        }
    }

    private function updateCities()
    {
        $cities = NP()->api->getCities();
        $this->db->wpdb->query('START TRANSACTION');

        $this->clearLocations(self::LOCATION_TYPE_CITY);
        foreach ($cities as $city) {
            $this->db->wpdb->insert($this->db->tableLocations, array(
                'type' => self::LOCATION_TYPE_CITY,
                'Ref' => $city['Ref'],
                'Area' => $city['Area'],
                'Description' => $city['Description']
            ));
        }
        $this->db->wpdb->insert($this->db->tableLocationsUpdate, array('type' => self::LOCATION_TYPE_CITY, 'time' => time()));

        if (!$this->db->wpdb->last_error) {
            $this->db->wpdb->query('COMMIT');
        } else {
            $this->db->wpdb->query('ROLLBACK');
        }
    }

    private function updateWarehouses()
    {
        $warehouses = NP()->api->getWarehouses();
        $this->db->wpdb->query('START TRANSACTION');

        $this->clearLocations(self::LOCATION_TYPE_WAREHOUSE);
        foreach ($warehouses as $warehouse) {
            $this->db->wpdb->insert($this->db->tableLocations, array(
                'type' => self::LOCATION_TYPE_WAREHOUSE,
                'Ref' => $warehouse['Ref'],
                'Area' => $warehouse['CityRef'],
                'Description' => $warehouse['Description']
            ));
        }
        $this->db->wpdb->insert($this->db->tableLocationsUpdate, array('type' => self::LOCATION_TYPE_WAREHOUSE, 'time' => time()));

        if (!$this->db->wpdb->last_error) {
            $this->db->wpdb->query('COMMIT');
        } else {
            $this->db->wpdb->query('ROLLBACK');
        }
    }

    private function clearLocations($location)
    {
        $query = $this->db->wpdb->prepare("DELETE FROM {$this->db->tableLocations} WHERE `type` = %s", $location);
        $this->db->wpdb->query($query);
    }

    /**
     * @var NovaPoshta
     */
    private static $_instance;

    /**
     * @return NovaPoshta
     */
    public static function instance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * @access private
     */
    private function __construct()
    {
        //update every week
        $this->interval = 60 * 60 * 24 * 7;
    }

    /**
     * @access private
     */
    private function __clone()
    {
    }
}