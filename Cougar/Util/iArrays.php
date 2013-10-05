<?php

namespace Cougar\util;

/**
 * Provides several array manipulation routines
 *
 * @history
 * 2013.09.30:
 *   (AT)  Initial release
 *
 * @version 2013.09.30
 * @package Cougar
 * @licence MIT
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
interface iArrays
{
    /**
     * Re-indexes a multi-dimensional array by setting the key of each element
     * to an array value in the element. This allows you to create a hashed list
     * of records from a non-associative list.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param array $array Array to reindex
     * @param string $index Index name
     * @return array Re-indexed array
     */
    public static function toAssociative(array $array, $index);
    
    /**
     * Sorts a 2-dimensional array that represents a record set by the specified
     * indexes in the second array or object property.
     * 
     * This method is a simpler interface to array_multisort() function. See
     * Example 3 in the PHP documentation for implementation details.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param array $array Array to sort
     * @param string $index1 First index to sort by
     * @param string $index2 Second index to sort by ...
     * @param string $indexN ... Continue specifying indexes to sort by
     * @return array Sorted array
     */
    public static function dataSort($array, $index1);
}
?>
