<?php

namespace Cougar\Autoload;

/**
 * This class implements a PSR-0 compliant autoloader. It is used by the
 * Cougar framework to load its classes, interfaces and traits.
 *
 * To learn more about PSR-0, a standard by the PHP Framework Interop Group
 * (PHP_FIG), see:
 *
 *   http://www.php-fig.org/psr/0/
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

class Autoload
{
    /***********************************************************************
     * PUBLIC STATIC PROPERTIES AND METHODS
     **********************************************************************/

    /**
     * @var bool Whether the Autoload routine has been registered
     */
    public static $registered = false;

    /**
     * Stores the framework's root directory and registers the
     * spl_autoload method.
     *
     * @history:
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string $cougar_root_dir
     *  Path to the cougar root directory
     */
    public static function register($cougar_root_dir)
    {
        # Store the framework root directory
        # TODO: Should we make sure the path is valid?
        self::$frameworkRoot = $cougar_root_dir;

        # Register the autoload function
        spl_autoload_register(__NAMESPACE__ . "\\Autoload::splAutoload",
            true);

        # Declare the autoload function registered externally
        self::$registered = true;
    }

    /**
     * Implements an spl_autoload function that will attempt to load
     * a file based on the rules of PSR-0.
     *
     * PSR-0 states that any namespace separator (\) or underscore should
     * be converted into a directory separator. The layout of the files
     * must therefore reflect the namespace of the files. Additionally,
     * the file must be named after the name of the class and have a .php
     * extension.
     *
     * @history:
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string $class_name
     *   Name of the class to load (including namespace)
     */
    public static function splAutoload($class_name)
    {
        # Replace the namespace separators and underscores with the directory
        # separator, and add the .php extension
        $class_path =  str_replace(array("\\", "_"),
                array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR), $class_name) .
            ".php";

        # See if the file exists in the Cougar directory
        if (file_exists(self::$frameworkRoot . DIRECTORY_SEPARATOR .
            $class_path))
        {
            # Load the file
            include(self::$frameworkRoot . DIRECTORY_SEPARATOR . $class_path);
        }
        else
        {
            # See if we can find the file based on the include path
            $full_class_path = stream_resolve_include_path($class_path);

            if ($full_class_path)
            {
                # Include the file
                include($full_class_path);
            }
        }
    }


    /***********************************************************************
     * PROTECTED STATIC PROPERTIES
     **********************************************************************/

    /**
     * @var string Root directory to the Cougar framework
     */
    protected static $frameworkRoot = null;
}
?>
