<?php

namespace plugins\NovaPoshta\classes\base;

/**
 * Class Options
 * @package plugins\NovaPoshta\classes\base
 *
 * @property int locationsLastUpdateDate
 * @property string areasHash
 * @property string citiesHash
 * @property string warehousesHash
 * @property array shippingMethodSettings
 * @property string senderArea
 * @property string senderCity
 * @property string senderWarehouse
 * @property string apiKey
 * @property bool useFixedPriceOnDelivery
 * @property float fixedPrice
 * @property bool pluginRated
 *
 */
class Options extends Base
{
    const AREA_NAME = 'area_name';
    const AREA = 'area';
    const CITY_NAME = 'city_name';
    const CITY = 'city';
    const WAREHOUSE_NAME = 'warehouse_name';
    const WAREHOUSE = 'warehouse';
    const API_KEY = 'api_key';
    const DEBUG = 'debug';
    const USE_FIXED_PRICE_ON_DELIVERY = 'use_fixed_price_on_delivery';
    const FIXED_PRICE = 'fixed_price';
    const OPTION_CASH_ON_DELIVERY = 'on_delivery';
    const OPTION_FIXED_PRICE = 'fixed_price';
    const OPTION_PLUGIN_RATED = 'plugin_rated';

    /**
     * @return void
     */
    public function ajaxPluginRate()
    {
        NP()->log->info('Plugin marked as rated');
        $this->setOption(self::OPTION_PLUGIN_RATED, 1);
        $result = array(
            'result' => true,
            'message' => __('Thank you :)', NOVA_POSHTA_DOMAIN)
        );
        echo json_encode($result);
        exit;
    }

    /**
     * @return bool
     */
    protected function getUseFixedPriceOnDelivery()
    {
        return filter_var($this->shippingMethodSettings[self::USE_FIXED_PRICE_ON_DELIVERY], FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return float
     */
    protected function getFixedPrice()
    {
        return $this->useFixedPriceOnDelivery ? (float)$this->shippingMethodSettings[self::FIXED_PRICE] : null;
    }

    /**
     * @return int
     */
    public function getLocationsLastUpdateDate()
    {
        return $this->getOption('locations_last_update_date') ?: 0;
    }

    /**
     * @param int $value
     */
    public function setLocationsLastUpdateDate($value)
    {
        $this->setOption('locations_last_update_date', $value);
        $this->locationsLastUpdateDate = $value;
    }

    /**
     * @return string
     */
    public function getAreasHash()
    {
        return $this->getOption('areas_hash') ?: '';
    }

    /**
     * @param string $value
     */
    public function setAreasHash($value)
    {
        $this->setOption('areas_hash', $value);
        $this->areasHash = $value;
    }

    /**
     * @return string
     */
    public function getCitiesHash()
    {
        return $this->getOption('cities_hash') ?: '';
    }

    /**
     * @param string $citiesHash
     */
    public function setCitiesHash($citiesHash)
    {
        $this->setOption('cities_hash', $citiesHash);
        $this->citiesHash = $citiesHash;
    }

    /**
     * @return string
     */
    public function getWarehousesHash()
    {
        return $this->getOption('warehouses_hash') ?: '';
    }

    /**
     * @param string $warehousesHash
     */
    public function setWarehousesHash($warehousesHash)
    {
        $this->setOption('warehouses_hash', $warehousesHash);
        $this->warehousesHash = $warehousesHash;
    }

    /**
     * @return array
     */
    protected function getShippingMethodSettings()
    {
        return get_site_option('woocommerce_nova_poshta_shipping_method_settings');
    }

    /**
     * @return string
     */
    protected function getSenderArea()
    {
        return $this->shippingMethodSettings[self::AREA];
    }

    /**
     * @return string
     */
    protected function getSenderCity()
    {
        return $this->shippingMethodSettings[self::CITY];
    }

    /**
     * @return string
     */
    protected function getSenderWarehouse()
    {
        return $this->shippingMethodSettings[self::WAREHOUSE];
    }

    protected function getPluginRated()
    {
        return filter_var($this->getOption(self::OPTION_PLUGIN_RATED), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return string
     */
    protected function getApiKey()
    {
        return $this->shippingMethodSettings[self::API_KEY];
    }

    /**
     * @return bool
     */
    public function isDebug()
    {
        return filter_var(ArrayHelper::getValue($this->shippingMethodSettings, self::DEBUG), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Delete all plugin specific options from options table
     * @return void
     */
    public function clearOptions()
    {
        $table = NP()->db->options;
        $query = "DELETE FROM `$table` WHERE option_name LIKE CONCAT ('_nova_poshta_', '%')";
        NP()->db->query($query);
    }

    /**
     * @param $optionName
     * @return mixed
     */
    private function getOption($optionName)
    {
        $key = "_nova_poshta_" . $optionName;
        return get_option($key);
    }

    /**
     * @param string $optionName
     * @param mixed $optionValue
     */
    private function setOption($optionName, $optionValue)
    {
        $key = "_nova_poshta_" . $optionName;
        update_option($key, $optionValue);
    }

    /**
     * @var Options
     */
    private static $_instance;

    /**
     * @return Options
     */
    public static function instance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Options constructor.
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