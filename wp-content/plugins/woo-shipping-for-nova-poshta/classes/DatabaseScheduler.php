<?php

namespace plugins\NovaPoshta\classes;

use plugins\NovaPoshta\classes\base\Base;

/**
 * Class Scheduler
 * @property int $locationsLastUpdateDate
 * @property int interval
 * @package plugins\NovaPoshta\classes
 */
class DatabaseScheduler extends Base
{

    /**
     * Entry point for admin_init hook
     */
    public function ensureSchedule()
    {
        if ($this->requiresUpdate()) {
            DatabaseSync::instance()->synchroniseLocations();
        }
    }

    /**
     * @return bool
     */
    protected function requiresUpdate()
    {
        return ($this->locationsLastUpdateDate + $this->interval) < time();
    }

    /**
     * @return int
     */
    protected function getInterval()
    {
        return DAY_IN_SECONDS;
    }

    /**
     * @return int
     */
    protected function getLocationsLastUpdateDate()
    {
        return NP()->options->locationsLastUpdateDate;
    }

    /**
     * @param int $value
     */
    protected function setLocationsLastUpdateDate($value)
    {
        NP()->options->setLocationsLastUpdateDate($value);
        $this->locationsLastUpdateDate = $value;
    }

}