<?php

namespace plugins\NovaPoshta\classes;

use plugins\NovaPoshta\classes\base\ArrayHelper;
use plugins\NovaPoshta\classes\base\Base;

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