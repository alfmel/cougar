<?php

namespace Cougar\Util;

use Cougar\Model\Struct;

# Initialize the framework (disabled; should have been done by application)
#require_once(__DIR__ . "/../../cougar.php");

/**
 * The ClassAnnotation strcut stores the annotations for a class and its
 * public properties and methods.
 *
 * @history
 * 2013.09.30:
 *   (AT)  Initial release
 * 2014.02.20:
 *   (AT)  Add properties to store descriptions
 *
 * @version 2014.02.20
 * @package Cougar
 * @licence MIT
 *
 * @copyright 2013-2014 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class ClassAnnotations extends Struct
{
    /**
     * @var string classDescription
     */
    public $classDescription;

    /**
     * @var array List of annotations for the class
     */
    public $class = array();

    /**
     * @var array Associative array of property descriptions
     */
    public $propertyDescriptions = array();

    /**
     * @var array Associative array of properties and their annotations
     */
    public $properties = array();

    /**
     * @var array Associative array of method descriptions
     */
    public $methodDescriptions = array();

    /**
     * @var array Associative array of properties and their annotations
     */
    public $methods = array();
    
    /**
     * @var bool Whether the annotations were retrieved from the cache
     */
    public $cached = false;
}
?>
