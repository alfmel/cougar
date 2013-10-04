<?php

namespace Cougar\Util;

use Cougar\Exceptions\Exception;

# Initialize the framework
require_once("cougar.php");

/**
 * Encrypts passwords, keys and other sensitive strings using the ARC4 algorithm
 * (or RC4) so that they are not stored in plain text in the code.
 * 
 * The implementation is based on the algorithm description on the Wikipedia
 * (http://en.wikipedia.org/wiki/RC4).
 * 
 * Because ARC4 encryption requires an encryption key, you must set the key
 * through the Arc4::setKey() method. Calls to encode/decode will fail if the
 * key has not been set. You may optionally add a magic value to the string via
 * Arc4::setMagic().
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

class Arc4 implements iSecureString
{
	/***************************************************************************
	 * PUBLIC PROPERTIES AND METHODS
	 **************************************************************************/

	/**
	 * Loads ARC4 encryption parameters from a file
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @param string $filename Config file with ARC4 parameters
	 */
	static public function loadParameters($filename)
	{
		$config = new Config($filename);
		
		$key = $config->value("key");
		if ($key)
		{
			self::setKey($key);
		}
		
		self::$magic = (string) $config->value("magic");
		
		$compress = $config->value("compress");
		Format::strToBool($compress);
		self::$compress = $compress;
	}
	
	/**
	 * Sets the encryption key. The key should be in hexadecimal, and not
	 * base64 or binary. This call must be made before any encode or decode
	 * calls, but it only needs to be made once. You may change the key at any
	 * time by making this call again.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @param string $key Encryption key in hexadecimal
     * @throws \Cougar\Exceptions\Exception
	 */
	static public function setKey($key)
	{
		# For optimization, this method will not store the key. It will store
		# the state table created using the key-scheduling algorithm (KSA) based
		# on the key. The state table is what gets used to encrypt and decrypt
		# the data. By doing it here, KSA will only run once every time the key
		# is set.
		
		# Convert the key to a character array
		$key = self::stringToCharArray(hex2bin($key));

		# Make sure the key is between 1 and 256 bytes
		$key_length = count($key);
		if ($key_length < 1 || $key_length > 256)
		{
			throw new Exception("Key must be between 1 and 256 bytes");
		}
		
		# Initialize the KSA parameters, state table and key stream variables
		self::$state = range(0, 255);
		self::$keyStream = array();
		self::$i = 0;
		self::$j = 0;
		$j = 0;
		
		# Do KSA
		for ($i = 0; $i < 256; $i++)
		{
			# Compute j
			$j = ($j + self::$state[$i] + $key[$i % $key_length]) % 256;
			
			# Swap S[i] and S[j]
			$state_i = self::$state[$i];
			self::$state[$i] = self::$state[$j];
			self::$state[$j] = $state_i;
		}
	}

	/**
	 * Sets a magic value. The value will be added to the string that will be
	 * encoded. During decoding, the magic value will be checked to ensure
	 * the data was decoded correctly. The magic value will be removed when the
	 * decoded data is returned.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @param string $magic Magic string value
	 */
	static public function setMagic($magic)
	{
		self::$magic = $magic;
	}

	/**
	 * Whether to compress the string before encrypting.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @param bool Compression value
	 */
	static public function useCompression($value)
	{
		self::$compress = (bool) $value;
	}
	
	/**
	 * Encrypts the given string with the key given via Arc4::setKey().
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
	 * @param string $string Password or string to encrypt
     * @return string Encrypted password or string (in hex)
	 */
	static public function encode($string)
	{
		# Xor the string with the keystream
		if (self::$compress)
		{
			return bin2hex(self::doXor(self::$magic .
				gzcompress($string, 9)));
		}
		else
		{
			return bin2hex(self::doXor(self::$magic . $string));
		}
	}

    /**
     * Decrypts the given string with the key given via Arc4::setKey().
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string $encoded_string Password or string to decode
     * @return string decoded password or string
     * @throws \Cougar\Exceptions\Exception
     */
	static public function decode($encoded_string)
	{
		# See if we have a key; if not, return the given string
		if (! count(self::$state))
		{
			return $encoded_string;
		}
		
		# See if this is a hex-encoded string; if not, return given string
		if (! preg_match("/^[0-9A-Fa-f]*$/", $encoded_string))
		{
			return $encoded_string;
		}
		
		$decoded_string = self::doXor(hex2bin($encoded_string));

		# See if we have magic
		if (self::$magic)
		{
			if (strpos($decoded_string, self::$magic) === 0)
			{
				if (self::$compress)
				{
					return gzuncompress(
						substr($decoded_string, strlen(self::$magic)));
				}
				else
				{
					return substr($decoded_string, strlen(self::$magic));
				}
			}
			else
			{
				throw new Exception("String decoding error: magic value " .
					"does not match");
			}
		}
		else
		{
			if (self::$compress)
			{
				return gzuncompress($decoded_string);
			}
			else
			{
				return $decoded_string;
			}
		}
	}

	
	/***************************************************************************
	 * PROTECTED PROPERTIES AND METHODS
	 **************************************************************************/

	/**
	 * @var string Encryption magic value
	 */
	protected static $magic = "";
	
	/**
	 * @var bool Whether to use compression
	 */
	protected static $compress = false;


	/**
	 * Converts a string into an array of characters. Because PHP does not know
	 * of characters, per se, the array elements will be decimal numbers of the
	 * corresponding bytes of the string.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @param string $string The string to convert
	 * @return array The string as a character array
	 */
	protected static function stringToCharArray($string)
	{
		$array = str_split($string);
		foreach($array as &$char)
		{
			$char = ord($char);
		}
		
		return $array;
	}

	/**
	 * Converts the character array created by stringToCharArray() back to a
	 * string.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @param array $array The array to convert
	 * @return string The reconstructed string
	 */
	protected static function charArrayToString(array $array)
	{
		$string = "";
		foreach($array as $char)
		{
			$string .= chr($char);
		}
		
		return $string;
	}
	
	/**
	 * XOR the given string with the keystream
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @param string string The string to XOR
	 * @return string The XOR'd string
	 */
	protected static function doXor($string)
	{
		# Make sure we have enough entries in the key stream
		if (count(self::$keyStream) < strlen($string))
		{
			self::generateKeyStream(strlen($string));
		}
		
		# Convert the string to the character array
		$string = self::stringToCharArray($string);
		
		# Go through the string
		foreach ($string as $i => &$char)
		{
			$char ^= self::$keyStream[$i];
		}
		
		return self::charArrayToString($string);
	}
	
	
	/***************************************************************************
	 * PRIVATE PROPERTIES AND METHODS
	 **************************************************************************/
	
	/**
	 * @var array Initialized state table
	 */
	private static $state = array();

	/**
	 * @var array Pre-computed key stream table
	 */
	private static $keyStream = array();
	
	/**
	 * @var int the value i used in generating the key stream
	 */
	private static $i = 0;
	
	/**
	 * @var int the value j used in generating the key stream
	 */
	private static $j = 0;

	/**
	 * Computes the next entries in the keystream, up to the given length.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @param int length Compute the keystream to this length
	 */
	private static function generateKeyStream($length)
	{
		# Generate an intithe keystream using PRGA
		for ($k = count(self::$keyStream); $k < $length; $k++)
		{
			self::$i = (self::$i + 1) % 256;
			self::$j = (self::$j + self::$state[self::$i]) % 256;
			
			# Swap S[i] and S[j]
			$s_i = self::$state[self::$i];
			self::$state[self::$i] = self::$state[self::$j];
			self::$state[self::$j] = $s_i;
			
			# Add the entry to the key stream
			self::$keyStream[] = self::$state[(self::$state[self::$i] +
				self::$state[self::$j]) % 256];
		}
	}
}
?>
