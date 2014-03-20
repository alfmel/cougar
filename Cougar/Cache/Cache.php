<?php

namespace Cougar\Cache;

use Cougar\Exceptions\Exception;

# Initialize the framework (disabled; should have been done by application)
#require_once(__DIR__ . "/../../cougar.php");

/**
 * This class implements a multi-purpose caching system that uses an existing
 * memcache or memcached object, the APC cache, WinCache or an in-memory
 * cache. The cache type is automatically detected at runtime, or can be
 * specified manually.
 *
 * @history
 * 2013.09.30:
 *   (AT)  Initial release
 * 2014.03.19:
 *   (AT)  Fix typo where type was being accessed incorrectly
 *
 * @version 2014.03.19
 * @package Cougar
 * @license MIT
 *
 * @copyright 2013 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 *
 * @todo: Implement cache groups
 */
class Cache implements iCache
{
    /**
     * Initializes the cache object. You may pass a Memcache or Memcached object
     * as the type to use a previously configured Memcache(d) object.
     *
     * Supported types:
     *   Memcached (pass object)
     *   Memcache (pass object)
     *   APC
     *   WinCache
     *   Memory (stored internally in the Cache object)
     *   NoCache (fake cache; never caches anything)
     *
     * You may optionally specify a key prefix that will be automatically
     * attached to all keys (for example, a prefix of "my.cache." will turn a
     * key of "value1" top "my.cache.value1"). This is useful if you need to
     * avoid key collisions.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param mixed $type
     *   Cache type or resource id for memcache(d)
     * @param string $key_prefix
     *   Prefix to add to the key (optional)
     * @throws \Cougar\Exceptions\Exception
     */
    public function __construct($type = "auto", $key_prefix = "")
    {
        if (is_object($type))
        {
            switch (get_class($type))
            {
                case "Memcached":
                    $this->type = "memcached";
                    $this->Memcached = $type;
                    break;
                case "Memcache":
                    $this->type = "memcache";
                    $this->Memcache = $type;
                    break;
                default:
                    throw new Exception("Unsupported object type: " .
                        get_class($type));
                    break;
            }
        }
        else
        {
            # Figure out what we are using
            switch (strtolower($type))
            {
                case "apc":
                    # Make sure APC is available
                    if (! extension_loaded("apc"))
                    {
                        throw new Exception("APC is not enabled");
                    }
                    $this->type = "apc";
                    break;
                case "wincache":
                    if (! extension_loaded("wincache"))
                    {
                        throw new Exception("WinCache is not enabled");
                    }
                    $this->type = "wincache";
                    break;
                case "memory":
                    $this->type = "memory";
                    break;
                case "nocache":
                    $this->type = "nocache";
                    break;
                case "local":
                case "auto":
                    # See if APC is enabled
                    if (extension_loaded("apc"))
                    {
                        $this->type = "apc";
                    }
                    else if (extension_loaded("wincache"))
                    {
                        $this->type = "wincache";
                    }
                    else
                    {
                        $this->type = "memory";
                    }
                    break;
                default:
                    throw new Exception("Unsupported cache type: " . $type);
            }
        }
        
        # Store the key prefix
        if ($key_prefix)
        {
            # See if we need to add a period to the end of the prefix
            if (substr($key_prefix, -1) == ".")
            {
                $this->keyPrefix = (string) $key_prefix;
            }
            else
            {
                $this->keyPrefix = (string) $key_prefix . ".";
            }
        }
    }


