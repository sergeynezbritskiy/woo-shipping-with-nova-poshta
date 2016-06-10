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
        $this->createTableAreas();
    }

    public function dropTables()
    {
        $this->dropTableAreas();
    }

    private function createTableAreas()
    {
        $table = Area::table();
        $region = Area::REGION_KEY;
        $city = Area::CITY_KEY;
        $warehouse = Area::WAREHOUSE_KEY;

        $query = <<<QUERY
            CREATE TABLE IF NOT EXISTS {$table} (
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `ref` VARCHAR(50) NOT NULL,
                `area_type` ENUM('$region','$city','$warehouse') NOT NULL,
                `description` TINYTEXT NOT NULL,
                `description_ru` TINYTEXT,
                `parent_area_ref` VARCHAR(50) DEFAULT NULL,
                `updated_at` INT(10) UNSIGNED NOT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY `id` (`id`),
                UNIQUE KEY `uk_ref` (`ref`)
            )ENGINE=INNODB
QUERY;
        $this->db->query($query);
    }

    private function dropTableAreas()
    {
        $this->dropTableByName(Area::table());
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