<?php

namespace Cougar\RestService;

use Cougar\Model\Struct;

# Initialize the framework (disabled; should have been done by application)
#require_once(__DIR__ . "/../../cougar.php");

class Parameter extends Struct
{
    public $source = null;
    public $index = null;
    public $type = null;
    public $array = false;
}
?>