    /***************************************************************************
    PUBLIC PROPERTIES AND METHODS
    ***************************************************************************/
    
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
    public function getCacheType()
    {
        return $this->type;
    }
    
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
    public function getMemoryCacheSize()
    {
        return $this->memoryCacheSize;
    }
    
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
     * @param int $size
     *   The new cache size
     */
    public function setMemoryCacheSize($size)
    {
        # Make sure the value is valid
        $size = (int) $size;
        
        if ($size < 0)
        {
            $size = 0;
        }
        
        $this->memoryCacheSize = $size;
        
        # Resize the cache
        $size_diff = count($this->memoryCache) - $size;
        for ($i = 0; $i < $size_diff; $i++)
        {
            array_shift($this->memoryCache);
        }
    }
    
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
     * @param string $key
     *   The key used to identify the entry
     * @param string
     *   $value The value to store
     * @param int
     *   $expiration The entry's time-to-live
     * @return bool success
     */
    public function set($key, $value, $expiration = null)
    {
        # See which type of key we are using
        switch ($this->type)
        {
            case "memcached":
                try
                {
                    if ($expiration === null)
                    {
                        return $this->Memcached->set($this->keyPrefix . $key,
                            $value);
                    }
                    else
                    {
                        return $this->Memcached->set($this->keyPrefix . $key,
                            $value, $expiration);
                    }
                }
                catch (\Exception $e)
                {
                    return false;
                }
                break;
            case "memcache":
                try
                {
                    if ($expiration === null)
                    {
                        return $this->Memcache->set($this->keyPrefix . $key,
                            $value, MEMCACHE_COMPRESSED);
                    }
                    else
                    {
                        return $this->Memcache->set($this->keyPrefix . $key,
                            $value, MEMCACHE_COMPRESSED, $expiration);
                    }
                }
                catch (\Exception $e)
                {
                    return false;
                }
                break;
            case "apc":
                try
                {
                    if ($expiration === null)
                    {
                        return apc_store($this->keyPrefix . $key, $value);
                    }
                    else
                    {
                        return apc_store($this->keyPrefix . $key, $value,
                            $expiration);
                    }
                }
                catch (\Exception $e)
                {
                    return false;
                }
                break;
            case "wincache":
                try
                {
                    if ($expiration === null)
                    {
                        return wincache_ucache_set($this->keyPrefix . $key,
                            $value);
                    }
                    else
                    {
                        return wincache_ucache_set($this->keyPrefix . $key,
                            $value, $expiration);
                    }
                }
                catch (\Exception $e)
                {
                    return false;
                }
                break;
            case "memory":
                # See if we need to make room in the array
                if (count($this->memoryCache) >= $this->memoryCacheSize)
                {
                    array_shift($this->memoryCache);
                }
                
                # See if the element already exists
                if (array_key_exists($key, $this->memoryCache))
                {
                    # Remove this element (LRU)
                    unset($this->memoryCache[$key]);
                }
                
                # Store the value
                if ($expiration)
                {
                    $expiration = (int) $expiration;
                    if ($expiration > 2592000)
                    {
                        # Store the expiration as a timestamp
                        $this->memoryCache[$key] = array("exp" => $expiration,
                            "value" => $value);
                    }
                    else
                    {
                        # Store it as an offset of current time
                        $this->memoryCache[$key] = array(
                            "exp" => time() + $expiration,
                            "value" => $value);
                    }
                }
                else
                {
                    $this->memoryCache[$key] = array("exp" => $expiration,
                        "value" => $value);
                }
                
                return true;
                break;
            default:
                # Nocache and others, pretend to store the value only
                return false;
                break;
        }
    }
    
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
     * @param string $key
     *   The key used to identify the entry
     * @return mixed value
     */
    public function get($key)
    {
        # See which type of key we are using
        switch ($this->type)
        {
            case "memcached":
                try
                {
                    return $this->Memcached->get($this->keyPrefix . $key);
                }
                catch (\Exception $e)
                {
                    return false;
                }
                break;
            case "memcache":
                try
                {
                    return $this->Memcache->get($this->keyPrefix . $key,
                        MEMCACHE_COMPRESSED);
                }
                catch (\Exception $e)
                {
                    return false;
                }
                break;
            case "apc":
                try
                {
                    return apc_fetch($this->keyPrefix . $key);
                }
                catch (\Exception $e)
                {
                    return false;
                }
                break;
            case "wincache":
                try
                {
                    return wincache_ucache_get($this->keyPrefix . $key);
                }
                catch (\Exception $e)
                {
                    return false;
                }
                break;
            case "memory":
                # See if the value exists
                if (! array_key_exists($key, $this->memoryCache))
                {
                    return false;
                }
                
                # See if the value is expired
                if ($this->memoryCache[$key]["exp"] === null ||
                    $this->memoryCache[$key]["exp"] > time())
                {
                    return $this->memoryCache[$key]["value"];
                }
                else
                {
                    return false;
                }
                break;
            default:
                # Nocache and others, pretend to store the value only
                return false;
                break;
        }
    }
    
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
     * @param string $key
     *   The key used to identify the entry
     * @return bool Success
     */
    public function clear($key)
    {
        # See which type of key we are using
        switch ($this->type)
        {
            case "memcached":
                try
                {
                    return $this->Memcached->delete($this->keyPrefix . $key);
                }
                catch (\Exception $e)
                {
                    return false;
                }
                break;
            case "memcache":
                try
                {
                    return $this->Memcache->delete($this->keyPrefix . $key);
                }
                catch (\Exception $e)
                {
                    return false;
                }
                break;
            case "apc":
                try
                {
                    return apc_delete($this->keyPrefix . $key);
                }
                catch (\Exception $e)
                {
                    return false;
                }
                break;
            case "wincache":
                try
                {
                    return wincache_ucache_clear($this->keyPrefix . $key);
                }
                catch (\Exception $e)
                {
                    return false;
                }
                break;
            case "memory":
                if (array_key_exists($key, $this->memoryCache))
                {
                    unset ($this->memoryCache[$key]);
                    return true;
                }
                else
                {
                    return false;
                }
            default:
                # Nocache and others, pretend to store the value only
                return false;
                break;
        }
    }


    /***************************************************************************
    PROTECTED PROPERTIES AND METHODS
    ***************************************************************************/

    /**
     * @var string The cache type in use
     */
    protected $type = null;

    /**
     * @var string Cache key prefix
     */
    protected $keyPrefix = "";

    /**
     * @var \Memcache Memcache object reference
     */
    protected $memcache = null;

    /**
     * @var \Memcached Memcached object reference
     */
    protected $memcached = null;

    /**
     * @var array Memory cache
     */
    protected $memoryCache = array();

    /**
     * @var int Memory cache size
     */
    protected $memoryCacheSize = 250;
}
?>
