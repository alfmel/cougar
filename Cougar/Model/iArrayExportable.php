<?php

namespace Cougar\Model;

/**
 * The ArrayExportable interface provides the definition of the __toArray()
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
interface iArrayExportable
{
    /**
     * Defines the __toArray() pseudo-magic method.
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
    public function __toArray();
}
?>
