<?php

namespace Cougar\Security;

use Cougar\Exceptions\Exception;

# Initialize the framework
require_once("cougar.php");

/**
 * This class implements a base identity.
 * 
 * The goal of the class is to expose the id, parent and identity attributes as
 * class attributes. The class will allow identity attributes to be queried as
 * class attributes. Extensions of this class may define additional attributes.
 * These attributes will be unset by the constructor so that control of the
 * attributes is passed to the __set() and __unset() methods.
 * 
 * All attributes will be read-only. Attempts to change an identity will throw
 * an exception.
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
class Identity implements iIdentity
{
	/**
	 * Saves the values of the primary ID and the attributes. Also unsets the
	 * public properties so that they are no longer directly accessible.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @param string $id
     *   The primary ID of the identity
	 * @param array $attributes
     *   The identity attributes (associative array)
	 * @param iIdentity $parent
     *   The parent identity
	 */
	public final function __construct($id = null, $attributes = null,
		iIdentity $parent = null)
	{
		# Grab the ID and parent properties and make them private
		$this->__id = $id;
		$this->__parent = $parent;
		unset($this->id);
		unset($this->parent);
		
		if (is_array($attributes))
		{
			# Store the identity attributes
			$this->attributes = $attributes;
			$this->lc_attributes = array_change_key_case($this->attributes);
		}
		
		# Store the public properties and their default values in the private
		# array
		$reflection = new \ReflectionClass($this);
		foreach($reflection->getProperties(\ReflectionProperty::IS_PUBLIC)
			as $property)
		{
			$property_name = $property->name;
			
			if ($property_name == "id" || $property_name = "parent")
			{
				continue;
			}
			
			$this->__properties[$property_name] = $this->$property_name;
			unset($this->$property_name);
			
			# Map the lowercase name to its actual name
			$this->__propertyMap[strtolower($property_name)] =
				&$this->__properties[$property_name];
		}
	}
	
	/**
	 * Performs object clean-up.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 */
	public final function __destruct()
	{
		# Nothing to do at this time
	}
	
	
	/***************************************************************************
	 * PUBLIC PROPERTIES AND METHODS
	 **************************************************************************/
	
	/**
	 * @var string Primary ID
	 */
	public $id = null;
	
	/**
	 * @var iIdentity Parent identity
	 */
	public $parent = null;

	/**
	 * Returns true if the identity has a parent.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @return bool True if identity has parent, false otherwise
	 */
	public function hasParent()
	{
		if ($this->__parent === null)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
     * Returns the root identity. The root identity is the last identity up the
     * identity chain.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @return iIdentity Root identity
	 */
	public function root()
	{
		if ($this->__parent === null)
		{
			return $this->__parent->root();
		}
		else
		{
			return $this;
		}
	}
	
	/**
	 * Returns the identity attributes.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @return array Identity attributes
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}
	
	/***************************************************************************
	 * MAGIC METHODS
	 **************************************************************************/
	
	/**
	 * Called every time a property is set. You can override this method to
	 * check values are properly set. The best way to do this is to do a switch
	 * statement for each of your properties, then call the parent method.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @param string $name
     *   Property name
	 * @param mixed $value
     *   Property value
     * @throws \Cougar\Exceptions\Exception
	 */
	final public function __set($name, $value)
	{
		throw new Exception(
			"You are not allowed to add or modify identity properties");
	}
	
	/**
	 * Called every time a property is read.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @param string $name
     *   Property name
	 * @return mixed Property value
	 */
	final public function __get($name)
	{
		# Make name lowercase
		$name = strtolower($name);
		
		# See what property we have
		switch($name)
		{
			case "id":
				return $this->__id;
				break;
			case "parent":
				if ($this->__parent !== null)
				{
					return $this->__parent;
				}
				else
				{
					return $this;
				}
				break;
			default:
				if (array_key_exists($name, $this->__propertyMap))
				{
					return $this->__propertyMap[$name];
				}
				else if (array_key_exists($name, $this->lc_attributes))
				{
					return $this->lc_attributes[$name];
				}
				else
				{
					return null;
				}
				break;
		}
	}
	
	/**
	 * Called every time isset() or empty() is called on a property.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @param string $name
     *   Property name
	 * @return bool True if property exists, false otherwise
	 */
	final public function __isset($name)
	{
		return (array_key_exists($name, $this->__properties) ||
			array_key_exists($name, $this->attributes));
	}
	
	/**
	 * Called every time unset() is called on a property.
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
	final public function __unset($name)
	{
		throw new Exception("You cannot remove identity attributes");
	}
	
	
	/***************************************************************************
	 * PROTECTED PROPERTIES AND METHODS
	 **************************************************************************/
	
	/**
	 * @var array Identity attributes
	 */
	protected $attributes = array();
	
	/**
	 * @var array Identity attributes, with lowercase indexes
	 */
	protected $lc_attributes = array();
	
	/***************************************************************************
	 * PRIVATE PROPERTIES AND METHODS
	 **************************************************************************/

	/**
	 * @var array Private version of the ID
	 */
	private $__id = array();

	/**
	 * @var array Private version of the parent
	 */
	private $__parent = array();

	/**
	 * @var array Defined properties
	 */
	private $__properties = array();

	/**
	 * @var array Map of properties from case-insensitive to case-sensitive
	 */
	private $__propertyMap = array();
}
?>
