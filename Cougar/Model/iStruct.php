<?php

namespace Cougar\Model;

/**
 * The Struct interface includes the the __get() and __set() magic methods to
 * ensures class properties cannot be added. Thus creating a struct-like data
 * object.
 * 
 * The interface also extends the iArrayExportable to make the struct exportable
 * as an array.
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
interface iStruct extends iArrayExportable
{
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
     * @return mixed Property value
     */
    public function __get($name);
    
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
     * @return bool True if successful, throws exception on error
     */
    public function __set($name, $value);
}
?>
