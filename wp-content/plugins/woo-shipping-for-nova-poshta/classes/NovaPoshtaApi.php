<?php

namespace plugins\NovaPoshta\classes;

use Exception;
use LisDev\Delivery\NovaPoshtaApi2;
use plugins\NovaPoshta\classes\base\Base;

/**
 * Class Api
 * @package plugins\NovaPoshta\classes
 * @property string apiKey
 * @property NovaPoshtaApi2 api
 * @method getAreas()
 * @method getCities()
 * @method getWarehouses($city = null)
 * @method newInternetDocument($sender, $recipient, $params)
 * @method getCounterparties($counterpartyProperty, $page, $findByString, $cityRef)
 * @method getDocument($ref)
 * @method documentsTracking($ref)
 * @method getDocumentPrice($citySender, $cityRecipient, $serviceType, $weight, $cost)
 */
class NovaPoshtaApi extends Base
{

    /**
     * @return string
     */
    protected function getApiKey()
    {
        $this->apiKey = NP()->options->apiKey;
        return $this->apiKey;
    }

    /**
     * @param string $ref
     * @param string $type
     * @return string
     */
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
        $outputArgs = array_pad($args, 5, null);
        $result = $this->api->$method($outputArgs[0], $outputArgs[1], $outputArgs[2], $outputArgs[3], $outputArgs[4]);
        return $result['data'];
    }

    /**
     * @var NovaPoshtaApi
     */
    private static $_instance;

    /**
     * @return NovaPoshtaApi
     */
    public static function instance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * NovaPoshtaApi constructor.
     *
     * @access private
     */
    private function __construct()
    {
        $this->api = new NovaPoshtaApi2($this->apiKey, 'ru', true);
    }

    /**
     * @access private
     */
    private function __clone()
    {
    }
}