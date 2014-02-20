<?php

namespace Cougar\Util;

use SimpleXMLElement;

/**
 * Converts data to and from XML
 *
 * @history
 * 2013.09.30:
 *   (AT)  Initial release
 * 2013.11.25:
 *   (AT)  Convert XML to instance of stdClass (not any object)
 *
 * @version 2013.11.25
 * @package Cougar
 * @license MIT
 *
 * @copyright 2013 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
interface iXml
{
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
     * @return SimpleXMLElement XML representation of the data
     */
    public static function toXml($data, $root_element = null,
        $child_element = null);

    /**
     * Converts the given XML data into an associative array.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     * 2013.11.25:
     *   (AT)  Force incoming data to be instance of SimpleXMLElement
     *
     * @version 2013.11.25
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param \SimpleXMLElement $xml_data
     *   XML object to convert
     * @return array Array representation of XML data
     */
    public static function toArray(SimpleXMLElement $xml_data);
    
    /**
     * Converts the given XML data into an instance of stdClass. If the XML does
     * not have any child elements, the method will return a string with the
     * string value of the root element.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     * 2013.11.25:
     *   (AT)  Force incoming data to be instance of SimpleXMLElement
     *   (AT)  Remove object type parameter; will always return instance of
     *         stdClass, similar to json_decode
     *
     * @version 2013.11.25
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param \SimpleXMLElement $xml_data
     *   XML object to convert
     * @return mixed Object representation of XML data
     */
    public static function toObject(SimpleXMLElement $xml_data);
}
?>
