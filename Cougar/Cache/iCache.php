<?php

namespace Cougar\Cache;

/**
 * This class implements a multi-purpose caching system that can automatically
 * use an existing memcache or memcached object, the APC cache, WinCache or
 * a built-in array-based cache. The cache type is automatically detected at
 * runtime, or can be specified manually.
 * 
 * The purpose of this class it to provide a caching solution to applications
 * and Web Services clients so that they store information locally. The class
 * abstracts the solution allowing for a zero-configuration solution.
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
interface iCache
{
	/**
	 * Initializes the cache object. You may pass a Memcache or Memcahced object
	 * as the type to use a previously configured Memcache(d) object.
	 * 
	 * Supported types:
	 *   Memcached (pass object)
	 *   Memcache (pass object)
	 *   APC
	 *   WinCache
	 *   Memory (array-based internal cache, where n specifices the maximum
	 *           maximum number objects to cache - defaults to 250)
	 *   NoCache (fake cache; never caches anything)
	 * 
	 * You may optionally specify a key prefix that will be automatically
	 * attached to all keys (for example, a prefix of "my.cache." will turn a
	 * key of "value1" top "my.cache.value1"). This is useful to avoid naming
	 * collisions.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @param mixed type Cache type or resrouce id for memcache(d)
	 * @param string key_prefix Prefix to add to the key (optional)
	 */
	public function __construct($type = "auto", $key_prefix = "");
	
	/**
	 * Returns the type of cache in use
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @return string Cache type
	 */
	public function getCacheType();
	
	/**
	 * Returns the size of the in-memory cache size
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @return int Cache size
	 */
	public function getMemoryCacheSize();
	
	/**
	 * Sets the size of the in-memory cache size in number of elements
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @param int size The new cache size
	 */
	public function setMemoryCacheSize($size);
	
	/**
	 * Stores an item in the cache
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @param string key The key used to identify the entry
	 * @param string value The value to store
	 * @param int expiration The entry's time-to-live
	 * @return bool success
	 */
	public function set($key, $value, $expiration = null);

	/**
	 * Gets an item from the cache
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @param string key The key used to identify the entry
	 * @return mixed value
	 */
	public function get($key);

	/**
	 * Clears an item from the cache
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @param string key The key used to identify the entry
	 * @return mixed value
	 */
	public function clear($key);
}
?>
