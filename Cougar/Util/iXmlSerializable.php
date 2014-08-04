<?php

namespace Cougar\util;

/**
 * Defines the interface that allows objects to be serialized to XML. When
 * implementing this interface, objects must implement the xmlSerialize()
 * method. The method must return a SimpleXMLElement representing the object.
 *
 * @history
 * 2014.05.20:
 *   (AT)  Initial definition
 *
 * @version 2014.05.20
 * @package Cougar
 * @licence MIT
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
interface iXmlSerializable
{
    /**
     * Converts the object to an XML representation.
     *
     * @history
     * 2014.05.20:
     *   (AT)  Initial definition
     *
     * @version 2014.05.20
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * @return \SimpleXMLElement XML representation of the object
     */
    public function xmlSerialize();
}
?>
