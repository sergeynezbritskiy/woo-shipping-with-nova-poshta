<?php

namespace plugins\NovaPoshta\classes;

use plugins\NovaPoshta\classes\base\ArrayHelper;
use plugins\NovaPoshta\classes\base\Base;
use plugins\NovaPoshta\classes\repository\AreaRepositoryFactory;

/**
 * Class Calculator
 * @package plugins\NovaPoshta\classes
 */
class Calculator extends Base
{

    /**
     * @var Calculator
     */
    private static $_instance;

    /**
     * @return Calculator
     */
    public static function instance()
    {
        if (static::$_instance == null) {
            static::$_instance = new static();
        }
        return static::$_instance;
    }

    public function init()
    {
        //set up calculator
        add_action('woocommerce_after_calculate_totals', array($this, 'setupCalculatorFields'));
        add_action('woocommerce_calculated_shipping', array($this, 'initNovaPoshtaCalculatorOptions'));
        add_filter('woocommerce_formatted_address_replacements', array($this, 'formatNovaPoshtaFields'));

    }

    public function initNovaPoshtaCalculatorOptions()
    {
        if (NP()->isNP()) {
            $city = ArrayHelper::getValue($_POST, 'calc_nova_poshta_shipping_city');
            $customer = Customer::instance();
            $customer->setMetadata('nova_poshta_city', $city, Area::SHIPPING);
            $customer->setMetadata('nova_poshta_city', $city, Area::BILLING);
        }
    }

    /**
     * hook for action woocommerce_before_shipping_calculator
     * called in woocommerce/templates/cart/shipping-calculator.php
     */
    public function setupCalculatorFields()
    {
        if (NP()->isNP()) {
            add_filter('woocommerce_shipping_calculator_enable_city', '__return_true');
            add_filter('woocommerce_shipping_calculator_enable_postcode', '__return_false');
        }
    }

    /**
     * @param array $fields
     * @return array
     */
    public function formatNovaPoshtaFields($fields)
    {
        if (NP()->isNP()) {
            $factory = AreaRepositoryFactory::instance();

            $regionCode = $fields['{state}'];
            $cityCode = $fields['{city}'];
            $warehouseCode = $fields['{address_1}'];

            $region = $factory->regionRepo()->findByRef($regionCode);
            $city = $factory->cityRepo()->findByRef($cityCode);
            $warehouse = $factory->warehouseRepo()->findByRef($warehouseCode);

            $regionName = $region ? $region->description : '';
            $cityName = $city ? $city->description : '';
            $warehouseName = $warehouse ? $warehouse->description : '';

            $fields['{state}'] = $regionName;
            $fields['{city}'] = $cityName;
            $fields['{address_1}'] = $warehouseName;

            $fields['{state_upper}'] = strtoupper($regionName);
            $fields['{city_upper}'] = strtoupper($cityName);
            $fields['{address_1_upper}'] = strtoupper($warehouseName);
        }

        return $fields;
    }

    /**
     * Calculator constructor.
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