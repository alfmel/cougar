<?php

namespace Cougar\Util;

# Initialize the framework
require_once("cougar.php");

/**
 * Obfuscates and decodes passwords, keys and other sensitive strings so that
 * they are not stored in plain text in the code.
 * 
 * This implementation uses a random XOR scheme to encode the data. It is not
 * very secure but raises the bar slightly over storing sensitive information
 * in plain text.
 * 
 * There is a cougar-obfuscate-string script which can be used to easily
 * obfuscate the strings before adding them to your code.
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

class StringObfuscator implements iSecureString
{
	/**
	 * Obfuscates the given string
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
	 * @param string $string
     *   Password or string to encode
	 * @return string Encoded password or string
	 */
	public static function encode($string)
	{
		# Get the length of the current string
		$length = strlen($string);

		# Get a random string of the same length as the key
		$random_string = StringObfuscator::getRandomString($length);

		# XOR the string with the key
		$xor_string = $string ^ $random_string;

		# Combine the key and the value
		$combined_string = "";
		for($i = 0; $i < $length; $i++)
		{
            $combined_string .= substr($random_string, $i, 1) .
                substr($xor_string, $i, 1);
		}

		# Reverse the string and encode it as base64, then reverse it again
		$encoded_string = strrev(base64_encode(strrev($combined_string)));

		# Return the encoded string without the ==
		return ":!:" . str_replace("=", "", $encoded_string) . ":!:";
	}

	/**
	 * Decodes an obfuscated string. If the string does not appear to be
     * obfuscated, it will return the original string.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @param string $encoded_string
     *   Password or string to decode
     * @return string Decoded password or string
	 */
	public static function decode($encoded_string)
	{
		if (substr($encoded_string, 0, 3) != ":!:" ||
			substr($encoded_string, -3) != ":!:")
		{
			# This is not an obfuscated string; return it as is
			return $encoded_string;
		}
		
		# Decode the base64 string and reverse it
		$combined_string = strrev(base64_decode(strrev($encoded_string)));

		$length = strlen($combined_string);
		$random_string = "";
		$xor_string = "";
		for ($i = 0; $i < $length; $i += 2)
		{
			$random_string .= substr($combined_string, $i, 1);
			$xor_string .= substr($combined_string, $i + 1, 1);
		}

		# XOR the string with the key
		$string = $xor_string ^ $random_string;

		# Return the original string
		return $string;
	}

	/**
	 * Returns a random binary string of the specified length
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
	 * @param int $length
     *   String length
	 * @return string Random string of given length
	 */
	public static function getRandomString($length)
	{
		# Define an empty string
		$random_string = "";

		for ($i = 0; $i < $length; $i++)
		{
			$random_string .= chr(mt_rand(0, 255));
		}

		# Return the string
		return $random_string;
	}
}
?>
