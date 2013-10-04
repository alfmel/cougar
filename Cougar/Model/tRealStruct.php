<?php

namespace Cougar\Model;

use Cougar\Exceptions\Exception;

# Load the framework foundation
require_once("cougar.php");

/**
 * The RealStruct trait uses reflection to move all public properties in the
 * protected space. It then implements the __get () and __set() magic methods
 * to ensure class properties cannot be added. Thus creating a struct-like
 * data object.
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
trait tRealStruct
{
	/**
	 * Moves all public properties to the protected __property array to force
	 * all set and get operations to pass through __get() and __set().
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 */
	public function __construct()
	{
		$reflection = new \ReflectionObject($this);
		foreach($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as
			$property)
		{
			$this->__values[$property->name] = $this->{$property->name};
			$this->__properties[] = $property->name;
			unset($this->{$property->name});
		}
	}
	
	
	/***************************************************************************
	 * MAGIC METHODS
	 **************************************************************************/
	
	/**
	 * Gets the value of the given property. If the property does not exist,
	 * it will throw an exception.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @param string $name Property name
	 * @return mixed Property value
     * @throws \Cougar\Exceptions\Exception
	 */
	public function __get($name)
	{
		if (array_key_exists($name, $this->__values))
		{
			return $this->__values[$name];
		}
		else
		{
			throw new Exception(get_class($this) . " object does not have a " .
				"property named " . $name);
		}
	}
	
	/**
	 * Sets the value of the given property. If the property does not exist,
	 * it will throw an exception.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @param string $name Property name
	 * @param mixed $value Property value
	 * @return bool True if successful, throws exception on error
     * @throws \Cougar\Exceptions\Exception
	 */
	public function __set($name, $value)
	{
		if (array_key_exists($name, $this->__values))
		{
			$this->__values[$name] = $value;
		}
		else
		{
			throw new Exception(get_class($this) . " object does not have a " .
				"property named " . $name);
		}
	}
	
	/**
	 * Returns true if the given property exists.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @param string $name Property name
	 * @return bool True if property exist, false otherwise
	 */
	public function __isset($name)
	{
		return(array_key_exists($name, $this->__values));
	}
	
	/**
	 * Throws an exception when trying to unset a property. Message will differ
	 * if the property actually exists
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @param string $name Property name
     * @throws \Cougar\Exceptions\Exception
	 */
	public function __unset($name)
	{
		if (array_key_exists($name, $this->__values))
		{
			throw new Exception("Cannot unset " . $name . " property on " .
				get_class($this) . " object");
		}
		else
		{
			throw new Exception("Cannot unset " . $name . " property on " .
				get_class($this) . " object: property does not exist");
		}
	}
	
	/**
	 * Exports the public properties of the object and their values as an array.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 *
	 * @return array Associative array with public properties and their values
	 */
	public function __toArray()
	{
		return $this->__values;
	}
	
	/**
	 * Returns an associative array with the public properties and their values
	 * in preparation for serializing the object to JSON.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @return array Associative array with public properties and their values
	 */
	public function jsonSerialize()
	{
		return $this->__values;
	}
	
	
	/***************************************************************************
	 * ITERATOR METHODS
	 **************************************************************************/
	
	/**
	 * Return the current element
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @return mixed Element value
	 */
	public function current()
	{
		return $this->__values[$this->__iterator];
	}
	
	/**
	 * Return the key of the current element
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @return scalar Key
	 */
	public function key()
	{
		return $this->__properties[$this->__iterator];
	}
	
	/**
	 * Move forward to the next element
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 */
	public function next()
	{
		$this->__iterator++;
	}
	
	/**
	 * Rewind the Iterator to the first element, but skip until a non-optional,
	 * non-null value is found
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 */
	public function rewind()
	{
		$this->__iterator = 0;
	}
	
	/**
	 * Checks if the current position is valid
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 */
	public function valid()
	{
		return array_key_exists($this->__iterator, $this->__properties);
	}
	
	
	/***************************************************************************
	 * PROTECTED PROPERTIES AND METHODS
	 **************************************************************************/
	
	/**
	 * @var array Stores the public properties and their values (associative)
	 */
	protected $__values = array();
	
	/**
	 * @var array Property names (used by iterator to determine key name)
	 */
	protected $__properties = array();
	
	/**
	 * @var int Current position of the iterator
	 */
	protected $__iterator = 0;
}
?>
