<?php

namespace Cougar\Util;

use SimpleXMLElement;

# Initialize the framework (disabled; should have been done by application)
#require_once(__DIR__ . "/../../cougar.php");

/**
 * Converts data to and from XML
 *
 * @history
 * 2013.09.30:
 *   (AT)  Initial release
 * 2014.02.20:
 *   (AT)  Modify toArray and toObject signatures
 * 2014.05.20:
 *   (AT)  Add support for iXmlSerializable
 * 2014.08.04:
 *   (AT)  Implement toObject() and toArray()
 * 2014.08.07:
 *   (AT)  Remove use of htmlentities() in toXml()
 *
 * @version 2014.08.07
 * @package Cougar
 * @license MIT
 *
 * @copyright 2013-2014 Brigham Young University
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
     * 2014.05.20:
     *   (AT)  Add support for iXmlSerializable
     *
     * @version 2014.05.20
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param mixed $data
     *   The data to convert
     * @param string $root_element
     *   Optional name of the root element (default: response)
     * @param string $child_element
     *   Optional name of the child elements (default: object)
     * @param bool $child_as_list
     *   Whether the child should be treated as a list of other object
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
        if (is_object($data) && $data instanceof DateTime)
        {
            # Convert the data to a string
            $data = (string) $data;
        }

        # See if we have an object
        if (is_object($data))
        {
            # See if the object implements iXmlSerializable
            if ($data instanceof iXmlSerializable)
            {
                # Let the object do its own XML export
                $xml = $data->xmlSerialize();
            }
            else
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
                self::toXmlRecursive($xml, $data, $child_element);
            }
        }
        # See if we have an array
        else if (is_array($data))
        {
            # Initialize the XML object
            $xml = new \SimpleXMLElement("<" . $root_element . "/>");
            
            # Iterate through the array
            self::toXmlRecursive($xml, $data, $child_element, $child_as_list);
        }
        # Assume we have a scalar
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
     * 2013.11.25:
     *   (AT)  Implement the method
     * 2014.08.04:
     *   (AT)  Make improvements based on the actual implementation of toObject
     *
     * @version 2014.08.04
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param \SimpleXMLElement $xml_data
     *   XML object to convert
     * @return mixed Array representation of XML data
     */
    public static function toArray(SimpleXMLElement $xml_data)
    {
        $object = self::toObject($xml_data);

        if ($object instanceof \stdClass)
        {
            // Convert everything to an array
            $object = json_decode(json_encode($object), true);
        }
        else if (is_array($object))
        {
            // convert all objects to an array
            foreach($object as &$value)
            {
                if ($value instanceof \stdClass)
                {
                    $value = json_decode(json_encode($value), true);
                }
            }
        }

        return $object;
    }
    
    /**
     * Converts the given XML data into an instance of stdClass. If the XML does
     * not have any child elements, the method will return a string with the
     * string value of the root element.
     *
     * @history
     * 2014.08.04:
     *   (AT)  Initial implementation
     *
     * @version 2014.08.04
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param \SimpleXMLElement $xml_data
     *   XML object to convert
     * @param boolean $ignore_id_attribute
     *   Whether to ignore ID attributes
     * @return mixed Object representation of XML data
     */
    public static function toObject(SimpleXMLElement $xml_data,
        $ignore_id_attribute = false)
    {
        // See if we have attributes or children
        if (! count($xml_data->attributes()) && ! count($xml_data->children()))
        {
            // This is a simple value; return as string or float
            $value = (string) $xml_data[0];

            if (is_numeric($value))
            {
                $value = (float) $value;
            }
            else if ($value == "false")
            {
                $value = false;
            }
            else if ($value == "true")
            {
                $value = true;
            }

            return $value;
        }

        // See if there is a naming conflict between properties and elements,
        // and if certain children are an array
        $has_attributes = false;
        $names = array();
        $duplicate_names = array();
        $child_names = array();
        $array_names = array();
        foreach($xml_data->attributes() as $name => $value)
        {
            $has_attributes = true;
            $names[] = $name;
        }

        foreach($xml_data->children() as $name => $value)
        {
            if (in_array($name, $names))
            {
                $duplicate_names[] = $name;
            }

            if (in_array($name, $child_names))
            {
                if (! in_array($name, $array_names))
                {
                    $array_names[] = $name;
                }
            }
            else
            {
                $child_names[] = $name;
            }
        }

        // See if we are dealing win an actual object or an array
        if (! $has_attributes && count($child_names) == 1 &&
            count($array_names) == 1)
        {
            // Object is a simple array
            $object = array();

            foreach($xml_data->children() as $child)
            {
                // See if we have an ID (use as key)
                $key = (string) $child["id"];

                // See if the child has children
                if (count($child->children()))
                {
                    // Add them recursively
                    if ($key)
                    {
                        $object[$key] = self::toObject($child, true);
                    }
                    else
                    {
                        $object[] = self::toObject($child);
                    }
                }
                else
                {
                    // Just add the value
                    $value = (string) $child;

                    if (is_numeric($value))
                    {
                        $value = (float) $value;
                    }
                    else if ($value == "false")
                    {
                        $value = false;
                    }
                    else if ($value == "true")
                    {
                        $value = true;
                    }

                    if ($key)
                    {
                        $object[$key] = $value;
                    }
                    else
                    {
                        $object[] = $value;
                    }
                }
            }
        }
        else
        {
            // This is an object
            $object = new \stdClass();

            // Add the attributes, adding a _ where necessary
            foreach($xml_data->attributes() as $name => $value)
            {
                // See if we are ignoring ID attributes
                if ($name == "id" && $ignore_id_attribute)
                {
                    continue;
                }

                // See if the name has been duplicated
                if (in_array($name, $duplicate_names))
                {
                    $name = "_" . $name;
                }

                // Add the value
                $object->$name = (string) $value;
                if (is_numeric($object->$name))
                {
                    $object->$name = (float) $object->$name;
                }
                else if ($object->$name == "false")
                {
                    $object->$name = false;
                }
                else if ($object->$name == "true")
                {
                    $object->$name = true;
                }
            }

            // Add the values
            foreach($xml_data->children() as $name => $value)
            {
                // See if the value has children
                if (count($xml_data->$name->children()))
                {
                    // Add them recursively
                    $object->$name = self::toObject($value);
                }
                else
                {
                    // Add the value
                    $object->$name = (string) $value;
                    if (is_numeric($object->$name))
                    {
                        $object->$name = (float) $object->$name;
                    }
                    else if ($object->$name == "false")
                    {
                        $object->$name = false;
                    }
                    else if ($object->$name == "true")
                    {
                        $object->$name = true;
                    }
                }
            }
        }

        // Return the object
        return $object;
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
     * 2014.05.22:
     *   (AT)  Renamed from iterableToXml to toXmlRecursive
     *   (AT)  Added support for iXmlSerializable
     * 2014.08.07:
     *   (AT)  Add child values indirectly; this allows the full XML entities
     *         parser in SimpleXMLElement to work without using htmlentities()
     *
     * @version 2014.05.22
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
    protected static function toXmlRecursive(SimpleXMLElement $xml, $data,
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
                    self::toXmlRecursive($child, $value, $element);
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
                        $xml->addChild($element)[0] = $value;
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
                    # See if the value implements iXmlSerializable
                    if (is_object($value) && $value instanceof iXmlSerializable)
                    {
                        # Add the XML generated by the object
                        $child = $value->xmlSerialize();
                        $xml->{$child->getName()}[] = $child;
                    }
                    # Handle objects and arrays
                    else if (is_object($value) || is_array($value))
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
                        self::toXmlRecursive($child, $value, $element);
                    }
                    # Handle basic types
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
                                $child = $xml->addChild($child_element);
                                $child[0] = $value;
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
                                $xml->addChild($element)[0] = $value;
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
                    # See if the value implements iXmlSerializable
                    if (is_object($value) && $value instanceof iXmlSerializable)
                    {
                        # Add the XML generated by the object
                        $child = $value->xmlSerialize();
                        $xml->{$child->getName()}[] = $child;
                    }
                    # Handle objects and arrays
                    else if (is_object($value) || is_array($value))
                    {
                        # Create the child entry
                        $child = $xml->addChild($child_element);

                        # Iterate through this one more time
                        self::toXmlRecursive($child, $value, $child_element);
                    }
                    # Handle basic types
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
                            $xml->addChild($child_element)[0] = $value;
                        }
                    }
                }
            }
        }
    }
}
?>
