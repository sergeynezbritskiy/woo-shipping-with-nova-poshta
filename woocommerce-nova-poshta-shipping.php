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
use plugins\NovaPoshta\classes\base\ArrayHelper;
use plugins\NovaPoshta\classes\Log;
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
define('NOVA_POSHTA_SHIPPING_PLUGIN_URL', trailingslashit(plugin_dir_url(__FILE__)));
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
     * @property Log log
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
            add_action('wp_enqueue_scripts', array($this, 'styles'));
            add_action('admin_enqueue_scripts', array($this, 'adminScripts'));

            //register new shipping method
            add_action('woocommerce_shipping_init', array($this, 'initNovaPoshtaShippingMethod'));
            add_filter('woocommerce_shipping_methods', array($this, 'addNovaPoshtaShippingMethod'));

            //set up checkout
            add_filter('woocommerce_checkout_fields', array($this, 'maybeDisableDefaultShippingMethods'));
            add_filter('woocommerce_billing_fields', array($this, 'addNovaPoshtaBillingFields'));
            add_filter('woocommerce_shipping_fields', array($this, 'addNovaPoshtaShippingFields'));
            add_action('woocommerce_checkout_process', array($this, 'saveNovaPoshtaOptions'), 10, 2);
            add_action('woocommerce_checkout_update_order_meta', array($this, 'updateOrderMeta'));

            add_filter('nova_poshta_disable_default_fields', array($this, 'disableDefaultFields'));
            add_filter('nova_poshta_disable_nova_poshta_fields', array($this, 'disableNovaPoshtaFields'));

            add_filter('default_checkout_billing_nova_poshta_region', array($this, 'getDefaultRegion'));
            add_filter('default_checkout_billing_nova_poshta_city', array($this, 'getDefaultCity'));
            add_filter('default_checkout_billing_nova_poshta_warehouse', array($this, 'getDefaultWarehouse'));
            add_filter('default_checkout_shipping_nova_poshta_region', array($this, 'getDefaultRegion'));
            add_filter('default_checkout_shipping_nova_poshta_city', array($this, 'getDefaultCity'));
            add_filter('default_checkout_shipping_nova_poshta_warehouse', array($this, 'getDefaultWarehouse'));

            //set up calculator
            add_action('woocommerce_before_shipping_calculator', array($this, 'setupCalculatorFields'));
            add_action('woocommerce_after_shipping_calculator', array($this, 'initNovaPoshtaCalculator'));
        }

        /**
         * hook for action woocommerce_before_shipping_calculator
         * called in woocommerce/templates/cart/shipping-calculator.phpAx
         */
        public function setupCalculatorFields()
        {
            if ($this->isNP()) {
                add_filter('woocommerce_shipping_calculator_enable_city', '__return_true');
                add_filter('woocommerce_shipping_calculator_enable_postcode', '__return_false');
            }
        }

        /**
         * @return string
         */
        public function getDefaultRegion()
        {
            /** @noinspection PhpUndefinedFieldInspection */
            return WC()->customer->nova_poshta_region;
        }

        /**
         * @return string
         */
        public function getDefaultCity()
        {
            /** @noinspection PhpUndefinedFieldInspection */
            return WC()->customer->nova_poshta_city;
        }

        /**
         * @return string
         */
        public function getDefaultWarehouse()
        {
            /** @noinspection PhpUndefinedFieldInspection */
            return WC()->customer->nova_poshta_warehouse;
        }

        public function saveNovaPoshtaOptions()
        {
            if ($this->isPost() && $this->isNP()) {
                $customer = WC()->customer;
                $fieldGroup = $this->shipToDifferentAddress() ? Area::SHIPPING : Area::BILLING;
                /** @noinspection PhpUndefinedFieldInspection */
                $customer->nova_poshta_region = ArrayHelper::getValue($_POST, Region::key($fieldGroup), '');
                /** @noinspection PhpUndefinedFieldInspection */
                $customer->nova_poshta_city = ArrayHelper::getValue($_POST, City::key($fieldGroup), '');
                /** @noinspection PhpUndefinedFieldInspection */
                $customer->nova_poshta_warehouse = ArrayHelper::getValue($_POST, Warehouse::key($fieldGroup), '');
            }
        }

        /**
         * Filter for hook woocommerce_shipping_init
         * @param $fields
         * @return mixed
         */
        public function maybeDisableDefaultShippingMethods($fields)
        {
            if ($this->isPost() && $this->isNP()) {
                $fields = apply_filters('nova_poshta_disable_default_fields', $fields);
                $fields = apply_filters('nova_poshta_disable_nova_poshta_fields', $fields);
            }
            return $fields;
        }

        /**
         * Hook for adding nova poshta billing fields
         * @param array $fields
         * @return array
         */
        public function addNovaPoshtaBillingFields($fields)
        {
            return $this->addNovaPoshtaFields($fields, Area::BILLING);
        }

        /**
         * Hook for adding nova poshta shipping fields
         * @param array $fields
         * @return array
         */
        public function addNovaPoshtaShippingFields($fields)
        {
            return $this->addNovaPoshtaFields($fields, Area::SHIPPING);
        }

        /**
         * @param array $fields
         * @param string $location
         * @return array
         */
        private function addNovaPoshtaFields($fields, $location)
        {
            /** @noinspection PhpUndefinedFieldInspection */
            $area = WC()->customer->nova_poshta_region;
            /** @noinspection PhpUndefinedFieldInspection */
            $city = WC()->customer->nova_poshta_city;
            $fields[Region::key($location)] = [
                'label' => __('Region', NOVA_POSHTA_DOMAIN),
                'type' => 'select',
                'required' => $this->isGet() ?: $this->isNP(),
                'default' => '',
                'options' => OptionsHelper::getList(Region::findAll()),
                'class' => array(),
                'custom_attributes' => array(),
            ];
            $fields[City::key($location)] = [
                'label' => __('City', NOVA_POSHTA_DOMAIN),
                'type' => 'select',
                'required' => $this->isGet() ?: $this->isNP(),
                'options' => OptionsHelper::getList(City::findByParentAreaRef($area)),
                'class' => array(),
                'value' => '',
                'custom_attributes' => array(),
            ];
            $fields[Warehouse::key($location)] = [
                'label' => __('Nova Poshta Warehouse (#)', NOVA_POSHTA_DOMAIN),
                'type' => 'select',
                'required' => $this->isGet() ?: $this->isNP(),
                'options' => OptionsHelper::getList(Warehouse::findByParentAreaRef($city)),
                'class' => array(),
                'value' => '',
                'custom_attributes' => array(),
            ];
            return $fields;
        }


        /**
         * @param array $fields
         * @return array
         */
        public function disableNovaPoshtaFields($fields)
        {
            $location = $this->shipToDifferentAddress() ? 'billing' : 'shipping';
            $fields[$location][Region::key($location)]['required'] = false;
            $fields[$location][City::key($location)]['required'] = false;
            $fields[$location][Warehouse::key($location)]['required'] = false;
            return $fields;
        }

        /**
         * @param array $fields
         * @return array
         */
        public function disableDefaultFields($fields)
        {
            $location = $this->shipToDifferentAddress() ? 'shipping' : 'billing';
            if (array_key_exists($location . '_state', $fields[$location])) {
                $fields[$location][$location . '_state']['required'] = false;
            }
            if (array_key_exists($location . '_city', $fields[$location])) {
                $fields[$location][$location . '_city']['required'] = false;
            }
            if (array_key_exists($location . '_address_1', $fields[$location])) {
                $fields[$location][$location . '_address_1']['required'] = false;
            }
            if (array_key_exists($location . '_postcode', $fields[$location])) {
                $fields[$location][$location . '_postcode']['required'] = false;
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
                $fieldGroup = $this->shipToDifferentAddress() ? Area::SHIPPING : Area::BILLING;

                $regionKey = Region::key($fieldGroup);
                $regionRef = sanitize_text_field($_POST[$regionKey]);
                $area = new Region($regionRef);
                update_post_meta($orderId, '_' . $fieldGroup . '_state', $area->description);

                $cityKey = City::key($fieldGroup);
                $cityRef = sanitize_text_field($_POST[$cityKey]);
                $city = new City($cityRef);
                update_post_meta($orderId, '_' . $fieldGroup . '_city', $city->description);

                $warehouseKey = Warehouse::key($fieldGroup);
                $warehouseRef = sanitize_text_field($_POST[$warehouseKey]);
                $warehouse = new Warehouse($warehouseRef);
                update_post_meta($orderId, '_' . $fieldGroup . '_address_1', $warehouse->description);


                $shippingFieldGroup = Area::SHIPPING;
                if ($this->shipToDifferentAddress()) {
                    update_post_meta($orderId, '_' . Region::key($shippingFieldGroup), $area->ref);
                    update_post_meta($orderId, '_' . City::key($shippingFieldGroup), $city->ref);
                    update_post_meta($orderId, '_' . Warehouse::key($shippingFieldGroup), $warehouse->ref);
                } else {
                    update_post_meta($orderId, '_' . $shippingFieldGroup . '_state', $area->description);
                    update_post_meta($orderId, '_' . $shippingFieldGroup . '_city', $city->description);
                    update_post_meta($orderId, '_' . $shippingFieldGroup . '_address_1', $warehouse->description);
                }

            }
        }

        /**
         * @return bool
         */
        public function isNP()
        {
            /** @noinspection PhpUndefinedFieldInspection */
            $sessionShippingMethods = WC()->session->chosen_shipping_methods;

            $chosenShippingMethod = '';
            if ($this->isPost() && ($shippingMethods = ArrayHelper::getValue($_POST, 'shipping_method', array()))) {
                $chosenShippingMethod = array_shift($shippingMethods);
            } elseif (isset($sessionShippingMethods) && count($sessionShippingMethods) > 0) {
                $chosenShippingMethod = array_shift($sessionShippingMethods);
            } else {
                /** @noinspection PhpUndefinedFieldInspection */
                $packages = WC()->shipping->get_packages();
                foreach ($packages as $i => $package) {
                    $chosenShippingMethod = isset($sessionShippingMethods[$i]) ? $sessionShippingMethods[$i] : '';
                }
            }
            return $chosenShippingMethod == NOVA_POSHTA_SHIPPING_METHOD;
        }

        /**
         * @return bool
         */
        public function shipToDifferentAddress()
        {
            $shipToDifferentAddress = isset($_POST['ship_to_different_address']) ? true : false;

            if (isset($_POST['shiptobilling'])) {
                _deprecated_argument('WC_Checkout::process_checkout()', '2.1', 'The "shiptobilling" field is deprecated. The template files are out of date');
                $shipToDifferentAddress = $_POST['shiptobilling'] ? false : true;
            }

            // Ship to billing only option
            if (wc_ship_to_billing_address_only()) {
                $shipToDifferentAddress = false;
            }
            return $shipToDifferentAddress;
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
            $suffix = $this->options->isDebug() ? '.js' : '.min.js';
            $fileName = 'assets/js/nova-poshta' . $suffix;
            wp_register_script(
                'nova-poshta-js',
                NOVA_POSHTA_SHIPPING_PLUGIN_URL . $fileName,
                ['jquery-ui-autocomplete'],
                filemtime(NOVA_POSHTA_SHIPPING_PLUGIN_DIR . $fileName)
            );

            $this->localizeHelper('nova-poshta-js');

            wp_enqueue_script('nova-poshta-js');
        }

        /**
         * Enqueue all required scripts
         */
        public function styles()
        {
            global $wp_scripts;
            $jquery_version = isset($wp_scripts->registered['jquery-ui-core']->ver) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';
            wp_register_style('jquery-ui-style', '//code.jquery.com/ui/' . $jquery_version . '/themes/smoothness/jquery-ui.css', array(), $jquery_version);
            wp_enqueue_style('jquery-ui-style');
        }

        /**
         * Enqueue all required scripts for admin panel
         */
        public function adminScripts()
        {
            $suffix = $this->options->isDebug() ? '.js' : '.min.js';
            $fileName = 'assets/js/nova-poshta-admin' . $suffix;
            wp_register_script(
                'nova-poshta-admin-js',
                NOVA_POSHTA_SHIPPING_PLUGIN_URL . $fileName,
                ['jquery-ui-autocomplete', 'jquery-ui-style'],
                filemtime(NOVA_POSHTA_SHIPPING_PLUGIN_DIR . $fileName)
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
                /** @noinspection PhpIncludeInspection */
                require_once NOVA_POSHTA_SHIPPING_PLUGIN_DIR . 'classes/WC_NovaPoshta_Shipping_Method.php';
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
         * @return bool
         */
        public function isDebug()
        {
            return $this->options->isDebug();
        }

        /**
         * @return Options
         */
        protected function getOptions()
        {
            $this->options = Options::instance();
            return $this->options;
        }

        /**
         * @return Log
         */
        protected function getLog()
        {
            $this->log = Log::instance();
            return $this->log;
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
            if (static::$_instance == null) {
                static::$_instance = new static();
            }
            return static::$_instance;
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