<?php

namespace Cougar\RestService;

use Cougar\Model\Struct;

# Initialize the framework
require_once("cougar.php");

class Parameter extends Struct
{
	public $source = null;
	public $index = null;
	public $type = null;
	public $array = false;
}
?>
