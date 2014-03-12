<?php
namespace Cougar\Util;

use Cougar\Exceptions\Exception;

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
}
?>
