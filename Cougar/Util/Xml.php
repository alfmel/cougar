<?php

namespace Cougar\Util;

use SimpleXMLElement;
use Cougar\Exceptions\NotImplementedException;

# Initialize the framework (disabled; should have been done by application)
#require_once(__DIR__ . "/../../cougar.php");

/**
 * Converts data to and from XML
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
class Xml implements iXml
{
    /***************************************************************************
     * PUBLIC PROPERTIES AND METHODS
     **************************************************************************/
    
    /**
     * Converts the given data to XML. Data can be any type. You may optionally
     * specify the root element name, child element name for non-associative
     * arrays. If arrays are associative, the name of the key will be used
     * instead. If there are objects, the object name will be used instead.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param mixed $data
     *   The data to convert
     * @param string $root_element
     *   Optional name of the root element (default: response)
     * @param string $child_element
     *   Optional name of the child elements (default: object)
     * @param bool $child_as_list
     *   Whether the child should be treated as a list of other bojects
     * @return SimpleXMLElement XML representation of the data
     */
    public static function toXml($data, $root_element = null,
        $child_element = null, $child_as_list = false)
    {
        # See if we need to assign default values to the root and child elements
        if (! $root_element || is_numeric($root_element))
        {
            $root_element = "response";
        }
        
        if (! $child_element || is_numeric($child_element))
        {
            $child_element = "object";
        }
        
        # See if the object is a date/time object
        if (is_object($data))
        {
            if ($data instanceof DateTime)
            {
                # Convert the data to a string
                $data = (string) $data;
            }
        }
        
        # See if we have an object
        if (is_object($data))
        {
            # Get the root element from the class name if not overridden
            if ($root_element == "response")
            {
                $class = get_class($data);
                $slash_rpos = strrpos($class, "\\");
                if ($slash_rpos !== false)
                {
                    $class = substr($class, $slash_rpos + 1);
                }
                $root_element = $class;
            }
            
            # Initialize the root element
            $xml = new \SimpleXMLElement("<" . $root_element . "/>");
            
            # Iterate through the object's properties
            self::iteratableToXml($xml, $data, $child_element);
        }
        # See if we have an array
        else if (is_array($data))
        {
            # Initialize the XML object
            $xml = new \SimpleXMLElement("<" . $root_element . "/>");
            
            # Iterate through the array
            self::iteratableToXml($xml, $data, $child_element, $child_as_list);            
        }
        # Assume we have a string
        else
        {
            # Initialize the XML object
            $xml = new \SimpleXMLElement("<" . $root_element . "/>");
            
            # Add the data
            if (is_bool($data))
            {
                if ($data)
                {
                    $xml[0] = "true";
                }
                else
                {
                    $xml[0] = "false";
                }
            }
            else
            {
                $xml[0] = (string) $data;
            }
        }
        
        return $xml;
    }

    /**
     * Converts the given XML data into an associative array.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param SimpleXMLElement $xml_data
     *   XML object to convert
     * @return array Array representation of XML data
     */
    public static function toArray($xml_data)
    {
        throw new NotImplementedException("Not implemented");
    }
    
    /**
     * Converts the given XML data into an object of the given type
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param SimpleXMLElement $xml_data
     *   XML object to convert
     * @param string $object_type
     *   The object type to create (stdClass if not specified)
     * @return object Object representation of XML data
     */
    public static function toObject($xml_data, $object_type = "\\stdClass")
    {
        throw new NotImplementedException("Not implemented");
    }

    
    /***************************************************************************
     * PROTECTED PROPERTIES AND METHODS
     **************************************************************************/
    
    /**
     * Determines if the given array is associative (non-numeric indexes)
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @todo Move to Cougar\Util\Arrays
     * 
     * @param array $array Array to test
     * @return bool True if array is associative, false otherwise
     */
    protected static function arrayIsAssociative(array $array)
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }
    
    /**
     * Add the contents of the given object or array to the XML object provided
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param \SimpleXMLElement $xml
     *   XML object
     * @param mixed $data
     *   Data to add to the XML object
     * @param string $child_element
     *   Child element name
     * @param bool $child_as_list
     *   Treat elements in an array as a list of other objects
     */
    protected static function iteratableToXml(SimpleXMLElement $xml, $data,
        $child_element, $child_as_list = false)
    {
        # See if our data is an object
        if (is_object($data))
        {
            # Go through the properties in the object
            foreach($data as $element => $value)
            {
                # See if we have a DateTime object
                if (is_object($value))
                {
                    if ($value instanceof DateTime)
                    {
                        # Convert the data to a string
                        $value = (string) $value;
                    }
                }

                # See if the value is an object or array
                if (is_object($value) || is_array($value))
                {
                    # Create the child entry
                    if (is_numeric($element[0]))
                    {
                        $child = $xml->addChild($child_element);
                        $child->addAttribute("id", $element);
                    }
                    else
                    {
                        $child = $xml->addChild($element);
                    }
                    
                    # Iterate through this one more time
                    self::iteratableToXml($child, $value, $element);
                }
                else
                {
                    # See if the value is a boolean
                    if (is_bool($value))
                    {
                        # See if value is true or false
                        if ($value)
                        {
                            $xml->addChild($element, "true");
                        }
                        else
                        {
                            $xml->addChild($element, "false");
                        }
                    }
                    else
                    {
                        # Add the value
                        $xml->addChild($element, htmlentities($value));
                    }
                }
            }
        }
        else if (is_array($data))
        {
            # See if the array is associative
            if (self::arrayIsAssociative($data))
            {
                # Go through the array
                foreach($data as $element => $value)
                {
                    # See if the value is an object or array
                    if (is_object($value) || is_array($value))
                    {
                        # Create the child entry
                        if (is_numeric($element) || is_numeric($element[0])
                            || $child_as_list)
                        {
                            $child = $xml->addChild($child_element);
                            $child->addAttribute("id", $element);
                        }
                        else
                        {
                            $child = $xml->addChild($element);
                        }

                        # Iterate through this one more time
                        self::iteratableToXml($child, $value, $element);
                    }
                    else
                    {
                        if (is_numeric($element) || is_numeric($element[0])
                            || $child_as_list)
                        {
                            # See if the value is a boolean
                            if (is_bool($value))
                            {
                                # See if value is true or false
                                if ($value)
                                {
                                    $child = $xml->addChild($child_element,
                                        "true");
                                }
                                else
                                {
                                    $child = $xml->addChild($child_element,
                                        "false");
                                }
                            }
                            else
                            {
                                # Add the value
                                $child = $xml->addChild($child_element,
                                    htmlentities($value));
                            }
                            $child->addAttribute("id", $element);
                        }
                        else
                        {
                            # See if the value is a boolean
                            if (is_bool($value))
                            {
                                # See if value is true or false
                                if ($value)
                                {
                                    $xml->addChild($element, "true");
                                }
                                else
                                {
                                    $xml->addChild($element, "false");
                                }
                            }
                            else
                            {
                                # Add the value
                                $xml->addChild($element, htmlentities($value));
                            }
                        }
                    }
                }
            }
            else
            {
                # Go through the array
                foreach($data as $id => $value)
                {
                    # See if the value is an object or array
                    if (is_object($value) || is_array($value))
                    {
                        # Create the child entry
                        $child = $xml->addChild($child_element);

                        # Iterate through this one more time
                        self::iteratableToXml($child, $value, $child_element);
                    }
                    else
                    {
                        # See if the value is a boolean
                        if (is_bool($value))
                        {
                            # See if value is true or false
                            if ($value)
                            {
                                $xml->addChild($child_element, "true");
                            }
                            else
                            {
                                $xml->addChild($child_element, "false");
                            }
                        }
                        else
                        {
                            # Add the value
                            $xml->addChild($child_element,
                                htmlentities($value));
                        }
                    }
                }
            }
        }
    }
}
?>
