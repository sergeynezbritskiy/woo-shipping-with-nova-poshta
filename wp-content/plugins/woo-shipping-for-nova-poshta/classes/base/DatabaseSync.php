<?php

namespace plugins\NovaPoshta\classes\base;

use plugins\NovaPoshta\classes\Log;
use wpdb;

/**
 * Class DatabaseSync
 * @package plugins\NovaPoshta\classes\base
 * @property wpdb db
 * @property int updatedAt
 * @property Log log
 * @property array areas
 */
abstract class DatabaseSync extends Base
{

    /**
     * @return array
     */
    protected function getAreas()
    {
        /** @noinspection PhpIncludeInspection */
        return require NOVA_POSHTA_SHIPPING_PLUGIN_DIR . 'vendor/sergeynezbritskiy/nova-poshta-api-2/src/Delivery/NovaPoshtaApi2Areas.php';
    }

    /**
     * @return Log
     */
    protected function getLog()
    {
        return NP()->log;
    }

    /**
     * @return wpdb
     */
    protected function getDb()
    {
        return NP()->db;
    }

    /**
     * @return int
     */
    protected function getUpdatedAt()
    {
        return time();
    }

}