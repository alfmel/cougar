<?php

namespace Cougar\Util;

use ReflectionClass;
use ReflectionProperty;
use ReflectionMethod;
use Cougar\Cache\iCache;
use Cougar\Cache\CacheFactory;
use Cougar\Exceptions\Exception;

# Initialize the framework (disabled; should have been done by application)
#require_once(__DIR__ . "/../../cougar.php");

/**
 * Extracts and caches annotations from interfaces, classes and objects, and
 * their public properties and methods via reflection. This implementation
 * caches the data at two levels: the local cache (APC or WinCache) and in
 * something called the execution cache which is implemented directly in the
 * class.
 *
 * The class will automatically create a new local cache via the Cache Factory.
 * If you wish to specify your own cache, set it in the cache property.
 *
 * @history
 * 2013.09.30:
 *   (AT)  Initial release
 * 2014.02.20:
 *   (AT)  Reduced number of annotations that will be ignored in preparation for
 *         producing web service documentation
 *   (AT)  Capture class, property and method descriptions
 *   (AT)  Capture annotations that span more than one line
 * 2014.02.26:
 *   (AT)  Added extractFromDocumentBlock() method
 * 2014.03.05:
 *   (AT)  Make sure cached flag is properly preserved when extracting with
 *         inheritance
 * 2014.03.17:
 *   (AT)  Cache inherited annotations directly to improve performance
 * 2014.03.19:
 *   (AT)  Make sure cached inherited annotations are cloned when stored and
 *         retrieved
 *
 * @version 2014.03.19
 * @package Cougar
 * @licence MIT
 *
 * @copyright 2013-2014 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class Annotations implements iAnnotations
{
    /***************************************************************************
     * PUBLIC ABSTRACT PROPERTIES AND METHODS
     **************************************************************************/

    /**
     * @var array List of annotations to ignore
     */
    public static $ignoreList = array(
        "author",
        "copyright",
        "history",
        "license",
        "package",
        "todo"
    );

    /**
     * @var \Cougar\Cache\iCache Local cache
     */
    public static $cache;

    /**
     * @var string Annotations cache prefix
     */
    public static $fileMtimeCachePrefix = "cougar.framework.file.mtime";
    
    /**
     * @var string Annotations cache prefix
     */
    public static $annotationsCachePrefix = "cougar.framework.annotations";
    
    /**
     * @var int Cache duration time (24 hours by default)
     */
    public static $cacheTime = 86400;

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
     * @throws \Cougar\Exceptions\Exception
     * @return \Cougar\Util\ClassAnnotations Full object annotations
     */
    public static function extractFromObject($object, $all_members = true)
    {
        // Make sure we have a cache
        if (! self::$cache instanceof iCache)
        {
            self::$cache = CacheFactory::getLocalCache();
        }

        // Get the name of the object, class or interface
        if (is_object($object))
        {
            $object_class_name = get_class($object);
        }
        else if (is_string($object))
        {
            $object_class_name = $object;
        }
        else
        {
            throw new Exception(
                "Object must be an object reference or class name");
        }

        // See if we are returning all members to determine the cache key
        if ($all_members)
        {
            $class_cache_key = $object_class_name;
        }
        else
        {
            $class_cache_key = $object_class_name . ".partial";
        }

        // See if we have an entry in the execution cache
        if (array_key_exists($class_cache_key, self::$executionCache))
        {
            // Return a clone of the annotations object from the execution cache
            return clone self::$executionCache[$class_cache_key];
        }

        // Reflect the object
        $r_object = new ReflectionClass($object_class_name);

        $cache_valid = ! self::filesHaveChanged(
            $r_object->getFileName());

        // Get the annotations from the cache if the cache is valid
        $annotations = false;
        if ($cache_valid)
        {
            $annotations = self::$cache->get(self::$annotationsCachePrefix .
                "." . $class_cache_key);
        }

        // See if we had cached annotations
        if ($annotations === false)
        {
            // Initialize the annotations object
            $annotations = new ClassAnnotations();

            // Get the class annotations
            $annotations->class = self::extractFromDocumentBlock(
                $r_object->getDocComment());

            // Go through the public properties in the object
            foreach($r_object->getProperties(ReflectionProperty::IS_PUBLIC)
                as $property)
            {
                // See if we are filtering inherited members and if this
                // property needs to be filtered
                if (! $all_members &&
                    $object_class_name != $property->getDeclaringClass()->name)
                {
                    // Skip this property
                    continue;
                }

                // Get the property annotations
                $annotations->properties[$property->name] =
                    self::extractFromDocumentBlock($property->getDocComment());

            }

            // Go through the public methods in the object
            foreach($r_object->getMethods(ReflectionMethod::IS_PUBLIC)
                as $method)
            {
                // See if we are filtering inherited members and if this method
                // needs to be filtered
                if (! $all_members &&
                    $object_class_name != $method->getDeclaringClass()->name)
                {
                    // Skip this method
                    continue;
                }

                // Get the method annotations
                $annotations->methods[$method->name] =
                    self::extractFromDocumentBlock($method->getDocComment());

            }

            // Store the annotations in the cache
            $annotations->cached = true;
            self::$cache->set(self::$annotationsCachePrefix . "." .
                $class_cache_key, $annotations, self::$cacheTime);
            self::$executionCache[$class_cache_key] = clone $annotations;

            // The "cached" flag was set to true above so that when the
            // annotations come from the cache they automatically have this flag
            // set. However, the annotations did not come from the cache, so we
            // will set it back to false when we return the object
            $annotations->cached = false;
        }
        else
        {
            // Store the annotations in the execution cache
            self::$executionCache[$class_cache_key] = clone $annotations;
        }

        return $annotations;
    }

    /**
     * Returns the annotations for the class and public methods and properties
     * from the given object. It will also aggregate the annotations in parent
     * classes and optionally from traits and/or interfaces that are directly
     * used by the class.
     *
     * @history
     * 2014.02.26:
     *   (AT)  Initial implementation from deprecated extract() method
     * 2014.03.05:
     *   (AT)  Make sure to set the cached flag on the empty annotations object
     *         so that the child annotation object cached flags may be preserved
     * 2014.03.17:
     *   (AT)  Cache inherited annotations directly to improve performance
     * 2014.03.19:
     *   (AT)  Clone annotations object before storing in execution cache
     *
     * @version 2014.03.19
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
     * @throws \Cougar\Exceptions\Exception
     * @return \Cougar\Util\ClassAnnotations
     *   ClassAnnotations object with annotations
     */
    public static function extractFromObjectWithInheritance($object,
        array $exclude_class_list = array(), $inherit_from_traits = true,
        $inherit_from_interfaces = true)
    {
        // Make sure we have a cache
        if (! self::$cache instanceof iCache)
        {
            self::$cache = CacheFactory::getLocalCache();
        }

        // Get the name of the object, class or interface
        if (is_object($object))
        {
            $object_class_name = get_class($object);
        }
        else if (is_string($object))
        {
            $object_class_name = $object;
        }
        else
        {
            throw new Exception(
                "Object must be an object reference or class name");
        }

        // Figure out the cache key
        $cache_key = self::$annotationsCachePrefix . "." . $object_class_name .
            ".inherited." . implode(",", $exclude_class_list) . "." .
            (int) $inherit_from_traits . "." . (int) $inherit_from_interfaces;

        // See if we have an entry in the execution cache
        if (array_key_exists($cache_key, self::$executionCache))
        {
            // Return the annotations from the execution cache
            return clone self::$executionCache[$cache_key];
        }

        // Reflect the object
        $r_object = new ReflectionClass($object_class_name);

        // Start the class hierarchy, trait list and filename list
        $class_hierarchy = array();
        $trait_list = array();
        $filename_list = array();

        // Recursively get the parent classes
        $parent = $r_object;
        do
        {
            // See if class is in the Cougar\Model namespace or the exclude list
            if (substr($parent->name, 0, 12) !== "Cougar\\Model" &&
                ! in_array($parent->name, $exclude_class_list))
            {
                // Add the class to the hierarchy
                array_unshift($class_hierarchy, $parent->name);

                // Get the class filename
                $filename_list[$parent->name] = $parent->getFileName();

                // See if we are extracting from traits
                if ($inherit_from_traits)
                {
                    // Go through the traits
                    foreach($parent->getTraitNames() as $trait)
                    {
                        // Skip if in Cougar\Model or exclude_class_list
                        if (substr($trait, 0, 12) !== "Cougar\\Model" &&
                            ! in_array($trait, $exclude_class_list))
                        {
                            // Add the trait to the class hierarchy
                            array_unshift($class_hierarchy, $trait);

                            // Add the trait to the list of traits
                            $trait_list[] = $trait;
                        }
                    }
                }

                // See if we are extracting from interfaces
                if ($inherit_from_interfaces)
                {
                    // Go through the interfaces
                    foreach($parent->getInterfaceNames() as $interface)
                    {
                        // Skip if in Cougar\Model or exclude_class_list
                        if (substr($interface, 0, 12) !== "Cougar\\Model" &&
                            ! in_array($interface, $exclude_class_list))
                        {
                            // Add the interface to the class hierarchy
                            array_unshift($class_hierarchy, $interface);
                        }
                    }
                }

                // Get the parent
                $parent = $parent->getParentClass();
            }
            else
            {
                // Don't get the next parent since this class was excluded
                $parent = false;
            }
        }
        while ($parent !== false);

        // See if the files have been modified
        if (! self::filesHaveChanged($filename_list, false))
        {
            // See if we have an entry in the local cache
            $annotations = self::$cache->get($cache_key);

            if ($annotations !== false)
            {
                // Store the annotations in the execution cache
                self::$executionCache[$cache_key] = clone $annotations;

                // Return the cached annotations
                return $annotations;
            }
        }

        // Define an empty set of annotations and consider them cached
        $annotations = new ClassAnnotations();
        $annotations->cached = true;

        // Go through each class, trait and interface
        foreach($class_hierarchy as $class)
        {
            // See if we are inheriting from traits and if this is a trait
            if ($inherit_from_traits && in_array($class, $trait_list))
            {
                // Get the annotations for the trait
                $trait_annotations = self::extractFromObject($class, false);

                // Trait functions are automatically included in the class; we
                // only need the annotations from the trait definition
                $trait_annotations->properties = array();
                $trait_annotations->methods = array();

                // Merge the annotations
                self::merge($annotations, $trait_annotations);
            }
            else
            {
                // Merge the annotations
                self::merge($annotations, self::extractFromObject($class,
                    false));
            }
        }

        // Store the annotations in the cache
        $orig_cache_flag = $annotations->cached;
        $annotations->cached = true;
        self::$executionCache[$cache_key] = clone $annotations;
        self::$cache->set($cache_key, $annotations, self::$cacheTime);
        $annotations->cached = $orig_cache_flag;

        // Return the annotations
        return $annotations;
    }

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
     * 2014.02.26:
     *   Initial implementation
     *
     * @version 2014.02.26:
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param mixed $object
     *   Interface name, class name or object to extract from
     * @param string $property
     *   Property name
     * @throws \Cougar\Exceptions\Exception
     * @return \Cougar\Util\Annotation[] Property annotations
     */
    public static function extractFromProperty($object, $property)
    {
        // Get the annotations from the object
        $annotations = self::extractFromObject($object);

        // See if the property exists
        if (array_key_exists($property, $annotations->properties))
        {
            return $annotations->methods[$property];
        }
        else
        {
            throw new Exception("Class or Interface does not have a property " .
                "named " . $property);
        }
    }

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
     * @throws \Cougar\Exceptions\Exception
     * @return \Cougar\Util\Annotation[] Method annotations
     */
    public static function extractFromMethod($object, $method)
    {
        // Get the annotations from the object
        $annotations = self::extractFromObject($object);

        // See if the method exists
        if (array_key_exists($method, $annotations->methods))
        {
            return $annotations->methods[$method];
        }
        else
        {
            throw new Exception("Class or Interface does not have a method " .
                "named " . $method);
        }
    }

    /**
     * Extracts multi-line annotations from the given document block. Any
     * comments before the first annotation will be returned in the special
     * _comment annotation. If there are no comments, the _comment annotation
     * will not exist.
     *
     * These annotations will not be cached.
     *
     * We used to do this in the extract() method using a regular expression,
     * but I could never figure out how to make multi-line annotations work
     * together with @ characters in the annotation value, like in email
     * addresses. This method solves this problem.
     *
     * @history
     * 2014.02.21:
     *   (AT)  Initial implementation
     *
     * @version 2014.02.20
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string $document_block Full document block
     * @return \Cougar\Util\Annotation[] Array of annotation objects
     */
    public static function extractFromDocumentBlock($document_block)
    {
        # Remove the asterisk comment markers; we also add a \n at the start to
        # match later on the \n@ combination to split annotations
        $block = "\n" . preg_replace(
                array(':/\*\*:', ':\*/:', '/[ \t]*\*[ \t]*/'),
                array("", "", ""),
                trim($document_block));

        # Split on the \n@ combination
        $parts = explode("\n@", $block);

        # Initialize the annotation list
        $annotations = array();

        # Go through the annotations
        foreach($parts as $index => $part)
        {
            # See if this is the first entry (comment area)
            if ($index === 0)
            {
                $comment = trim($part);
                if ($comment)
                {
                    $annotations[] = new Annotation("_comment", trim($part));
                }
            }
            else
            {
                # Split the annotation on white space
                $values = preg_split('/\s/', trim($part), 2);

                # See if this annotation is in the ignore list
                if (! in_array($values[0], self::$ignoreList))
                {
                    # See how many parts we have
                    if (count($values) == 1)
                    {
                        # Add the annotation with an empty value
                        $annotations[] = new Annotation($values[0], "");
                    }
                    else
                    {
                        # Add the annotation with the proper value
                        $annotations[] = new Annotation($values[0],
                            trim($values[1]));
                    }
                }
            }
        }

        # Return the annotations
        return $annotations;
    }

    /**
     * Merges annotations in object2 and object1 by appending the object2's
     * annotations into object1. Since object1 is passed by reference, the
     * changes will be reflected in the object1 reference.
     *
     * @history
     * 2014.02.24:
     *   (AT)  Initial implementation
     *
     * @version 2014.02.24
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param \Cougar\Util\ClassAnnotations $object1
     *   Class annotation objects to merge into
     * @param \Cougar\Util\ClassAnnotations $object2
     *   Class annotations to merge into object1
     */
    public static function merge(ClassAnnotations $object1,
        ClassAnnotations $object2)
    {
        // Merge the class annotations
        $object1->class = array_merge($object1->class, $object2->class);

        // Merge the property annotations
        foreach($object2->properties as $property => $annotations)
        {
            if (array_key_exists($property, $object1->properties))
            {
                $object1->properties[$property] =
                    array_merge($object1->properties[$property], $annotations);
            }
            else
            {
                $object1->properties[$property] = $annotations;
            }
        }

        // Merge the method annotations
        foreach($object2->methods as $method => $annotations)
        {
            if (array_key_exists($method, $object1->methods))
            {
                $object1->methods[$method] =
                    array_merge($object1->methods[$method], $annotations);
            }
            else
            {
                $object1->methods[$method] = $annotations;
            }
        }

        // Logically AND the cached flag
        $object1->cached = $object1->cached && $object2->cached;
    }

    /**
     * Returns the annotations for the class and its public methods and
     * properties from the given object. Annotations in the interfaces the
     * object may implement are ignored.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     * 2014.02.20:
     *   (AT)  Reduced number of annotations that are ignored
     *   (AT)  Capture class, property and method descriptions
     *   (AT)  Capture annotations that span more than one line
     * 2014.02.26:
     *   (AT)  Calls the new extractFromDocumentBlock() method
     *
     * @version 2014.02.26
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
     * @throws \Cougar\Exceptions\Exception
     */
    public static function extract(iCache $local_cache, $object,
        array $exclude_class_list = array())
    {
        // Set up the cache and call extractFromObjectWithInheritance
        self::$cache = $local_cache;
        return self::extractFromObjectWithInheritance($object,
            $exclude_class_list, true, false);
    }


    /***************************************************************************
     * PROTECTED STATIC PROPERTIES AND METHODS
     **************************************************************************/
    
    /**
     * @var array Execution cache
     */
    public static $executionCache = array();

    /**
     * Determines whether one or more files have been modified since the last
     * time it was checked by this method. You may pass a single filename as a
     * string or a list of filenames in an array. If the file, or any file in
     * the list has changed since the last check, the method will return true.
     *
     * @history
     * 2014.03.17:
     *   (AT)  Initial implementation
     *
     * @version 2014.03.17
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string|array $filename
     *   Filename or array of filenames to check for changes
     * @param bool $store_change
     *   Whether to record the new changes (true by default)
     * @return bool True if any file has changed, false otherwise
     */
    protected static function filesHaveChanged($filename, $store_change = true)
    {
        // See if we have been given an array
        if (is_array($filename))
        {
            $file_list = &$filename;
        }
        else
        {
            $file_list = array($filename);
        }

        // Go through each item in the array
        $results = array();
        foreach($file_list as $file)
        {
            // Create the cache key
            $cache_key = self::$fileMtimeCachePrefix . "." . $file;

            // See if we have a value in our execution cache; if not extract
            // from the real cache
            if (array_key_exists($cache_key, self::$executionCache))
            {
                $last_known_file_mtime = self::$executionCache[$cache_key];
            }
            else
            {
                $last_known_file_mtime = self::$cache->get($cache_key);
            }

            // Check the cache for the last known modification time
            if ($last_known_file_mtime === false)
            {
                // We don't know what the last modification time was; consider
                // it changed; see if we need to store the result
                if ($store_change)
                {
                    // Store the file's modification time for next time
                    $file_mtime = filemtime($file);
                    self::$executionCache[$cache_key] = $file_mtime;
                    self::$cache->set($cache_key, $file_mtime,
                        self::$cacheTime);
                }

                // Return that the file has changed
                return true;
            }
            else
            {
                // The response may come from the local cache; make sure we have
                // an entry in the execution cache
                self::$executionCache[$cache_key] = $last_known_file_mtime;

                // Get the file's modification time
                $file_mtime = filemtime($file);

                // See if the time has changed
                if ($file_mtime != $last_known_file_mtime)
                {
                    // The file has been modified; see if we need to store the
                    // change
                    if ($store_change)
                    {
                        // Store this file's modification time in the caches
                        self::$executionCache[$cache_key] = $file_mtime;
                        self::$cache->set($cache_key, $file_mtime,
                            self::$cacheTime);
                    }

                    // Return that the file has changed
                    return true;
                }
            }
        }

        // None of the files have changed; return false
        return false;
    }
}
?>
