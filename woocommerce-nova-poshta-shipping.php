<?php
/*
Plugin Name: WooСommerce Nova Poshta Shipping
Plugin URI: http://woothemes.com/woocommerce
Description: Plugin for administrating Nova Poshta shipping method within WooСommerce Plugin
Version: 1.0.0
Author: Sergey Nezbritskiy
Author URI: http://sergey-nezbritskiy.com
*/

use plugins\NovaPoshta\classes\base\Base;
use plugins\NovaPoshta\classes\base\DatabaseSync;
use plugins\NovaPoshta\classes\Database;
use plugins\NovaPoshta\classes\DocumentGenerator;
use plugins\NovaPoshta\classes\NovaPoshtaApi;

define('NOVA_POSHTA_SHIPPING_PLUGIN_DIR', dirname(__FILE__) . '/');
define('NOVA_POSHTA_SHIPPING_PLUGIN_URL', plugin_dir_url(__FILE__));
define('NOVA_POSHTA_SHIPPING_TEMPLATES_DIR', NOVA_POSHTA_SHIPPING_PLUGIN_DIR . 'templates/');
define('NOVA_POSHTA_SHIPPING_CLASSES_DIR', NOVA_POSHTA_SHIPPING_PLUGIN_DIR . 'classes/');
define('NOVA_POSHTA_DOMAIN', untrailingslashit(basename(dirname(__FILE__))));
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/autoload.php';

/**
 * Check if WooCommerce is active
 */
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {


    /**
     * Class NovaPoshta
     *
     * @property wpdb db
     * @property NovaPoshtaApi api
     */
    class NovaPoshta extends Base
    {

        /**
         * Register main plugin hooks
         */
        public function init()
        {
            register_activation_hook(__FILE__, array($this, 'activatePlugin'));
            register_deactivation_hook(__FILE__, array($this, 'deactivatePlugin'));
            add_action('plugins_loaded', array($this, 'loadPluginDomain'));
//            add_action('init', array(DocumentGenerator::instance(), 'init'));
//            add_action('init', array(DatabaseSync::instance(), 'synchroniseLocations'));
        }

        /**
         * Activation hook handler
         */
        public function activatePlugin()
        {
            Database::instance()->createTables();
        }

        /**
         * Activation hook handler
         */
        public function deactivatePlugin()
        {
            Database::instance()->dropTables();
        }

        /**
         * Register translations directory
         * Register text domain
         */
        public function loadPluginDomain()
        {
            load_plugin_textdomain(NOVA_POSHTA_DOMAIN, false, './woocommerce-nova-poshta-shipping/i18n');
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
        }

        /**
         * @return wpdb
         */
        protected function getDb()
        {
            global $wpdb;
            $this->db = $wpdb;
            return $this->db;
        }

        /**
         * @return NovaPoshtaApi
         */
        protected function getApi()
        {
            $this->api = NovaPoshtaApi::instance();
            return $this->api;
        }

        /**
         * @access private
         */
        private function __clone()
        {
        }
    }

    NovaPoshta::instance()->init();
}

/**
 * @return NovaPoshta
 */
function NP()
{
    return NovaPoshta::instance();
}


