<?php
namespace plugins\NovaPoshta\classes\base;

/**
 * Class Options
 * @package plugins\NovaPoshta\classes\base
 *
 * @property int locationsLastUpdateDate
 * @property string regionsHash
 * @property string citiesHash
 * @property string warehousesHash
 *
 */
class Options extends Base
{
    /**
     * @return int
     */
    public function getLocationsLastUpdateDate()
    {
        $this->locationsLastUpdateDate = $this->getOption('locations_last_update_date') ?: 0;
        return $this->locationsLastUpdateDate;
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
    public function getRegionsHash()
    {
        $this->regionsHash = $this->getOption('regions_hash') ?: '';
        return $this->regionsHash;
    }

    /**
     * @param string $regionsHash
     */
    public function setRegionsHash($regionsHash)
    {
        $this->setOption('regions_hash', $regionsHash);
        $this->regionsHash = $regionsHash;
    }

    /**
     * @return string
     */
    public function getCitiesHash()
    {
        $this->citiesHash = $this->getOption('cities_hash') ?: '';
        return $this->citiesHash;
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
        $this->warehousesHash = $this->getOption('warehouses_hash') ?: '';
        return $this->warehousesHash;
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
     * Delete all plugin specific options from wp_options table
     */
    public function clearOptions()
    {
        $query = "DELETE FROM wp_options WHERE option_name LIKE CONCAT ('_nova_poshta_', '%')";
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