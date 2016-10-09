<?php
/*
Plugin Name: Woo Shipping for Nova Poshta
Plugin URI: http://altair-solutions.com.ua/portfolio/nova-poshta-shipping-for-woocommerce
Description: Plugin for administrating Nova Poshta shipping method within WooСommerce Plugin
Version: 1.2.1
Author: Altair Solutions, Ltd.
Author URI: http://altair-solutions.com.ua
*/

/*  Copyright 2016 Altair Solutions, Ltd (email: support@altair-solutions.com.ua)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

use plugins\NovaPoshta\classes\AjaxRoute;
use plugins\NovaPoshta\classes\base\ArrayHelper;
use plugins\NovaPoshta\classes\Calculator;
use plugins\NovaPoshta\classes\Checkout;
use plugins\NovaPoshta\classes\Log;
use plugins\NovaPoshta\classes\base\Base;
use plugins\NovaPoshta\classes\base\Options;
use plugins\NovaPoshta\classes\Database;
use plugins\NovaPoshta\classes\DatabaseSync;
use plugins\NovaPoshta\classes\NovaPoshtaApi;

define('NOVA_POSHTA_SHIPPING_PLUGIN_DIR', trailingslashit(dirname(__FILE__)));
define('NOVA_POSHTA_SHIPPING_PLUGIN_URL', trailingslashit(plugin_dir_url(__FILE__)));
define('NOVA_POSHTA_SHIPPING_TEMPLATES_DIR', trailingslashit(NOVA_POSHTA_SHIPPING_PLUGIN_DIR . 'templates'));
define('NOVA_POSHTA_SHIPPING_CLASSES_DIR', trailingslashit(NOVA_POSHTA_SHIPPING_PLUGIN_DIR . 'classes'));
define('NOVA_POSHTA_DOMAIN', untrailingslashit(basename(dirname(__FILE__))));
define('NOVA_POSHTA_SHIPPING_METHOD', 'nova_poshta_shipping_method');

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/autoload.php';

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

        if ($this->isWoocommerce()) {
            //general plugin actions
            add_action('init', array(AjaxRoute::getClass(), 'init'));
            add_action('admin_init', array(DatabaseSync::instance(), 'synchroniseLocations'));
            add_action('plugins_loaded', array($this, 'loadPluginDomain'));
            add_action('wp_enqueue_scripts', array($this, 'scripts'));
            add_action('wp_enqueue_scripts', array($this, 'styles'));
            add_action('admin_enqueue_scripts', array($this, 'adminScripts'));
            add_action('admin_enqueue_scripts', array($this, 'adminStyles'));

            //register new shipping method
            add_action('woocommerce_shipping_init', array($this, 'initNovaPoshtaShippingMethod'));
            add_filter('woocommerce_shipping_methods', array($this, 'addNovaPoshtaShippingMethod'));

            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'pluginActionLinks'));

            Checkout::instance()->init();
            Calculator::instance()->init();
        }
    }

    /**
     * @return bool
     */
    public function isWoocommerce()
    {
        return in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')));
    }


    /**
     * @return bool
     */
    public function isCheckout()
    {
        return Checkout::instance()->isCheckout();
    }

    /**
     * This method can be used safely only after woocommerce_after_calculate_totals hook
     * when $_SERVER['REQUEST_METHOD'] == 'GET'
     *
     * @return bool
     */
    public function isNP()
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $sessionMethods = WC()->session->chosen_shipping_methods;

        $chosenMethods = array();
        if ($this->isPost() && ($postMethods = (array)ArrayHelper::getValue($_POST, 'shipping_method', array()))) {
            $chosenMethods = $postMethods;
        } elseif (isset($sessionMethods) && count($sessionMethods) > 0) {
            $chosenMethods = $sessionMethods;
        }
        return in_array(NOVA_POSHTA_SHIPPING_METHOD, $chosenMethods);
    }

    /**
     * @return bool
     */
    public function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * @return bool
     */
    public function isGet()
    {
        return !$this->isPost();
    }

    /**
     * Enqueue all required scripts
     */
    public function scripts()
    {
        $suffix = $this->isDebug() ? '.js' : '.min.js';
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
     * Enqueue all required styles
     */
    public function styles()
    {
        global $wp_scripts;
        $jquery_version = isset($wp_scripts->registered['jquery-ui-core']->ver) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';
        wp_register_style('jquery-ui-style', '//code.jquery.com/ui/' . $jquery_version . '/themes/smoothness/jquery-ui.css', array(), $jquery_version);
        wp_enqueue_style('jquery-ui-style');
    }

    /**
     * Enqueue all required styles for admin panel
     */
    public function adminStyles()
    {
        $suffix = $this->isDebug() ? '.css' : '.min.css';
        $fileName = 'assets/css/style' . $suffix;
        wp_register_style('nova-poshta-style',
            NOVA_POSHTA_SHIPPING_PLUGIN_URL . $fileName,
            ['jquery-ui-style'],
            filemtime(NOVA_POSHTA_SHIPPING_PLUGIN_DIR . $fileName)
        );
        wp_enqueue_style('nova-poshta-style');
    }

    /**
     * Enqueue all required scripts for admin panel
     */
    public function adminScripts()
    {
        $suffix = $this->isDebug() ? '.js' : '.min.js';
        $fileName = 'assets/js/nova-poshta-admin' . $suffix;
        wp_register_script(
            'nova-poshta-admin-js',
            NOVA_POSHTA_SHIPPING_PLUGIN_URL . $fileName,
            ['jquery-ui-autocomplete'],
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
            'markPluginsAsRated' => AjaxRoute::MARK_PLUGIN_AS_RATED,
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
        $path = sprintf('./%s/i18n', NOVA_POSHTA_DOMAIN);
        load_plugin_textdomain(NOVA_POSHTA_DOMAIN, false, $path);
    }

    /**
     * @return bool
     */
    public function isDebug()
    {
        return $this->options->isDebug();
    }

    /**
     * @param array $links
     * @return array
     */
    public function pluginActionLinks($links)
    {
        $href = admin_url('admin.php?page=wc-settings&tab=shipping&section=' . NOVA_POSHTA_SHIPPING_METHOD);
        $settingsLink = sprintf('<a href="%s" title="%s">%s</a>', $href, esc_attr(__('View Plugin Settings', NOVA_POSHTA_DOMAIN)), __('Settings', NOVA_POSHTA_DOMAIN));
        array_unshift($links, $settingsLink);
        return $links;
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


/**
 * @return NovaPoshta
 */
function NP()
{
    return NovaPoshta::instance();
}