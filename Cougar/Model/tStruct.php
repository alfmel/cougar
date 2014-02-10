<?php

namespace Cougar\Model;

use Cougar\Exceptions\Exception;

# Initialize the framework (disabled; should have been done by application)
#require_once(__DIR__ . "/../../cougar.php");

/**
 * The Struct trait implements the __get() and __set() magic methods to ensure
 * class properties cannot be added. Thus creating a strcut-like data object.
 * 
 * This struct also includes the ArrayExportable trait to provide the array
 * export code.
 *
 * This Struct is very fast since it works directly on the public properties and
 * only prevents new properties from being added. However, it does not restrict
 * properties from being unset. If you wish to have full struct functionality,
 * use the RealStruct trait or abstract class.
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
trait tStruct
{
    use tArrayExportable;
    
    /***************************************************************************
     * MAGIC METHODS
     **************************************************************************/
    
    /**
     * Throws an exception indicating the property does not exist.
     * 
     * If this method is called, it means the requested property does not exist.
     * Since the struct does not allow the creation of new properties, an
     * exception is thrown.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param string $name Property name
     * @throws \Cougar\Exceptions\Exception
     */
    public function __get($name)
    {
        throw new Exception(get_class($this) . " object does not have a " .
            "property named " . $name);
    }
    
    /**
     * Throws an exception indicating the property does not exist.
     * 
     * If this method is called, it means the requested property does not exist.
     * Since the struct does not allow the creation of new properties, an
     * exception is thrown.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param string $name Property name
     * @param mixed $value Property value
     * @throws \Cougar\Exceptions\Exception
     */
    public function __set($name, $value)
    {
        throw new Exception(get_class($this) . " object does not have a " .
            "property named " . $name);
    }
}
?>
