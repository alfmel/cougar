<?php

namespace Cougar\RestService\Models;

use Cougar\Model\Model;
use Cougar\Util\Arrays;

/**
 * Resources are the building blocks of the REST API. A resource will represent
 * application data and it will have certain actions associated with it.
 *
 * @Views list list_with_actions
 */
class Resource extends Model
{
    /**
     * @var string Resource ID (based on the name, must be unique)
     */
    public $resourceId;

    /**
     * @var string Resource name
     */
    public $name;

    /**
     * @var string Short description for the user
     */
    public $shortDescription;

    /**
     * @View __default__ hidden
     * @View list hidden
     * @View list_with_actions hidden
     * @var string Internal class name for the resource
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
