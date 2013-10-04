<?php

namespace Cougar\UnitTests\Util;

use Cougar\Util\StringObfuscator;

require_once(__DIR__ . '/../../../cougar.php');

/**
 * Test class for StringObfuscator.
 * Generated by PHPUnit on 2012-07-18 at 17:54:38.
 */
class StringObfuscatorTest extends \PHPUnit_Framework_TestCase 
{
	protected $strings = array();
	protected $obfuscatedStrings = array();

	/**
	 * Defines the list of strings that will be encoded and decoded
	 */
	protected function setUp()
	{
		$this->strings[] = "12345";
		$this->strings[] = "abc123";
		$this->strings[] = "password";
		$this->strings[] = "Password";
		$this->strings[] = "P@ssw0rd";
		$this->strings[] = "Some long string :!: with\r\nweird\t\characters!";
		$this->strings[] = "!@#$%^&*()";
		$this->strings[] = "Sckn3iSdk2:!:";
		$this->strings[] = ":!:xdj2-0dk#s0/sldk~";
		$this->strings[] = "";
	}
	
	/**
	 * @covers \Cougar\Util\StringObfuscator::encode
	 * @todo Implement testEncode().
	 */
	public function testEncode()
	{
		foreach($this->strings as $index => $string)
		{
			$this->obfuscatedStrings[$index] =
				StringObfuscator::encode($string);
			$this->assertNotEquals("", $this->obfuscatedStrings[$index]);
		}
	}

	/**
	 * @covers \Cougar\Util\StringObfuscator::decode
	 * @todo Implement testDecode().
	 */
	public function testDecode()
	{
		# Test decoding the obfuscated strings
		foreach($this->obfuscatedStrings as $index => $enc_string)
		{
			$dec_string = StringObfuscator::decode($enc_string);
			$this->assertEquals($this->strings[$index], $dec_string);
		}
		
		# Test decoding the original strings (should always return as equal)
		foreach($this->strings as $index => $string)
		{
			$dec_string = StringObfuscator::decode($string);
			$this->assertEquals($this->strings[$index], $dec_string);
		}
	}

	/**
	 * @covers \Cougar\Util\StringObfuscator::getRandomString
	 * @todo Implement testGetRandomString().
	 */
	public function testGetRandomString()
	{
		foreach(array(0, 1, 5, 10, 15, 27, 39, 50, 100, 500, 1024) as $length)
		{
			$str = StringObfuscator::getRandomString($length);
			$this->assertEquals($length, strlen($str));
		}
	}
}
?>
