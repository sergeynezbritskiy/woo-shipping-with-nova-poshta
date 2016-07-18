<?php

namespace plugins\NovaPoshta\classes;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use plugins\NovaPoshta\classes\base\ArrayHelper;
use plugins\NovaPoshta\classes\base\Base;

/**
 * Class Log
 * @package plugins\NovaPoshta\classes
 * @property Logger logger
 * @property Logger[] loggers
 */
class Log extends Base
{
    const MAIN = 'main';
    const LOCATIONS_UPDATE = 'db_update';

    private $loggersTargets = array(
        self::MAIN => array(
            'fileName' => 'main.log',
            'name' => 'General Log',
            'level' => Logger::DEBUG
        ),
        self::LOCATIONS_UPDATE => array(
            'fileName' => 'updates.log',
            'name' => 'Locations Updates',
            'level' => Logger::INFO
        ),
    );

    /**
     * @param string $target
     * @return void
     */
    public function clear($target = self::MAIN)
    {
        $filePath = NOVA_POSHTA_SHIPPING_PLUGIN_DIR . $this->loggersTargets[$target]['fileName'];
        $file = fopen($filePath, 'w');
        fclose($file);
    }

    /**
     * @return void
     */
    public function clearAll()
    {
        foreach ($this->loggersTargets as $target => $data) {
            $this->clear($target);
        }
    }

    /**
     * @param string $message
     * @param string $target
     * @param array $content
     */
    public function info($message, $target = self::MAIN, array $content = array())
    {
        $this->loggers[$target]->info($message, $content);
    }

    /**
     * @param string $message
     * @param string $target
     * @param array $content
     */
    public function debug($message, $target = self::MAIN, array $content = array())
    {
        if(NP()->isDebug()){
            $this->loggers[$target]->debug($message, $content);
        }
    }

    /**
     * @param string $message
     * @param string $target
     */
    public function warning($message, $target = self::MAIN)
    {
        $this->loggers[$target]->warning($message);
    }

    /**
     * @param string $message
     * @param string $target
     * @param array $content
     */
    public function error($message, $target = self::MAIN, array $content = array())
    {
        $this->loggers[$target]->error($message, $content);
    }

    /**-------------------------------------*/
    /*******Singleton pattern elements*******/
    /**-------------------------------------*/

    /**
     * @var self
     */
    private static $_instance;

    /**
     * @return Database
     */
    public static function instance()
    {
        if (static::$_instance == null) {
            static::$_instance = new static();
        }
        return static::$_instance;
    }

    /**
     * @access private
     */
    private function __construct()
    {
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        $this->logger = new Logger('main');
        $this->logger->pushHandler(new StreamHandler('path/to/your.log', Logger::WARNING));
        return $this->logger;
    }

    /**
     * @return Logger[]
     */
    protected function getLoggers()
    {
        $loggers = array();
        foreach ($this->loggersTargets as $key => $target) {
            $level = ArrayHelper::getValue($target, 'level', Logger::INFO);
            $file = NOVA_POSHTA_SHIPPING_PLUGIN_DIR . 'logs/' . $target['fileName'];
            $log = new Logger($key);
            $log->pushHandler(new StreamHandler($file, $level));
            $loggers[$key] = $log;
        }
        $this->loggers = $loggers;
        return $this->loggers;
    }

    /**
     * @access private
     */
    private function __clone()
    {
    }
}