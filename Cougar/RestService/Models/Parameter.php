<?php

namespace Cougar\RestService\Models;

use Cougar\Model\Struct;

/**
 * Information about an put parameter or attribute
 *
 * @Views returnValue
 */
class Parameter extends Struct
{
    /**
     * @var string Parameter name
     */
    public $name;

    /**
     * @var string Location (URI, GET, POST or Body)
     */
    public $location;

    /**
     * @var string Type
     */
    public $type;

    /**
     * @var bool Whether the parameter is a list
     */
    public $list = false;

    /**
     * @var string Regex constraint
     */
    public $constraint;

    /**
     * @var string Description
     */
    public $description;
}
?>
