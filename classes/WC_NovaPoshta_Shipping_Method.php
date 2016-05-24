<?php
use plugins\NovaPoshta\classes\Area;
use plugins\NovaPoshta\classes\base\Options;
use plugins\NovaPoshta\classes\City;
use plugins\NovaPoshta\classes\Warehouse;

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
        $this->id = 'nova_poshta_shipping_method';
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
    function init_form_fields()
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
            Options::OPTION_KEY_API_KEY => array(
                'title' => __('API Key', NOVA_POSHTA_DOMAIN),
                'type' => 'text',
                'description' => __('Get your API Key. <a href="https://my.novaposhta.ua/settings/index#apikeys">My Account (Nova Poshta)</a>', NOVA_POSHTA_DOMAIN),
                'default' => ''
            ),
            Options::OPTION_KEY_AREA => array(
                'title' => __('Area', NOVA_POSHTA_DOMAIN),
                'type' => 'select',
                'description' => __('Specify the area, from where you are sending goods. (After save API key)', NOVA_POSHTA_DOMAIN),
                'default' => '',
                'options' => $this->getAreasList(),
            ),
            Options::OPTION_KEY_CITY => array(
                'title' => __('City', NOVA_POSHTA_DOMAIN),
                'type' => 'select',
                'description' => __('Specify the city, from where you are sending goods. (After save API key)', NOVA_POSHTA_DOMAIN),
                'default' => '',
                'options' => $this->getCitiesList(),
            ),
            Options::OPTION_KEY_WAREHOUSE => array(
                'title' => __('Warehouse (Number)', NOVA_POSHTA_DOMAIN),
                'type' => 'select',
                'description' => __('Specify the warehouse, from where you are sending goods. (After save API key)', NOVA_POSHTA_DOMAIN),
                'options' => $this->getWarehousesList(),
            ),
        );
    }

    /**
     * @return array
     */
    private function getAreasList()
    {
        $result = array('' => __('Choose an option', NOVA_POSHTA_DOMAIN));
        $areas = Area::findAll();
        /** @var Area $area */
        foreach ($areas as $area) {
            $result[$area->ref] = $area->description;
        }
        return $result;
    }

    /**
     * @return array
     */
    private function getCitiesList()
    {
        $result = array('' => __('Choose an option', NOVA_POSHTA_DOMAIN));
        $cities = City::findByAreaRef(NP()->options->senderArea);
        /** @var City $city */
        foreach ($cities as $city) {
            $result[$city->ref] = $city->description;
        }
        return $result;
    }

    /**
     * @return array
     */
    private function getWarehousesList()
    {
        $result = array('' => __('Choose an option', NOVA_POSHTA_DOMAIN));
        $warehouses = Warehouse::findByCityRef(NP()->options->senderCity);
        /** @var Warehouse $warehouse */
        foreach ($warehouses as $warehouse) {

            $result[$warehouse->ref] = $warehouse->description;
        }
        return $result;
    }
}