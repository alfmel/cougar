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
     * Sorts a 2-dimensional record set array by the specified indexes in the
     * second array or object property.
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

    /**
     * Filters records from a 2-dimensional record set array by the specified
     * index in the second array or object property and the given values. For
     * example, to filter a list of addresses that are either from Canada,
     * United States or Mexico you would call:
     *
     *   Arrays::dataFilter($address_list, "country", array("CA", "US", "MX"));
     *
     * You may optionally pass false in the fourth argument to negate the filter;
     * that is, all address except those in CA, US, or MX.
     *
     * @history:
     * 2014.04.24:
     *   (AT)  Initial definition
     *
     * @version 2014.04.24
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param array $array Array with data to filter
     * @param string $index Index or property name to filter by
     * @param mixed $value String or array of values to filter by
     * @param bool $equal Set to false to return records that don't match values
     * @return array Filtered array
     */
    public static function dataFilter(array $array, $index, $value,
        $equal = true);
}
?>
