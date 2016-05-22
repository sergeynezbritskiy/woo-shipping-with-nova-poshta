<?php
namespace plugins\NovaPoshta\classes;

use plugins\NovaPoshta\classes\base\Base;
use wpdb;

/**
 * Class DatabaseSync
 * @package plugins\NovaPoshta\classes
 * @property int interval
 * @property wpdb db
 * @property string $areasHash
 * @property string citiesHash
 * @property string warehousesHash
 * @property int $locationsLastUpdateDate
 * @property int updatedAt
 */
class DatabaseSync extends Base
{

    /**
     * Synchronize Nova Poshta areas, cities and warehouses
     * Synchronization every $this->interval but before insert
     * data to table check location has in order to identify any
     * changes so insertion will appear on in case of hash mismatch
     */
    public function synchroniseLocations()
    {
        if ($this->requiresUpdate()) {
            $this->db->query('START TRANSACTION');

            $this->updateAreas();
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
     * Update content of table nova_poshta_area
     */
    private function updateAreas()
    {
        $table = Area::table();
        $areas = NP()->api->getAreas();
        $areasHashOld = $this->areasHash;
        $areasHashNew = md5(serialize($areas));
        $updatedAt = $this->updatedAt;


        if ($areasHashNew !== $areasHashOld) {
            $insert = array();
            foreach ($areas as $area) {
                $insert[] = $this->db->prepare(
                    "('%s', '%s', %d)",
                    $area['Ref'],
                    $area['Description'],
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

            $this->setAreasHash($areasHashNew);
            $this->db->query($queryInsert);
            $this->db->query($queryDelete);
        }
    }

    /**
     * Update content of table nova_poshta_city
     */
    private function updateCities()
    {
        $cities = NP()->api->getCities();
        $table = City::table();
        $citiesHashOld = $this->citiesHash;
        $citiesHashNew = md5(serialize($cities));
        $updatedAt = $this->updatedAt;


        if ($citiesHashNew !== $citiesHashOld) {
            $insert = array();
            foreach ($cities as $city) {
                $insert[] = $this->db->prepare(
                    "('%s', '%s', '%s', '%s', %d)",
                    $city['Ref'],
                    $city['Description'],
                    $city['DescriptionRu'],
                    $city['Area'],
                    $updatedAt
                );
            }
            $queryInsert = "INSERT INTO $table (`ref`, `description`, `description_ru`, `area_ref`, `updated_at`) VALUES ";
            $queryInsert .= implode(",", $insert);
            $queryInsert .= " ON DUPLICATE KEY UPDATE 
            `ref` = VALUES(`ref`), 
            `description` = VALUES(`description`), 
            `description_ru`=VALUES(`description_ru`), 
            `area_ref`=VALUES(`area_ref`), 
            `updated_at` = VALUES(`updated_at`)";

            $queryDelete = $this->db->prepare("DELETE FROM $table WHERE `updated_at` < %d", $updatedAt);

            $this->setCitiesHash($citiesHashNew);
            $this->db->query($queryInsert);
            $this->db->query($queryDelete);
        }
    }

    /**
     * Update content of table nova_poshta_warehouse
     */
    private function updateWarehouses()
    {
        $warehouses = NP()->api->getWarehouses();
        $table = Warehouse::table();
        $warehousesHashOld = $this->warehousesHash;
        $warehousesHashNew = md5(serialize($warehouses));
        $updatedAt = $this->updatedAt;

        if ($warehousesHashNew !== $warehousesHashOld) {
            $insert = array();
            foreach ($warehouses as $warehouse) {
                $insert[] = $this->db->prepare(
                    "('%s', '%s', '%s', '%s', '%s', %d)",
                    $warehouse['Ref'],
                    $warehouse['Description'],
                    $warehouse['DescriptionRu'],
                    $warehouse['CityRef'],
                    $warehouse['CityDescription'],
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
    protected function getLocationsLastUpdateDate()
    {
        $this->locationsLastUpdateDate = NP()->options->locationsLastUpdateDate;
        return $this->locationsLastUpdateDate;
    }

    /**
     * @param int $value
     */
    private function setLocationsLastUpdateDate($value)
    {
        NP()->options->setLocationsLastUpdateDate($value);
        $this->locationsLastUpdateDate = $value;
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
     * @return string
     */
    protected function getAreasHash()
    {
        $this->areasHash = NP()->options->areasHash;
        return $this->areasHash;
    }

    /**
     * @param string $hash
     */
    protected function setAreasHash($hash)
    {
        NP()->options->setAreasHash($hash);
        $this->areasHash = $hash;
    }

    /**
     * @return string
     */
    protected function getCitiesHash()
    {
        $this->citiesHash = NP()->options->citiesHash;
        return $this->citiesHash;
    }

    /**
     * @param string $hash
     */
    protected function setCitiesHash($hash)
    {
        NP()->options->setCitiesHash($hash);
        $this->citiesHash = $hash;
    }

    /**
     * @return string
     */
    protected function getWarehousesHash()
    {
        $this->warehousesHash = NP()->options->getWarehousesHash();
        return $this->warehousesHash;
    }

    /**
     * @param string $hash
     */
    protected function setWarehousesHash($hash)
    {
        NP()->options->setWarehousesHash($hash);
        $this->warehousesHash = $hash;
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