<?php

namespace Cougar\RestService\Models;

use Cougar\Model\Struct;

/**
 * Describes an individual value or attribute of a resource.
 */
class Value extends Struct
{
    /**
     * @var string Value name
     */
    public $name;

    /**
     * @var string Value type
     */
    public $type;

    /**
     * @var bool Whether the value contains a list
     */
    public $list = false;

    /**
     * @var bool Whether the value is a resource
     */
    public $isResource = false;

    /**
     * @var bool Whether the value is optional
     */
    public $optional = false;

    /**
     * @var string Description
     */
    public $description;
}
?>
