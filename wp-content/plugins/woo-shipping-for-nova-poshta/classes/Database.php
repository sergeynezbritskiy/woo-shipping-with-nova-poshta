<?php

namespace plugins\NovaPoshta\classes;

use plugins\NovaPoshta\classes\base\Base;
use plugins\NovaPoshta\classes\repository\AreaRepositoryFactory;
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
     * Database upgrade entry point
     */
    public function upgrade()
    {
        if (version_compare(NP()->pluginVersion, '2.1.0', '>=')) {
            $this->dropTableByName($this->db->prefix . 'nova_poshta_area');
        }
        $this->dropTables();
        $this->createTables();
    }

    /**
     * Database downgrade entry point
     */
    public function downgrade()
    {
        $this->dropTables();
        delete_site_option('nova_poshta_db_version');
    }

    /**
     * @return wpdb
     */
    protected function getDb()
    {
        return NP()->db;
    }

    private function createTables()
    {
        $factory = AreaRepositoryFactory::instance();
        if ($this->db->has_cap('collation')) {
            $collate = $this->db->get_charset_collate();
        } else {
            $collate = '';
        }

        /*
         * create Regions table
         */
        $regionTableName = $factory->regionRepo()->table();
        $regionQuery = <<<AREA
            CREATE TABLE {$regionTableName} (
                `ref` VARCHAR(50) NOT NULL,
                `description` VARCHAR(256) NOT NULL,
                `description_ru` VARCHAR(256) NOT NULL,
                `updated_at` INT(10) UNSIGNED NOT NULL,
                PRIMARY KEY (`ref`),
                KEY (`description`),
                KEY (`description_ru`)
            ) $collate;
AREA;
        $this->db->query($regionQuery);

        /*
         * Create cities table
         */
        $cityTableName = $factory->cityRepo()->table();
        $cityQuery = <<<CITY
            CREATE TABLE {$cityTableName} (
                `ref` VARCHAR(50) NOT NULL,
                `description` VARCHAR(256) NOT NULL,
                `description_ru` VARCHAR(256) NOT NULL,
                `parent_ref` VARCHAR(50) NOT NULL,
                `updated_at` INT(10) UNSIGNED NOT NULL,
                PRIMARY KEY (`ref`),
                KEY (`parent_ref`, `description`),
                KEY (`parent_ref`, `description_ru`),
                FOREIGN KEY (`parent_ref`) REFERENCES {$regionTableName}(`ref`) ON DELETE CASCADE 
            ) $collate;
CITY;
        $this->db->query($cityQuery);

        /*
         * create warehouses table
         */
        $warehouseTableName = $factory->warehouseRepo()->table();
        $warehouseQuery = <<<WAREHOUSE
            CREATE TABLE {$warehouseTableName} (
                `ref` VARCHAR(50) NOT NULL,
                `description` VARCHAR(256) NOT NULL,
                `description_ru` VARCHAR(256) NOT NULL,
                `parent_ref` VARCHAR(50) NOT NULL,
                `updated_at` INT(10) UNSIGNED NOT NULL,
                PRIMARY KEY (`ref`),
                FOREIGN KEY (`parent_ref`) REFERENCES {$cityTableName}(`ref`) ON DELETE CASCADE 
            ) $collate;
WAREHOUSE;
        $this->db->query($warehouseQuery);

    }

    private function dropTables()
    {
        $factory = AreaRepositoryFactory::instance();
        $factory->cityRepo()->table();
        $this->dropTableByName($factory->warehouseRepo()->table());
        $this->dropTableByName($factory->cityRepo()->table());
        $this->dropTableByName($factory->regionRepo()->table());
    }

    /**
     * @param string $table
     */
    private function dropTableByName($table)
    {
        $query = "DROP TABLE IF EXISTS {$table}";
        $this->db->query($query);
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