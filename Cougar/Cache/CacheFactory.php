<?php
namespace Cougar\Cache;

use Cougar\Exceptions\ServiceUnavailableException;

# Initialize the framework (disabled; should have been done by application)
#require_once(__DIR__ . "/../../cougar.php");

/**
 * The abstract methods getMemoryCache, getLocalCache and getApplicationCache
 * will return a Cache object either for a memory, local cache or the system's
 * specified application cache.
 *
 * The application cache is defined using the APPLICATION_CACHE_CONFIGURATION
 * constant (using hidef to define the constant is highly recommended). The
 * value of APPLICATION_CACHE_CONFIGURATION must specify a cache type and an
 * list of servers and ports for memcache and memcached. The possible values
 * are:
 *
 *   none
 *   memory
 *   local
 *   memcache server1[:port1] [server2[:port2] ...]
 *   memcached server1[:port1] [server2[:port2] ...]
 *
 * @history
 * 2013.09.30:
 *   (AT)  Initial release
 * 2014.08.06:
 *   (AT)  Add getMemoryCache()
 *   (AT)  Fixed problem when using memcached in configuration in
 *         getApplicationCache()
 *   (AT)  Documented the use of the APPLICATION_CACHE_CONFIGURATION constant
 *
 * @version 2014.08.06
 * @package Cougar
 * @license MIT
 *
 * @copyright 2013 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class CacheFactory
{
    /***************************************************************************
     * PUBLIC STATIC PROPERTIES AND METHODS
     **************************************************************************/

    /**
     * Returns a memory cache
     *
     * @history
     * 2014.08.06:
     *   (AT)  Initial implementation
     *
     * @version 2014.08.06
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @return \Cougar\Cache\Cache Memory cache object
     */
    static public function getMemoryCache()
    {
        if (self::$memoryCache === null)
        {
            self::$memoryCache = new Cache("memory");
        }

        return self::$memoryCache;
    }

    /**
     * Returns a local cache (APC, Wincache or memory)
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @return \Cougar\Cache\Cache Local cache object
     */
    static public function getLocalCache()
    {
        if (self::$localCache === null)
        {
            self::$localCache = new Cache("local");
        }
        
        return self::$localCache;
    }

    /**
     * Creates a new connection to the application cache. The application cache
     * configuration should be defined using the APPLICATION_CACHE_CONFIGURATION
     * constant. (See the documentation for the CacheFactory for details.)
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     * 2014.08.06:
     *   (AT)  Fixed problem when using memcached in configuration
     *   (AT)  Improve parsing of configuration constant
     *
     * @version 2014.08.06
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @return \Cougar\Cache\Cache Application cache object
     * @throws ServiceUnavailableException
     */
    static public function getApplicationCache()
    {
        if (self::$applicationCache === null)
        {
            # See if the application cache configuration is defined
            if (! defined("APPLICATION_CACHE_CONFIGURATION"))
            {
                # See if we are in a local or development environment
                if (! defined(ENVIRONMENT))
                {
                    self::$applicationCache = new Cache("local");
                }
                else
                {
                    if (ENVIRONMENT == "local" ||
                        ENVIRONMENT == "development")
                    {
                        self::$applicationCache = new Cache("local");
                    }
                    else
                    {
                        # Other environments must have cache configured
                        throw new ServiceUnavailableException(
                            "Application cache has not been configured");
                    }
                }
            }
            else
            {
                # Split the configuration on blank space
                $config = preg_split('/\s+/u', APPLICATION_CACHE_CONFIGURATION);
                
                # See which kind of cache we have
                switch(strtolower($config[0]))
                {
                    case "memcache":
                        $memcache = new \Memcache();
                        foreach(array_splice($config, 1) as $config)
                        {
                            $server = explode(":", $config);
                            if (array_key_exists(1, $server))
                            {
                                if ($server[1])
                                {
                                    $memcache->addServer($server[0],
                                        $server[1]);
                                }
                                else
                                {
                                    $memcache->addServer($server[0]);
                                }
                            }
                            else
                            {
                                $memcache->addServer($server[0]);
                            }
                        }
                        
                        # Create the cache object
                        self::$applicationCache = new Cache($memcache);
                        break;
                    case "memcached":
                        $memcached = new \Memcache();
                        foreach(array_splice($config, 1) as $config)
                        {
                            $server = explode(":", $config);
                            if (array_key_exists(1, $server))
                            {
                                if ($server[1])
                                {
                                    $memcached->addServer($server[0],
                                        $server[1]);
                                }
                                else
                                {
                                    $memcached->addServer($server[0]);
                                }
                            }
                            else
                            {
                                $memcached->addServer($server[0]);
                            }
                        }
                        
                        # Create the cache object
                        self::$applicationCache = new Cache($memcached);
                        break;
                    case "local":
                        self::$applicationCache = new Cache("local");
                        break;
                    case "memory":
                        self::$applicationCache = new Cache("memory");
                        break;
                    case "none":
                        self::$applicationCache = new Cache("nocache");
                        break;
                    default:
                        throw new ServiceUnavailableException(
                            "Invalid cache type: " . $config[0]);
                }
            }
        }
        
        return self::$applicationCache;
    }


    /***************************************************************************
     * PROTECTED STATIC PROPERTIES AND METHODS
     **************************************************************************/

    /**
     * @var Cache Memory cache reference
     */
    protected static $memoryCache = null;

    /**
     * @var Cache Local cache reference
     */
    protected static $localCache = null;
    
    /**
     * @var Cache Application cache reference
     */
    protected static $applicationCache = null;
}
?>
