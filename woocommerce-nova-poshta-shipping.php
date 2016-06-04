<?php
/*
Plugin Name: WooСommerce Nova Poshta Shipping
Plugin URI: http://woothemes.com/woocommerce
Description: Plugin for administrating Nova Poshta shipping method within WooСommerce Plugin
Version: 1.0.0
Author: Sergey Nezbritskiy
Author URI: http://sergey-nezbritskiy.com
*/

use plugins\NovaPoshta\classes\AjaxRoute;
use plugins\NovaPoshta\classes\Area;
use plugins\NovaPoshta\classes\base\Base;
use plugins\NovaPoshta\classes\base\Options;
use plugins\NovaPoshta\classes\City;
use plugins\NovaPoshta\classes\Database;
use plugins\NovaPoshta\classes\DatabaseSync;
use plugins\NovaPoshta\classes\NovaPoshtaApi;
use plugins\NovaPoshta\classes\Warehouse;

define('NOVA_POSHTA_SHIPPING_PLUGIN_DIR', trailingslashit(dirname(__FILE__)));
define('NOVA_POSHTA_SHIPPING_PLUGIN_URL', plugin_dir_url(__FILE__));
define('NOVA_POSHTA_SHIPPING_TEMPLATES_DIR', trailingslashit(NOVA_POSHTA_SHIPPING_PLUGIN_DIR . 'templates'));
define('NOVA_POSHTA_SHIPPING_CLASSES_DIR', trailingslashit(NOVA_POSHTA_SHIPPING_PLUGIN_DIR . 'classes'));
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
     * @property Options options
     */
    class NovaPoshta extends Base
    {
        const LOCALE_RU = 'ru_RU';

        /**
         * Register main plugin hooks
         */
        public function init()
        {
            register_activation_hook(__FILE__, array($this, 'activatePlugin'));
            register_deactivation_hook(__FILE__, array($this, 'deactivatePlugin'));

            add_action('init', array(AjaxRoute::class, 'init'));
            add_action('admin_init', array(DatabaseSync::instance(), 'synchroniseLocations'));
            add_action('plugins_loaded', array($this, 'loadPluginDomain'));
            add_action('woocommerce_shipping_init', array($this, 'initNovaPoshtaShippingMethod'));
            add_action('wp_enqueue_scripts', array($this, 'scripts'));
            add_action('admin_enqueue_scripts', array($this, 'scripts'));

            add_filter('woocommerce_shipping_methods', array($this, 'addNovaPoshtaShippingMethod'));
            add_filter('woocommerce_locate_template', array($this, 'locateTemplate'), 1, 3);
            add_filter("woocommerce_checkout_fields", array($this, 'changeFieldsOrder'));
            add_filter('woocommerce_order_formatted_billing_address', array($this, 'filterWoocommerceOrderFormattedBillingAddress'), 10, 3);
            add_filter('woocommerce_order_formatted_shipping_address', array($this, 'filterWoocommerceOrderFormattedBillingAddress'), 10, 3);
        }

        /**
         * @param array $instance
         * @return array
         */
        public function filterWoocommerceOrderFormattedBillingAddress($instance)
        {
            $state = Area::findByRef($instance['state']);
            $instance['state'] = $state->description;

            $city = City::findByRef($instance['city']);
            $instance['city'] = $city->description;

            $warehouse = Warehouse::findByRef($instance['address_1']);
            $instance['address_1'] = $warehouse->description;

            // make filter magic happen here...
            return $instance;
        }

        /**
         * Enqueue all required scripts
         */
        public function scripts()
        {
            wp_register_script(
                'nova-poshta-js',
                NOVA_POSHTA_SHIPPING_PLUGIN_URL . '/assets/js/nova-poshta.js',
                ['jquery-ui-autocomplete'],
                filemtime(NOVA_POSHTA_SHIPPING_PLUGIN_DIR . 'assets/js/nova-poshta.js')
            );

            wp_localize_script('nova-poshta-js', 'NovaPoshtaHelper', [
                'ajaxUrl' => admin_url('admin-ajax.php', 'relative'),
                'chooseAnOptionText' => __('Choose an option', NOVA_POSHTA_DOMAIN),
                'getAreasBySuggestionAction' => AjaxRoute::GET_AREAS_BY_SUGGESTION,
                'getCitiesBySuggestionAction' => AjaxRoute::GET_CITIES_BY_SUGGESTION,
                'getWarehousesBySuggestionAction' => AjaxRoute::GET_WAREHOUSES_BY_SUGGESTION,
                'getCitiesAction' => AjaxRoute::GET_CITIES_ROUTE,
                'getWarehousesAction' => AjaxRoute::GET_WAREHOUSES_ROUTE,
            ]);

            wp_enqueue_script('nova-poshta-js');
        }

        /**
         * @param array $fields
         * @return array
         */
        function changeFieldsOrder($fields)
        {
            $order = array(
                "billing_email",
                "billing_phone",
                "billing_first_name",
                "billing_last_name",
                "billing_state",
                "billing_city",
                "billing_address_1",
            );
            $orderedFields = array();
            foreach ($order as $field) {
                $orderedFields[$field] = $fields["billing"][$field];
            }
            $fields["billing"] = $orderedFields;
            return $fields;
        }

        /**
         * @param string $template
         * @param string $templateName
         * @param string $templatePath
         * @return string
         */
        public function locateTemplate($template, $templateName, $templatePath)
        {
            global $woocommerce;
            $_template = $template;
            if (!$templatePath)
                $templatePath = $woocommerce->template_url;

            $pluginPath = NOVA_POSHTA_SHIPPING_TEMPLATES_DIR . 'woocommerce/';

            // Look within passed path within the theme - this is priority
            $template = locate_template(array(
                $templatePath . $templateName,
                $templateName
            ));

            if (!$template && file_exists($pluginPath . $templateName)) {
                $template = $pluginPath . $templateName;
            }

            return $template ?: $_template;
        }

        /**
         * @param array $methods
         * @return array
         */
        public function addNovaPoshtaShippingMethod($methods)
        {
            $methods[] = 'WC_NovaPoshta_Shipping_Method';
            return $methods;
        }

        /**
         * Init NovaPoshta shipping method class
         */
        public function initNovaPoshtaShippingMethod()
        {
            if (!class_exists('WC_NovaPoshta_Shipping_Method')) {
                require_once __DIR__ . '/classes/WC_NovaPoshta_Shipping_Method.php';
            }
        }

        /**
         * Activation hook handler
         */
        public function activatePlugin()
        {
            Database::instance()->createTables();
            DatabaseSync::instance()->synchroniseLocations();
        }

        /**
         * Activation hook handler
         */
        public function deactivatePlugin()
        {
            Database::instance()->dropTables();
            Options::instance()->clearOptions();
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
         * @return Options
         */
        public function getOptions()
        {
            $this->options = Options::instance();
            return $this->options;
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
         * NovaPoshta constructor.
         *
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

    NovaPoshta::instance()->init();
}

/**
 * @return NovaPoshta
 */
function NP()
{
    return NovaPoshta::instance();
}