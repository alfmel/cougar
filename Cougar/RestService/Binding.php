<?php

namespace Cougar\RestService;

use Cougar\Model\Struct;

# Initialize the framework (disabled; should have been done by application)
#require_once(__DIR__ . "/../../cougar.php");

class Binding extends Struct
{
    public $object = null;
    public $method = null;
    public $http_methods = array();
    public $accepts = null;
    public $returns = null;
    public $xmlRootElement = null;
    public $xmlObjectName = null;
    public $xmlObjectList = false;
    public $xsd = null;
    public $xsl = null;
    public $parameters = array();
    public $authentication = "none";
}
?>
