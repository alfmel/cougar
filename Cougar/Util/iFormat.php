<?php

namespace Cougar\Util;

# Initialize the framework
require_once("cougar.php");

/**
 * Provides several static methods for formatting (and in some cases fixing)
 * data.
 *
 * Please note ALL static methods work on REFERENCES and DO NOT return any
 * data.
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
interface iFormat
{
    /**
     * Trims whitespace from the elements in the given object. This may seem
     * redundant when PHP already has a trim function, except that this function
     * can work on strings in an array and object, and also works by reference.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param mixed $object Object, array or string in which to operate
     */
    public static function trim(&$object);
    
    /**
     * Converts the "null" string into a null value.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param mixed $object Object, array or string in which to operate
     */
    public static function null(&$object);
    
    /**
     * Converts string representations of a boolean (true, false, T, F, etc.) to
     * an actual boolean value. If force is set to true, it will force the
     * conversion of the value. If it doesn't have any known variations, the
     * object will be set to false.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param mixed $object Object, array or string in which to operate
     * @param bool $force Whether to force the conversion to boolean
     */
    public static function strToBool(&$object, $force = false);
    
    /**
     * Converts boolean values to a string (true or false)
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param mixed $object Object, array or string in which to operate
     */
    public static function boolToStr(&$object);
    
    /**
     * Resolves gender words (M, F, Male, Female) into a single character
     * representation (M, F). If the force flag is set, values that do not match
     * will be returned as null (unspecified gender)
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param mixed $object Object, array or string in which to operate
     * @param bool $force Whether to return a null if the object is not gender
     */
    public static function gender(&$object, $force = false);

    /**
     * Resolves gender words (M, F, Male, Female) into a word representation
     * (Male, Female). If the force flag is set, values that do not match
     * will be returned as null (unspecified gender)
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param mixed object Object, array or string in which to operate
     * @param bool force Whether to return a null if the object is not gender
     */
    public static function genderWord(&$object, $force = false);
    
    /**
     * Removes the word null or NULL that may occur at the end of a string
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param mixed object Object, array or string in which to operate
     */
    public static function removeNullWord(&$object);

}
?>
