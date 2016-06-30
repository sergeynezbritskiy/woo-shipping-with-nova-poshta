<?php
use plugins\NovaPoshta\classes\base\ArrayHelper;
use plugins\NovaPoshta\classes\base\Options;

/**
 * Class WC_NovaPoshta_Shipping_Method
 */
class WC_NovaPoshta_Shipping_Method extends WC_Shipping_Method
{
    /**
     * Constructor for your shipping class
     *
     * @access public
     */
    public function __construct()
    {
        $this->id = NOVA_POSHTA_SHIPPING_METHOD;
        $this->method_title = __('Nova Poshta', NOVA_POSHTA_DOMAIN);
        $this->method_description = __('Shipping with popular Ukrainian logistic company Nova Poshta', NOVA_POSHTA_DOMAIN);

        $this->init();

        // Get setting values
        $this->title = $this->settings['title'];
        $this->enabled = $this->settings['enabled'];
    }

    /**
     * Init your settings
     *
     * @access public
     * @return void
     */
    function init()
    {
        $this->init_form_fields();
        $this->init_settings();
        // Save settings in admin if you have any defined
        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
    }

    /**
     * Initialise Gateway Settings Form Fields
     */
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', NOVA_POSHTA_DOMAIN),
                'label' => __('Enable NovaPoshta', NOVA_POSHTA_DOMAIN),
                'type' => 'checkbox',
                'description' => '',
                'default' => 'no'
            ),
            'title' => array(
                'title' => __('Nova Poshta', NOVA_POSHTA_DOMAIN),
                'type' => 'text',
                'description' => __('This controls the title which the user sees during checkout.', NOVA_POSHTA_DOMAIN),
                'default' => __('Nova Poshta', NOVA_POSHTA_DOMAIN)
            ),
            Options::DEBUG => array(
                'title' => __('Enable Debug Mode', NOVA_POSHTA_DOMAIN),
                'label' => __('Enable Debug Mode', NOVA_POSHTA_DOMAIN),
                'type' => 'checkbox',
                'description' => __('Extended logging, use full version of script instead of min versions', NOVA_POSHTA_DOMAIN),
                'default' => 'yes'
            ),
            Options::API_KEY => array(
                'title' => __('API Key', NOVA_POSHTA_DOMAIN),
                'type' => 'text',
                'description' => __('Get your API Key. <a href="https://my.novaposhta.ua/settings/index#apikeys">My Account (Nova Poshta)</a>', NOVA_POSHTA_DOMAIN),
                'default' => ''
            ),
            Options::AREA_NAME => array(
                'title' => __('Area', NOVA_POSHTA_DOMAIN),
                'type' => 'text',
                'description' => __('Specify the area, from where you are sending goods. (After save API key)', NOVA_POSHTA_DOMAIN),
                'default' => '',
            ),
            Options::AREA => array(
                'type' => 'hidden',
                'default' => '',
            ),
            Options::CITY_NAME => array(
                'title' => __('City', NOVA_POSHTA_DOMAIN),
                'type' => 'input',
                'description' => __('Specify the city, from where you are sending goods. (After save API key)', NOVA_POSHTA_DOMAIN),
                'default' => '',
            ),
            Options::CITY => array(
                'type' => 'hidden',
                'default' => '',
            ),
            Options::WAREHOUSE_NAME => array(
                'title' => __('Warehouse (Number)', NOVA_POSHTA_DOMAIN),
                'type' => 'input',
                'description' => __('Specify the warehouse, from where you are sending goods. (After save API key)', NOVA_POSHTA_DOMAIN),
            ),
            Options::WAREHOUSE => array(
                'type' => 'hidden',
                'default' => '',
            ),
        );
    }

    /**
     * calculate_shipping function.
     *
     * @access public
     * @return void
     */
    public function calculate_shipping()
    {
        $rate = array(
            'id' => $this->id,
            'label' => $this->title,
            'cost' => 0,
            'calc_tax' => 'per_item'
        );

        /** @noinspection PhpUndefinedFieldInspection */
        if ($cityRecipient = WC()->customer->nova_poshta_city) {
            $citySender = NP()->options->senderCity;
            $serviceType = 'WarehouseWarehouse';
            /** @noinspection PhpUndefinedFieldInspection */
            $cartWeight = WC()->cart->cart_contents_weight;
            $cartTotal = max(1, WC()->cart->cart_contents_total);
            try {
                $result = NP()->api->getDocumentPrice($citySender, $cityRecipient, $serviceType, $cartWeight, $cartTotal);
                $cost = array_shift($result);
                $rate['cost'] = ArrayHelper::getValue($cost, 'Cost', 0);
            } catch (Exception $e) {
            }
        }
        // Register the rate
        $this->add_rate($rate);
    }
}