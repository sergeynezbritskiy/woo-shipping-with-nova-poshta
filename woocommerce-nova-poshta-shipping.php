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
use plugins\NovaPoshta\classes\base\ArrayHelper;
use plugins\NovaPoshta\classes\Region;
use plugins\NovaPoshta\classes\base\Base;
use plugins\NovaPoshta\classes\base\Options;
use plugins\NovaPoshta\classes\base\OptionsHelper;
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
define('NOVA_POSHTA_SHIPPING_METHOD', 'nova_poshta_shipping_method');

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

            //general plugin actions
            add_action('init', array(AjaxRoute::class, 'init'));
            add_action('admin_init', array(DatabaseSync::instance(), 'synchroniseLocations'));
            add_action('plugins_loaded', array($this, 'loadPluginDomain'));
            add_action('wp_enqueue_scripts', array($this, 'scripts'));
            add_action('admin_enqueue_scripts', array($this, 'adminScripts'));

            //register new shipping method
            add_action('woocommerce_shipping_init', array($this, 'initNovaPoshtaShippingMethod'));
            add_filter('woocommerce_shipping_methods', array($this, 'addNovaPoshtaShippingMethod'));

            add_filter('woocommerce_checkout_fields', array($this, 'addNewBillingFields'));
            add_action('woocommerce_checkout_update_order_meta', array($this, 'updateOrderMeta'));
        }

        /**
         * Filter for hook woocommerce_shipping_init
         * @param $fields
         * @return mixed
         */
        function addNewBillingFields($fields)
        {
            //TODO get city and region values
            //$area = WC()->checkout()->get_value(Region::key());
            //$city = WC()->checkout()->get_value(City::key());
            $area = '';
            $city = '';
            $fields['billing'][Region::key()] = [
                'label' => __('Region', NOVA_POSHTA_DOMAIN),
                'type' => 'select',
                'required' => $this->isGet() ?: $this->isNP(),
                'default' => '',
                'options' => OptionsHelper::getList(Region::findAll()),
                'class' => array(),
                'custom_attributes' => array(),
            ];
            $fields['billing'][City::key()] = [
                'label' => __('City', NOVA_POSHTA_DOMAIN),
                'type' => 'select',
                'required' => $this->isGet() ?: $this->isNP(),
                'options' => OptionsHelper::getList(City::findByParentAreaRef($area)),
                'class' => array(),
                'value' => '',
                'custom_attributes' => array(),
            ];
            $fields['billing'][Warehouse::key()] = [
                'label' => __('Nova Poshta Warehouse (#)', NOVA_POSHTA_DOMAIN),
                'type' => 'select',
                'required' => $this->isGet() ?: $this->isNP(),
                'options' => OptionsHelper::getList(Warehouse::findByParentAreaRef($city)),
                'class' => array(),
                'value' => '',
                'custom_attributes' => array(),
            ];
            //disable required validation for location default fields
            if ($this->isPost() && $this->isNP()) {
                if (array_key_exists('billing_state', $fields['billing'])) {
                    $fields['billing']['billing_state']['required'] = false;
                }
                if (array_key_exists('billing_city', $fields['billing'])) {
                    $fields['billing']['billing_city']['required'] = false;
                }
                if (array_key_exists('billing_address_1', $fields['billing'])) {
                    $fields['billing']['billing_address_1']['required'] = false;
                }
                if (array_key_exists('billing_postcode', $fields['billing'])) {
                    $fields['billing']['billing_postcode']['required'] = false;
                }
            }
            return $fields;
        }

        /**
         * Update the order meta with field value
         * @param int $orderId
         */
        public function updateOrderMeta($orderId)
        {
            if ($this->isNP()) {
                $regionKey = Region::key();
                $regionRef = sanitize_text_field($_POST[$regionKey]);
                $area = new Region($regionRef);
                update_post_meta($orderId, '_billing_' . $regionKey, $area->ref);
                update_post_meta($orderId, '_billing_state', $area->description);

                $cityKey = City::key();
                $cityRef = sanitize_text_field($_POST[$cityKey]);
                $city = new City($cityRef);
                update_post_meta($orderId, '_billing_' . $cityKey, $city->ref);
                update_post_meta($orderId, '_billing_city', $city->description);

                $warehouseKey = Warehouse::key();
                $warehouseRef = sanitize_text_field($_POST[$warehouseKey]);
                $warehouse = new Warehouse($warehouseRef);
                update_post_meta($orderId, '_billing_' . $warehouseKey, $warehouse->ref);
                update_post_meta($orderId, '_billing_address_1', $warehouse->description);
            }
        }

        /**
         * @return bool
         */
        public function isNP()
        {
            $chosenShippingMethod = '';
            if ($this->isPost() && ($shippingMethods = ArrayHelper::getValue($_POST, 'shipping_method', array()))) {
                $chosenShippingMethod = array_shift($shippingMethods);
            } else {
                /** @noinspection PhpUndefinedFieldInspection */
                $packages = WC()->shipping->get_packages();
                foreach ($packages as $i => $package) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $chosenShippingMethod = isset(WC()->session->chosen_shipping_methods[$i]) ? WC()->session->chosen_shipping_methods[$i] : '';
                }
            }
            return $chosenShippingMethod == NOVA_POSHTA_SHIPPING_METHOD;
        }

        /**
         * @return bool
         */
        private function isPost()
        {
            return $_SERVER['REQUEST_METHOD'] === 'POST';
        }

        /**
         * @return bool
         */
        private function isGet()
        {
            return !$this->isPost();
        }

        /**
         * Enqueue all required scripts
         */
        public function scripts()
        {
            wp_register_script(
                'nova-poshta-js',
                NOVA_POSHTA_SHIPPING_PLUGIN_URL . '/assets/js/nova-poshta.js',
                ['jquery'],
                filemtime(NOVA_POSHTA_SHIPPING_PLUGIN_DIR . 'assets/js/nova-poshta.js')
            );

            $this->localizeHelper('nova-poshta-js');

            wp_enqueue_script('nova-poshta-js');
        }

        /**
         * Enqueue all required scripts for admin panel
         */
        public function adminScripts()
        {
            wp_register_script(
                'nova-poshta-admin-js',
                NOVA_POSHTA_SHIPPING_PLUGIN_URL . '/assets/js/nova-poshta-admin.js',
                ['jquery-ui-autocomplete'],
                filemtime(NOVA_POSHTA_SHIPPING_PLUGIN_DIR . 'assets/js/nova-poshta-admin.js')
            );

            $this->localizeHelper('nova-poshta-admin-js');

            wp_enqueue_script('nova-poshta-admin-js');
        }

        /**
         * @param string $handle
         */
        public function localizeHelper($handle)
        {
            wp_localize_script($handle, 'NovaPoshtaHelper', [
                'ajaxUrl' => admin_url('admin-ajax.php', 'relative'),
                'chooseAnOptionText' => __('Choose an option', NOVA_POSHTA_DOMAIN),
                'getRegionsByNameSuggestionAction' => AjaxRoute::GET_REGIONS_BY_NAME_SUGGESTION,
                'getCitiesByNameSuggestionAction' => AjaxRoute::GET_CITIES_BY_NAME_SUGGESTION,
                'getWarehousesBySuggestionAction' => AjaxRoute::GET_WAREHOUSES_BY_NAME_SUGGESTION,
                'getCitiesAction' => AjaxRoute::GET_CITIES_ROUTE,
                'getWarehousesAction' => AjaxRoute::GET_WAREHOUSES_ROUTE,
            ]);
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
         * Deactivation hook handler
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