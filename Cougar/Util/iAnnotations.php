<?php

namespace Cougar\Util;

use Cougar\Cache\iCache;

/**
 * Defines the Annotations interface used for retreiving and caching file
 * annotations
 *
 * @history
 * 2013.09.30:
 *   (AT)  Initial release
 * 2014.02.26:
 *   (AT)  Added extractFromDocumentBlock() method
 *   (AT)  Added extractFromObject() and extractFromObjectWithInheritance()
 *         methods
 *   (AT)  Added extractFromProperty() and extractFromMethod() methods
 *
 * @version 2014.02.26
 * @package Cougar
 * @licence MIT
 *
 * @copyright 2013-2014 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
interface iAnnotations
{
    /**
     * Returns the annotations from an interface, class or object, and the
     * annotations of its public properties and methods. It will not return the
     * annotations of any of its parents, only the given construct.
     *
     * To extract annotations from an interface or class, provide the fully-
     * qualified interface or class name in the object parameter. To extract
     * from an existing object, pass the object reference in the object
     * parameter.
     *
     * If the all_members parameter is set to true (default) the method will
     * return annotations for all class members. If it is set to true, it will
     * only return annotations for members that are defined in that particular
     * class and ignore any members defined in parent classes.
     *
     * @history
     * 2014.02.24:
     *   Initial definition
     *
     * @version 2014.02.24:
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param mixed $object
     *   Interface name, class name or object to extract from
     * @param bool $all_members
     *   Whether to return annotations from all class members or only those that
     *   are specified in the class definition (no inherited members)
     * @return \Cougar\Util\ClassAnnotations Full object annotations
     */
    public static function extractFromObject($object, $all_members = true);

    /**
     * Returns the annotations for the class and public methods and properties
     * from the given object. It will also aggregate the annotations in parent
     * classes and optionally from traits and/or interfaces that are directly
     * used by the class.
     *
     * @history
     * 2014.02.26:
     *   (AT)  Initial definition
     *
     * @version 2014.02.26
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param mixed $object
     *   Interface name, class name or object to extract from
     * @param array $exclude_class_list
     *   List of classes to exclude from in the object's inheritance tree
     * @param bool $inherit_from_traits
     *   Whether to extract annotations in the trait document block
     * @param bool $inherit_from_interfaces
     *   Whether to extract annotations in the interface document block and its
     *   methods
     * @return \Cougar\Util\ClassAnnotations
     *   ClassAnnotations object with annotations
     */
    public static function extractFromObjectWithInheritance($object,
        array $exclude_class_list = array(), $inherit_from_traits = true,
        $inherit_from_interfaces = true);

    /**
     * Returns the annotations associated with the given interface, class or
     * object's public property. Protected or private properties are not
     * supported by design.
     *
     * When specifying an interface or class, provide the fully-qualified
     * interface or class name in the object parameter. When specifying an
     * object, pass the object reference in the object parameter.
     *
     * The property parameter must always be a string with the name of the
     * property.
     *
     * @history
     * 2014.02.21:
     *   Initial definition
     *
     * @version 2014.02.21:
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param mixed $object
     *   Interface name, class name or object to extract from
     * @param string $property
     *   Property name
     * @return \Cougar\Util\Annotation[] Property annotations
     */
    public static function extractFromProperty($object, $property);

    /**
     * Returns the annotations associated with the given interface, class or
     * object's public method. Protected or private methods are not supported
     * by design.
     *
     * When specifying an interface or class, provide the fully-qualified
     * interface or class name in the object parameter. When specifying an
     * object, pass the object reference in the object parameter.
     *
     * The method parameter must always be a string with the name of the method.
     *
     * @history
     * 2014.02.21:
     *   Initial definition
     *
     * @version 2014.02.21:
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param mixed $object
     *   Interface name, class name or object to extract from
     * @param string $method
     *   method name
     * @return \Cougar\Util\Annotation[] Method annotations
     */
    public static function extractFromMethod($object, $method);

    /**
     * Extracts multi-line annotations from the given document block. Any
     * comments before the first annotation will be returned in the special
     * _comment annotation. If there are no comments, the _comment annotation
     * will not exist.
     *
     * @history
     * 2014.02.21:
     *   (AT)  Initial implementation
     *
     * @version 2014.02.21
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string $document_block Full document block
     * @return \Cougar\Util\Annotation[] Array of annotation objects
     */
    public static function extractFromDocumentBlock($document_block);

    /**
     * Returns the annotations for the class and public methods and properties
     * from the given object. Annotations in the interfaces the object may
     * implement are ignored.
     *
     * This is now an alias of extractFromObjectWithInheritance().
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     * 2014.02.21:
     *   (AT)  Deprecated in favor of extractFromObjectWithInheritance()
     *
     * @deprecated
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param \Cougar\Cache\iCache $local_cache
     *   Local cache object
     * @param mixed $object
     *   Object to extract annotations from
     * @param array $exclude_class_list
     *   List of classes to exclude
     * @return \Cougar\Util\ClassAnnotations
     *   ClassAnnotations object with annotations
     */
    public static function extract(iCache $local_cache, $object,
        array $exclude_class_list = array());
}
?>
