<?php

namespace Cougar\Util;

# Initialize the framework
require_once("cougar.php");

/**
 * Provides several static methods for formatting (and in some cases fixing)
 * data.
 * 
 * Please note ALL static methods work on REFERENCES and DO NOT return any
 * data.
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
class Format implements iFormat
{

    /**
     * @var Defines the strings that evalutae as a boolean true
     */
    public static $boolTrue = array("true", "t", "yes", "y", "on", "1");

    /**
     * @var Defines the strings that evalutae as a boolean false
     */
    public static $boolFalse = array("false", "f", "no", "n", "off", "0");

    /**
     * @var Defines the strings that evaluate as male
     */
    public static $genderMale = array("male", "m");

    /**
     * @var Defines the strings that evaluate as female
     */
    public static $genderFemale = array("female", "f");

    /**
     * Trims whitespace from the elements in the given object. This may seem
     * redundant when PHP already has a trim function, except that this function
     * can work on strings in an array and object, and also works by reference.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param mixed $object Object, array or string in which to operate
     */
	public static function trim(&$object)
	{
		# See if we have an array, object or string
		if (is_array($object) || is_object($object))
		{
			foreach($object as &$entry)
			{
				if (is_string($entry))
				{
					# We do this inline for speed
					$entry = trim($entry);
				}
				else if (is_object($entry) || is_array($entry))
				{
					# Here we call ourselves to iterate through values
					self::trim($entry);
				}
			}
		}
		else if (is_string($object))
		{
			$object = trim($object);
		}
	}
	
	/**
	 * Converts the "null" string into a null value.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @param mixed $object Object, array or string in which to operate
	 */
	public static function null(&$object)
	{
		# See if we have an array, object or string
		if (is_array($object) || is_object($object))
		{
			foreach($object as &$entry)
			{
				if (is_string($entry))
				{
					# We do this inline for speed
					if (mb_strtolower($entry) == "null")
					{
						$entry = null;
					}
				}
				else if (is_object($entry) || is_array($entry))
				{
					# Here we call ourselves to iterate through values
					self::null($entry);
				}
			}
		}
		else if (is_string($object))
		{
			if (mb_strtolower($object) == "null")
			{
				$object = null;
			}
		}
	}
	
	/**
	 * Converts string representations of a boolean (true, false, yes, T, F,
	 * etc.) to an actual boolean value. If force is set to true, it will force
	 * the conversion of the value. If it doesn't have any known variations, the
	 * object will be set to false.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @param mixed $object Object, array or string in which to operate
	 * @param bool $force Whether to force the conversion to boolean
	 */
	public static function strToBool(&$object, $force = false)
	{
		# See if we have an array, object or string
		if (is_array($object) || is_object($object))
		{
			foreach($object as &$entry)
			{
				if (is_string($entry))
				{
					# We do this inline for speed
					if (in_array(mb_strtolower($entry), self::$boolTrue))
					{
						$entry = true;
					}
					else if (in_array(mb_strtolower($entry), self::$boolFalse)
						|| $force)
					{
						$entry = false;
					}
				}
				else if (is_object($entry) || is_array($entry))
				{
					# Here we call ourselves to iterate through values
					self::strToBool($entry);
				}
			}
		}
		else if (is_string($object))
		{
			if (in_array(mb_strtolower($object), self::$boolTrue))
			{
				$object = true;
			}
			else if (in_array(mb_strtolower($object), self::$boolFalse)
				|| $force)
			{
				$object = false;
			}
		}
	}
	
	/**
	 * Converts boolean values to a string (true or false)
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @param mixed $object Object, array or string in which to operate
	 */
	public static function boolToStr(&$object)
	{
		# See if we have an array, object or string
		if (is_array($object) || is_object($object))
		{
			foreach($object as &$entry)
			{
				if (is_bool($entry))
				{
					# We do this inline for speed
					if ($entry)
					{
						$entry = "true";
					}
					else
					{
						$entry = "false";
					}
				}
				else if (is_object($entry) || is_array($entry))
				{
					# Here we call ourselves to iterate through values
					self::boolToStr($entry);
				}
			}
		}
		else if (is_bool($object))
		{
			if ($object)
			{
				$object = "true";
			}
			else
			{
				$object = "false";
			}
		}
	}
	
	/**
	 * Resolves gender words (M, F, Male, Female) into a single character
	 * representation (M, F). If the force flag is set, values that do not match
	 * will be returned as null (unspecified gender)
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @param mixed $object Object, array or string in which to operate
	 * @param bool $force Whether to return a null if the object is not gender
	 */
	public static function gender(&$object, $force = false)
	{
		# See if we have an array, object or string
		if (is_array($object) || is_object($object))
		{
			foreach($object as &$entry)
			{
				if (is_string($entry))
				{
					# We do this inline for speed
					if (in_array(mb_strtolower($entry), self::$genderMale))
					{
						$entry = "M";
					}
					else if (in_array(mb_strtolower($entry),
						self::$genderFemale))
					{
						$entry = "F";
					}
					else if ($force)
					{
						$entry = null;
					}
				}
				else if (is_object($entry) || is_array($entry))
				{
					# Here we call ourselves to iterate through values
					self::gender($entry);
				}
			}
		}
		else if (is_string($object))
		{
			if (in_array(mb_strtolower($object), self::$genderMale))
			{
				$object = "M";
			}
			else if (in_array(mb_strtolower($object), self::$genderFemale))
			{
				$object = "F";
			}
			else if ($force)
			{
				$object = null;
			}
		}
	}
	
	/**
	 * Resolves gender words (M, F, Male, Female) into a word representation
	 * (Male, Female). If the force flag is set, values that do not match
	 * will be returned as null (unspecified gender)
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @param mixed $object Object, array or string in which to operate
	 * @param bool $force Whether to return a null if the object is not gender
	 */
	public static function genderWord(&$object, $force = false)
	{
		# See if we have an array, object or string
		if (is_array($object) || is_object($object))
		{
			foreach($object as &$entry)
			{
				if (is_string($entry))
				{
					# We do this inline for speed
					if (in_array(mb_strtolower($entry), self::$genderMale))
					{
						$entry = "Male";
					}
					else if (in_array(mb_strtolower($entry),
						self::$genderFemale))
					{
						$entry = "Female";
					}
					else if ($force)
					{
						$entry = null;
					}
				}
				else if (is_object($entry) || is_array($entry))
				{
					# Here we call ourselves to iterate through values
					self::genderWord($entry);
				}
			}
		}
		else if (is_string($object))
		{
			if (in_array(mb_strtolower($object), self::$genderMale))
			{
				$object = "Male";
			}
			else if (in_array(mb_strtolower($object), self::$genderFemale))
			{
				$object = "Female";
			}
			else if ($force)
			{
				$object = null;
			}
		}
	}
	
	/**
	 * Removes the word null or NULL that may occur at the end of a string
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @param mixed $object Object, array or string in which to operate
	 */
	public static function removeNullWord(&$object)
	{
		# See if we have an array, object or string
		if (is_array($object) || is_object($object))
		{
			foreach($object as &$entry)
			{
				if (is_string($entry))
				{
					# We do this inline for speed
					if (mb_strtolower(mb_substr($entry, -4)) == "null")
					{
						$entry = trim(mb_substr($entry, 0, -4));
					}
				}
				else if (is_object($entry) || is_array($entry))
				{
					# Here we call ourselves to iterate through values
					self::removeNullWord($entry);
				}
			}
		}
		else if (is_string($object))
		{
			if (mb_strtolower(mb_substr($object, -4)) == "null")
			{
				$object = trim(mb_substr($object, 0, -4));
			}
		}
	}
}
?>
