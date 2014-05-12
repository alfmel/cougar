<?php

namespace Cougar\RestService\Models;

use Cougar\Model\Model;
use Cougar\Util\Arrays;

/**
 * An action that can be performed on a specific resource.
 *
 * @Views list
 */
class Action extends Model
{
    /**
     * @var string Action ID
     */
    public $actionId;

    /**
     * @var string Action name
     */
    public $name;

    /**
     * @var string Short description
     */
    public $shortDescription;

    /**
     * @View list hidden
     * @var string Full description
     */
    public $description;

    /**
     * @var array HTTP methods
     */
    public $httpMethods = array();

    /**
     * @var array Resource URI(s) (path)
     */
    public $paths = array();

    /**
     * @View list hidden
     * @var \Cougar\RestService\Models\Parameter[] Action (method) parameters
     */
    public $parameters = array();

    /**
     * @var \Cougar\RestService\Models\ReturnValue Return value information
     */
    public $returnValue;

    /**
     * @var string Authentication requirement (none, optional, required)
     */
    public $authentication = "none";

    /**
     * Makes sure the parameter list and return value are cloned.
     */
    public function __clone()
    {
        Arrays::cloneObjects($this->parameters);
        $this->returnValue = clone $this->returnValue;
    }
}
?>
