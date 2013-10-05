<?php

namespace Cougar\Util;

use Cougar\Cache\iCache;
use Cougar\Exceptions\Exception;

# Initialize the framework
require_once("cougar.php");

/**
 * Extracts annotations from source files via reflection. The class also
 * implements an execution cache which keeps annotations cached in the class
 * itself during execution time. This is useful when instantiating multiple
 * objects from the same class.
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
class Annotations implements iAnnotations
{
    /***************************************************************************
     * PUBLIC ABSTRACT PROPERTIES AND METHODS
     **************************************************************************/
    
    /**
     * @var array List of annotations to ignore
     */
    public static $ignoreList = array(
        "package",
        "license",
        "version",
        "author",
        "param",
        "todo",
        "return",
        "history"
    );
    
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
     * Returns the annotations for the class and public methods and properties
     * from the given object. Annotations in the interfaces the object may
     * implement are ignored.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
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
     * @throws \Cougar\Exceptions\Exception
     */
    public static function extract(iCache $local_cache, $object,
        array $exclude_class_list = array())
    {
        # Get the class name if we have an actual object
        if (is_object($object))
        {
            $object_class = get_class($object);
        }
        else if (is_string($object))
        {
            $object_class = $object;
        }
        else
        {
            throw new Exception(
                "Object must be an object reference or class name");
        }
        
        # See if we have an entry in the current execution class
        if (array_key_exists($object_class, self::$executionCache))
        {
            # Return the object from the execution cache
            return self::$executionCache[$object_class];
        }
        
        # Reflect the object
        $r_object = new \ReflectionClass($object);
        
        # Initialize the files and parent list with the object's file and traits
        $files = array($r_object->getFileName());
        $parents = array($r_object);
        foreach($r_object->getTraits() as $trait)
        {
            $trait_name = $trait->getName();

            # Ignore traits from the Cougar\Model namespace
            if (substr($trait_name, 0, 12) == "Cougar\\Model")
            {
                continue;
            }

            # See if this trait needs to be excluded
            if (! in_array($trait_name, $exclude_class_list))
            {
                # Add the trait as a parent class
                $parents[] = $trait;

                # Add the file associated wit it
                $files[] = $trait->getFileName();
            }
        }
        
        # Get the parent classes, traits and files for the object
        $parent = $r_object;
        while ($parent = $parent->getParentClass())
        {
            $parent_name = $parent->getName();

            # Ignore classes from the Cougar\Model namespace
            if (substr($parent_name, 0, 12) == "Cougar\\Model")
            {
                continue;
            }

            # See if this class needs to be excluded
            if (! in_array($parent_name, $exclude_class_list))
            {
                $parents[] = $parent;
                $files[] = $parent->getFileName();
                
                # Get the traits and files associated with this class
                foreach($parent->getTraits() as $trait)
                {
                    $trait_name = $trait->getName();

                    # Ignore traits from the Cougar\Model namespace
                    if (substr($trait_name, 0, 12) == "Cougar\\Model")
                    {
                        continue;
                    }

                    # See if this trait needs to be excluded
                    if (! in_array($trait_name, $exclude_class_list))
                    {
                        # Add the trait as a parent class
                        $parents[] = $trait;

                        # Add the file associated wit it
                        $files[] = $trait->getFileName();
                    }
                }
            }
        }
        
        # Remove duplicate file names
        $files = array_unique($files);
        
        # See if any of the files associated with the object have been modified
        # since the last execution
        $cache_valid = true;
        foreach($files as $file)
        {
            # Skip PHP shell code
            if ($file === "php shell code")
            {
                continue;
            }
            
            # Get the value from the cache
            $cached_mtime = $local_cache->get(self::$fileMtimeCachePrefix .
                "." . $file);
            
            if ($cached_mtime === false)
            {
                $cached_mtime = time() + 5;
            }
            
            # Get the file's modification time
            $mtime = filemtime($file);
            
            # See if the values match
            if ($mtime != $cached_mtime)
            {
                # Declare the cache as invalid
                $cache_valid = false;
                
                # Store this file's mtime in the cache
                $local_cache->set(self::$fileMtimeCachePrefix . "." . $file,
                    $mtime, self::$cacheTime);
            }
        }
        
        # Get the cached annoations
        $annotations = $local_cache->get(self::$annotationsCachePrefix .
            "." . $r_object->name);
        
        # See if the cache was valid
        if (! $cache_valid || $annotations === false)
        {
            # Define the annotations array
            $annotations = new ClassAnnotations();
            
            # Define the regular expression for extracting annotations
            $annotation_regex = "/(\*[ \t]+@)(\w+)[ \t]*(.*)/";

            # Go through the parent classes in reverse order
            foreach(array_reverse($parents) as $parent)
            {
                # Extract the parent annotations
                $matches = array();
                preg_match_all($annotation_regex, $parent->getDocComment(),
                    $matches, PREG_SET_ORDER);
                foreach($matches as $match)
                {
                    # See if this is a skipped annotation
                    if (in_array($match[2], self::$ignoreList))
                    {
                        # Skip the annotation
                        continue;
                    }
                    
                    # Store the annoation
                    $annotations->class[] =
                        new Annotation($match[2], $match[3]);
                }
            }
                
            # Go through the public properties of the object
            foreach($r_object->getProperties(\ReflectionProperty::IS_PUBLIC)
                as $property)
            {
                # Create an entry in the properties
                $annotations->properties[$property->name] = array();
                
                # Extract the parent annotations
                $matches = array();
                preg_match_all($annotation_regex, $property->getDocComment(),
                    $matches, PREG_SET_ORDER);
                foreach($matches as $match)
                {
                    # See if this is a skipped annotation
                    if (in_array($match[2], self::$ignoreList))
                    {
                        # Skip the annotation
                        continue;
                    }
                    
                    # Store the annoation
                    $annotations->properties[$property->name][] =
                        new Annotation($match[2], $match[3]);
                }
            }
            
            # Go through the public methods of the object
            foreach($r_object->getMethods(\ReflectionMethod::IS_PUBLIC)
                as $method)
            {
                # Skip constructor and destructor
                if ($method->isConstructor() || $method->isDestructor())
                {
                    continue;
                }
                
                # Create an entry in the method array
                $annotations->methods[$method->name] = array();
                
                # Extract the method annotations
                $matches = array();
                preg_match_all($annotation_regex, $method->getDocComment(),
                    $matches, PREG_SET_ORDER);
                foreach($matches as $match)
                {
                    # See if this is a skipped annotation
                    if (in_array($match[2], self::$ignoreList))
                    {
                        # Skip the annotation
                        continue;
                    }
                    
                    # Store the annoation
                    $annotations->methods[$method->name][] =
                        new Annotation($match[2], $match[3]);
                }
            }
            
            # Store the annotations in the cache
            $annotations->cached = true;
            $local_cache->set(self::$annotationsCachePrefix . "." .
                $r_object->name, $annotations, self::$cacheTime);
            self::$executionCache[$object_class] = $annotations;
            $annotations->cached = false;
        }
        else
        {
            # Store the annotations in the execution cache
            self::$executionCache[$object_class] = $annotations;
        }
        
        return $annotations;
    }
    
    
    /***************************************************************************
     * PROTECTED STATIC PROPERTIES AND METHODS
     **************************************************************************/
    
    /**
     * @var array Execution cache
     */
    protected static $executionCache = array();
}
?>
