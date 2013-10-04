<?php

namespace Cougar\UnitTests\Cache;

use Cougar\Cache\Cache;

require_once(__DIR__ . "/../../../cougar.php");

/**
 * Test class for Cache.
 * Generated by PHPUnit on 2012-10-10 at 15:59:06.
 */
class CacheTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		# Nothing to set up
	}

	/**
	 * @covers \Cougar\Cache\Cache::set
	 * @covers \Cougar\Cache\Cache::get
	 * @covers \Cougar\Cache\Cache::clear
	 * @author Alberto Trevino <alberto@byu.edu>
	 */
	public function testCacheMemcached()
	{
		$memcached = new \Memcached();
		$memcached->addServer("localhost", "11211");
		
		$cache = new Cache($memcached);
		
		$this->assertEquals($cache->getCacheType(), "memcached");
		$this->assertEquals($cache->set("UnitTest", "Sample value", 5), true);
		$this->assertEquals($cache->get("UnitTest"), "Sample value");
		$this->assertEquals($cache->clear("UnitTest"), true);
		$this->assertEquals($cache->get("UnitTest"), false);
	}

	/**
	 * @covers \Cougar\Cache\Cache::set
	 * @covers \Cougar\Cache\Cache::get
	 * @covers \Cougar\Cache\Cache::clear
	 * @author Alberto Trevino <alberto@byu.edu>
	 */
	public function testCacheMemcache()
	{
		$memcache = new \Memcache();
		$memcache->connect("localhost", "11211");
		
		$cache = new Cache($memcache);
		
		$this->assertEquals($cache->getCacheType(), "memcache");
		$this->assertEquals($cache->set("UnitTest", "Sample value", 5), true);
		$this->assertEquals($cache->get("UnitTest"), "Sample value");
		$this->assertEquals($cache->clear("UnitTest"), true);
		$this->assertEquals($cache->get("UnitTest"), false);
	}

	/**
	 * @covers \Cougar\Cache\Cache::set
	 * @covers \Cougar\Cache\Cache::get
	 * @covers \Cougar\Cache\Cache::clear
	 * @author Alberto Trevino <alberto@byu.edu>
	 */
	public function testCacheDefault()
	{
		$cache = new Cache();
		
		# Value can be APC or WinCache; simply ignore.
		#$this->assertEquals($cache->getCacheType(), "apc");
		$this->assertEquals($cache->set("UnitTest", "Sample value", 5), true);
		$this->assertEquals($cache->get("UnitTest"), "Sample value");
		$this->assertEquals($cache->clear("UnitTest"), true);
		$this->assertEquals($cache->get("UnitTest"), false);
	}
	
	/**
	 * @covers \Cougar\Cache\Cache::set
	 * @covers \Cougar\Cache\Cache::get
	 * @covers \Cougar\Cache\Cache::clear
	 * @author Alberto Trevino <alberto@byu.edu>
	 */
	public function testCacheMemory()
	{
		$cache = new Cache("memory");
		$this->assertEquals($cache->getCacheType(), "memory");
		$this->assertEquals($cache->set("UnitTest", "Sample value", 5), true);
		$this->assertEquals($cache->get("UnitTest"), "Sample value");
		$this->assertEquals($cache->clear("UnitTest"), true);
		$this->assertEquals($cache->get("UnitTest"), false);
		
		# Test expirations
		$this->assertEquals($cache->set("UnitTest", "Sample value", 1), true);
		sleep(2);
		$this->assertEquals($cache->get("UnitTest"), false);
		
		# Test evictions
		$this->assertEquals($cache->set("UnitTest", "Sample value", 15), true);
		for ($i = 0; $i < $cache->getMemoryCacheSize(); $i++)
		{
			$this->assertEquals($cache->set("UnitTest" . $i,
				"Sample value " . $i, 15), true);
		}
		$this->assertEquals($cache->get("UnitTest"), false);
	}
	
	/**
	 * @covers \Cougar\Cache\Cache::set
	 * @covers \Cougar\Cache\Cache::get
	 * @covers \Cougar\Cache\Cache::clear
	 * @author Alberto Trevino <alberto@byu.edu>
	 */
	public function testCacheNoCache()
	{
		$cache = new Cache("nocache");
		
		$this->assertEquals($cache->getCacheType(), "nocache");
		$this->assertEquals($cache->set("UnitTest", "Sample value", 5), false);
		$this->assertEquals($cache->get("UnitTest"), false);
		$this->assertEquals($cache->clear("UnitTest"), false);
		$this->assertEquals($cache->get("UnitTest"), false);
	}
}
?>
