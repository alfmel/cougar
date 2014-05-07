<?php

namespace Cougar\Model;

use Cougar\Util\DateTime;
use Cougar\Exceptions\Exception;
use Cougar\Exceptions\BadRequestException;

# Initialize the framework (disabled; should have been done by application)
#require_once(__DIR__ . "/../../cougar.php");

/**
 * The StrictModel trait implements the Model interface by using the RealStruct
 * class rather than the struct class. It is therefore a more proper but slower
 * implementation.
 *
 * For full information about what a Model does, see the documentation for
 * iModel, the interface it implements.
 *
 * @history
 * 2013.09.30:
 *   (AT)  Initial release
 * 2014.04.02:
 *   (AT)  Internal implementation changes corresponding to changes in
 *         tModel.php
 * 2014.05.07:
 *   (AT)  Allow property types to include [] to denote arrays
 *
 * @version 2014.05.07
 * @package Cougar
 * @license MIT
 *
 * @copyright 2013-2014 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 *
 * @todo Only perform casts in __set(); move constraints to __validate()
 */
trait tStrictModel
{
    use tModel
    {
        __construct as protected __constructModel;
    }
    
    /**
     * Extracts the annotation for the class and parses them into the
     * __-prefixed protected properties.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param mixed $object
     *   Array or object with initial values (cast from)
     * @param string $view
     *   Set the given view once values are loaded
     * @param bool $strict
     *   Whether to perform strict property checking (on by default)
     */
    public function __construct($object = null, $view = null, $strict = true)
    {
        # Call the parent constructor
        $this->__constructModel(null, $view);
        
        # Go through the properties and move them to the protected array
        foreach($this->__properties as $property)
        {
            $this->__values[$property] = $this->$property;
            unset($this->$property);
        }
        
        # Import the values
        if (is_array($object) || is_object($object))
        {
            $this->__import($object, $strict);
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
     * @throws Exception
     */
    public function __get($name)
    {
        # See if property names are case-insensitive
        if ($this->__caseInsensitive)
        {
            $property = strtolower($name);
        }
        else
        {
            $property = $name;
        }
        
        # See if the property exists
        if (array_key_exists($property, $this->__alias))
        {
            # Return the value
            return $this->__values[$this->__alias[$property]];
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
     * 2014.04.02:
     *   (AT)  Remove reference to obsolete internal __hasChanges property
     * 2014.05.07:
     *   (AT)  Allow property types to include [] to denote arrays
     *
     * @version 2014.05.07
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param string $name
     *   Property name
     * @param mixed $value
     *   Property value
     * @return bool True if successful, throws exception on error
     * @throws Exception
     * @throws BadRequestException
     */
    public function __set($name, $value)
    {
        # See if property names are case-insensitive
        if ($this->__caseInsensitive)
        {
            $property = strtolower($name);
        }
        else
        {
            $property = $name;
        }
        
        # Make sure the property name or alias is valid
        if (array_key_exists($property, $this->__alias))
        {
            # Get the real name of the property
            $property = $this->__alias[$name];
        }
        else
        {
            throw new Exception(get_class($this) . " does not have a " .
                "\"" . $name . "\" property");
        }
        
        # See if the value can be null
        if (! $this->__null[$property] && $value === null)
        {
            throw new BadRequestException($name . " cannot be null");
        }
        
        # Perform the type cast if value is not null
        if ($value !== null)
        {
            switch($this->__type[$property])
            {
                case "string":
                    $value = (string) $value;
                    
                    # Enforce regex constraints (if any)
                    if ($this->__regex[$property])
                    {
                        $match = false;
                        foreach($this->__regex[$property] as $regex)
                        {
                            $result = preg_match($regex, $value);
                            
                            if ($result)
                            {
                                $match = true;
                                break;
                            }
                            else if ($result === false)
                            {
                                throw new Exception("Regex validation error " .
                                    preg_last_error());
                            }
                        }
                        
                        if (! $match)
                        {
                            throw new BadREquestException($property .
                                " property does not conform to accepted" .
                                " values");
                        }
                    }
                    break;
                case "int":
                    $value = (int) $value;
                    break;
                case "float":
                    $value = (float) $value;
                    break;
                case "bool":
                    if (is_string($value))
                    {
                        $value = Format::strToBool($value);
                    }
                    else
                    {
                        $value = (bool) $value;
                    }
                    break;
                case "array":
                    if (! array($this->$property))
                    {
                        throw new BadRequestException(
                            $property . " property must be an array");
                    }
                    break;
                case "DateTime":
                    if ($value === null || $value === "")
                    {
                        $value = null;
                    }
                    else
                    {
                        $value = new DateTime($value);
                        $value->format = $this->__dateTimeFormat[$property];
                    }
                    break;
                default:
                    # See if this is an array of objects
                    if (substr($this->__type[$name], -2) == "[]")
                    {
                        # Extract the actual type
                        $type = substr($this->__type[$name], 0, -2);

                        # Make sure this is an array
                        if (! is_array($value))
                        {
                            throw new BadRequestException(
                                $name . " property must be an array of " .
                                $type);
                        }

                        # Make sure all the elements are of the right type
                        foreach($value as $element)
                        {
                            if (! $element instanceof $type)
                            {
                                throw new BadRequestException("element in " .
                                    $name . " property must be instance " .
                                    "of " . $type);
                            }
                        }
                    }
                    else
                    {
                        # Assume object; make sure it is an instance
                        if (! $value instanceof $this->__type[$name])
                        {
                            throw new Exception("Property " . $name ." must " .
                                "be instance of " . $this->__type[$name]);
                        }
                    }
            }
        }
        
        # Set the value
        $this->__values[$property] = $value;
    }

    /**
     * The __get() and __set() methods already take care of all validation. This
     * method only runs the __preValidate() and __postValidate() methods.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     */
    public function __validate()
    {
        # See if the class has a __preValidate method
        if (method_exists($this, "__preValidate"))
        {
            $this->__preValidate();
        }
        
        # See if the class has a __postValidate method
        if (method_exists($this, "__postValidate"))
        {
            $this->__postValidate();
        }
    }
    
    
    /***************************************************************************
     * PROTECTED PROPERTIES AND METHODS
     **************************************************************************/
    
    /**
     * @var array Values of public properties
     */
    protected $__values = array();
}
?>
