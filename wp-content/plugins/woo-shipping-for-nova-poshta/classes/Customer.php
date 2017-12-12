<?php

namespace plugins\NovaPoshta\classes;

use plugins\NovaPoshta\classes\base\ArrayHelper;
use plugins\NovaPoshta\classes\base\Base;

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
     * @return mixed
     */
    public function getMetadata($key)
    {
        if (method_exists($this->wooCustomer, 'get_meta_data')) {
            return ArrayHelper::getValue($this->wooCustomer->get_meta_data(), $key);
        } else {
            return $this->wooCustomer->$key;
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setMetadata($key, $value)
    {
        if (method_exists($this->wooCustomer, 'add_meta_data')) {
            $this->wooCustomer->add_meta_data($key, $value);
        } else {
            $this->wooCustomer->$key = $value;
        }
    }

    /**
     * @deprecated
     * @param $string
     * @param $getValue
     */
    public function add_meta_data($string, $getValue)
    {
        $this->setMetadata($string, $getValue);
    }

    /**
     * @return \WC_Customer
     */
    protected function getWooCustomer()
    {
        return WC()->customer;
    }

}