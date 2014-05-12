<?php

namespace Cougar\RestService\Models;

use Cougar\Model\Model;
use Cougar\Util\Arrays;

/**
 * Components are the major sub-components of an application. The most important
 * part about them is the resources they contain.
 *
 * @Views list
 */
class Component extends Model
{
    /**
     * @var string Component ID (descriptive string)
     */
    public $componentId;

    /**
     * @var string Human-readable Component name
     */
    public $name;

    /**
     * @var string component description
     */
    public $description;

    /**
     * @View list hidden
     * @var \Cougar\RestService\Models\Resource[] List of resources
     */
    public $resources = array();

    /**
     * Makes sure the resource list is cloned.
     */
    public function __clone()
    {
        Arrays::cloneObjects($this->resources);
    }
}
?>
