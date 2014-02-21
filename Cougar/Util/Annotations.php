<?php

namespace Cougar\Util;

use Cougar\Cache\iCache;
use Cougar\Exceptions\Exception;

# Initialize the framework (disabled; should have been done by application)
#require_once(__DIR__ . "/../../cougar.php");

/**
 * Extracts annotations from source files via reflection. The class also
 * implements an execution cache which keeps annotations cached in the class
 * itself during execution time. This is useful when instantiating multiple
 * objects from the same class.
 *
 * @history
 * 2013.09.30:
 *   (AT)  Initial release
 * 2014.02.20:
 *   (AT)  Reduced number of annotations that will be ignored in preparation for
 *         producing web service documentation
 *   (AT)  Capture class, property and method descriptions
 *   (AT)  Capture annotations that span more than one line
 * 2014.02.21:
 *   (AT)  Added extractFromDocumentBlock() method
 *
 * @version 2014.02.21
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
                $values = preg_split("/\s/", trim($part), 2);

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

        # Return the annotations
        return $annotations;
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
     * 2014.02.21:
     *   (AT)  Use the new extractFromDocumentBlock() method
     *
     * @version 2014.02.21
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
        
        # Get the cached annotations
        $annotations = $local_cache->get(self::$annotationsCachePrefix .
            "." . $r_object->name);
        
        # See if the cache was valid
        if (! $cache_valid || $annotations === false)
        {
            # Initialize the annotations array
            $annotations = new ClassAnnotations();
            
            # Go through the parent classes in reverse order
            foreach(array_reverse($parents) as $parent)
            {
                # Extract the annotations from the document block
                $all_annotations =
                    self::extractFromDocumentBlock($parent->getDocComment());

                # Remove the skipped annotations
                foreach($all_annotations as $annotation)
                {
                    # See if this is a skipped annotation
                    if (in_array($annotation->name, self::$ignoreList))
                    {
                        # Skip the annotation
                        continue;
                    }
                    
                    # Store the annotation
                    $annotations->class[] = $annotation;
                }
            }
                
            # Go through the public properties of the object
            foreach($r_object->getProperties(\ReflectionProperty::IS_PUBLIC)
                as $property)
            {
                # Create an entry in the properties array
                $annotations->properties[$property->name] = array();

                # Extract the annotations from the document block
                $all_annotations =
                    self::extractFromDocumentBlock($property->getDocComment());

                # Remove the skipped annotations
                foreach($all_annotations as $annotation)
                {
                    # See if this is a skipped annotation
                    if (in_array($annotation->name, self::$ignoreList))
                    {
                        # Skip the annotation
                        continue;
                    }

                    # Store the annotation
                    $annotations->properties[$property->name][] = $annotation;
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

                # Extract the annotations from the document block
                $all_annotations =
                    self::extractFromDocumentBlock($method->getDocComment());

                # Remove the skipped annotations
                foreach($all_annotations as $annotation)
                {
                    # See if this is a skipped annotation
                    if (in_array($annotation->name, self::$ignoreList))
                    {
                        # Skip the annotation
                        continue;
                    }

                    # Store the annotation
                    $annotations->methods[$method->name][] = $annotation;
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
