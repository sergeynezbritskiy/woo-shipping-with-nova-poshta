<?php
namespace plugins\NovaPoshta\classes;

use plugins\NovaPoshta\classes\base\Base;
use wpdb;

/**
 * Class DatabaseSync
 * @package plugins\NovaPoshta\classes
 * @property int interval
 * @property wpdb db
 * @property string regionsHash
 * @property string citiesHash
 * @property string warehousesHash
 * @property int $locationsLastUpdateDate
 * @property int updatedAt
 */
class DatabaseSync extends Base
{
    public static function table()
    {
        return NP()->db->prefix . 'nova_poshta_locations_synchronisation';
    }

    public function synchroniseLocations()
    {
        if ($this->requiresUpdate()) {
            $this->db->query('START TRANSACTION');

            $this->updateRegions();
            $this->updateCities();
            $this->updateWarehouses();
            $this->setLocationsLastUpdateDate($this->updatedAt);

            if (!$this->db->last_error) {
                $this->db->query('COMMIT');
            } else {
                $this->db->query('ROLLBACK');
            }
        }


    }

    /**
     * @return bool
     */
    private function requiresUpdate()
    {
        return ($this->locationsLastUpdateDate + $this->interval) < time();
    }

    /**
     * Update content of table regions
     */
    private function updateRegions()
    {
        $table = Region::table();
        $regions = NP()->api->getAreas();
        $regionsHashOld = $this->regionsHash;
        $regionsHashNew = md5(serialize($regions));
        $updatedAt = $this->updatedAt;


        if ($regionsHashNew !== $regionsHashOld) {
            $insert = array();
            foreach ($regions as $region) {
                $insert[] = $this->db->prepare(
                    "('%s', '%s', %d)",
                    $region['Ref'],
                    $region['Description'],
                    $updatedAt
                );
            }
            $queryInsert = "INSERT INTO $table (`ref`, `description`, `updated_at`) VALUES ";
            $queryInsert .= implode(",", $insert);
            $queryInsert .= " ON DUPLICATE KEY UPDATE 
            `ref` = VALUES(`ref`), 
            `description` = VALUES(`description`), 
            `updated_at` = VALUES(`updated_at`)";

            $queryDelete = $this->db->prepare("DELETE FROM $table WHERE `updated_at` < %d", $updatedAt);

            $this->setRegionsHash($regionsHashNew);
            $this->db->query($queryInsert);
            $this->db->query($queryDelete);
        }
    }

    private function updateCities()
    {
        $cities = NP()->api->getCities();
        $table = City::table();
        $citiesHashOld = $this->citiesHash;
        $citiesHashNew = md5(serialize($cities));
        $updatedAt = $this->updatedAt;


        if ($citiesHashNew !== $citiesHashOld) {
            $insert = array();
            foreach ($cities as $region) {
                $insert[] = $this->db->prepare(
                    "('%s', '%s', '%s', '%s', %d)",
                    $region['Ref'],
                    $region['Description'],
                    $region['DescriptionRu'],
                    $region['Area'],
                    $updatedAt
                );
            }
            $queryInsert = "INSERT INTO $table (`ref`, `description`, `description_ru`, `region_ref`, `updated_at`) VALUES ";
            $queryInsert .= implode(",", $insert);
            $queryInsert .= " ON DUPLICATE KEY UPDATE 
            `ref` = VALUES(`ref`), 
            `description` = VALUES(`description`), 
            `description_ru`=VALUES(`description_ru`), 
            `region_ref`=VALUES(`region_ref`), 
            `updated_at` = VALUES(`updated_at`)";

            $queryDelete = $this->db->prepare("DELETE FROM $table WHERE `updated_at` < %d", $updatedAt);

            $this->setCitiesHash($citiesHashNew);
            $this->db->query($queryInsert);
            $this->db->query($queryDelete);
        }
    }

    private function updateWarehouses()
    {
        $warehouses = NP()->api->getWarehouses();
        $table = Warehouse::table();
        $warehousesHashOld = $this->warehousesHash;
        $warehousesHashNew = md5(serialize($warehouses));
        $updatedAt = $this->updatedAt;

        if ($warehousesHashNew !== $warehousesHashOld) {
            $insert = array();
            foreach ($warehouses as $region) {
                $insert[] = $this->db->prepare(
                    "('%s', '%s', '%s', '%s', '%s', %d)",
                    $region['Ref'],
                    $region['Description'],
                    $region['DescriptionRu'],
                    $region['CityRef'],
                    $region['CityDescription'],
                    $updatedAt
                );
            }
            $queryInsert = "INSERT INTO $table (`ref`, `description`, `description_ru`, `city_ref`, `city_description`, `updated_at`) VALUES ";
            $queryInsert .= implode(",", $insert);
            $queryInsert .= " ON DUPLICATE KEY UPDATE 
            `ref` = VALUES(`ref`), 
            `description` = VALUES(`description`), 
            `description_ru`=VALUES(`description_ru`), 
            `city_ref`=VALUES(`city_ref`), 
            `city_description` = VALUES(`city_description`), 
            `updated_at` = VALUES(`updated_at`)";

            $queryDelete = $this->db->prepare("DELETE FROM $table WHERE `updated_at` < %d", $updatedAt);

            $this->setWarehousesHash($warehousesHashNew);
            $this->db->query($queryInsert);
            $this->db->query($queryDelete);
        }
    }

    /**
     * @return int
     */
    public function getLocationsLastUpdateDate()
    {
        $this->locationsLastUpdateDate = NP()->options->locationsLastUpdateDate;
        return $this->locationsLastUpdateDate;
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
     * @return int
     */
    protected function getUpdatedAt()
    {
        $this->updatedAt = time();
        return $this->updatedAt;
    }

    /**
     * @return int
     */
    protected function getLocationLastUpdateDate()
    {
        return NP()->options->locationsLastUpdateDate;
    }

    /**
     * @return string
     */
    protected function getRegionsHash()
    {
        $this->regionsHash = NP()->options->regionsHash;
        return $this->regionsHash;
    }

    /**
     * @param string $regionsHash
     */
    public function setRegionsHash($regionsHash)
    {
        NP()->options->setRegionsHash($regionsHash);
        $this->regionsHash = $regionsHash;
    }

    /**
     * @return string
     */
    public function getCitiesHash()
    {
        $this->citiesHash = NP()->options->citiesHash;
        return $this->citiesHash;
    }

    /**
     * @param string $citiesHash
     */
    public function setCitiesHash($citiesHash)
    {
        NP()->options->setCitiesHash($citiesHash);
        $this->citiesHash = $citiesHash;
    }

    /**
     * @return string
     */
    public function getWarehousesHash()
    {
        $this->warehousesHash = NP()->options->getWarehousesHash();
        return $this->warehousesHash;
    }

    /**
     * @param string $warehousesHash
     */
    public function setWarehousesHash($warehousesHash)
    {
        NP()->options->setWarehousesHash($warehousesHash);
        $this->warehousesHash = $warehousesHash;
    }

    private function setLocationsLastUpdateDate($value)
    {
        NP()->options->setLocationsLastUpdateDate($value);
        $this->locationsLastUpdateDate = $value;
    }

    /**
     * @var DatabaseSync
     */
    private static $_instance;

    /**
     * @return DatabaseSync
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