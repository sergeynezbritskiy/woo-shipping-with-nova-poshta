<?php

namespace plugins\NovaPoshta\classes;

use plugins\NovaPoshta\classes\base\Base;
use wpdb;

/**
 * Class Base
 * @package plugins\NovaPoshta\classes
 * @property string tableLocations
 * @property string tableLocationsUpdate
 * @property wpdb $db
 * @property mixed last_error
 * @method prepare($query, $args)
 * @method get_row($query)
 * @method get_results($query)
 * @method query($query);
 * @method insert($table, $data, $format = null)
 * @method get_var($query = null, $x = 0, $y = 0)
 */
class Database extends Base
{

    /**
     * @return wpdb
     */
    public function getDb()
    {
        global $wpdb;
        $this->db = $wpdb;
        return $this->db;
    }

    public function createTables()
    {
        $this->createTableRegions();
        $this->createTableCities();
        $this->createTableWarehouses();
    }

    public function dropTables()
    {
        $this->dropTableWarehouses();
        $this->dropTableCities();
        $this->dropTableRegions();
    }

    private function createTableRegions()
    {
        $table = Region::table();
        $query = <<<QUERY
            CREATE TABLE IF NOT EXISTS {$table} (
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `ref` VARCHAR(50) NOT NULL,
                `description` tinytext NOT NULL,
                `description_ru` tinytext,
                `updated_at` INT(10) UNSIGNED NOT NULL,
                PRIMARY KEY `id` (`id`),
                UNIQUE KEY `uk_ref` (`ref`)
            )ENGINE=INNODB
QUERY;
        $this->db->query($query);
    }

    private function createTableCities()
    {
        $table = City::table();
        $query = <<<QUERY
            CREATE TABLE IF NOT EXISTS {$table} (
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `ref` VARCHAR(50) NOT NULL,
                `description` TINYTEXT NOT NULL,
                `description_ru` TINYTEXT,
                `region_ref` VARCHAR(50) NOT NULL,
                `updated_at` INT(10) UNSIGNED NOT NULL,
                PRIMARY KEY `id` (`id`),
                UNIQUE KEY `uk_ref` (`ref`)
            )ENGINE=INNODB
QUERY;
        $this->db->query($query);
    }

    private function createTableWarehouses()
    {
        $table = Warehouse::table();
        $query = <<<QUERY
            CREATE TABLE IF NOT EXISTS {$table} (
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `ref` VARCHAR(50) NOT NULL,
                `description` TINYTEXT NOT NULL,
                `description_ru` TINYTEXT,
                `city_ref` VARCHAR(50) NOT NULL,
                `city_description` TINYTEXT,
                `updated_at` INT(10) UNSIGNED NOT NULL,
                PRIMARY KEY `id` (`id`),
                UNIQUE KEY `uk_ref` (`ref`)
            )ENGINE=INNODB
QUERY;
        $this->db->query($query);
    }

    private function dropTableRegions()
    {
        $this->dropTableByName(Region::table());
    }

    private function dropTableCities()
    {
        $this->dropTableByName(City::table());
    }

    private function dropTableWarehouses()
    {
        $this->dropTableByName(Warehouse::table());
    }

    private function dropTableByName($table)
    {
        $query = "DROP TABLE IF EXISTS {$table}";
        $this->db->query($query);
    }

    /**-------------------------------------*/
    /*******Singleton pattern elements*******/
    /**-------------------------------------*/

    /**
     * @var self
     */
    private static $_instance;

    /**
     * @return Database
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