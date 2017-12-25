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
 * @property Log log
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
            $this->log->info("Synchronization required", Log::LOCATIONS_UPDATE);
            $this->db->query('START TRANSACTION');

            $this->updateAreas();
            $this->updateCities();
            $this->updateWarehouses();
            $this->setLocationsLastUpdateDate($this->updatedAt);

            if (!$this->db->last_error) {
                $this->log->info("Synchronization finished successfully", Log::LOCATIONS_UPDATE);
                $this->db->query('COMMIT');
            } else {
                $this->log->error("Synchronization failed. Rollback.", Log::LOCATIONS_UPDATE);
                $this->db->query('ROLLBACK');
            }
            $this->log->info("", Log::LOCATIONS_UPDATE);
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
            $type = Area::KEY_REGION;
            $insert = array();
            foreach ($areas as $area) {
                $insert[] = $this->db->prepare(
                    "('%s', '%s', '%s', %d)",
                    $type,
                    $area['Ref'],
                    $area['Description'],
                    $updatedAt
                );
            }
            $queryInsert = "INSERT INTO $table (`area_type`, `ref`, `description`, `updated_at`) VALUES ";
            $queryInsert .= implode(",", $insert);
            $queryInsert .= " ON DUPLICATE KEY UPDATE 
            `area_type` = '$type',
            `ref` = VALUES(`ref`), 
            `description` = VALUES(`description`), 
            `updated_at` = VALUES(`updated_at`)";

            $queryDelete = $this->db->prepare("DELETE FROM $table WHERE `updated_at` < %d AND `area_type` = %s", $updatedAt, $type);

            $this->setAreasHash($areasHashNew);

            $rowsAffected = $this->db->query($queryInsert);
            $rowsDeleted = $this->db->query($queryDelete);
            $this->log->info("Areas were successfully updated, affected $rowsAffected rows, deleted $rowsDeleted rows", Log::LOCATIONS_UPDATE);
        } else {
            $this->log->info("Areas are up-to-date, synchronization does not required", Log::LOCATIONS_UPDATE);
        }
    }

    /**
     * Update content of table nova_poshta_city
     */
    private function updateCities()
    {
        $type = Area::KEY_CITY;
        $cities = NP()->api->getCities();
        $table = Area::table();
        $citiesHashOld = $this->citiesHash;
        $citiesHashNew = md5(serialize($cities));
        $updatedAt = $this->updatedAt;


        if ($citiesHashNew !== $citiesHashOld) {
            $insert = array();
            foreach ($cities as $city) {
                $insert[] = $this->db->prepare(
                    "('%s', '%s', '%s', '%s', '%s', %d)",
                    $type,
                    $city['Ref'],
                    $city['Description'],
                    $city['DescriptionRu'],
                    $city['Area'],
                    $updatedAt
                );
            }
            $queryInsert = "INSERT INTO $table (`area_type`, `ref`, `description`, `description_ru`, `parent_area_ref`, `updated_at`) VALUES ";
            $queryInsert .= implode(",", $insert);
            $queryInsert .= " ON DUPLICATE KEY UPDATE 
            `area_type` = '$type',
            `ref` = VALUES(`ref`),
            `description` = VALUES(`description`),
            `description_ru`=VALUES(`description_ru`),
            `parent_area_ref`=VALUES(`parent_area_ref`),
            `updated_at` = VALUES(`updated_at`)";

            $queryDelete = $this->db->prepare("DELETE FROM $table WHERE `updated_at` < %d AND `area_type` = %s", $updatedAt, $type);

            $this->setCitiesHash($citiesHashNew);
            $rowsAffected = $this->db->query($queryInsert);
            $rowsDeleted = $this->db->query($queryDelete);
            $this->log->info("Cities were successfully updated, affected $rowsAffected rows, deleted $rowsDeleted rows", Log::LOCATIONS_UPDATE);
        } else {
            $this->log->info("Cities are up-to-date, synchronization does not required", Log::LOCATIONS_UPDATE);
        }
    }

    /**
     * Update content of table nova_poshta_warehouse
     */
    private function updateWarehouses()
    {
        $type = Area::KEY_WAREHOUSE;
        $warehouses = NP()->api->getWarehouses();
        $table = Area::table();
        $warehousesHashOld = $this->warehousesHash;
        $warehousesHashNew = md5(serialize($warehouses));
        $updatedAt = $this->updatedAt;

        if ($warehousesHashNew !== $warehousesHashOld) {
            $insert = array();
            foreach ($warehouses as $warehouse) {
                $insert[] = $this->db->prepare(
                    "('%s', '%s', '%s', '%s', '%s', %d)",
                    $type,
                    $warehouse['Ref'],
                    $warehouse['Description'],
                    $warehouse['DescriptionRu'],
                    $warehouse['CityRef'],
                    $updatedAt
                );
            }
            $queryInsert = "INSERT INTO $table (`area_type`, `ref`, `description`, `description_ru`, `parent_area_ref`, `updated_at`) VALUES ";
            $queryInsert .= implode(",", $insert);
            $queryInsert .= " ON DUPLICATE KEY UPDATE 
            `area_type` = '$type',
            `ref` = VALUES(`ref`), 
            `description` = VALUES(`description`), 
            `description_ru`=VALUES(`description_ru`), 
            `parent_area_ref`=VALUES(`parent_area_ref`), 
            `updated_at` = VALUES(`updated_at`)";

            $queryDelete = $this->db->prepare("DELETE FROM $table WHERE `updated_at` < %d AND `area_type`=%s", $updatedAt, $type);

            $this->setWarehousesHash($warehousesHashNew);
            $rowsAffected = $this->db->query($queryInsert);
            $rowsDeleted = $this->db->query($queryDelete);
            $this->log->info("Warehouses were successfully updated, affected $rowsAffected rows, deleted $rowsDeleted rows", Log::LOCATIONS_UPDATE);
        } else {
            $this->log->info("Warehouses are up-to-date, synchronization does not required", Log::LOCATIONS_UPDATE);
        }
    }

    /**
     * @return int
     */
    protected function getInterval()
    {
        //604800 = 60*60*24*7 (update every week)
        //86400 =60*60*24 (update every day)
        $this->interval = 86400;
        return $this->interval;
    }

    /**
     * @return Log
     */
    protected function getLog()
    {
        $this->log = NP()->log;
        return $this->log;
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
    }

    /**
     * @access private
     */
    private function __clone()
    {
    }
}