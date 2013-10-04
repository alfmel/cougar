<?php

namespace Cougar\Util;

/**
 * The DateTime class extends the PHP DateTime class. The extended class
 * provides a format string and a __toString() method. This allows the date/time
 * format to be easily exported. The object also implements JsonSerializable to
 * export the date/time as a string in the given format.
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
class DateTime extends \DateTime implements \JsonSerializable
{
	/***************************************************************************
	 * PUBLIC STATIC PROPERTIES
	 **************************************************************************/
	
	/**
	 * @var string Default date/time format
	 */
	public static $defaultDateTimeFormat = "c";
	
	/**
	 * @var string Default date format
	 */
	public static $defaultDateFormat = "Y-m-d";
	
	/**
	 * @var string Default time format
	 */
	public static $defaultTimeFormat = "H:i:s";
	
	
	/***************************************************************************
	 * PUBLIC PROPERTIES AND METHODS
	 **************************************************************************/
	
	/**
	 * Export format (this is a property, not to be confused with the method).
	 * 
	 * You may use the special date/time formats DateTime, Date, and Time to
	 * export the date using the standard formats. You may also specify any
	 * other string acceptable to the format(). The definitions for the 
	 * 
	 * @var string
	 */
	public $format;
	
	/**
	 * Returns the date and time stored in the object as a string using the
	 * format stored in the format property.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @return string Formatted date/time
	 */
	public function __toString()
	{
		switch (strtolower($this->format))
		{
			case "datetime":
			case "":
				return $this->format(self::$defaultDateTimeFormat);
				break;
			case "date":
				return $this->format(self::$defaultDateFormat);
				break;
			case "time":
				return $this->format(self::$defaultTimeFormat);
				break;
			default:
				return $this->format($this->format);
				break;
		}
	}
	
	/**
	 * Return the date/time as a string when encoding as JSON
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @return string Output of __toString()
	 */
	public function jsonSerialize()
	{
		return $this->__toString();
	}
}
?>
