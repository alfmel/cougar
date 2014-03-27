<?php

namespace Cougar\Model;

use Cougar\Cache\CacheFactory;
use Cougar\Util\Annotations;
use Cougar\Util\DateTime;
use Cougar\Exceptions\Exception;
use Cougar\Exceptions\BadRequestException;

# Initialize the framework (disabled; should have been done by application)
#require_once(__DIR__ . "/../../cougar.php");

/**
 * The Model trait implements the Model interface in a less strict but much
 * faster implementation by using the Struct class rather than the RealStruct
 * class.
 *
 * Since the implementation takes some shortcuts to benefit speed, you should
 * always call the __validate() method to enforce all property behavior.
 * Exporting will cast all values.
 * 
 * For full information about what a Model does, see the documentation for
 * iModel, the interface it implements.
 *
 * @history
 * 2013.09.30:
 *   (AT)  Initial release
 * 2014.01.09:
 *   (AT)  Fixed array check when validating array value when performing casts
 * 2014.02.13:
 *   (AT)  Check if an array value first arrives as JSON; this allows the PDO
 *         Model to convert arrays into JSON for storage and for the incoming
 *         data to be converted back to an array.
 * 2014.02.26:
 *   (AT)  Extract annotations with extractFromObjectWithInheritance()
 * 2014.03.27:
 *   (AT)  Make sure we fully validate the object when exporting as an array but
 *         still allow the object to be exported with its default values
 *
 * @version 2014.03.27
 * @package Cougar
 * @license MIT
 *
 * @copyright 2013-2014 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
trait tModel
{
    use tAnnotatedClass;

    /**
     * Extracts the annotation for the class and parses them into the
     * __-prefixed protected properties.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     * 2014.02.26:
     *   (AT)  Extract annotations with extractFromObjectWithInheritance()
     * 2014.03.05:
     *   (AT)  Don't clobber cached annotations when loading parsed annotations
     *         from cache
     *
     * @version 2014.03.05
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param mixed $object
     *   Assoc. array or object with initial values
     * @param string $view
     *   Set the given view once values are loaded
     * @param bool $strict
     *   Whether to perform strict property checking (on by default)
     * @throws Exception
     * @throws BadRequestException
     */
    public function __construct($object = null, $view = null, $strict = true)
    {
        # Store the value of the requested view (avoid clobbering later)
        $requested_view = $view;
        
        # Get a local cache
        # TODO: Set through static property(?)
        $local_cache = CacheFactory::getLocalCache();
        
        # Create our cache keys
        $class = get_class($this) . ".model";
        $cache_key = Annotations::$annotationsCachePrefix . "." . $class;
        
        # See if the execution cache has the object properties
        $parsed_annotations = false;
        if (array_key_exists($class, self::$__executionCache))
        {
            # Get the parsed annotations from the cache
            $parsed_annotations = self::$__executionCache[$class];
        }
        else
        {
            # Get the annotations
            $this->__annotations =
                Annotations::extractFromObjectWithInheritance($this, array(),
                    true, false);

            # See if the annotations came from the cache
            $parsed_annotations = false;
            if ($this->__annotations->cached)
            {
                $parsed_annotations = $local_cache->get($cache_key);
            }
        }
        
        # See if we have pre-parsed annotations
        if ($parsed_annotations === false)
        {
            # Go through the class annotations
            $view_list = array("__default__");
            foreach ($this->__annotations->class as $annotation)
            {
                switch($annotation->name)
                {
                    case "CaseInsensitive":
                        $this->__caseInsensitive = true;
                        break;
                    case "Views":
                        # See which views are defined
                        $views = preg_split("/\s+/u", $annotation->value, null,
                            PREG_SPLIT_NO_EMPTY);

                        # Create the views (if we have any)
                        foreach($views as $view)
                        {
                            $this->__views[$view] =
                                $this->__views["__default__"];
                            $view_list[] = $view;
                        }
                        break;
                }
            }
            
            # Go through the public properties of the object
            foreach(array_keys($this->__annotations->properties) as
                $property_name)
            {
                # Add the property to the list of properties
                $this->__properties[] = $property_name;

                # Set the default property options
                $this->__type[$property_name] = "string";
                $this->__readOnly[$property_name] = false;
                $this->__null[$property_name] = true;
                $this->__regex[$property_name] = array();
                $this->__alias[$property_name] = $property_name;

                # See if the properties are case-insensitive
                if ($this->__caseInsensitive)
                {
                    # Store the lowercase property name as an alias
                    $this->__alias[strtolower($property_name)] = $property_name;
                }

                # Set the view-based values
                foreach($view_list as $view)
                {
                    $this->__views[$view]["optional"][$property_name] = false;
                    $this->__views[$view]["visible"][$property_name] = true;
                    $this->__views[$view]["exportAlias"][$property_name] =
                        $property_name;
                }

                # Go through the annotations
                foreach($this->__annotations->properties[$property_name] as
                    $annotation)
                {
                    switch ($annotation->name)
                    {
                        case "Alias":
                        case "Column":
                            $this->__alias[$annotation->value] =
                                $property_name;
                            if ($this->__caseInsensitive)
                            {
                                $this->__alias[
                                        strtolower($annotation->value)] =
                                    $property_name;
                            }
                            break;
                        case "NotNull":
                            $this->__null[$property_name] = false;
                            break;
                        case "Regex":
                            $this->__regex[$property_name][] =
                                $annotation->value;
                            break;
                        case "Optional":
                            # Set the option in all views
                            foreach($view_list as $view)
                            {
                                $this->__views[$view]["optional"]
                                    [$property_name] = true;
                            }
                            break;
                        case "DateTimeFormat":
                            $this->__dateTimeFormat[$property_name] =
                                $annotation->value;
                            break;
                        case "View":
                            # Separate the values
                            $view_values = preg_split("/\s+/u",
                                $annotation->value);

                            # Extract the view (first value)
                            $view = array_shift($view_values);

                            # Make sure the view exists
                            if (! array_key_exists($view, $this->__views))
                            {
                                throw new Exception($property_name .
                                    " property defines \"" . $view .
                                    "\" but the view does not exist.");
                            }

                            # Go through the rest of the options
                            $export_alias_set = false;
                            foreach($view_values as $index => $value)
                            {
                                switch(strtolower($value))
                                {
                                    case "hidden":
                                        $this->__views[$view]["visible"]
                                            [$property_name] = false;
                                        break;
                                    case "optional":
                                        $this->__views[$view]["optional"]
                                            [$property_name] = true;
                                        break;
                                    default:
                                        # Add the real value (not lowercase) as
                                        # the export alias and as an alias
                                        if (! $export_alias_set)
                                        {
                                            $this->__views[$view]["exportAlias"]
                                                    [$property_name] =
                                                $view_values[$index];
                                            $this->__alias[$view_values[$index]]
                                                = $property_name;
                                            if ($this->__caseInsensitive)
                                            {
                                                $this->__alias[
                                                    strtolower($view_values[
                                                        $index])] =
                                                    $property_name;
                                            }
                                        }
                                        $export_alias_set = true;
                                        break;
                                }
                            }

                            break;
                        case "var":
                            # Separate the variable name from the comment
                            $var_values = preg_split("/\s+/u",
                                $annotation->value);
                            switch($var_values[0])
                            {
                                case "string":
                                case "":
                                    # Type is already set to string
                                    break;
                                case "int":
                                case "integer":
                                    $this->__type[$property_name] = "int";
                                    break;
                                case "float":
                                case "double":
                                    $this->__type[$property_name] = "float";
                                    break;
                                case "bool":
                                case "boolean":
                                    $this->__type[$property_name] = "bool";
                                    break;
                                case "DateTime":
                                    $this->__type[$property_name] = "DateTime";
                                    if (! array_key_exists($property_name,
                                        $this->__dateTimeFormat))
                                    {
                                        $this->__dateTimeFormat[$property_name]
                                            = "";
                                    }
                                    break;
                                default:
                                    $this->__type[$property_name] =
                                        $var_values[0];
                            }
                            break;
                    }
                }

                # Remove the public property
                #unset($this->$property_name);
            }

            # Store the record properties in the caches
            $parsed_annotations = array(
                "annotations" => $this->__annotations,
                "properties" => $this->__properties,
                "type" => $this->__type,
                "readOnly" => $this->__readOnly,
                "null" => $this->__null,
                "dateTimeFormat" => $this->__dateTimeFormat,
                "regex" => $this->__regex,
                "alias" => $this->__alias,
                "caseInsensitive" => $this->__caseInsensitive,
                "view" => $this->__views
            );
            self::$__executionCache[$class] = $parsed_annotations;
            $local_cache->set($cache_key, $parsed_annotations,
                Annotations::$cacheTime);
        }
        else
        {
            # Make sure we don't clobber any previous annotations
            if (! $this->__annotations)
            {
                $this->__annotations = $parsed_annotations["annotations"];
            }

            # Restore the property values
            $this->__properties = $parsed_annotations["properties"];
            $this->__type = $parsed_annotations["type"];
            $this->__readOnly = $parsed_annotations["readOnly"];
            $this->__null = $parsed_annotations["null"];
            $this->__dateTimeFormat = $parsed_annotations["dateTimeFormat"];
            $this->__regex = $parsed_annotations["regex"];
            $this->__alias = $parsed_annotations["alias"];
            $this->__caseInsensitive = $parsed_annotations["caseInsensitive"];
            $this->__views = $parsed_annotations["view"];
        }
        
        # Point the protected properties to the values in the default view
        $this->__exportAlias = &$this->__views["__default__"]["exportAlias"];
        $this->__optional = &$this->__views["__default__"]["optional"];
        $this->__visible = &$this->__views["__default__"]["visible"];

        # See if object is set
        if (is_array($object) || is_object($object))
        {
            # Load the default values (if we have any
            $this->__import($object, $strict);
        }
        else if (! is_null($object))
        {
            throw new BadRequestException(
                "Casting from object requires an object or array");
        }
        else
        {
            # Store the default values
            foreach($this->__properties as $property)
            {
                $this->__defaultValues[$property] = $this->$property;
            }
        }
        
        # Set the desired view
        if ($requested_view)
        {
            $this->__setView($requested_view);
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
     * 2013.10.28:
     *   (AT)  Don't set a value for the alias; just set the reference
     * 2014.03.03:
     *   (AT)  Cleaned up commented code
     *
     * @version 2014.03.03
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
            # Create a new public property for the alias linked to the real one
            $this->$name = &$this->{$this->__alias[$property]};
            return $this->$name;
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
     * 2014.03.03:
     *   (AT)  Don't return anything since __set() shouldn't return anything
     *
     * @version 2014.03.03
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param string $name Property name
     * @param mixed $value Property value
     * @throws Exception
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
        
        if (array_key_exists($property, $this->__alias))
        {
            # Create a new public property and link it to the real property
            $this->$name = null;
            $this->$name = &$this->{$this->__alias[$property]};

            # Save the value
            $this->$name = $value;

            # Declare we have changes
            $this->__hasChanges = true;
        }
        else
        {
            if ($this->__strictPropertyChecks)
            {
                throw new Exception(get_class($this) . " object does not have a " .
                    "property named " . $name);
            }
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
        # See if property names are case-insensitive
        if ($this->__caseInsensitive)
        {
            $property = strtolower($name);
        }
        else
        {
            $property = $name;
        }
        
        return(array_key_exists($property, $this->__values));
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
     * @throws Exception
     */
    public function __unset($name)
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
     * 2014.03.27:
     *   (AT)  Validate the object when exporting instead of simply performing
     *         casts
     *
     * @version 2014.03.27
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @return array Associative array with public properties and their values
     */
    public function __toArray()
    {
        # Validate the values
        $this->__validate();
        
        # Initialize the output array
        $output_array = array();
        
        # Go through the properties
        foreach($this->__properties as $property)
        {
            # See if this property is hidden
            if (! $this->__visible[$property])
            {
                # Skip the property
                continue;
            }
            
            # See if the property is optional and is null
            if ($this->__optional[$property] && $this->$property === null)
            {
                # Skip this property
                continue;
            }
            
            # Check the property type
            switch($this->__type[$property])
            {
                case "string":
                case "int":
                case "float":
                case "bool":
                    # Add the value
                    $output_array[$this->__exportAlias[$property]] =
                        $this->$property;
                    break;
                case "DateTime":
                    if ($this->$property)
                    {
                        $output_array[$this->__exportAlias[$property]] =
                            (string) $this->$property;
                    }
                    else
                    {
                        $output_array[$this->__exportAlias[$property]] =
                            $this->$property;
                    }
                    break;
                case "array":
                    # If the array has objects in them, call __toArray()
                    $tmp_array = (array) $this->$property;
                    foreach($tmp_array as &$sub_value)
                    {
                        if (is_object($sub_value))
                        {
                            if (method_exists($sub_value, "__toArray"))
                            {
                                $sub_value = $sub_value->__toArray();
                            }
                            else if (method_exists($sub_value, "toArray"))
                            {
                                $sub_value = $sub_value->toArray();
                            }
                            else
                            {
                                $sub_value = (array) $sub_value;
                            }
                        }
                    }
                    
                    # Add the value of the array
                    $output_array[$this->__exportAlias[$property]] = $tmp_array;
                    break;
                default:
                    $object = $this->$property;
                    if (method_exists($object, "__toArray"))
                    {
                        $object = $object->__toArray();
                    }
                    else if (method_exists($sub_value, "toArray"))
                    {
                        $object = $sub_value->toArray();
                    }
                    else
                    {
                        $object = (array) $sub_value;
                    }
                    
                    # Save the value
                    $output_array[$this->__exportAlias[$property]][$property] =
                        $object;
                    break;
            }
        }
        
        # Return the array
        return $output_array;
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
        return $this->__toArray();
    }
    
    /**
     * Imports values from an object or array into the existing object. 
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param mixed $object
     *   Object or array to import values from
     * @param bool $strict
     *   Whether to perform strict property checking (on by default)
     * @throws BadRequestException
     */
    public function __import($object, $strict = true)
    {
        # Make sure object is an object or array
        if (is_array($object) || is_object($object))
        {
            # Set the value of strict flag
            $this->__strictPropertyChecks = $strict;
        
            # Go through the object and import its values
            foreach($object as $key => $value)
            {
                $this->$key = $value;
            }
            
            # Reset the strict value (strict from now on)
            $this->__strictPropertyChecks = true;
            
            # Declare we have changes
            $this->__hasChanges = true;
        }
        else
        {
            throw new BadRequestException(
                "Importing from object requires an object or array");
        }
        
        # Store the default values
        foreach($this->__properties as $property)
        {
            $this->__defaultValues[$property] = $this->$property;
        }
        
        # Validate all the values
        #$this->__validate();
        $this->__performCasts();
    }
    
    /**
     * Sets the view of the object to the specified view. Developers need to
     * override this object and create their own implementation. If no alternate
     * views are desired, this method can be left alone and any calls to it will
     * throw InvalidRecordViewException.
     * 
     * To reset the view to the default, ommit the view parameter or set it to
     * null.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string $view View to set
     * @return array Associative array of the object in its current view
     * @throws \Cougar\Exceptions\Exception
     */
    public function __setView($view = null)
    {
        # See if we are setting the view to the default
        if (! $view)
        {
            $view = "__default__";
        }
        
        # See if this view exists
        if (! array_key_exists($view, $this->__views))
        {
            throw new Exception(get_class($this) .
                " does not implement the \"" . $view . "\" view");
        }

        # Point the protected properties to the values in the view
        $this->__exportAlias = &$this->__views[$view]["exportAlias"];
        $this->__optional = &$this->__views[$view]["optional"];
        $this->__visible = &$this->__views[$view]["visible"];
        $this->__currentView = $view;
    }

    
    /**
     * Validates that all properties are of the right type and follow their
     * attributes. This method may be overridden to provide additional
     * constraint checks.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     * 2014.03.27:
     *   (AT)  Only validate if the object has not changed; this allows the
     *         model to be exported with its default values without throwing
     *         validation errors
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * @throws \Cougar\Exceptions\Exception
     * @throws \Cougar\Exceptions\BadRequestException
     */
    public function __validate()
    {
        # See if the values have changed
        if ($this->__validationWithDefaultValuesOk)
        {
            $values_changed = false;
            foreach($this->__properties as $property)
            {
                if ($this->$property !== $this->__defaultValues[$property])
                {
                    $values_changed = true;
                    break;
                }
            }

            if (! $values_changed)
            {
                // We don't have any changes; consider the validation complete
                return;
            }
        }

        # Perform the casts
        $this->__performCasts();

        # See if the class has a __preValidate method
        if (method_exists($this, "__preValidate"))
        {
            $this->__preValidate();
        }
        
        # Go through the data types and make sure the data is the right type
        foreach($this->__properties as $property)
        {
            # See if the property is null and whether it can be null
            if ($this->__null[$property] && $this->$property === null)
            {
                # This property is fine
                continue;
            }
            
            # See if the property is read-only
            if ($this->__enforceReadOnly && $this->__readOnly[$property] &&
                $this->$property !== $this->__defaultValues[$property])
            {
                throw new BadRequestException("Cannot modify " . $property .
                    ": property is read-only");
            }
            
            # Check for property constraints
            switch($this->__type[$property])
            {
                case "string":
                    # Enforce regex constraints (if any)
                    if ($this->__regex[$property])
                    {
                        $match = false;
                        foreach($this->__regex[$property] as $regex)
                        {
                            $result = preg_match($regex, $this->$property);
                            
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
                            throw new BadRequestException($property .
                                " property does not conform to accepted" . 
                                " values");
                        }
                    }
                    break;
            }
        }
        
        # See if the class has a __postValidate method
        if (method_exists($this, "__postValidate"))
        {
            $this->__postValidate();
        }
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
        return $this->{$this->__properties[$this->__iterator]};
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
        return $this->__exportAlias[$this->__properties[$this->__iterator]];
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
        # Increase the iterator position
        $this->__iterator++; 
        
        # Make sure the property is valid, that it is visible and not optional
        $property_count = count($this->__properties);
        for ($this->__iterator;
            $this->__iterator < $property_count;
            $this->__iterator++)
        {
            # Move to the next property if it is hidden
            if (! $this->__visible[
                    $this->__properties[$this->__iterator]])
            {
                continue;
            }
            
            # Move to the next property if it is optional and has a null value
            if ($this->__optional[$this->__properties[$this->__iterator]] &&
                $this->{$this->__properties[$this->__iterator]} === null)
            {
                continue;
            }
            
            # The property is valid; break the loop
            break;
        }
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
        # Validate the values
        #$this->__validate();
        $this->__performCasts();
        
        # Move back one position past the start
        $this->__iterator = -1;
        
        # Get the next property
        $this->next();
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
     * @var array List of public properties
     */
    protected $__properties = array();
    
    /**
     * @var array Default property values (used to check read-only status)
     */
    protected $__defaultValues = array();
    
    /**
     * @var bool Whether there have been any changes to properties
     */
    protected $__hasChanges = false;

    /**
     * @var bool Whether to allow validation to pass with default values
     */
    protected $__validationWithDefaultValuesOk = true;

    /**
     * @var array Property type
     */
    protected $__type = array();
    
    /**
     * @var array Whether the property is read-only (used by subclasses)
     */
    protected $__readOnly = array();
    
    /**
     * @var array Whether the property can have a null value
     */
    protected $__null = array();
    
    /**
     * @var array List of regex expressions for a property
     */
    protected $__regex = array();
    
    /**
     * @var array Date/Time format
     */
    protected $__dateTimeFormat = array();
    
    /**
     * @var array Aliases to the property
     */
    protected $__alias = array();
    
    /**
     * @var array Property alias to use on export; may be modified through views
     */
    protected $__exportAlias = array();
    
    /**
     * @var array Whether the property is visible in the current view
     */
    protected $__visible = array();
    
    /**
     * @var array Whether the property should not be exported if null
     */
    protected $__optional = array();
    
    /**
     * @var bool Whether property names are case-insenstive
     */
    protected $__caseInsensitive = false;
    
    /**
     * @var bool Whether to enforce read-only flags
     */
    protected $__enforceReadOnly = true;
    
    /**
     * @var bool Whether to perform strict property checking on load
     */
    protected $__strictPropertyChecks = true;
    
    /**
     * @var array An assoc. array of views and their corresponding data
     */
    protected $__views = array("__default__" => array(
        "exportAlias" => array(),
        "optional" => array(),
        "visible" => array()
    ));
    
    /**
     * @var string Current view
     */
    protected $__currentView = "__default__";
    
    /**
     * @var int Current position of the iterator
     */
    protected $__iterator = -1;
    
    /**
     * @var array Execution record property cache
     */
    protected static $__executionCache = array();
    
    /**
     * Performs all property casts.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     * 2014.01.09:
     *   (AT)  Fixed array check when validating array value
     * 2014.02.13:
     *   (AT)  Check if an array value is in JSON; this allows the PDO Model to
     *         convert arrays into JSON for storage and for the incoming data to
     *         be converted back to an array.
     *
     * @version 2014.02.13
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     */
    public function __performCasts()
    {
        # Go through the data types and make sure the data is the right type
        foreach($this->__properties as $property)
        {
            # See if the property is null and whether it can be null
            if ($this->__null[$property] && $this->$property === null)
            {
                # This property is fine
                continue;
            }
            
            # Check the property type and cast as necessary
            switch($this->__type[$property])
            {
                case "string":
                    $this->$property = (string) $this->$property;
                    break;
                case "int":
                    $this->$property = (int) $this->$property;
                    break;
                case "float":
                    $this->$property = (float) $this->$property;
                    break;
                case "bool":
                    $this->$property = (bool) $this->$property;
                    break;
                case "array":
                    // If the string is JSON, convert to an array
                    if (is_string($this->$property))
                    {
                        try
                        {
                            $this->$property = json_decode($this->$property,
                                true);
                        }
                        catch (\Exception $e)
                        {
                            // Ignore the error
                        }
                    }

                    // See if we have an array
                    if (! is_array($this->$property))
                    {
                        throw new BadRequestException(
                            $property . " property must be an array");
                    }
                    break;
                case "DateTime":
                    if ($this->$property)
                    {
                        if (is_string($this->$property))
                        {
                            $this->$property = new DateTime($this->$property);
                            $this->$property->format =
                                $this->__dateTimeFormat[$property];
                        }
                        else if (! $this->$property instanceof DateTime)
                        {
                            throw new Exception($property .
                                " property must be an instance of " .
                                "Cougar\\Util\\DateTime");
                        }
                    }
                    break;
                default:
                    if (! $this->$property instanceof $this->__type[$property])
                    {
                        throw new Exception($property .
                            " property must be an instance of " .
                            $this->__type[$property]);
                    }
            }
        }
    }
}
?>
