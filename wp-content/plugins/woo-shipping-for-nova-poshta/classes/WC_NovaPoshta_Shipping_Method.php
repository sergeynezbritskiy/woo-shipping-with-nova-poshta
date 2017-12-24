<?php

use plugins\NovaPoshta\classes\Area;
use plugins\NovaPoshta\classes\base\ArrayHelper;
use plugins\NovaPoshta\classes\base\Options;
use plugins\NovaPoshta\classes\Checkout;
use plugins\NovaPoshta\classes\Customer;

/**
 * Class WC_NovaPoshta_Shipping_Method
 */
class WC_NovaPoshta_Shipping_Method extends WC_Shipping_Method
{
    /**
     * Constructor for your shipping class
     *
     * @access public
     * @param int $instance_id
     */
    public function __construct($instance_id = 0)
    {
        parent::__construct($instance_id);
        $this->id = NOVA_POSHTA_SHIPPING_METHOD;
        $this->method_title = __('Nova Poshta', NOVA_POSHTA_DOMAIN);
        $this->method_description = $this->getDescription();

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

    public function test($packages){

        return $packages;
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
                'class' => 'js-hide-nova-poshta-option'
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
                'class' => 'js-hide-nova-poshta-option'
            ),
            Options::WAREHOUSE_NAME => array(
                'title' => __('Nova Poshta Warehouse (#)', NOVA_POSHTA_DOMAIN),
                'type' => 'input',
                'description' => __('Specify the warehouse, from where you are sending goods. (After save API key)', NOVA_POSHTA_DOMAIN),
            ),
            Options::WAREHOUSE => array(
                'type' => 'hidden',
                'default' => '',
                'class' => 'js-hide-nova-poshta-option'
            ),
            Options::USE_FIXED_PRICE_ON_DELIVERY => [
                'title' => __('Set Fixed Price for Delivery.', NOVA_POSHTA_DOMAIN),
                'label' => __('If checked, fixed price will be set for delivery.', NOVA_POSHTA_DOMAIN),
                'type' => 'checkbox',
                'default' => 'no',
                'description' => '',
            ],
            Options::FIXED_PRICE => [
                'title' => __('Fixed price', NOVA_POSHTA_DOMAIN),
                'type' => 'text',
                'description' => __('Delivery Fixed price.', NOVA_POSHTA_DOMAIN),
                'default' => 0.00
            ],
        );
    }

    /**
     * calculate_shipping function.
     *
     * @access public
     *
     * @param array $package
     */
    public function calculate_shipping($package = array())
    {
        $rate = array(
            'id' => $this->id,
            'label' => $this->title,
            'cost' => 0,
            'calc_tax' => 'per_item'
        );
        $customer = Customer::instance();


        $location = Checkout::instance()->getLocation();
        $cityRecipient = Customer::instance()->getMetadata('nova_poshta_city', $location)
            //for backward compatibility with woocommerce 2.x.x
            ?: Customer::instance()->getMetadata('nova_poshta_city', '');

        if (NP()->options->useFixedPriceOnDelivery) {
            $rate['cost'] = NP()->options->fixedPrice;
        } elseif ($cityRecipient) {
            $citySender = NP()->options->senderCity;
            $serviceType = 'WarehouseWarehouse';
            /** @noinspection PhpUndefinedFieldInspection */
            $cartWeight = max(1, WC()->cart->cart_contents_weight);
            /** @noinspection PhpUndefinedFieldInspection */
            $cartTotal = max(1, WC()->cart->cart_contents_total);
            try {
                $result = NP()->api->getDocumentPrice($citySender, $cityRecipient, $serviceType, $cartWeight, $cartTotal);
                $cost = array_shift($result);
                $rate['cost'] = ArrayHelper::getValue($cost, 'Cost', 0);
            } catch (Exception $e) {
                NP()->log->error($e->getMessage());
            }
        }
        // Register the rate
        $this->add_rate($rate);
    }

    /**
     * Is this method available?
     * @param array $package
     * @return bool
     */
    public function is_available($package)
    {
        return $this->is_enabled();
    }

    /**
     * @return string
     */
    private function getDescription()
    {
        $href = "https://wordpress.org/support/view/plugin-reviews/woo-shipping-for-nova-poshta?filter=5#postform";
        $link = sprintf('<a href="%s" target="_blank" class="np-rating-link">&#9733;&#9733;&#9733;&#9733;&#9733;</a>', $href);

        $descriptions = array();
        $descriptions[] = __('Shipping with popular Ukrainian logistic company Nova Poshta', NOVA_POSHTA_DOMAIN);
        if (!NP()->options->pluginRated) {
            $descriptions[] = sprintf(__("If you like our work, please leave us a %s rating!", NOVA_POSHTA_DOMAIN), $link);
        } else {
            $descriptions[] = __('Thank you for encouraging us!', NOVA_POSHTA_DOMAIN);
        }
        return implode($descriptions, '<br>');
    }
}