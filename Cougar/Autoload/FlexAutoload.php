<?php

namespace Cougar\Autoload;

use Cougar\Cache\CacheFactory;
use Cougar\Exceptions\Exception;

/**
 * The FlexAutoload class provides a more flexible autoloader than the PSR-0
 * specification. By providing a directory, the FlexAutoload class will scan
 * the directory and its subdirectories for PHP files and extract namespaces,
 * classes, interfaces and traits. It will then map the class/interface/trait
 * names to the files where they are located. When an autoload request comes
 * through, FlexAutoload will look map the class name to the file where it is
 * defined and include the file.
 *
 * Since looking for classes in PHP source files is very processor intensive,
 * FlexAutoload will cache the results in a local cache so that it does not
 * have to scan the filesystem every time.
 *
 * If a class in the given namespace is not found or the file no longer
 * exists, FlexAutoload will rescan the filesystem. Be aware that this may
 * have a huge performance hit if a class does not exist in the given
 * namespace or if it exists in another directory that has not been explicitly
 * declared. To help find these errors you may log scanning requests by
 * setting the logRescan property to true.
 *
 * @history
 * 2013.09.30:
 *   (AT)  Initial release
 * 2013.11.13:
 *   (AT)  Improve the autoload rescan heuristics to find moved files
 *   (AT)  Fix bug where new files did not trigger a rescan and were therefore
 *         not found
 *
 * @version 2013.09.30
 * @package Cougar
 * @license MIT
 *
 * @copyright 2013 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class FlexAutoload
{
    /***********************************************************************
     * PUBLIC STATIC PROPERTIES AND METHODS
     **********************************************************************/

    /**
     * A list (array) of directories that will be excluded from the class
     * search routine. Note the hidden directories (those starting with a
     * single period) are always excluded.
     *
     * @var array
     */
    public static $excludePaths =
        array("bin", "conf", "examples", "nbproject", "unit_tests");

    /**
     * @var string Cache prefix
     */
    public static $cachePrefix = "Cougar.FlexAutoload";

    /**
     * @var int Cache time (infinite by default)
     */
    public static $cacheTime = 0;

    /**
     * Adds the given path to the autoload mechanism and optionally to the
     * include path.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     * 2014.02.10:
     *   (AT)  Add the application path to the beginning of the array, or after
     *         the . entry. That way config files will be loaded from the
     *         current directory, or the application directory before other
     *         global files are loaded.
     *
     * @version 2014.02.10
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string $path
     *   The path to add. This may be the root path of the application, or a
     *   subdirectory of the application. If it is a subdirectory you should
     *   specify the depth.
     * @param int $depth
     *   The number of subdirectory levels from the application root directory
     *   where the path is located. By specifying the current directory of the
     *   file (for example, using the __DIR__ constant) and the depth you will
     *   avoid unecessary calls to dirname() to resolve the path to the
     *   application root.
     * @param bool $add_to_include_path
     *   Whether to append the path to the include_path
     */
    public static function addPath($path, $depth = 0,
        $add_to_include_path = true)
    {
        if (in_array($path, self::$paths))
        {
            # We already have this path;
            return;
        }

        # Resolve the application path
        for ($i = $depth; $i > 0; $i--)
        {
            $path = dirname($path);
        }

        # See if we already have this path
        if (! in_array($path, self::$paths))
        {
            self::initializeClassMap($path);

            # See if we are adding the path to the include_path
            if ($add_to_include_path)
            {
                # Get the parts of the include path
                $include_path = get_include_path();
                $include_path_array =
                    explode(PATH_SEPARATOR, $include_path);

                # Check if the path is already part of the include path
                if (! in_array($path, $include_path_array))
                {
                    # See where the . entry is
                    $dot_index = array_search(".", $include_path_array);

                    if ($dot_index === false)
                    {
                        # Add this path to the start of the include path
                        set_include_path($path . PATH_SEPARATOR .
                            $include_path);
                    }
                    else
                    {
                        array_splice($include_path_array, $dot_index + 1, 0,
                            array($path));
                        set_include_path(implode(PATH_SEPARATOR,
                            $include_path_array));
                    }
                }
            }
        }

        # See if the splAutoload function has been registered
        if (! self::$registered)
        {
            # Register this autoloader
            spl_autoload_register(__NAMESPACE__ .
                "\\FlexAutoload::splAutoload");

            self::$registered = true;
        }
    }

    /**
     * Implements an spl_autoload function which will see if the given
     * class name is in the class map. If it is, then the file will be
     * loaded.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     * 2013.11.13:
     *   (AT)  Make sure we re-initialize the class map if the file we are
     *         trying to load does not exist (for example, has moved or has been
     *         deleted)
     *   (AT)  Search the namespace hierarchy when looking for a class
     *
     * @version 2013.11.13
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @var string $class_name
     *   Name of class to load
     */
    public static function splAutoload($class_name)
    {
        # See if this class is in the class map
        $class_exists = false;
        if (array_key_exists($class_name, self::$classMap))
        {
            # Make sure the file exists
            if (file_exists(self::$classMap[$class_name]))
            {
                $class_exists = true;
            }
        }

        # See if we need to look for classes again
        if (! $class_exists)
        {
            # Break up the namespace hierarchy
            $namespace_hierarchy = explode("\\", $class_name);

            # Go through the namespace hierarchy, from most specific to least
            while (count($namespace_hierarchy) > 0 && ! $class_exists)
            {
                $namespace = implode("\\", $namespace_hierarchy);

                # See if we have directory entries for this namespace
                if (array_key_exists($namespace, self::$namespaceMap))
                {
                    # Go through each directory that has this namespace
                    foreach(self::$namespaceMap[$namespace] as $path)
                    {
                        # See if this path has been rebuilt
                        if (! array_key_exists($path, self::$rebuiltPaths))
                        {
                            # Rebuild the classMap
                            self::initializeClassMap($path, true);

                            # Mark the path as rebuilt
                            self::$rebuiltPaths[$path] = true;
                        }
                    }

                    # Check again if this class is in the class map
                    if (array_key_exists($class_name, self::$classMap))
                    {
                        # Make sure the file exists
                        if (file_exists(self::$classMap[$class_name]))
                        {
                            $class_exists = true;
                        }
                    }
                }

                # Remove the last part of the element
                array_pop($namespace_hierarchy);
            }
        }

        # Attempt to load the file
        if ($class_exists)
        {
            include(self::$classMap[$class_name]);
        }
    }


    /***********************************************************************
     * PROTECTED STATIC PROPERTIES AND METHODS
     **********************************************************************/

    /**
     * @var bool Whether the autoload method has been registered
     */
    protected static $registered = false;

    /**
     * @var array Paths that are loaded into this autoload mechanism
     */
    protected static $paths = array();

    /**
     * @var array Class/file mappings
     */
    protected static $classMap = array();

    /**
     * @var array Namespace/path mappings
     */
    protected static $namespaceMap = array();

    /**
     * @var array Paths that have been rebuilt
     */
    protected static $rebuiltPaths = array();

    /**
     * Initializes the class map, either by loading it from the cache or
     * rebuilding it from the code.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     * 2013.11.13:
     *   (AT)  Actually build the namespace map
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * @author (JPK) Jillian Koontz, Brigham Young Univ. <jpkoontz@gmail.com>
     *
     * @param string $path
     *   Path to build from
     * @param bool $rescan
     *   Whether to ignore the cache entries and rescan the path
     * @throws \Cougar\Exceptions\Exception
     */
    protected static function initializeClassMap($path, $rescan = false)
    {
        # Make sure the path exists
        if (! is_dir($path))
        {
            throw new Exception("Cannot initialize class map: " . $path .
                " is not a valid directory");
        }

        # Get a new local cache
        $cache = CacheFactory::getLocalCache();

        # Define the cache key
        $key = self::$cachePrefix . ":" . $path;

        # Get the value from the cache
        $cached_maps = false;
        if (! $rescan)
        {
            $cached_maps = $cache->get($key);
        }

        # See if we got anything from the cache
        if (! $cached_maps)
        {
            # Get the list of PHP scripts in the given directory
            $file_list = array();
            $dir_list = array();
            self::findPhpScripts($path, $file_list, $dir_list);

            # Get the classes and namespaces
            $classes = array();
            $namespaces = array();
            self::extractClasses($file_list, $path, $classes, $namespaces);

            # Prepare the cached class map values
            $cached_maps = array("classes" => $classes,
                "directories" => $dir_list,
                "namespaces" => $namespaces);

            # Store the class map and directory list in the cache
            $cache->set($key, $cached_maps, self::$cacheTime);
        }

        # Merge the results
        self::$classMap = array_merge(self::$classMap, $cached_maps["classes"]);

        # Add the path to the path list
        self::$paths = array_merge(self::$paths, $cached_maps["directories"]);

        # Merge the namespaces
        foreach($cached_maps["namespaces"] as $namespace => $namespace_path)
        {
            if (array_key_exists($namespace, self::$namespaceMap))
            {
                if (! in_array($namespace_path,
                    self::$namespaceMap[$namespace]))
                {
                    self::$namespaceMap[$namespace][] = $namespace_path;
                }
            }
            else
            {
                self::$namespaceMap[$namespace] = array($namespace_path);
            }
        }
    }

    /**
     * Extracts and namespaces, classes and interfaces in the given list of
     * PHP scripts, returning a mapping of class names to files.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param array $file_list
     *   List of PHP files
     * @param string $path
     *   Path to associate with namespaces
     * @param array $class_map
     *   Reference to class_map array
     * @param array $namespace_map
     *   Reference to namespace_map array
     */
    protected static function extractClasses(array $file_list, $path,
        array &$class_map, array &$namespace_map)
    {
        foreach($file_list as $file)
        {
            try
            {
                # Get the PHP code
                $code = php_strip_whitespace($file);

                # Extract the namespace
                $matches = array();
                preg_match_all("/namespace ([A-Za-z0-9\\\\_]+);/", $code,
                    $matches);
                if (count($matches) == 2)
                {
                    if (count($matches[1]) > 0)
                    {
                        $namespace = $matches[1][0];
                        $namespace_map[$namespace] = $path;
                    }
                    else
                    {
                        $namespace = "";
                    }
                }
                else
                {
                    $namespace = "";
                }

                # Extract the classes and interfaces
                preg_match_all("/(class|interface|trait) ([A-Za-z0-9_]+)/",
                    $code, $matches);
                if (count($matches) == 3)
                {
                    # Go through the results
                    foreach($matches[2] as $class)
                    {
                        # Add the mapping
                        if ($namespace)
                        {
                            $class_map[$namespace . "\\" . $class] = $file;
                        }
                        else
                        {
                            $class_map[$class] = $file;
                        }
                    }
                }
            }
            catch (\Exception $e)
            {
                # Ignore the error (do we need to do something better here?)
            }
        }
    }

    /**
     * Gets all the PHP files in the given directory and subdirectories
     * adding them to the given file list and directory list. Subdirectories
     * in the excludePaths property will be ignored.
     *
     * The file and directory lists are passed as references, which means
     * the method will return the results in the provided lists, rather than
     * as a return value of the method.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string $directory
     *   The directory to look in
     * @param array $file_list
     *   Reference to PHP file list
     * @param array $directory_list
     *   Reference to subdirectory list
     * @param bool $first
     *   Whether this is the first iteration
     */
    protected static function findPhpScripts($directory, array &$file_list,
        &$directory_list, $first = true)
    {
        # Get the PHP scripts in the given directory
        $php_files = glob($directory . DIRECTORY_SEPARATOR . "*.php");

        # Go through the list
        foreach($php_files as $php_file)
        {
            if (is_file($php_file))
            {
                # Add it to the list
                $file_list[] = $php_file;
            }
        }

        # See if this subdirectory needs to be added
        if (count($php_files) > 0 || $first)
        {
            $directory_list[] = $directory;
        }

        # Get the subdirectories
        $subdirectories =
            glob($directory . DIRECTORY_SEPARATOR . "*", GLOB_ONLYDIR);

        if (count($subdirectories) > 0)
        {
            # Create a list of excluded directories
            $excluded_directories = array();
            foreach(self::$excludePaths as $exclusion)
            {
                $excluded_directories[] = $directory .
                    DIRECTORY_SEPARATOR . $exclusion;
            }

            # Remove exclusions from the subdirectories
            $subdirectory_list = array_diff($subdirectories,
                $excluded_directories);

            # Get the PHP files and subdirectories in the subdirectories
            foreach($subdirectory_list as $subdirectory)
            {
                self::findPhpScripts($subdirectory, $file_list, $directory_list,
                    false);
            }
        }
    }
}
?>
