<?php
namespace Cougar\Util;

use Cougar\Exceptions\Exception;
use Cougar\Model\iModel;

# Initialize the framework (disabled; should have been done by application)
#require_once(__DIR__ . "/../../cougar.php");

/**
 * Provides several static methods for array manipulation.
 *
 * @history
 * 2013.09.30:
 *   (AT)  Initial release
 * 2014.03.06:
 *   (AT)  Add renameKeys() method
 * 2014.08.20:
 *   (AT)  Add renameKeysExtended() method
 *
 * @version 2014.03.06
 * @package Cougar
 * @licence MIT
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class Arrays implements iArrays
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
     * @param string $index Array index to use as the key
     * @return array Re-indexed array
     * @throws \Cougar\Exceptions\Exception
     */
    public static function toAssociative(array $array, $index)
    {
        # Define our new associative array
        $assoc_array = array();
        
        # Go through each element of the array
        $first = true;
        $is_array = true;
        foreach($array as $key => $value)
        {
            if ($first)
            {
                if (is_array($value))
                {
                    $is_array = true;
                    if (! array_key_exists($index, $value))
                    {
                        throw new Exception(
                            "Array value does not have given index key");
                    }
                }
                else if (is_object($value))
                {
                    $is_array = false;
                    if (! property_exists($value, $index))
                    {
                        throw new Exception(
                            "Array value does not have given index property");
                    }
                }
                else
                {
                    throw new Exception(
                        "Array must contain associative arrays or objects");
                }
                
                $first = false;
            }
            
            # Save the value with the new index
            if ($is_array)
            {
                $assoc_array[$value[$index]] = $value;
            }
            else
            {
                $assoc_array[$value->$index] = $value;
            }
        }
        
        # Return the associative array
        return $assoc_array;
    }

    /**
     * Renames the keys in an array of associative arrays. This is useful to
     * convert the keys of a SQL database result set from one set of values to
     * another.
     *
     * The key map must be an associative array where the key is the old name
     * and the value is the new name. You must provide a complete map of old
     * keys to new keys, even if the new key will rename the same. This saves
     * calls to array_key_exists() and speeds up the operation considerably.
     *
     * @history
     * 2014.03.06
     *   (AT)  Initial release
     *
     * @version 2014.03.06
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param array $data Array on which to rename the keys
     * @param array $key_map Assoc. array mapping old key names to the new ones
     * @return array Re-keyed array
     */
    public static function renameKeys(array $data, array $key_map)
    {
        // Go through each element in the array
        foreach($data as &$row)
        {
            // Create a new array to hold the new values
            $new_row = array();

            // Go through each key/value pair
            foreach($row as $key => $value)
            {
                $new_row[$key_map[$key]] = $value;
            }

            // Replace the row with the new row
            $row = $new_row;
        }

        // Return the data
        return $data;
    }

    /**
     * Renames the keys in an array of associative arrays with extended
     * functionality. This is much like the renameKeys() method except that
     * it is recursive and the key map does not have to be complete.
     *
     * For example, if you wish to keep a key name the same, you do not need to
     * specify it in the map. Additionally, if you wish to remove a specific
     * array member, you may specify this by leaving the key's value empty in
     * the key map.
     *
     * @history
     * 2014.08.20
     *   (AT)  Initial release
     *
     * @version 2014.08.20
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param array $data Array on which to rename the keys
     * @param array $key_map Assoc. array mapping old key names to the new ones
     * @return array Re-keyed array
     */
    public static function renameKeysExtended(array $data, array $key_map)
    {
        // Create the new array
        $array = array();

        // Go through each element in the array
        foreach($data as $key => &$value)
        {
            // See if the key is in the key map
            if (array_key_exists($key, $key_map))
            {
                $key = $key_map[$key];

                // See if the key will be skipped
                if (! $key)
                {
                    continue;
                }
            }

            // See if the value is another array
            if (is_array($value))
            {
                // Save the value with its new key name and its renamed keys
                $array[$key] = Arrays::renameKeysExtended($value, $key_map);
            }
            else
            {
                // Save the value with the new key
                $array[$key] = $value;
            }
        }

        // Return the new array
        return $array;
    }

    /**
     * Sorts a 2-dimensional array with a record set or an array of objects by
     * the specified indexes in the second array or properties in the object,
     * in ascending order.
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
    public static function dataSort($array, $index1)
    {
        # Get the indexes we will sort by
        $index_list = array_slice(func_get_args(), 1);
        
        # Go through the array and extract the values for each index in the list
        $sort_values = array();
        $first = true;
        $is_array = true;
        foreach($array as $key => $value)
        {
            # See if we have an object or array
            foreach($index_list as $i => $index)
            {
                if ($first)
                {
                    if (is_array($value))
                    {
                        $is_array = true;
                        if (! array_key_exists($index, $value))
                        {
                            throw new Exception(
                                "Array value does not have given index key");
                        }
                    }
                    else if (is_object($value))
                    {
                        $is_array = false;
                        if (! property_exists($value, $index))
                        {
                            throw new Exception("Array value does not have " .
                                "given index property");
                        }
                    }
                    else
                    {
                        throw new Exception(
                            "Array must contain associative arrays or objects");
                    }
                }
                
                $first = false;
                
                if ($is_array)
                {
                    $sort_values[$i][$key] = $value[$index];
                }
                else
                {
                    $sort_values[$i][$key] = $value->$index;
                }
            }
        }
        
        # Come up with the argument list
        $arguments = array();
        
        foreach($sort_values as $sort_value)
        {
            $arguments[] = $sort_value;
            $arguments[] = SORT_NATURAL;
        }
        
        # Add the array to sort at the end of the arguments
        $arguments[] = &$array;
        
        # Call array_multisort with the arguments
        call_user_func_array("array_multisort", $arguments);
        
        # Return the sorted array
        return $array;
    }

    /**
     * Filters records from a 2-dimensional record set array by the specified
     * index in the second array or object property and the given values. For
     * example, to filter a list of addresses that are either from Canada,
     * United States or Mexico you would call:
     *
     *   Arrays::dataFilter($address_list, "country", array("CA", "US", "MX"));
     *
     * You may optionally pass false as the fourth argument to negate the filter;
     * that is, all address except those in CA, US, or MX.
     *
     * @history:
     * 2014.04.24:
     *   (AT)  Initial implementation
     *
     * @version 2014.04.24
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param array $array Array with data to filter
     * @param string $index Index or property name to filter by
     * @param mixed $value String or array of values to filter by
     * @param bool $equal Set to true to return records that don't match values
     * @throws \Cougar\Exceptions\Exception
     * @return array Filtered array
     */
    public static function dataFilter(array $array, $index, $value,
        $equal = true)
    {
        # Prepare the array that will store the results
        $filtered_array = array();

        # See if the value is a single value
        if (! is_array($value))
        {
            # Turn the value into an array
            $value = array($value);
        }

        $first = true;
        foreach($array as $key => $record)
        {
            # See if we have an object or array and make sure the index exists
            if ($first)
            {
                if (is_array($record))
                {
                    $is_array = true;
                    if (! array_key_exists($index, $record))
                    {
                        throw new Exception(
                            "Array value does not have given index key");
                    }
                }
                else if (is_object($record))
                {
                    $is_array = false;
                    if (! property_exists($record, $index))
                    {
                        throw new Exception("Array value does not have " .
                            "given index property");
                    }
                }
                else
                {
                    throw new Exception(
                        "Array must contain associative arrays or objects");
                }
            }

            $first = false;

            // Get the value in the record
            if ($is_array)
            {
                $record_value = $record[$index];
            }
            else
            {
                $record_value = $record->$index;
            }

            // See if the value matches any of the given values
            if (in_array($record_value, $value))
            {
                // If we are returning records that are equal, add this record
                if ($equal)                {
                    $filtered_array[$key] = $record;
                }
            }
            else
            {
                // If we are not returning records that are equal, add this
                // record
                if (! $equal)
                {
                    $filtered_array[$key] = $record;
                }
            }
        }

        # Return the filtered results
        return $filtered_array;
    }

    /**
     * Sets the view on all elements in the array. The elements in an array must
     * implement the Cougar\Model\iModel interface.
     *
     * The array is passed as a reference, and changes will be made directly to
     * the array.
     *
     * @history:
     * 2014.05.06:
     *   (AT)  Initial implementation
     *
     * @version 2014.05.06
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param \Cougar\Model\iModel[] $array Array of model objects
     * @param string $view New view to set on the objects
     * @throws \Cougar\Exceptions\Exception
     */
    public static function setModelView(array &$array, $view = "__default__")
    {
        # Go through the elements in the array
        foreach($array as $model)
        {
            # Make sure the object is an instance of iModel
            if (! $model instanceof iModel)
            {
                throw new Exception("Array element must be instance of " .
                    "Cougar\\Model\\iModel");
            }

            # Set the view
            $model->__setView($view);
        }
    }

    /**
     * Clones all objects in the array. Because PHP always passes all objects
     * as references and calling clone on array is not possible, this method
     * will iterate through the array and clone its objects.
     *
     * Note that this will only perform a shallow copy. That is, object
     * references within the object will not be cloned. If you need a deep copy
     * where all objects references are cloned, make sure the objects in your
     * array clone those objects in the __clone() method.
     *
     * Note that the array is passed by reference. If you wish to create a new
     * array with the cloned objects use cloneObjectArray().
     *
     * @history:
     * 2014.05.06:
     *   (AT)  Initial implementation
     * 2014.05.07:
     *   (AT)  Don't return the array; only perform in given array
     *
     * @version 2014.05.07
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param array $array Array of objects to be cloned
     */
    public static function cloneObjects(array &$array)
    {
        # Go through each element of the array
        foreach($array as &$object)
        {
            # See if we have an object
            if (is_object($object))
            {
                # Clone the object
                $object = clone $object;
            }
        }
    }

    /**
     * Clones the given array and all objects in it. Because PHP always passes
     * all objects as references and calling clone on array is not possible,
     * this method iterate through the array and return a new array with clones
     * of its elements.
     *
     * Note that this will only perform a shallow copy. That is, object
     * references within the object will not be cloned. If you need a deep copy
     * where all objects references are cloned, make sure the objects in your
     * array clone those objects in the __clone() method.
     *
     * This implementation will create a new array. If you wish to clone the
     * objects in place, use cloneObjects().
     *
     * @history:
     * 2014.05.07:
     *   (AT)  Initial implementation
     *
     * @version 2014.05.07
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param array $array Array of objects to be cloned
     * @return array New array of cloned objects
     */
    public static function cloneObjectArray(array $array)
    {
        # Define the target array
        $cloned_array = array();

        # Go through each element of the array
        foreach($array as $key => $object)
        {
            # See if we have an object
            if (is_object($object))
            {
                # Clone the object
                $cloned_array[$key] = clone $object;
            }
            else
            {
                # Just pass the object as is
                $cloned_array[$key] = $object;
            }
        }

        return $cloned_array;
    }
}
?>
