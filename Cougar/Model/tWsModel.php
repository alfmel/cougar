<?php

namespace Cougar\Model;

use Cougar\Security\iSecurity;
use Cougar\Cache\iCache;
use Cougar\Cache\CacheFactory;
use Cougar\RestClient\iRestClient;
use Cougar\Util\Annotations;
use Cougar\Util\QueryParameter;
use Cougar\Exceptions\Exception;
use Cougar\Exceptions\NotImplementedException;

# Initialize the framework (disabled; should have been done by application)
#require_once(__DIR__ . "/../../cougar.php");

/**
 * The WS Model trait and class allows programmers to easily extend Model
 * objects and map them to REST web service resources. These resources can be
 * retrieved one at a time or queried to obtain a list of records.
 *
 * For full information on how to use a PdoModel, see the documentation for the
 * WsModel class.
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
trait tWsModel
{
    use tModel
    {
        __construct as protected __constructModel;
    }

    /**
     * Initializes the REST resource. If properties are provided the resource
     * will be loaded. Otherwise, a new resource will be created on save().
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param iSecurity $security Security context
     * @param iCache $cache Cache object
     * @param iRestClient $rest_client Rest client object
     * @param mixed $object Object or assoc. array of property values
     * @param string $view Set view on load
     * @param bool $strict Whether to perform strict property checking (on by default)
     * @throws \Cougar\Exceptions\Exception
     */
    public function __construct(iSecurity $security, iCache $cache,
        iRestClient $rest_client, $object = null, $view = null, $strict = true)
    {
        # Get a local cache
        $local_cache = CacheFactory::getLocalCache();
        
        # Store the object references
        $this->__security = $security;
        $this->__cache = $cache;
        $this->__restClient = $rest_client;
        
        # Add the class name to the default cache prefix
        $this->__cachePrefix .= "." . get_class($this);
        
        # Create our own cache keys
        $class = get_class($this) . ".wsmodel";
        $cache_key = Annotations::$annotationsCachePrefix . "." . $class;
        
        # Call the parent constructor
        $this->__constructModel(null, $view);
        
        # See if the execution cache has the object properties
        $parsed_annotations = false;
        if (array_key_exists($class, self::$__executionCache))
        {
            $parsed_annotations = self::$__executionCache[$class];
        }
        else
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
                    case "Allow":
                        # Set all the operation to false
                        $this->__allowCreate = false;
                        $this->__allowRead = false;
                        $this->__allowUpdate = false;
                        $this->__allowDelete = false;
                        $this->__allowQuery = false;

                        # See which operations will be allowed
                        foreach(preg_split("/\s+/u",
                            strtolower($annotation->value)) as $operation)
                        {
                            switch($operation)
                            {
                                case "create":
                                    $this->__allowCreate = true;
                                    break;
                                case "read":
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
                                    break;
                            }
                        }
                        break;
                    case "BaseUri":
                        $values = preg_split("/\s+/u", $annotation->value, 2);
                        if (ENVIRONMENT == strtolower($values[0]))
                        {
                            $this->__baseUri = $values[1];
                        }
                        else if (ENVIRONMENT == "local" &&
                            strtolower($values[0]) == "development" &&
                            ! $this->__baseUri)
                        {
                            $this->__baseUri = $values[1];
                        }
                        break;
                    case "ResourceID":
                        if ($annotation->value)
                        {
                            foreach(preg_split("/\s+/u", $annotation->value) as
                                $property)
                            {
                                if (in_array($property, $this->__properties))
                                {
                                    $this->__resourceIds[] = $property;
                                    $this->__readOnly[$property] = true;
                                }
                                else
                                {
                                    throw new Exception(
                                        "Specified Resource ID property " .
                                        $property . " does not exist");
                                }
                            }
                        }
                        break;
                    case "ReadOnly":
                        if ($annotation->value)
                        {
                            foreach(preg_split("/\s+/u", $annotation->value) as
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
                    case "Create":
                        if ($annotation->value)
                        {
                            $values = preg_split("/\s+/u", $annotation->value,
                                2);
                            switch(count($values))
                            {
                                case 2:
                                    $this->__createCall["method"] = $values[0];
                                    $this->__createCall["uri"] = $values[1];
                                    break;
                                case 1:
                                    $this->__createCall["method"] = $values[0];
                                    $this->__createCall["uri"] = "";
                                    break;
                            }
                        }
                        break;
                    case "CreateGetFields":
                        if ($annotation->value)
                        {
                            foreach(preg_split("/\s+/u", $annotation->value) as
                                $property)
                            {
                                if (in_array($property, $this->__properties))
                                {
                                    $this->__createCall["get"][] = $property;
                                }
                            }
                        }
                        break;
                    case "CreatePostFields":
                        if ($annotation->value)
                        {
                            foreach(preg_split("/\s+/u", $annotation->value) as
                                $property)
                            {
                                if (in_array($property, $this->__properties))
                                {
                                    $this->__createCall["post"][] = $property;
                                }
                            }
                        }
                        break;
                    case "CreateBody":
                        switch(strtolower($annotation->value))
                        {
                            case "xml":
                                $this->__createCall["bodyType"] = "xml";
                                break;
                            case "json":
                                $this->__createCall["bodyType"] = "json";
                                break;
                            case "php":
                                $this->__createCall["bodyType"] = "php";
                                break;
                            default:
                                throw new Exception("Invalid create call " .
                                    "body type: " . $annotation->value);
                        }
                        break;
                    case "Read":
                        if ($annotation->value)
                        {
                            $values = preg_split("/\s+/u", $annotation->value,
                                2);
                            switch(count($values))
                            {
                                case 2:
                                    $this->__readCall["method"] = $values[0];
                                    $this->__readCall["uri"] = $values[1];
                                    break;
                                case 1:
                                    $this->__readCall["method"] = $values[0];
                                    $this->__readCall["uri"] = "";
                                    break;
                            }
                        }
                        break;
                    case "ReadGetFields":
                        if ($annotation->value)
                        {
                            foreach(preg_split("/\s+/u", $annotation->value) as
                                $property)
                            {
                                if (in_array($property, $this->__properties))
                                {
                                    $this->__readCall["get"][] = $property;
                                }
                            }
                        }
                        break;
                    case "ReadPostFields":
                        if ($annotation->value)
                        {
                            foreach(preg_split("/\s+/u", $annotation->value) as
                                $property)
                            {
                                if (in_array($property, $this->__properties))
                                {
                                    $this->__readCall["post"][] = $property;
                                }
                            }
                        }
                        break;
                    case "ReadBody":
                        switch(strtolower($annotation->value))
                        {
                            case "xml":
                                $this->__readCall["bodyType"] = "xml";
                                break;
                            case "json":
                                $this->__readCall["bodyType"] = "json";
                                break;
                            case "php":
                                $this->__readCall["bodyType"] = "php";
                                break;
                            default:
                                throw new Exception("Invalid read call " .
                                    "body type: " . $annotation->value);
                        }
                        break;
                    case "Update":
                        if ($annotation->value)
                        {
                            $values = preg_split("/\s+/u", $annotation->value,
                                2);
                            switch(count($values))
                            {
                                case 2:
                                    $this->__updateCall["method"] = $values[0];
                                    $this->__updateCall["uri"] = $values[1];
                                    break;
                                case 1:
                                    $this->__updateCall["method"] = $values[0];
                                    $this->__updateCall["uri"] = "";
                                    break;
                            }
                        }
                        break;
                    case "UpdateGetFields":
                        if ($annotation->value)
                        {
                            foreach(preg_split("/\s+/u", $annotation->value) as
                                $property)
                            {
                                if (in_array($property, $this->__properties))
                                {
                                    $this->__updateCall["get"][] = $property;
                                }
                            }
                        }
                        break;
                    case "UpdatePostFields":
                        if ($annotation->value)
                        {
                            foreach(preg_split("/\s+/u", $annotation->value) as
                                $property)
                            {
                                if (in_array($property, $this->__properties))
                                {
                                    $this->__updateCall["post"][] = $property;
                                }
                            }
                        }
                        break;
                    case "UpdateBody":
                        switch(strtolower($annotation->value))
                        {
                            case "xml":
                                $this->__updateCall["bodyType"] = "xml";
                                break;
                            case "json":
                                $this->__updateCall["bodyType"] = "json";
                                break;
                            case "php":
                                $this->__updateCall["bodyType"] = "php";
                                break;
                            default:
                                throw new Exception("Invalid update call " .
                                    "body type: " . $annotation->value);
                        }
                        break;
                    case "Delete":
                        if ($annotation->value)
                        {
                            $values = preg_split("/\s+/u", $annotation->value,
                                2);
                            switch(count($values))
                            {
                                case 2:
                                    $this->__deleteCall["method"] = $values[0];
                                    $this->__deleteCall["uri"] = $values[1];
                                    break;
                                case 1:
                                    $this->__deleteCall["method"] = $values[0];
                                    $this->__deleteCall["uri"] = "";
                                    break;
                            }
                        }
                        break;
                    case "DeleteGetFields":
                        if ($annotation->value)
                        {
                            foreach(preg_split("/\s+/u", $annotation->value) as
                                $property)
                            {
                                if (in_array($property, $this->__properties))
                                {
                                    $this->__deleteCall["get"][] = $property;
                                }
                            }
                        }
                        break;
                    case "DeletePostFields":
                        if ($annotation->value)
                        {
                            foreach(preg_split("/\s+/u", $annotation->value) as
                                $property)
                            {
                                if (in_array($property, $this->__properties))
                                {
                                    $this->__deleteCall["post"][] = $property;
                                }
                            }
                        }
                        break;
                    case "DeleteBody":
                        switch(strtolower($annotation->value))
                        {
                            case "xml":
                                $this->__deleteCall["bodyType"] = "xml";
                                break;
                            case "json":
                                $this->__deleteCall["bodyType"] = "json";
                                break;
                            case "php":
                                $this->__deleteCall["bodyType"] = "php";
                                break;
                            default:
                                throw new Exception("Invalid delete call " .
                                    "body type: " . $annotation->value);
                        }
                        break;
                    case "Query":
                        if ($annotation->value)
                        {
                            $values = preg_split("/\s+/u", $annotation->value,
                                2);
                            switch(count($values))
                            {
                                case 2:
                                    $this->__queryCall["method"] = $values[0];
                                    $this->__queryCall["uri"] = $values[1];
                                    break;
                                case 1:
                                    $this->__queryCall["method"] = $values[0];
                                    $this->__queryCall["uri"] = "";
                                    break;
                            }
                        }
                        break;
                    case "QueryGetFields":
                        if ($annotation->value)
                        {
                            foreach(preg_split("/\s+/u", $annotation->value) as
                                $property)
                            {
                                if (in_array($property, $this->__properties))
                                {
                                    $this->__queryCall["get"][] = $property;
                                }
                            }
                        }
                        break;
                    case "QueryPostFields":
                        if ($annotation->value)
                        {
                            foreach(preg_split("/\s+/u", $annotation->value) as
                                $property)
                            {
                                if (in_array($property, $this->__properties))
                                {
                                    $this->__queryCall["post"][] = $property;
                                }
                            }
                        }
                        break;
                    case "QueryBody":
                        switch(strtolower($annotation->value))
                        {
                            case "xml":
                                $this->__queryCall["bodyType"] = "xml";
                                break;
                            case "json":
                                $this->__queryCall["bodyType"] = "json";
                                break;
                            case "php":
                                $this->__queryCall["bodyType"] = "php";
                                break;
                            default:
                                throw new Exception("Invalid query call " .
                                    "body type: " . $annotation->value);
                        }
                        break;
                    case "QueryList":
                        if ($annotation->value)
                        {
                            foreach(preg_split("/\s+/u", $annotation->value) as
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
                        break;
                }
            }
            
            # Make sure we have a Base URI
            if (! $this->__baseUri)
            {
                throw new Exception("BaseURI must be specified in " .
                    get_class($this));
            }

            # See if we had query properties
            if (! $this->__queryProperties)
            {
                $this->__queryProperties = $this->__properties;
            }
            
            # Store the record properties in the caches
            $parsed_annotations = array(
                "baseUri" => $this->__baseUri,
                "resourceIds" => $this->__resourceIds,
                "createCall" => $this->__createCall,
                "readCall" => $this->__readCall,
                "updateCall" => $this->__updateCall,
                "deleteCall" => $this->__deleteCall,
                "allowCreate" => $this->__allowCreate,
                "allowRead" => $this->__allowRead,
                "allowUpdate" => $this->__allowUpdate,
                "allowDelete" => $this->__allowDelete,
                "allowQuery" => $this->__allowQuery,
                "queryProperties" => $this->__queryProperties,
                "queryView" => $this->__queryView,
                "readOnly" => $this->__readOnly,
                "cachePrefix" => $this->__cachePrefix,
                "cacheTime" => $this->__cacheTime,
                "voidCacheEntries" => $this->__voidCacheEntries,
                "noCache" => $this->__noCache
            );
            self::$__executionCache[$class] = $parsed_annotations;
            $local_cache->set($cache_key, $parsed_annotations,
                Annotations::$cacheTime);
        }
        else
        {
            # Restore the property values
            $this->__baseUri = $parsed_annotations["baseUri"];
            $this->__resourceIds = $parsed_annotations["resourceIds"];
            $this->__createCall = $parsed_annotations["createCall"];
            $this->__readCall = $parsed_annotations["readCall"];
            $this->__updateCall = $parsed_annotations["updateCall"];
            $this->__deleteCall = $parsed_annotations["deleteCall"];
            $this->__allowCreate = $parsed_annotations["allowCreate"];
            $this->__allowRead = $parsed_annotations["allowRead"];
            $this->__allowUpdate = $parsed_annotations["allowUpdate"];
            $this->__allowDelete = $parsed_annotations["allowDelete"];
            $this->__allowQuery = $parsed_annotations["allowQuery"];
            $this->__queryProperties = $parsed_annotations["queryProperties"];
            $this->__queryView = $parsed_annotations["queryView"];
            $this->__readOnly = $parsed_annotations["readOnly"];
            $this->__cachePrefix = $parsed_annotations["cachePrefix"];
            $this->__cacheTime = $parsed_annotations["cacheTime"];
            $this->__voidCacheEntries = $parsed_annotations["voidCacheEntries"];
            $this->__noCache = $parsed_annotations["noCache"];
        }

        # See if the object we received was an object or array
        # TODO: override the __import class for this
        if (is_array($object) || is_object($object))
        {
            # Separate the object's values into primary key and other values
            $id_values = array();
            $values = array();
            
            foreach($object as $key => $value)
            {
                if (array_key_exists($key, $this->__alias))
                {
                    if (in_array($this->__alias[$key], $this->__resourceIds))
                    {
                        # This is a primary key value
                        $id_values[$key] = $value;
                    }
                    else
                    {
                        # This is another value
                        $values[$key] = $value;
                    }
                }
                else
                {
                    # Store the value as-is
                    $values[$key] = $value;
                }
            }
            
            # Load the resource id values
            $this->__enforceReadOnly = false;
            $this->__import($id_values, $view, $strict);
            
            # Get the record
            $this->getRecord();
            $this->__enforceReadOnly = true;
        
            # Save the initial values
            foreach($this->__properties as $property)
            {
                $this->__defaultValues[$property] = $this->$property;
            }
            
            # Set the other properties
            $this->__import($values, $view, $strict);
        }
        else
        {
            $this->__createMode = true;
            $this->__enforceReadOnly = false;
        
            # Save the initial values
            foreach($this->__properties as $property)
            {
                $this->__defaultValues[$property] = $this->$property;
            }
        }
    }

    
    /***************************************************************************
     * PUBLIC PROPERTIES AND METHODS
     **************************************************************************/
    
    /**
     * Saves the record, either creating the new record or updating the existing
     * record.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     */
    public function save()
    {
        # Validate the object
        $this->__validate();

        # See if we are inserting or updating
        if ($this->__createMode)
        {
            # Make sure we can create
            if (! $this->__allowCreate)
            {
                throw new Exception("Cannot create this record");
            }
            
            # Make the call
            $response = $this->makeRequest("CREATE");
            
            # See if the response is an object or array
            # TODO: handle XML response
            if (is_array($response) || is_object($response))
            {
                # Import these values
                $this->__import($response);
            }
            
            # Save the entry in the cache
            if (! $this->__noCache)
            {
                $values = array();
                foreach($this->__properties as $property)
                {
                    $values[$property] = $this->$property;
                }
                $this->__cache->set($this->getCacheKey(), $values,
                    $this->__cacheTime);
            }
            
            # Turn off insert mode and turn on read-only checks
            $this->__createMode = false;
            $this->__enforceReadOnly = true;
        }
        else
        {
            # Make sure we can create
            if (! $this->__allowUpdate)
            {
                throw new Exception("Cannot create this record");
            }
            
            # See which columns have changed
            $have_changes = false;
            foreach($this->__properties as $property)
            {
                $value = $this->$property;
                
                if ($value !== $this->__defaultValues[$property])
                {
                    $have_changes = true;
                    break;
                }
            }
            
            # See if we had any changes
            if ($have_changes)
            {
                # Make the call
                $response = $this->makeRequest("UPDATE");

                # See if the response is an object or array
                # TODO: handle XML response
                if (is_array($response) || is_object($response))
                {
                    # Import these values
                    $this->__import($response);
                }

                # Save the entry in the cache
                if (! $this->__noCache)
                {
                    $values = array();
                    foreach($this->__properties as $property)
                    {
                        $values[$property] = $this->$property;
                    }
                    $this->__cache->set($this->getCacheKey(), $values,
                        $this->__cacheTime);
                }
            }
        }
            
        # Sync the initial values
        foreach($this->__properties as $property)
        {
            $this->__defaultValues[$property] = $this->$property;
        }
    }
    
    /**
     * Deletes the record.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     */
    public function delete()
    {
        # Make sure the record can be deleted
        if (! $this->__allowDelete)
        {
            throw new Exception("Cannot delete this record");
        }
        
        # See if we are in insert mode
        if ($this->__createMode)
        {
            throw new Exception("Cannot delete record: it doesn't exist!");
        }
        
        # Make the call
        $response = $this->makeRequest("DELETE");
        
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
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @todo Allow selecting into an object
     * 
     * @param array $parameters List of query parameters
     * @param string $class_name Use array to return list as an array, or class name to return objects
     * @param array $ctoargs Constructor arguments if returning objects
     * @return array Record list
     */
    public function query(array $parameters = array(), $class_name = "array",
        array $ctorargs = array())
    {
        # See if querying is allowed
        if (! $this->__allowQuery)
        {
            throw new BadRequestException(
                "This model does not support querying");
        }
        
        # Hash the array and see if we have an entry in the cache
        $results = false;
        if (! $this->__noCache)
        {
            $cache_key = $this->__cachePrefix . ".query." .
                md5(serialize($parameters) . $class_name);
            $results = $this->__cache->get($cache_key);
        }
        
        if ($results === false)
        {
            # Pass the query parameters
            $this->__queryCall["parameters"] = $parameters;
            
            # Make the call
            $results = $this->makeRequest("QUERY");
            
            # Store the results in the cache
            if (! $this->__noCache)
            {
                $this->__cache->set($cache_key, $results, $this->__cacheTime);
            }
        }
        
        # See if we need to convert the data
        if ($class_name !== "array")
        {
            # Cast the objects
            foreach($results as &$record);
            {
                $record = new $class_name($record);
            }
        }
        
        return $results;
    }
    
    
    /***************************************************************************
     * PROTECTED PROPERTIES AND METHODS
     **************************************************************************/
    
    /**
     * @var iSecurity Security context
     */
    protected $__security = null;
    
    /**
     * @var \Cougar\RestClient\iRestClient The REST HTTP client
     */
    protected $__restClient = null;
    
    /**
     * @var cache Reference to Cache object
     */
    protected $__cache = null;
    
    /**
     * @var string Cache prefix
     */
    protected $__cachePrefix = "byu.model";
    
    /**
     * @var array Cache entries to void on modification of this object
     */
    protected $__voidCacheEntries = array();
    
    /**
     * @var string 
     */
    protected $__cacheTime = 3600;
    
    /**
     * @var string 
     */
    protected $__noCache = false;
    
    /**
     * @var string Base URI for the resource
     */
    protected $__baseUri = "";
    
    /**
     * @var array Create call properties
     */
    protected $__createCall = array(
        "method" => "POST",
        "uri" => "",
        "get" => array(),
        "post" => array(),
        "bodyType" => null
    );
    
    /**
     * @var array Read call properties
     */
    protected $__readCall = array(
        "method" => "GET",
        "uri" => "",
        "get" => array(),
        "post" => array(),
        "bodyType" => null
    );
    
    /**
     * @var array Update call properties
     */
    protected $__updateCall = array(
        "method" => "PUT",
        "uri" => "",
        "get" => array(),
        "post" => array(),
        "bodyType" => null
    );
    
    /**
     * @var array Delete call properties
     */
    protected $__deleteCall = array(
        "method" => "DELETE",
        "uri" => "",
        "get" => array(),
        "post" => array(),
        "bodyType" => null
    );
    
    /**
     * @var array Query call properties
     */
    protected $__queryCall = array(
        "method" => "GET",
        "uri" => "",
        "parameters" => null,
        "get" => array(),
        "post" => array(),
        "bodyType" => null
    );
    
    /**
     * @var bool Whether read operation is allowed
     */
    protected $__allowRead = true;
    
    /**
     * @var bool Whether create operation is allowed
     */
    protected $__allowCreate = true;
    
    /**
     * @var bool Whether update operation is allowed
     */
    protected $__allowUpdate = true;
    
    /**
     * @var bool Whether delete operation is allowed
     */
    protected $__allowDelete = false;
    
    /**
     * @var bool Whether to allow queries
     */
    protected $__allowQuery = true;
    
    /**
     * @var array List of properties that can be queried
     */
    protected $__queryProperties = array();
    
    /**
     * @var bool The view to use during query
     */
    protected $__queryView = "__default__";
    
    /**
     * @var array Array with the properties that identify the resource
     */
    protected $__resourceIds = array();
    
    /**
     * @var bool True if creating a new record; false for updating
     */
    protected $__createMode = false;

    /**
     * Makes the REST call for the given operation
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string $operation One of CREATE, READ, UPDATE or DELETE
     * @return mixed REST response
     * @throws \Cougar\Exceptions\Exception
     * @throws \Cougar\Exceptions\NotImplementedException
     */
    protected function makeRequest($operation)
    {
        # See which operation we have
        switch ($operation)
        {
            case "CREATE":
                $call = $this->__createCall;
                break;
            case "READ":
                $call = $this->__readCall;
                break;
            case "UPDATE":
                $call = $this->__updateCall;
                break;
            case "DELETE":
                $call = $this->__deleteCall;
                break;
            case "QUERY":
                $call = $this->__queryCall;
                break;
            default:
                throw new Exception("Invalid REST operation: " . $operation);
                break;
        }
        
        # Create the URI and add property values
        $uri = $this->__baseUri . $call["uri"];
        $uri_param_list = array();
        preg_match_all("/:[A-Za-z0-9_]+\w/u", $uri, $uri_param_list);
        foreach($uri_param_list[0] as $property_param)
        {
            $property = substr($property_param, 1);
            if (in_array($property, $this->__properties))
            {
                $uri = str_replace($property_param, $this->$property, $uri);
            }
        }
        
        # See if we have query parameters
        if ($operation == "QUERY" && count($call["parameters"]))
        {
            # See if the URI has a ? already
            if (strpos($uri, "?") === false)
            {
                $uri .= "?" . QueryParameter::toHtml($call["parameters"]);
            }
            else
            {
                $uri .= "&" . QueryParameter::toHtml($call["parameters"]);
            }
        }
        
        # Gather the GET parameters
        $get_fields = array();
        foreach($call["get"] as $property)
        {
            $get_fields[$property] = $this->$property;
        }
        
        # See which kind of body we are sending
        switch($call["bodyType"])
        {
            case "json":
                $content_type = "application/json";
                $body = json_encode($this);
                break;
            case "php":
                $content_type = "application/vnd.php.serialized";
                $body = serialize((object) $this);
                break;
            case "xml":
                $content_type = "application/xml";
                throw new NotImplementedException(
                    "XML REST calls not yet supported");
                break;
            default:
                # Build an array of arguments
                $content_type = null;
                $body = null;
                if ($call["post"])
                {
                    $body = array();
                    foreach($call["post"] as $property)
                    {
                        $body[$property] = $this->$property;
                    }
                }
                break;
        }
        
        # Make the call and return the result
        return $this->__restClient->makeRequest($call["method"], $uri, null,
            $get_fields, $body, $content_type);
    }

    /**
     * Loads a record into this object
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
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
            $cache_entry = false;
        }
        else
        {
            $cache_entry = $this->__cache->get($this->getCacheKey());
        }
        
        if ($cache_entry === false)
        {
            # Make the call
            $response = $this->makeRequest("READ");
            
            # Import the record
            # TODO: handle XML
            if (is_object($response) || is_array($response))
            {
                foreach($response as $property => $value)
                {
                    $this->$property = $value;
                }
            }
            else if (($response))            {
                throw new Exception("Object does not know how to handle " .
                    "REST service response");
            }
            
            # Store the record in the cache
            if (! $this->__noCache)
            {
                $cache_entry = array();
                foreach($this->__properties as $property)
                {
                    $cache_entry[$property] = $this->$property;
                }
                $this->__cache->set($this->getCacheKey(), $cache_entry,
                    $this->__cacheTime);
            }
        }
        else
        {
            # Store the properties from the cache
            foreach($cache_entry as $property => $value)
            {
                $this->$property = $value;
            }
        }
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
        
        foreach($this->__resourceIds as $property)
        {
            # Get the value from the object properties
            $cache_key .= "." . $this->$property;
        }
        
        # Return the value
        return $cache_key;
    }
}
?>
