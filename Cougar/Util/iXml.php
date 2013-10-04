<?php

namespace Cougar\Util;

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
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @param SimpleXMLElement $xml_data
     *   XML object to convert
	 * @return array Array representation of XML data
	 */
	public static function toArray($xml_data);
	
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
	public static function toObject($xml_data, $object_type = "\\stdClass");
}
?>
