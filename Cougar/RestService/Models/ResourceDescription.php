<?php

namespace Cougar\RestService\Models;

use Cougar\Model\Struct;

/**
 * Describes the attributes (object properties) of a resource.
 */
class ResourceDescription extends Struct
{
    /**
     * @var string Resource ID
     */
    public $resourceId;

    /**
     * @var string Name
     */
    public $name;

    /**
     * @var string Description
     */
    public $description;

    /**
     * @var \Cougar\RestService\Models\Value[] Individual values
     */
    public $values = array();
}
?>
