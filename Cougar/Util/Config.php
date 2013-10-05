<?php

namespace Cougar\Util;

use Cougar\Cache\CacheFactory;
use Cougar\Exceptions\ConfigurationFileNotFoundException;

# Initialize the framework
require_once("cougar.php");

/**
 * Loads configuration files and provides methods to extract individual values.
 * The config files need to be in name = value format. Names and values may
 * contain spaces, but they will be trimmed to remove leading and trailing
 * whitespace. Lines will be ignored after a hash mark (#) and those will
 * be considered comments.
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
class Config implements iConfig
{
    /**
     * Loads the given config file
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @todo: check if the file has been modified since last cached
     *
     * @param string $config_file
     *   Filename of the config file to load; may include full or relative path
     * @throws \Cougar\Exceptions\ConfigurationFileNotFoundException
     */
    public function __construct($config_file)
    {
        # Get a local cache
        $local_cache = CacheFactory::getLocalCache();
        
        # Create the hash of the calling function
        foreach(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4) as
            $key => $caller)
        {
            # Skip ourselves
            if ($key == 0)
            {
                continue;
            }
            
            # Make sure this is not a factory method
            if (array_key_exists("class", $caller))
            {
                if (strpos($caller["class"], "Factory") !== false)
                {
                    continue;
                }
            }
            
            # We found the call we need
            break;
        }
        
        # Create the cache key for this call
        if (! array_key_exists("file", $caller))
        {
            $caller["file"] = "(Unknown)";
        }
        $call_cache_key = self::$cachePrefix . ".caller." .
            md5($caller["file"] . ":" . $caller["function"]) . "." .
            $config_file;
        
        # See if we have the filename in the cache
        $file = $local_cache->get($call_cache_key);
        
        if ($file === false)
        {
            # See if config file name has an absolute path
            $filename = "";
            if (substr($config_file, 0, 1) !== "/")
            {
                # Find the file
                foreach(self::$subdirList as $dir)
                {
                    $filename = stream_resolve_include_path(
                        $dir . DIRECTORY_SEPARATOR . $config_file);
                    if ($filename)
                    {
                        break;
                    }
                }
            }
            else
            {
                # See if the file exists
                if (file_exists($config_file))
                {
                    $filename = $config_file;
                }
            }

            # Make sure the file exists
            if (! $filename)
            {
                throw new ConfigurationFileNotFoundException(
                    "File does not exist: " . $config_file);
            }
            
            # Create the array with the file information
            $file["filename"] = $filename;
            $file["time"] = time();
            
            # Store the filename in the cache
            $local_cache->set($call_cache_key, $file);
        }
        
        # See if the contents of the file are in the cache
        $file_cache_key = self::$cachePrefix . ".file." . $file["filename"];
        $values = $local_cache->get($file_cache_key);
        
        # See if we need to reload the file
        $reload = false;
        if ($values === false)
        {
            $reload = true;
        }
        else
        {
            if (filemtime($file["filename"] > $file["time"]))
            {
                $reload = true;
            }
        }
        
        if ($reload)
        {
            # TODO: Block access to system files
            
            # Get the file, line by line
            $lines = file($file["filename"],
                FILE_IGNORE_NEW_LINES + FILE_SKIP_EMPTY_LINES);

            # Go through each line
            $values = array();
            foreach($lines as $line)
            {
                # See if we have a hash
                $hash_pos = strpos($line, "#");
                if ($hash_pos !== false)
                {
                    # Remove the comment
                    $line = substr($line, 0, $hash_pos);
                }

                # Trim the line
                $line = trim($line);

                # See if we have any contents
                if ($line)
                {
                    # Split on the equal sign
                    $split_line = explode("=", $line, 2);

                    # See how many values we have
                    if (count($split_line) == 2)
                    {
                        # Save the name and the value
                        $values[trim($split_line[0])] = trim($split_line[1]);
                    }
                    else
                    {
                        # Save the name only
                        $values[trim($split_line[0])] = "";
                    }
                }
            }
            
            # Store the entry in the cache
            $local_cache->set($file_cache_key, $values);
        }
        
        # Save the values in the protected property
        $this->values = $values;
    }


    /***************************************************************************
     * PUBLIC STATIC PROPERTIES
     **************************************************************************/

    /**
     * @var string Cache prefix
     */

    public static $cachePrefix = "cougar.config";

    /**
     * @var string Cache time
     */
    public static $cacheTime = 86400;

    /**
     * @var array List of subdirectories to search
     */
    public static $subdirList = array(
        "conf",
        "config",
        "etc",
        "db",
        "."
    );


    /***************************************************************************
     * PUBLIC PROPERTIES AND METHODS
     **************************************************************************/
    
    /**
     * Returns an array with all name/value pairs.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @return array All configuration values in the file
     */
    public function values()
    {
        return $this->values;
    }
    
    /**
     * Returns the value associated with the given name. If the name does not
     * exists, null will be returned
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param string $name The setting name
     * @return string Value of the config setting
     */
    public function value($name)
    {
        if (array_key_exists($name, $this->values))
        {
            return $this->values[$name];
        }
        else
        {
            return null;
        }
    }
    
    
    /***************************************************************************
     * PROTECTED PROPERTIES AND METHODS
     **************************************************************************/
    
    /**
     * @var array Configuration values
     */
    protected $values = array();
}
?>
