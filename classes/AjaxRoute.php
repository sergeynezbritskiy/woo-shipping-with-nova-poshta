<?php

namespace plugins\NovaPoshta\classes;

/**
 * Class AjaxRoute
 * @package plugins\NovaPoshta\classes
 */
class AjaxRoute
{
    const GET_CITIES_ROUTE = 'nova_poshta_get_cities_by_area';
    const GET_WAREHOUSES_ROUTE = 'nova_poshta_get_warehouses_by_city';

    const GET_REGIONS_BY_NAME_SUGGESTION = 'get_regions_by_name_suggestion';
    const GET_CITIES_BY_NAME_SUGGESTION = 'get_cities_by_suggestion';
    const GET_WAREHOUSES_BY_NAME_SUGGESTION = 'get_warehouses_by_suggestion';

    public static $handlers = array(
        self::GET_CITIES_ROUTE => array(City::class, 'ajaxGetAreasListByParentAreaRef'),
        self::GET_WAREHOUSES_ROUTE => array(Warehouse::class, 'ajaxGetAreasListByParentAreaRef'),

        self::GET_REGIONS_BY_NAME_SUGGESTION => array(Region::class, 'ajaxGetAreasByNameSuggestion'),
        self::GET_CITIES_BY_NAME_SUGGESTION => array(City::class, 'ajaxGetCitiesByNameSuggestion'),
        self::GET_WAREHOUSES_BY_NAME_SUGGESTION => array(Warehouse::class, 'ajaxGetAreasByNameSuggestion'),
    );

    public static function init()
    {
        foreach (self::$handlers as $key => $handler) {
            $ajaxRoute = new self($key, $handler);
            $ajaxRoute->handleRequest();
        }
    }

    /**
     * @var string
     */
    private $route;

    /**
     * @var array
     */
    private $handler;

    /**
     * AjaxRoute constructor.
     * @param string $route
     * @param string $handler
     */
    public function __construct($route, $handler)
    {
        $this->route = $route;
        $this->handler = $handler;
    }

    /**
     * Handle ajax request
     */
    public function handleRequest()
    {
        add_action('wp_ajax_' . $this->route, $this->handler);
        add_action('wp_nopriv_ajax_' . $this->route, $this->handler);

        if (isset($_REQUEST['action']) && $_REQUEST['action'] == $this->route) {
            do_action('wp_ajax_' . $_REQUEST['action']);
            do_action('wp_ajax_nopriv_' . $_REQUEST['action']);
        }
    }
}