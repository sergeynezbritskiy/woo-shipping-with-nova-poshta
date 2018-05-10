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

            try {
                $this->updateRegions();
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
            } catch (\Exception $e) {
                $this->addError($e);
                $this->log->error("Synchronization failed. " . $e->getMessage(), Log::LOCATIONS_UPDATE);
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
        return true;//($this->locationsLastUpdateDate + $this->interval) < time();
    }

    /**
     * Update content of table nova_poshta_area
     */
    private function updateRegions()
    {
        $table = Region::table();
        $areas = NP()->api->getAreas();
        $updatedAt = $this->updatedAt;
        $insert = array();
        foreach ($areas as $area) {
            $insert[] = $this->db->prepare(
                "('%s', '%s', '%s', %d)",
                $area['Ref'],
                $area['Description'],
                $area['Description'],
                $updatedAt
            );
        }
        $queryInsert = "INSERT INTO $table (`ref`, `description`, `description_ru`, `updated_at`) VALUES ";
        $queryInsert .= implode(",", $insert);
        $queryInsert .= " ON DUPLICATE KEY UPDATE 
            `ref` = VALUES(`ref`), 
            `description` = VALUES(`description`), 
            `description_ru` = VALUES(`description_ru`), 
            `updated_at` = VALUES(`updated_at`)";

        $queryDelete = $this->db->prepare("DELETE FROM $table WHERE `updated_at` < %d", $updatedAt);

        $rowsAffected = $this->db->query($queryInsert);
        $rowsDeleted = $this->db->query($queryDelete);
        $this->log->info("Areas were successfully updated, affected $rowsAffected rows, deleted $rowsDeleted rows", Log::LOCATIONS_UPDATE);
    }

    /**
     * Update content of table nova_poshta_city
     */
    private function updateCities()
    {
        $table = City::table();
        $updatedAt = $this->updatedAt;
        $page = 1;
        $rowsAffected = 0;
        $limit = 300;
        while (count($cities = NP()->api->getCities($page++, $limit)) > 0) {
            $rowsAffected += $this->saveCities($cities, $updatedAt);
        }

        $queryDelete = $this->db->prepare("DELETE FROM $table WHERE `updated_at` < %d", $updatedAt);
        $rowsDeleted = $this->db->query($queryDelete);

        $this->log->info("Cities were successfully updated, affected $rowsAffected rows, deleted $rowsDeleted rows", Log::LOCATIONS_UPDATE);
    }

    private function saveCities($cities, $updatedAt)
    {
        $table = City::table();
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
        $queryInsert = "INSERT INTO $table (`ref`, `description`, `description_ru`, `parent_ref`, `updated_at`) VALUES ";
        $queryInsert .= implode(",", $insert);
        $queryInsert .= " ON DUPLICATE KEY UPDATE 
            `ref` = VALUES(`ref`),
            `description` = VALUES(`description`),
            `description_ru`=VALUES(`description_ru`),
            `parent_ref`=VALUES(`parent_ref`),
            `updated_at` = VALUES(`updated_at`)";
        return $this->db->query($queryInsert);
    }

    /**
     * Update content of table nova_poshta_warehouse
     */
    private function updateWarehouses()
    {
        $table = Warehouse::table();
        $updatedAt = $this->updatedAt;

        $rowsAffected = 0;
        $page = 1;
        $limit = 300;
        while (count($warehouses = NP()->api->getWarehouses(null, $page++, $limit)) > 0) {
            $rowsAffected += $this->saveWarehouses($warehouses, $updatedAt);
        }

        $queryDelete = $this->db->prepare("DELETE FROM $table WHERE `updated_at` < %d", $updatedAt);

        $rowsDeleted = $this->db->query($queryDelete);
        $this->log->info("Warehouses were successfully updated, affected $rowsAffected rows, deleted $rowsDeleted rows", Log::LOCATIONS_UPDATE);
    }

    private function saveWarehouses($warehouses, $updatedAt)
    {
        $table = Warehouse::table();
        $insert = array();
        foreach ($warehouses as $warehouse) {
            $insert[] = $this->db->prepare(
                "('%s', '%s', '%s', '%s', %d)",
                $warehouse['Ref'],
                $warehouse['Description'],
                $warehouse['DescriptionRu'],
                $warehouse['CityRef'],
                $updatedAt
            );
        }
        $queryInsert = "INSERT INTO $table (`ref`, `description`, `description_ru`, `parent_ref`, `updated_at`) VALUES ";
        $queryInsert .= implode(",", $insert);
        $queryInsert .= " ON DUPLICATE KEY UPDATE 
            `ref` = VALUES(`ref`), 
            `description` = VALUES(`description`), 
            `description_ru`=VALUES(`description_ru`), 
            `parent_ref`=VALUES(`parent_ref`), 
            `updated_at` = VALUES(`updated_at`)";
        return $this->db->query($queryInsert);

    }

    /**
     * @return int
     */
    protected function getInterval()
    {
        //604800 = 60*60*24*7 (update every week)
        //86400 =60*60*24 (update every day)
        return 86400;
    }

    /**
     * @return Log
     */
    protected function getLog()
    {
        return NP()->log;
    }

    /**
     * @return wpdb
     */
    protected function getDb()
    {
        return NP()->db;
    }

    /**
     * @return int
     */
    protected function getLocationsLastUpdateDate()
    {
        return NP()->options->locationsLastUpdateDate;
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
        return time();
    }

    /**
     * @return string
     */
    protected function getAreasHash()
    {
        return NP()->options->areasHash;
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
        return NP()->options->citiesHash;
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
        return NP()->options->getWarehousesHash();
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
     * @param \Exception $exception
     */
    private function addError(\Exception $exception)
    {
        add_action('admin_notices', function () use ($exception) {
            $class = 'notice notice-error';
            $message = esc_html('Nova Poshta synchronisation failed: ' . $exception->getMessage());
            printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
        });
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