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
    public function init()
    {
        //set up calculator
        add_action('woocommerce_after_calculate_totals', array($this, 'setupCalculatorFields'));
        add_action('woocommerce_calculated_shipping', array($this, 'initNovaPoshtaCalculatorOptions'));
    }

    public function initNovaPoshtaCalculatorOptions()
    {
        if (NP()->isNP()) {
            /** @noinspection PhpUndefinedFieldInspection */
            WC()->customer->nova_poshta_city = ArrayHelper::getValue($_POST, 'calc_nova_poshta_shipping_city');
        }
    }

    /**
     * hook for action woocommerce_before_shipping_calculator
     * called in woocommerce/templates/cart/shipping-calculator.phpAx
     */
    public function setupCalculatorFields()
    {
        if (NP()->isNP()) {
            add_filter('woocommerce_shipping_calculator_enable_city', '__return_true');
            add_filter('woocommerce_shipping_calculator_enable_postcode', '__return_false');
        }
    }

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