<?php
namespace Cougar\Cache;

use Cougar\Exceptions\ServiceUnavailableException;

# Initialize the framework
require_once("cougar.php");

/**
 * The abstract methods getLocalCache and getApplicationCache will return a 
 * Cache object either for a local cache or the system's specified Memcache
 * application cache.
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
class CacheFactory
{
	/***************************************************************************
	 * PUBLIC STATIC PROPERTIES AND METHODS
	 **************************************************************************/
	
	/**
	 * Returns a local cache (APC, Wincache or internal)
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
     * constant.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
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
				$config = explode(" ", APPLICATION_CACHE_CONFIGURATION);
				
				# See which kind of cache we have
				switch($config[0])
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
						self::$applicationCache = new Cache($memcache);
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
	 * @var Cache Local cache reference
	 */
	protected static $localCache = null;
	
	/**
	 * @var Cache Application cache reference
	 */
	protected static $applicationCache = null;
}
?>
