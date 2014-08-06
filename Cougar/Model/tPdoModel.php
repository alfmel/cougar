<?php

namespace Cougar\Model;

use PDO;
use Cougar\Cache\iCache;
use Cougar\Cache\Cache;
use Cougar\Cache\CacheFactory;
use Cougar\Security\iSecurity;
use Cougar\Util\Annotations;
use Cougar\Util\Arrays;
use Cougar\Util\QueryParameter;
use Cougar\Exceptions\Exception;
use Cougar\Exceptions\AccessDeniedException;
use Cougar\Exceptions\BadRequestException;
use Cougar\Exceptions\RecordNotFoundException;

# Initialize the framework (disabled; should have been done by application)
#require_once(__DIR__ . "/../../cougar.php");

/**
 * The PDO Model trait allows programmers to easily extend Model objects to map
 * them to a relational database via PDO. PDO Models can be mapped to multiple
 * tables either through a 1:1 join, or via child objects. PDO Models support
 * basic CRUD operations (or INSERT, SELECT, UPDATE, DELETE in SQL lingo). They
 * also allow for the searching and retrieval of multiple records.
 *
 * For full information on how to use a PdoModel, see the documentation for the
 * PdoModel class.
 *
 * @history
 * 2013.09.30:
 *   (AT)  Initial release
 * 2013.10.24:
 *   (AT)  Refactor authorization() call
 * 2013.10.25:
 *   (AT)  Add security checks to save()
 *   (AT)  Match all security-related exceptions
 * 2013.10.28:
 *   (AT)  Perform extra __allowRead check after loading the record
 *   (AT)  Add property aliases to SELECT query in getRecord();
 * 2013.11.25:
 *   (AT)  Add support for _limit (_count) and _offset (_skip) parameters to
 *         query() method
 *   (AT)  Set default query() row limit to 10,000
 * 2014.02.13:
 *   (AT)  Export array properties to JSON when saving
 * 2014.02.18:
 *   (AT)  Allow unbound properties; they are still part of the model but they
 *         are not part of the queries. Useful for calculated values and such.
 *   (AT)  Fix unexpected exception when saving a DateTime property with a null
 *         value
 * 2014.02.27:
 *   (AT)  Fix bad logic when setting limit and offset on OCI
 * 2014.03.05:
 *   (AT)  Added queryUnique flag
 * 2014.03.06:
 *   (AT)  Make sure the keys in an array are in the proper case when returning
 *         an array in query() and using the OCI driver
 * 2014.03.18:
 *   (AT)  Add support for endPersistence()
 *   (AT)  Include the statement bound values when debugging statements
 * 2014.03.27:
 *   (AT)  Make sure we fully validate the object when saving or exporting as an
 *         array but still allow the object to be exported with its default
 *         values
 * 2014.04.02:
 *   (AT)  Fix bug where values changed via __import() method would not be saved
 *         to the database
 *   (AT)  Allow values that may not conform with a property's constraints to be
 *         loaded from the database
 * 2014.04.24:
 *   (AT)  When querying, make sure objects that implement the iModel interface
 *         are validated
 * 2014.08.05:
 *   (AT)  Cast all values before fetching the record to ensure values are
 *         handled properly
 *   (AT)  Properly handle query parameters that contain embedded query
 *         parameters
 * 2014.08.06:
 *   (AT)  Turn the execution cache into a proper memory cache
 *
 * @version 2014.08.06
 * @package Cougar
 * @license MIT
 *
 * @copyright 2013-2014 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
trait tPdoModel
{
    use tModel
    {
        __construct as protected __constructModel;
    }

    /**
     * Initializes the database record. If the object provides values for the
     * primary key properties, then the record is loaded from the database. If
     * no, a new record is created.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     * 2013.10.24:
     *   (AT)  Change authorization() call parameter order
     * 2014.02.18:
     *   (AT)  Add support for the unbound annotation
     * 2014.04.02:
     *   (AT)  Switch from using __defaultValues to __previousValues and
     *         __persistenceValues
     * 2014.08.06:
     *   (AT)  Turn the execution cache into a proper memory cache
     *
     * @version 2014.08.06
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param \Cougar\Security\iSecurity $security
     *   Security context
     * @param \Cougar\Cache\iCache $cache
     *   Cache object
     * @param \PDO $pdo
     *   Database connection
     * @param mixed $object
     *   Object or assoc. array of property values
     * @param string $view
     *   Set view on load
     * @param bool $strict
     *  Whether to perform strict property checking (on by default)
     * @throws \Cougar\Exceptions\Exception
     */
    public function __construct(iSecurity $security, iCache $cache,
        \PDO $pdo, $object = null, $view = null, $strict = true)
    {
        # Get a local cache
        # TODO: Set through static property(?)
        $local_cache = CacheFactory::getLocalCache();
        $execution_cache = CacheFactory::getMemoryCache();

        # Store the object references
        $this->__security = $security;
        $this->__cache = $cache;
        $this->__pdo = $pdo;
        
        # Add the class name to the default cache prefix
        $this->__cachePrefix .= "." . get_class($this);
        
        # Create our own cache keys
        $class = get_class($this) . ".PdoModel";
        $cache_key = Annotations::$annotationsCachePrefix . "." . $class;
        
        # Call the parent constructor
        $this->__constructModel(null, $view);

        # See if the execution cache has the object properties
        $parsed_annotations = $execution_cache->get($cache_key);
        if (! $parsed_annotations)
        {
            # See if the annotations came from the cache
            if ($this->__annotations->cached)
            {
                $parsed_annotations = $local_cache->get($cache_key);
            }
        }
        
        # See if we have pre-parsed annotations
        if ($parsed_annotations === false)
        {
            # Go through the class annotations
            foreach($this->__annotations->class as $annotation)
            {
                switch ($annotation->name)
                {
                    case "Table":
                        # Only take the first table value
                        if (! $this->__table)
                        {
                            $this->__table = $annotation->value;
                        }
                        break;
                    case "Allow":
                        # Set all the operation to false
                        $this->__allowCreate = false;
                        $this->__allowRead = false;
                        $this->__allowUpdate = false;
                        $this->__allowDelete = false;
                        $this->__allowQuery = false;

                        # See which operations will be allowed
                        foreach(preg_split('/\s+/u',
                            strtolower($annotation->value)) as $operation)
                        {
                            switch($operation)
                            {
                                case "create":
                                case "insert":
                                    $this->__allowCreate = true;
                                    break;
                                case "read":
                                case "select":
                                    $this->__allowRead = true;
                                    break;
                                case "update":
                                    $this->__allowUpdate = true;
                                    break;
                                case "delete";
                                    $this->__allowDelete = true;
                                    break;
                                case "query":
                                case "list":
                                    $this->__allowQuery = true;
                            }
                        }
                        break;
                    case "Join":
                    case "JOIN":
                        if ($annotation->value)
                        {
                            # See if the join already has the word JOIN
                            if (stripos($annotation->value, "join") === false)
                            {
                                $this->__joins[] = "JOIN " . $annotation->value;
                            }
                            else
                            {
                                $this->__joins[] = $annotation->value;
                            }
                        }
                        break;
                    case "PrimaryKey":
                        if ($annotation->value)
                        {
                            foreach(preg_split('/\s+/u', $annotation->value) as
                                $property)
                            {
                                if (in_array($property, $this->__properties))
                                {
                                    $this->__primaryKey[] = $property;
                                }
                                else
                                {
                                    throw new Exception("Specified primary key " .
                                        "property " . $property .
                                        " does not exist");
                                }
                            }
                        }
                        break;
                    case "ReadOnly":
                        if ($annotation->value)
                        {
                            foreach(preg_split('/\s+/u', $annotation->value) as
                                $property)
                            {
                                if (array_key_exists($property,
                                    $this->__readOnly))
                                {
                                    $this->__readOnly[$property] = true;
                                }
                                else
                                {
                                    throw new Exception(
                                        "Specified read-only property ".
                                        $property . " does not exist");
                                }
                            }
                        }
                        break;
                    case "DeleteFlag":
                        if ($annotation->value)
                        {
                            $tmp_array = preg_split('/\s+/u',
                                $annotation->value, 2);
                            if (count($tmp_array )!= 2)
                            {
                                throw new Exception("You must specify a " .
                                    "property name and value with " .
                                    "@DeleteFlag annotation");
                            }
                            if (in_array($tmp_array[0], $this->__properties))
                            {
                                $this->__deleteProperty = $tmp_array[0];
                                $this->__deletePropertyValue = $tmp_array[1];
                            }
                            else
                            {
                                throw new Exception("Delete flag property ");
                            }
                        }
                        break;
                    case "QueryList":
                        if ($annotation->value)
                        {
                            foreach(preg_split('/\s+/u', $annotation->value) as
                                $property)
                            {
                                if (in_array($property, $this->__properties))
                                {
                                    $this->__queryProperties[] = $property;
                                }
                                else
                                {
                                    throw new Exception(
                                        "Specified query property ".
                                        $property . " does not exist");
                                }
                            }
                        }
                        break;
                    case "QueryView":
                        if ($annotation->value)
                        {
                            if (array_key_exists(
                                $annotation->value, $this->__views))
                            {
                                $this->__queryView = $annotation->value;
                            }
                        }
                        break;
                    case "QueryUnique":
                        $this->__queryUnique = true;
                        break;
                    case "NoQuery":
                        # Here for backward compatibility
                        $this->__allowQuery = false;
                        break;
                    case "CachePrefix":
                        if ($annotation->value)
                        {
                            $this->__cachePrefix = $annotation->value;
                        }
                        break;
                    case "CacheTime":
                        if ($annotation->value)
                        {
                            $this->__cacheTime = (int) $annotation->value;
                        }
                        break;
                    case "VoidCacheEntry":
                        if ($annotation->value)
                        {
                            $this->__voidCacheEntries[] = $annotation->value;
                        }
                        break;
                    case "NoCache":
                        $this->__noCache = true;
                        $this->__noQueryCache = true;
                        break;
                    case "NoQueryCache":
                        $this->__noQueryCache = true;
                        break;
                }
            }

            # Make sure the table name has been defined
            if (! $this->__table)
            {
                throw new Exception("You must specify a table name using the " .
                    "@Table annotation in the class document block");
            }

            # Make sure we have a primary key
            if (! $this->__primaryKey)
            {
                throw new Exception("You must specify the columns that make ".
                    "up the Primary Key using the @PrimaryKey annotation in " .
                    "the class document block");
            }

            # Go through the properties
            foreach($this->__annotations->properties as $property_name => 
                $annotations)
            {
                # Create the property in the column map
                $this->__columnMap[$property_name] = $property_name;

                # Go through the annotations
                foreach($annotations as $annotation)
                {
                    switch ($annotation->name)
                    {
                        case "Column":
                            $this->__columnMap[$property_name] =
                                $annotation->value;
                            break;
                        case "Unbound":
                            unset($this->__columnMap[$property_name]);
                            break;
                        case "ReadOnly":
                            $this->__readOnly[$property_name] = true;
                    }
                }
            }
            
            # See if we had query properties
            if (! $this->__queryProperties)
            {
                # Declare all properties are queryable
                $this->__queryProperties = array_keys($this->__columnMap);
            }
            
            # Store the record properties in the caches
            $parsed_annotations = array(
                "table" => $this->__table,
                "primaryKey" => $this->__primaryKey,
                "allowSelect" => $this->__allowRead,
                "allowInsert" => $this->__allowCreate,
                "allowUpdate" => $this->__allowUpdate,
                "allowDelete" => $this->__allowDelete,
                "joins" => $this->__joins,
                "deleteProperty" => $this->__deleteProperty,
                "deletePropertyValue" => $this->__deletePropertyValue,
                "queryProperties" => $this->__queryProperties,
                "allowQuery" => $this->__allowQuery,
                "queryView" => $this->__queryView,
                "queryUnique" => $this->__queryUnique,
                "cachePrefix" => $this->__cachePrefix,
                "cacheTime" => $this->__cacheTime,
                "voidCacheEntries" => $this->__voidCacheEntries,
                "noCache" => $this->__noCache,
                "noQueryCache" => $this->__noQueryCache,
                "columnMap" => $this->__columnMap,
                "readOnly" => $this->__readOnly
            );

            $execution_cache->set($cache_key, $parsed_annotations);
            $local_cache->set($cache_key, $parsed_annotations,
                Annotations::$cacheTime);
        }
        else
        {
            # Restore the property values
            $this->__table = $parsed_annotations["table"];
            $this->__primaryKey = $parsed_annotations["primaryKey"];
            $this->__allowRead = $parsed_annotations["allowSelect"];
            $this->__allowCreate = $parsed_annotations["allowInsert"];
            $this->__allowUpdate = $parsed_annotations["allowUpdate"];
            $this->__allowDelete = $parsed_annotations["allowDelete"];
            $this->__joins = $parsed_annotations["joins"];
            $this->__deleteProperty = $parsed_annotations["deleteProperty"];
            $this->__deletePropertyValue =
                $parsed_annotations["deletePropertyValue"];
            $this->__queryProperties = $parsed_annotations["queryProperties"];
            $this->__allowQuery = $parsed_annotations["allowQuery"];
            $this->__queryView = $parsed_annotations["queryView"];
            $this->__queryUnique = $parsed_annotations["queryUnique"];
            $this->__cachePrefix = $parsed_annotations["cachePrefix"];
            $this->__cacheTime = $parsed_annotations["cacheTime"];
            $this->__voidCacheEntries = $parsed_annotations["voidCacheEntries"];
            $this->__noCache = $parsed_annotations["noCache"];
            $this->__noQueryCache = $parsed_annotations["noQueryCache"];
            $this->__columnMap = $parsed_annotations["columnMap"];
            $this->__readOnly = $parsed_annotations["readOnly"];
        }

        # See if the object we received was an object or array
        if (is_array($object) || is_object($object))
        {
            # Separate the object's values into primary key and other values
            $pk_values = array();
            $values = array();
            $has_primary_key_values = false;
            
            foreach($object as $key => $value)
            {
                if ($this->__caseInsensitive)
                {
                    $key = strtolower($key);
                }

                # See if this is a value we handle
                if (array_key_exists($key, $this->__alias))
                {
                    # Resolve the alias
                    $key = $this->__alias[$key];
                    
                    # See if this is a primary key value
                    if (in_array($key, $this->__primaryKey))
                    {
                        # Add the value to our list of primary key values
                        $pk_values[$key] = $value;
                        
                        # See if the value is not equivalent to default
                        if ($value != $this->{$this->__alias[$key]})
                        {
                            # This is a primary key value; save the value
                            $has_primary_key_values = true;
                        }
                    }
                    else
                    {
                        # This is another value; store it separately
                        $values[$key] = $value;
                    }
                }
            }
            
            # See if we have primary key values
            if ($has_primary_key_values)
            {
                # Set the PK properties
                foreach($pk_values as $key => $value)
                {
                    $this->$key = $value;
                }
                
                # Get the record; method will also cast values
                $this->getRecord();

                # Call the authorization method; we call it after we get the
                # record since the authorization may be based on the values
                $this->authorization($this->__security, $this->__allowCreate,
                    $this->__allowRead, $this->__allowUpdate,
                    $this->__allowDelete, $this->__allowQuery,
                    $this->__columnMap, $this->__readOnly, $this->__visible);

                # Make sure the identity is authorized to read the record
                if (! $this->__allowRead)
                {
                    throw new AccessDeniedException(
                        "You do not have access to this record");
                }
            }
            else
            {
                # Set up insert mode
                $this->__insertMode = true;
                $this->__enforceReadOnly = false;

                # Set the persistent values from the previous values
                $this->__persistentValues = $this->__previousValues;
            }
            
            # Set the other properties via the __import method
            $this->__import($values);
        }
        else
        {
            # Set the persistent values from the previous (default) values
            $this->__persistentValues = $this->__previousValues;

            # Call the authorization method
            $this->authorization($this->__security, $this->__allowCreate,
                $this->__allowRead, $this->__allowUpdate, $this->__allowDelete,
                $this->__allowQuery, $this->__columnMap, $this->__readOnly,
                $this->__visible);
            
            $this->__insertMode = true;
            $this->__enforceReadOnly = false;
        }
    }
    
    
    /***************************************************************************
     * PUBLIC PROPERTIES AND METHODS
     **************************************************************************/
    
    /**
     * Saves the record, either creating the new record in the database, or
     * updating the existing record.
     * 
     * Please note that this method will NOT issue a commit. The commit must
     * be issued in the PDO object provided at instantiation time.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     * 2013.10.25:
     *   (AT)  Add missing security checks :-D
     * 2014.02.18:
     *   (AT)  Make sure we use the bound properties rather than all properties
     *         When creating queries
     *   (AT)  Make sure we only call format() method on DateTime object, not on
     *         a null value
     * 2014.03.18:
     *   (AT)  Make sure the method is still persistent
     * 2014.03.27:
     *   (AT)  Make sure the object is fully validated
     * 2014.04.02:
     *   (AT)  Switch from using __defaultValues to __previousValues and
     *         __persistenceValues
     *
     * @version 2014.04.02
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @throws \Cougar\Exceptions\Exception
     */
    public function save()
    {
        # Make sure we are still persistent
        if (! $this->__persistent)
        {
            throw new Exception("Save is no longer allowed on this model");
        }

        # See if we are inserting or updating
        if ($this->__insertMode)
        {
            # Make sure we can create new records
            if (! $this->__allowCreate)
            {
                throw new AccessDeniedException(
                    "You are not allowed to create this record");
            }

            # Make sure we validate all values
            $this->__validateAllValues = true;

            # Validate the object
            $this->__validate();

            // Create the list of variable parameters
            $vars = array();
            foreach(array_keys($this->__columnMap) as $property)
            {
                $vars[] = ":" . $property;
            }

            # Create the INSERT statement
            $statement = "INSERT INTO " . $this->__table . " (" .
                implode(", ", $this->__columnMap) . ") VALUES(" .
                implode(", ", $vars) . ")";
            
            # Prepare the statement
            $pdo_statement = $this->__pdo->prepare($statement);
            
            # Get the values
            $values = array();
            foreach(array_keys($this->__columnMap) as $property)
            {
                // See what the type is
                switch($this->__type[$property])
                {
                    case "DateTime":
                        // See if we have a value
                        if ($this->$property)
                        {
                            // Set the proper date/time
                            switch($this->__dateTimeFormat[$property])
                            {
                                case "Date":
                                    $date_format = "Y-m-d";
                                    break;
                                case "Time":
                                    $date_format = "H:i:s";
                                    break;
                                case "DateTime":
                                default:
                                    $date_format = "Y-m-d H:i:s";
                                    break;
                            }
                            $values[$property] =
                                $this->$property->format($date_format);
                        }
                        else
                        {
                            $values[$property] = null;
                        }
                        break;
                    case "array":
                        // Convert to JSON
                        $values[$property] = json_encode($this->$property);
                        break;
                    default:
                        // Pass the value
                        $values[$property] = $this->$property;
                        break;
                }
            }
            
            # Execute the statement
            if ($this->__debug)
            {
                error_log("PdoModel Insert: " . $statement);
                error_log("Insert values: " . print_r($values, true));
            }
            $pdo_statement->execute($values);

            if ($pdo_statement->rowCount() !== 1)
            {
                throw new Exception("Row was not inserted");
            }
            
            # See if the primary key was specified
            foreach($this->__primaryKey as $primary_key_column)
            {
                if ($this->$primary_key_column === null)
                {
                    # Save the value from the last inserted ID
                    $this->$primary_key_column =
                        $this->__pdo->lastInsertId();
                    $values[$primary_key_column] = $this->$primary_key_column;

                    # Exit the loop since we don't know what to do with the
                    # other values
                    break;
                }
            }
            
            # Save the entry in the cache
            if (! $this->__noCache)
            {
                $this->__cache->set($this->getCacheKey(), $values,
                    $this->__cacheTime);
            }
            
            # Turn off insert mode and turn on read-only checks
            $this->__insertMode = false;
            $this->__enforceReadOnly = true;
        }
        else
        {
            # Make sure we can create new records
            if (! $this->__allowUpdate)
            {
                throw new AccessDeniedException(
                    "You are not allowed to update this record");
            }

            # Validate the object
            $this->__validate();

            # See which columns have changed
            $new_values = array();
            $set_declarations = array();
            foreach(array_keys($this->__columnMap) as $property)
            {
                $value = $this->$property;
                
                if ($value !== $this->__persistentValues[$property])
                {
                    // See what type this property is
                    switch($this->__type[$property])
                    {
                        case "DateTime":
                            // See if we have a value
                            if ($this->$property)
                            {
                                // Set the proper date/time
                                switch($this->__dateTimeFormat[$property])
                                {
                                    case "Date":
                                        $date_format = "Y-m-d";
                                        break;
                                    case "Time":
                                        $date_format = "H:i:s";
                                        break;
                                    case "DateTime":
                                    default:
                                        $date_format = "Y-m-d H:i:s";
                                        break;
                                }
                                $new_values[$property] =
                                    $this->$property->format($date_format);
                            }
                            else
                            {
                                $new_values[$property] = null;
                            }
                            break;
                        case "array":
                            // Convert to JSON
                            $new_values[$property] =
                                json_encode($this->$property);
                            break;
                        default:
                            // Pass the value
                            $new_values[$property] = $this->$property;
                            break;
                    }

                    # Add the set declaration
                    $set_declarations[] = $this->__columnMap[$property] .
                        " = :" . $property;
                }
            }
            
            # See if we had any changes
            if (count($set_declarations) > 0)
            {
                # Create the statement
                $statement = "UPDATE " . $this->__table . " SET " .
                    implode(", ", $set_declarations) . " " .
                    $this->buildWhereClause();
                
                # Prepare the statement
                $pdo_statement = $this->__pdo->prepare($statement);
                
                # Execute the statement
                if ($this->__debug)
                {
                    error_log("PdoModel Update: " . $statement);
                    error_log("Update values: " . print_r(array_merge(
                            $new_values, $this->getWhereParameters()), true));
                }
                $pdo_statement->execute(
                    array_merge($new_values, $this->getWhereParameters()));
                
                if ($pdo_statement->rowCount() > 1)
                {
                    throw new Exception("Too many rows updated");
                }
                
                # Save the entry in the cache
                if (! $this->__noCache)
                {
                    $cache_entry = array();
                    foreach(array_keys($this->__columnMap) as $property)
                    {
                        $cache_entry[$property] = $this->$property;
                    }
                    $this->__cache->set($this->getCacheKey(), $cache_entry,
                        $this->__cacheTime);
                }
            }
        }
            
        # Sync the initial values and save the values that changed
        $this->__lastChanges = array();
        foreach(array_keys($this->__columnMap) as $property)
        {
            if ($this->__persistentValues[$property] !== $this->$property)
            {
                $this->__lastChanges[$property] =
                    $this->__persistentValues[$property];
            }
            $this->__persistentValues[$property] = $this->$property;
        }
    }
    
    /**
     * Deletes the record from the database.
     * 
     * Please note that this method will NOT issue a commit. The commit must
     * be issued in the PDO provided at instantiation time.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     * 2013.10.25:
     *   (AT)  Improve thrown exceptions
     * 2014.03.18:
     *   (AT)  Make sure the method is still persistent
     *
     * @version 2014.03.18
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @throws \Cougar\Exceptions\Exception
     * @throws \Cougar\Exceptions\AccessDeniedException
     * @throws \Cougar\Exceptions\BadRequestException
     */
    public function delete()
    {
        # Make sure we are still persistent
        if (! $this->__persistent)
        {
            throw new Exception("Delete is no longer allowed on this model");
        }

        # Make sure the record can be deleted
        if (! $this->__allowDelete)
        {
            throw new AccessDeniedException(
                "You are not allowed to delete record");
        }
        
        # See if we are in insert mode
        if ($this->__insertMode)
        {
            throw new BadRequestException(
                "Cannot delete record: it doesn't exist!");
        }
        
        # See if we just need to flag a property as deleted
        if ($this->__deleteProperty)
        {
            # Prepare the UPDATE statement
            $statement = "UPDATE " . $this->__table . " " .
                "SET " . $this->__columnMap[$this->__deleteProperty] .
                    "= :" . $this->__deleteProperty . " ".
                $this->buildWhereClause();
            $values = $this->getWhereParameters();
            $values[$this->__deleteProperty] = $this->__deletePropertyValue;
        }
        else
        {
            # Prepare the DELETE statement
            $statement = "DELETE FROM " . $this->__table . " " .
                $this->buildWhereClause();
            $values = $this->getWhereParameters();
        }
        
        # Prepare the statement
        $pdo_statement = $this->__pdo->prepare($statement);

        # Execute the statement
        if ($this->__debug)
        {
            error_log("PdoModel Delete: " . $statement);
            error_log("Delete values: " . print_r($values, true));
        }
        $pdo_statement->execute($values);

        if ($pdo_statement->rowCount() > 1)
        {
            throw new Exception("More than one record was deleted");
        }
        else if ($pdo_statement->rowCount() == 0)
        {
            throw new Exception("The record was not deleted");
        }
        
        # Delete the entry from the cache
        if (! $this->__noCache)
        {
            $this->__cache->clear($this->getCacheKey());
        }
    }

    /**
     * Returns a list of records with the given values
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     * 2013.10.25:
     *   (AT)  Improve thrown exceptions
     * 2013.11.25:
     *   (AT)  Add support for _limit and _offset query parameters
     *   (AT)  Set default limit to 10,000 rows
     * 2014.02.27:
     *   (AT)  Fix bad logic when setting limit and offset on OCI
     * 2014.03.04:
     *   (AT)  Split function into a query-generating function and a query
     *         execution part to make it easier to extend query functionality
     * 2014.03.05:
     *   (AT)  Handle queryUnique flag
     * 2014.03.06:
     *   (AT)  Rename array keys when using the OCI driver
     * 2014.03.18:
     *   (AT)  Make sure the method is still persistent
     *
     * @version 2014.03.18
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param array $parameters
     *   List of query parameters
     * @param string $class_name
     *   Use array to return list as an array, or class name to return objects
     * @param array $ctorargs
     *   Constructor arguments if returning objects
     * @return array Record list
     * @throws \Cougar\Exceptions\Exception;
     * @throws \Cougar\Exceptions\AccessDeniedException;
     */
    public function query(array $parameters = array(), $class_name = "array",
        array $ctorargs = array())
    {
        # Make sure we are still persistent
        if (! $this->__persistent)
        {
            throw new Exception("Querying is no longer allowed on this model");
        }

        # See if querying is allowed
        if (! $this->__allowQuery)
        {
            throw new AccessDeniedException(
                "This model does not support querying");
        }
        
        # Set the view (if it hasn't been changed)
        if ($this->__currentView == "__default__" &&
            $this->__queryView !== "__default__")
        {
            $this->__setView($this->__queryView);
        }

        # Extract the columns and aliases for the columns we can query
        $query_aliases =
            array_intersect($this->__alias, $this->__queryProperties);
        $columns = array();
        $key_map = array();
        foreach($this->__queryProperties as $property)
        {
            if ($this->__visible[$property])
            {
                if ($this->__exportAlias[$property] ==
                    $this->__columnMap[$property])
                {
                    $columns[$property] = $this->__columnMap[$property];
                    $key_map[$this->__columnMap[$property]] =
                        $this->__columnMap[$property];;
                }
                else
                {
                    $columns[$property] = $this->__columnMap[$property] .
                        " AS " . $this->__exportAlias[$property];
                    $key_map[$this->__exportAlias[$property]] =
                        $this->__exportAlias[$property];
                }
            }
        }

        # Recursively iterate through the query parameters
        $this->iterateQueryParameters($parameters, $query_aliases, $columns,
            $key_map);

        # Prepare the array that will hold the parameter values
        $values = array();

        # Set the default limit to 10,000 rows
        $limit = 10000;
        $offset = 0;
        $used_parameters = array();

        # Prepare the query and execute the statement
        if ($this->__queryUnique)
        {
            $query = "SELECT DISTINCT ";
        }
        else
        {
            $query = "SELECT ";
        }
        $query .= implode(", ", $columns) .
            " FROM " . $this->__table . " " . implode(" ", $this->__joins);
        $where_clause = QueryParameter::toSql($parameters,
            $this->__columnMap, $query_aliases, $this->__caseInsensitive,
            $values, $used_parameters, $limit, $offset);
        if ($where_clause)
        {
            $query .= " WHERE " . $where_clause;
        }

        # Set the limit and offset
        $limit = (int) $limit;
        $offset = (int) $offset;
        if ($this->__pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "oci")
        {
            if ($where_clause)
            {
                $query .= " AND ";
            }
            else
            {
                $query .= " WHERE ";
            }
            $query .= "ROWNUM > " . $offset .
                " AND ROWNUM <= " . ($offset + $limit);
        }
        else
        {
            $query .= " LIMIT " . $limit .
                " OFFSET " . $offset;
        }

        # Execute the query
        $results = $this->executeQuery($query, $values, $class_name, $ctorargs);

        # Oracle will turn all column names as uppercase. This will rename them
        # if we are returning an array and are using OCI
        if ($class_name == "array" &&
            count($results) > 0 &&
            $this->__pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == "oci")
        {
            # Change the keys in the key map to uppercase
            $key_map = array_change_key_case($key_map, CASE_UPPER);

            # Rename the keys
            $results = Arrays::renameKeys($results, $key_map);
        }

        # Return the result
        return $results;
    }

    /**
     * Unlinks or breaks the model's persistence.
     *
     * After calling this method this method nobody will be able to save, delete
     * or query the persistent model, turning it into a non-persistent model.
     * This allows developers to safely return the model without fear of data
     * being modified or altered outside of their control.
     *
     * @history
     * 2014.03.18:
     *   (AT) Initial definition
     *
     * @version 2014.03.18
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     */
    public function endPersistence()
    {
        // Set the persistent flag to false
        $this->__persistent = false;

        // Unset the database and cache to truly disconnect the model from the
        // persistence layers
        $this->__cache = null;
        $this->__pdo = null;
    }

    /**
     * Returns an associative array with properties that changed since the last
     * save() call and their old values. This is useful for auditing or for
     * debugging.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @return array Changes
     */
    public function lastChanges()
    {
        return $this->__lastChanges;
    }

    /**
     * Enables debugging of SQL statements by writing them to the error log.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     */
    public function enableStatementDebugging()
    {
        $this->__debug = true;
    }

    
    /***************************************************************************
     * PROTECTED PROPERTIES AND METHODS
     **************************************************************************/
    
    /**
     * @var iSecurity Security context
     */
    protected $__security = null;
    
    /**
     * @var pdo The PDO connection
     */
    protected $__pdo = null;
    
    /**
     * @var cache Reference to Cache object
     */
    protected $__cache = null;
    
    /**
     * @var string Cache prefix
     */
    protected $__cachePrefix = "Cougar.Model";
    
    /**
     * @var array Cache entries to void on modification of this object
     */
    protected $__voidCacheEntries = array();
    
    /**
     * @var int Cache time in seconds
     */
    protected $__cacheTime = 3600;
    
    /**
     * @var bool Whether to disable caching
     */
    protected $__noCache = false;
    
    /**
     * @var bool Whether to disable caching
     */
    protected $__noQueryCache = true;
    
    /**
     * @var string The table name (used when creating the queries)
     */
    protected $__table = null;
    
    /**
     * @var array SQL Joins on the table
     */
    protected $__joins = array();
    
    /**
     * @var bool Whether CREATE operation is allowed
     */
    protected $__allowCreate = true;
    
    /**
     * @var bool Whether READ operation is allowed
     */
    protected $__allowRead = true;
    
    /**
     * @var bool Whether UPDATE operation is allowed
     */
    protected $__allowUpdate = true;
    
    /**
     * @var bool Whether DELETE operation is allowed
     */
    protected $__allowDelete = false;
    
    /**
     * @var bool Whether QUERY operation is allowed
     */
    protected $__allowQuery = true;
    
    /**
     * @var string Property used to mark row as deleted
     */
    protected $__deleteProperty = null;
    
    /**
     * @var string Value of the delete property
     */
    protected $__deletePropertyValue = null;
    
    /**
     * @var array List of properties that can be queried
     */
    protected $__queryProperties = array();

    /**
     * @var bool Whether to return unique records on query
     */
    protected $__queryUnique = false;

    /**
     * @var bool The view to use during query
     */
    protected $__queryView = "__default__";
    
    /**
     * @var array A hashed list of property names to column names
     */
    protected $__columnMap = array();
    
    /**
     * @var array Array with the properties that make up the Primary Key
     */
    protected $__primaryKey = array();
    
    /**
     * @var bool True if inserting a new record; false for updating
     */
    protected $__insertMode = false;

    /**
     * @var bool Whether the model is persistent; true by default
     */
    protected $__persistent = true;

    /**
     * @var array The last set of known persistent (saved) values
     */
    protected $__persistentValues = array();

    /**
     * @var array Properties and values that changed during the last save()
     */
    protected $__lastChanges = array();
    
    /**
     * @var bool If true, queries will be sent to the error log for debugging
     */
    protected $__debug = false;

    /**
     * Defines the authorization method which will perform an authorization
     * request and set the proper permissions in the object.
     *
     * This method is called from the constructor and passes the necessary
     * references to the security context and PDO. It also passes an array with
     * the columns that will be accessed by the object. Additionally, references
     * to the boolean operation permissions (select, insert, update and delete)
     * and to the readOnlyPropertyStatus array are passed. The method can modify
     * these permissions based on the result of its authorization results to
     * force the proper object behavior..
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     * 2013.10.24:
     *   (AT)  Change the order of the parameters to make them more logical
     *   (AT)  Add query parameter to control whether queries are allowed
     *   (AT)  Add ability to modify hidden property attribute
     *
     * @version 2013.10.24
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param \Cougar\Security\iSecurity $security
     *   Security context
     * @param bool $create
     *   Whether to allow CREATE operation
     * @param bool $read
     *   Whether to allow READ operation
     * @param bool $update
     *   Whether to allow UPDATE operation
     * @param bool $delete
     *   Whether to allow QUERY operation
     * @param $query
     * @param array $columns
     *   Columns accessed by this object
     * @param array $readOnlyPropertyAttributes
     *   Whether the properties are ready-only
     * @param array $propertyVisibility
     *   Whether the properties are visible
     */
    protected function authorization(iSecurity $security, &$create, &$read,
        &$update, &$delete, &$query, array $columns,
        array &$readOnlyPropertyAttributes, array &$propertyVisibility)
    {
        # Don't do anything; will be overridden as needed.
    }

    /**
     * Loads a record into this object
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     * 2013.10.28:
     *   (AT) Add column aliases to SELECT query to ensure properties are set
     *        properly
     * 2014.04.02:
     *   (AT)  Switch from using __defaultValues to __previousValues and
     *         __persistenceValues
     * 2014.08.05:
     *   (AT)  Cast values before making the query to ensure all values are
     *         passed properly
     *
     * @version 2014.08.05
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     */
    protected function getRecord()
    {
        # See if we can load a record
        if (! $this->__allowRead)
        {
            throw new AccessDeniedException(
                "You may not load a record using this object");
        }
        
        # See if the value is cached
        if ($this->__noCache)
        {
            $cached_record = false;
        }
        else
        {
            $cached_record = $this->__cache->get($this->getCacheKey());
        }
        
        if ($cached_record === false)
        {
            # Cast the values
            $this->__performCasts();

            # See if the columns need aliases
            $columns = array();
            foreach($this->__columnMap as $property => $column)
            {
                if ($property == $column)
                {
                    $columns[] = $column;
                }
                else
                {
                    $columns[] = $column . " AS " . $property;
                }
            }

            # Create the statement
            $statement_query = "SELECT " .
                implode(", ", $columns) .
                " FROM " . $this->__table;
            foreach($this->__joins as $join)
            {
                $statement_query .= " " . $join;
            }
            $statement_query .= " " . $this->buildWhereClause();
            if ($this->__debug)
            {
                error_log("PdoModel Select: " . $statement_query);
                error_log("Select values: " .
                    print_r($this->getWhereParameters(), true));
            }
            $statement = $this->__pdo->prepare($statement_query);
            $statement->setFetchMode(\PDO::FETCH_INTO, $this);

            # Execute the statement
            $statement->execute($this->getWhereParameters());
            
            # Fetch the result
            $fetch_result = $statement->fetch();

            # Make sure we got a row back
            if ($fetch_result === false)
            {
                throw new RecordNotFoundException("Record not found");
            }
            
            # See if we have a second row
            $row_check = $statement->fetch();
            if ($row_check !== false)
            {
                throw new RecordNotFoundException("Multiple records returned");
            }

            # Close the cursor
            $statement->closeCursor();

            # Store the record in the cache
            if (! $this->__noCache)
            {
                $cached_record = array();
                foreach(array_keys($this->__columnMap) as $property)
                {
                    $cached_record[$property] = $this->$property;
                }
                $this->__cache->set($this->getCacheKey(), $cached_record,
                    $this->__cacheTime);
            }
        }
        else
        {
            # Set the properties from the cached models
            foreach($cached_record as $property => $value)
            {
                $this->$property = $value;
            }
        }

        # Perform casts on that values that have just come through
        $this->__performCasts();


        # Store the values from the database in the previous value and
        # persistent value arrays
        foreach(array_keys($this->__columnMap) as $property)
        {
            $this->__previousValues[$property] = $this->$property;
            $this->__persistentValues[$property] = $this->$property;
        }
    }

    /**
     * Executes the given query with the given statement parameters and returns
     * the results as an associative array or or an array of objects if the
     * class name is given.
     *
     * @history
     * 2014.03.04:
     *   (AT)  Initial implementation from the code in the query() method
     * 2014.04.24:
     *   (AT)  Make sure objects that implement iModel interface are validated
     *
     * @version 2014.04.24
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string $query_statement
     * @param array $query_parameters
     *   List of query parameters
     * @param string $class_name
     *   Use array to return list as an array, or class name to return objects
     * @param array $ctorargs
     *   Constructor arguments if returning objects
     * @return array Record list
     * @throws \Cougar\Exceptions\AccessDeniedException;
     */
    protected function executeQuery($query_statement,
        array $query_parameters = array(), $class_name = "array",
        array $ctorargs = array())
    {
        # Hash the query and array to determine the cache key; then check if we
        # have a result in the query cache
        if ($this->__noQueryCache)
        {
            $result = false;
        }
        else
        {
            $cache_key = $this->__cachePrefix . ".Query." .
                md5($query_statement. ":" . serialize($query_parameters) .
                    $class_name . "." . $this->__currentView);
            $result = $this->__cache->get($cache_key);
        }

        if ($result === false)
        {
            # See if we need to display the query
            if ($this->__debug)
            {
                error_log("PdoModel Query: " . $query_statement);
                error_log("Query values: " . print_r($query_parameters, true));
            }
            $statement = $this->__pdo->prepare($query_statement);
            $statement->execute($query_parameters);

            # See what we will be returning
            if ($class_name == "array")
            {
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            }
            else
            {
                $result = $statement->fetchAll(
                    \PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $class_name,
                    $ctorargs);

                # See if the object implements the iModel interface
                if ($result)
                {
                    if ($result[0] instanceof iModel)
                    {
                        # Go through each row and validate the model
                        foreach($result as $row)
                        {
                            $row->__validate();
                        }
                    }
                }
            }

            # Store the results in the cache
            if (! $this->__noQueryCache)
            {
                $this->__cache->set($cache_key, $result, $this->__cacheTime);
            }
        }

        return $result;
    }

    /**
     * Iterates through the query parameters to build the foundation of the SQL
     * query.
     *
     * @history
     * 2014.08.05
     *   (AT)  Initial release
     *
     * @version 2014.08.05
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     */
    protected function iterateQueryParameters(array $parameters,
        array $query_aliases, array &$columns, &$key_map)
    {
        # Go through the query properties
        foreach($parameters as $parameter)
        {
            // See if this is an array of query parameters
            if (is_array($parameter->property))
            {
                // Iterate through these parameters
                $this->iterateQueryParameters($parameter->property,
                    $query_aliases, $columns, $key_map);

                // Go to the next parameter
                continue;
            }

            if ($this->__caseInsensitive)
            {
                $alias = strtolower($parameter->property);
            }
            else
            {
                $alias = $parameter->property;
            }

            if (array_key_exists($alias, $query_aliases))
            {
                $property = $query_aliases[$alias];

                # Make sure this column is visible if we are not querying unique
                # values
                if (! $this->__queryUnique)
                {
                    if ($this->__exportAlias[$property] ==
                        $this->__columnMap[$property])
                    {
                        $columns[$property] = $this->__columnMap[$property];
                        $key_map[$this->__columnMap[$property]] =
                            $this->__columnMap[$property];;
                    }
                    else
                    {
                        $columns[$property] = $this->__columnMap[$property] .
                            " AS " . $this->__exportAlias[$property];
                        $key_map[$this->__exportAlias[$property]] =
                            $this->__exportAlias[$property];
                    }
                }

                # See if the property has a date/time value
                if ($this->__type[$property] == "DateTime")
                {
                    switch($this->__dateTimeFormat[$property])
                    {
                        case "DateTime":
                        default:
                            $date_format = "Y-m-d H:i:s";
                            break;
                        case "Date":
                            $date_format = "Y-m-d";
                            break;
                        case "Time":
                            $date_format = "H:i:s";
                            break;
                    }

                    # Convert the value
                    $parameter->value = date($date_format,
                        strtotime($parameter->value));
                }
            }
        }
    }

    /**
     * Returns the WHERE clause based on the values of the primary key
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @return string WHERE clause
     */
    protected function buildWhereClause()
    {
        $first = true;
        $clause = "";
        foreach($this->__primaryKey as $property)
        {
            if ($first)
            {
                $clause .= "WHERE " . $this->__columnMap[$property] .
                    " = :" . $property;
                $first = false;
            }
            else
            {
                $clause .= " AND " . $this->__columnMap[$property] .
                    " = :" . $property;
            }
        }
        
        return $clause;
    }
    
    /**
     * Returns the values for the where clause based on the primary key
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @return array WHERE parameters
     */
    protected function getWhereParameters()
    {
        # Create the list of parameter values
        $parameters = array();
        foreach($this->__primaryKey as $property)
        {
            if ($this->__type[$property] == "DateTime")
            {
                switch($this->__dateTimeFormat[$property])
                {
                    case "DateTime":
                    default:
                        $date_format = "Y-m-d H:i:s";
                        break;
                    case "Date":
                        $date_format = "Y-m-d";
                        break;
                    case "Time":
                        $date_format = "H:i:s";
                        break;
                }
                $parameters[$property] = $this->$property->format($date_format);
            }
            else
            {
                $parameters[$property] = $this->$property;
            }
        }
        
        return $parameters;
    }
    
    /**
     * Generates the cache key based on the cache prefix and values of the
     * primary key.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @return string Cache key
     */
    protected function getCacheKey()
    {
        # Go through each value in the primary key and build the cache key
        $cache_key = $this->__cachePrefix;
        
        foreach($this->__primaryKey as $property)
        {
            # Get the value from the object properties
            $cache_key .= "." . $this->$property;
        }
        
        # Return the value
        return $cache_key;
    }
}
?>
