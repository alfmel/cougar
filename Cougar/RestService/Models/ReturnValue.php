<?php

namespace Cougar\RestService\Models;

use Cougar\Model\Struct;

/**
 * Basic description of property or attribute.
 */
class ReturnValue extends Struct
{
    /**
     * @var string Type
     */
    public $type;

    /**
     * @var bool Whether the value contains a list
     */
    public $list = false;

    /**
     * @var string Description
     */
    public $description;
}
?>
