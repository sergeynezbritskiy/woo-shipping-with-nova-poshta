<?php

namespace plugins\NovaPoshta\classes;

use plugins\NovaPoshta\classes\base\ArrayHelper;
use plugins\NovaPoshta\classes\base\Base;
use plugins\NovaPoshta\classes\base\OptionsHelper;

/**
 * Class Calculator
 * @property bool isCheckout
 * @package plugins\NovaPoshta\classes
 */
class Checkout extends Base
{

    /**
     * @var Checkout
     */
    private static $_instance;

    /**
     * @return Checkout
     */
    public static function instance()
    {
        if (static::$_instance == null) {
            static::$_instance = new static();
        }
        return static::$_instance;
    }

    /**
     * @return void
     */
    public function init()
    {
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
    }

    /**
     * @return void
     */
    public function saveNovaPoshtaOptions()
    {
        if (NP()->isPost() && NP()->isNP() && NP()->isCheckout()) {
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
        if (NP()->isPost() && NP()->isNP() && NP()->isCheckout()) {
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
     * Update the order meta with field value
     * @param int $orderId
     */
    public function updateOrderMeta($orderId)
    {
        if (NP()->isNP() && NP()->isCheckout()) {
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
    public function isCheckout()
    {
        global $post;
        $checkoutPageId = get_option('woocommerce_checkout_page_id');
        $pageId = ArrayHelper::getValue($post, 'ID', null);
        $this->isCheckout = $pageId && $checkoutPageId && ($pageId == $checkoutPageId);
        return $this->isCheckout;
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
        $required = NP()->isGet() ?: (NP()->isNP() && NP()->isCheckout());
        $fields[Region::key($location)] = [
            'label' => __('Region', NOVA_POSHTA_DOMAIN),
            'type' => 'select',
            'required' => $required,
            'default' => '',
            'options' => OptionsHelper::getList(Region::findAll()),
            'class' => array(),
            'custom_attributes' => array(),
        ];
        $fields[City::key($location)] = [
            'label' => __('City', NOVA_POSHTA_DOMAIN),
            'type' => 'select',
            'required' => $required,
            'options' => OptionsHelper::getList(City::findByParentAreaRef($area)),
            'class' => array(),
            'value' => '',
            'custom_attributes' => array(),
        ];
        $fields[Warehouse::key($location)] = [
            'label' => __('Nova Poshta Warehouse (#)', NOVA_POSHTA_DOMAIN),
            'type' => 'select',
            'required' => $required,
            'options' => OptionsHelper::getList(Warehouse::findByParentAreaRef($city)),
            'class' => array(),
            'value' => '',
            'custom_attributes' => array(),
        ];
        return $fields;
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