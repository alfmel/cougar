<?php

namespace Cougar\RestService\Models;

use Cougar\Model\Model;
use Cougar\Util\Arrays;

/**
 * Resources are the building blocks of the REST API. A resource will represent
 * application data and it will have certain actions associated with it.
 *
 * @Views list
 */
class Resource extends Model
{
    /**
     * @var string Resource ID
     */
    public $resourceId;

    /**
     * @var string Resource name (must be unique)
     */
    public $name;

    /**
     * @View __default__ hidden
     * @View list hidden
     * @var string Class that describes the resource
     */
    public $class;

    /**
     * @View list hidden
     * @var \Cougar\RestService\Models\Action[] Allowed actions on the resource
     */
    public $actions = array();

    /**
     * Makes sure the action list is cloned.
     */
    public function __clone()
    {
        Arrays::cloneObjects($this->actions);
    }
}
?>
