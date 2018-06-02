<?php

namespace plugins\NovaPoshta\classes\repository;

use plugins\NovaPoshta\classes\Area;
use plugins\NovaPoshta\classes\base\ArrayHelper;
use plugins\NovaPoshta\classes\base\Base;
use plugins\NovaPoshta\classes\base\OptionsHelper;

/**
 * Class AreaRepository
 * @property string areaClass
 * @package plugins\NovaPoshta\classes
 */
abstract class AbstractAreaRepository extends Base
{

    /**
     * @return string
     */
    abstract public function table();

    /**
     * @return string
     */
    abstract protected function getAreaClass();

    /**
     * Ajax requests entry point
     */
    public function ajaxGetAreasByNameSuggestion()
    {
        $areaRef = ArrayHelper::getValue($_POST, 'parent_ref', null);
        $name = ArrayHelper::getValue($_POST, 'name', null);

        $areas = $this->findByParentRefAndNameSuggestion($areaRef, $name);
        $result = OptionsHelper::getList($areas, false);
        natsort($result);
        echo json_encode($result);
        exit;
    }

    /**
     * @return Area[]
     */
    public function findAll()
    {
        return $this->findByParentRefAndNameSuggestion(null, null);
    }

    /**
     * @param string|null $parentRef
     * @param string|null $name
     * @return Area[]
     */
    public function findByParentRefAndNameSuggestion($parentRef = null, $name = null)
    {
        $searchCriteria = [];
        $searchCriteria[] = '(1=1)';
        if ($parentRef !== null) {
            $searchCriteria[] = $this->getParentRefSearchCriteria($parentRef);
        }
        if ($name !== null) {
            $searchCriteria[] = $this->getNameSearchCriteria($name);
        }
        $table = $this->table();
        $query = "SELECT * FROM $table WHERE " . implode(' AND ', $searchCriteria);
        return $this->findByQuery($query);
    }

    /**
     * @param string $query
     * @return Area[]
     */
    public function findByQuery($query)
    {
        $class = $this->areaClass;
        $result = NP()->db->get_results($query);
        return array_map(function ($location) use ($class) {
            return new $class($location);
        }, $result);
    }

    /**
     * @param string $parentRef
     * @return string
     */
    protected function getParentRefSearchCriteria($parentRef)
    {
        NP()->db->escape_by_ref($parentRef);
        return sprintf("(`parent_ref` = '%s')", $parentRef);
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getNameSearchCriteria($name)
    {
        NP()->db->escape_by_ref($name);
        return sprintf("(`description` LIKE CONCAT('%s', '%%') OR `description_ru` LIKE CONCAT('%s', '%%'))", $name, $name);
    }

}