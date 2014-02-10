<?php

namespace Cougar\Model;

# Initialize the framework (disabled; should have been done by application)
#require_once(__DIR__ . "/../../cougar.php");

/**
 * The ArrayExportable trait provides an implementation of the __toArray()
 * method. This method allows an object to have its public properties exported
 * as an array. Protected and private properties are ignored.
 *
 * @history
 * 2013.09.30:
 *   (AT)  Initial release
 *
 * @version 2013.09.30
 * @package Cougar
 * @license MIT
 *
 * @copyright 2013 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
trait tArrayExportable
{
    /**
     * Implements the __toArray() pseudo-magic method by reflecting the object
     * to extract the list of public properties and return their values as an
     * associative array.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @return array Associative array with public properties and their values
     */
    public function __toArray()
    {
        $array = array();
        $reflection = new \ReflectionObject($this);
        foreach($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as
            $property)
        {
            $array[$property->name] = $this->{$property->name};
        }
        
        return $array;
    }
}
?>
