<?php

namespace plugins\NovaPoshta\classes;

use Exception;
use LisDev\Delivery\NovaPoshtaApi2;
use plugins\NovaPoshta\classes\base\Base;

/**
 * Class Api
 * @package plugins\NovaPoshta\classes
 * @property string apiKey
 * @property-private api
 * @method getAreas()
 * @method getCities()
 * @method getWarehouses($city = null)
 * @method newInternetDocument($sender, $recipient, $params)
 * @method getCounterparties($counterpartyProperty, $page, $findByString, $cityRef)
 * @method getDocument($ref)
 * @method documentsTracking($ref)
 */
class NovaPoshtaApi extends Base
{

    /**
     * @return string
     */
    protected function getApiKey()
    {
        //TODO get api key from settings
//        $options = get_site_option('woocommerce_nova_poshta_shipping_method_settings');
        $this->apiKey = 'e21262024d80bee8b4b94768a19d88e7';//$options['api_key'];
        return $this->apiKey;
    }

    public function getDocumentLink($ref, $type = 'pdf')
    {
        return sprintf("https://my.novaposhta.ua/orders/printDocument/orders[]/$ref/type/$type/apiKey/{$this->apiKey}");
    }

    /**
     * @param $method
     * @param $args
     * @return mixed
     * @throws Exception
     */
    public function __call($method, $args)
    {
        //max count of args passed wia magic method is 3
        $outputArgs = array_pad($args, 3, null);
        $result = $this->api->$method($outputArgs[0], $outputArgs[1], $outputArgs[2]);
        return $result['data'];
    }

    /**
     * @var NovaPoshtaApi
     */
    private static $_instance;

    public static function instance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct()
    {
        $this->api = new NovaPoshtaApi2($this->apiKey, 'ru', true);
    }

    private function __clone()
    {
    }
}