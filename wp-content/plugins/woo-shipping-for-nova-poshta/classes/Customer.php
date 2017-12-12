<?php

namespace plugins\NovaPoshta\classes;

use plugins\NovaPoshta\classes\base\ArrayHelper;
use plugins\NovaPoshta\classes\base\Base;
use WC_Meta_Data;

/**
 * Class Customer
 *
 * @property \WC_Customer wooCustomer
 * @package plugins\NovaPoshta\classes
 */
class Customer extends Base
{

    /**
     * @var Customer
     */
    private static $_instance;

    /**
     * @return Customer
     */
    public static function instance()
    {
        if (static::$_instance == null) {
            static::$_instance = new static();
        }
        return static::$_instance;
    }

    /**
     * @param string $key
     * @param string $location
     * @return mixed
     */
    public function getMetadata($key, $location = '')
    {
        if (method_exists($this->wooCustomer, 'get_meta_data')) {
            $data = $this->wooCustomer->get_meta_data();
            /** @var WC_Meta_Data $item */
            foreach ($data as $item) {
                $itemData = $item->get_data();
                if ($itemData['key'] === $key) {
                    return $itemData['value'];
                }
                if ($location !== '' && $itemData['key'] === $location . '_' . $key) {
                    return $itemData['value'];
                }
            }
            return '';
        } else {
            return $this->wooCustomer->$key;
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param string $location
     * @return void
     */
    public function setMetadata($key, $value, $location = '')
    {
        if (method_exists($this->wooCustomer, 'add_meta_data')) {
            $this->wooCustomer->add_meta_data($key, $value);
            if($location !== ''){
                $this->wooCustomer->add_meta_data($location . '_' . $key, $value);
            }
        } else {
            $this->wooCustomer->$key = $value;
        }
    }

    /**
     * @return \WC_Customer
     */
    protected function getWooCustomer()
    {
        return WC()->customer;
    }

}