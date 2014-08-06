<?php

namespace Cougar\RestService;

use Cougar\Security\iSecurity;
use Cougar\Cache\CacheFactory;
use Cougar\Util\Annotations;
use Cougar\Util\Xml;
use Cougar\Exceptions\Exception;
use Cougar\Exceptions\AuthenticationRequiredException;
use Cougar\Exceptions\BadRequestException;
use Cougar\Exceptions\InvalidAnnotationException;
use Cougar\Exceptions\MethodNotAllowedException;
use Cougar\Exceptions\NotAcceptableException;

# Initialize the framework (disabled; should have been done by application)
#require_once(__DIR__ . "/../../cougar.php");

/**
 * Extends the RestService interface and adds the ability to create automatic
 * web service bindings based on annotations on a method's documentation block.
 * When services are bound and you call the handleRequest() method, the object
 * will find the appropriate method call to use, call the method and generate
 * a response from the method's return value.
 *
 * @history
 * 2013.09.30:
 *   (AT)  Initial release
 * 2013.10.16:
 *   (AT)  Fix issue where calling bindFromObject() twice will destroy bindings
 *         from previous calls
 * 2013.11.21:
 *   (AT)  Add __toHtml() and __toXml() support when converting method response
 *         to HTML or XML
 * 2014.02.26:
 *   (AT)  Fix bug where wrong method was called if two methods handled the same
 *         URI but accepted different content types
 *   (AT)  Extract annotations using extractFromObjectWithInheritance() method
 *         and enable inheritance from interfaces
 * 2014.03.10:
 *   (AT)  Improve matching of root-level paths (like / and /:id)
 * 2014.03.12:
 *   (AT)  Ignore trailing slashes in the URI
 *   (AT)  Improve URI matching by considering the total number of arguments and
 *         the number of literal arguments
 * 2014.03.14:
 *   (AT)  Make sure the root path (/) is always matched last
 *   (AT)  Add $ to path regular expression to make sure URIs match paths
 *         exactly (makes matching more strict)
 *   (AT)  Make sure variable path parameters with regular expressions get
 *         passed to the method at run time
 * 2014.03.14:
 *   (AT)  Make sure regex classes (like [[:alpha:]]) work in the path
 *         annotation
 * 2014.03.24:
 *   (AT)  Avoid using global variables for PATH and METHOD
 * 2014.08.05:
 *   (AT)  Properly handle XML, JSON and PHP in the Returns annotation
 *
 * @version 2014.08.05
 * @package Cougar
 * @license MIT
 *
 * @copyright 2013-2014 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class AnnotatedRestService extends RestService implements iAnnotatedRestService
{
    /**
     * Stores the Security object and initializes the REST request
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @todo Make sure the code works in CGI and Windows environments
     * 
     * @param iSecurity $security Reference to Security context
     */
    public function __construct(iSecurity $security)
    {
        # Call the parent constructor
        parent::__construct();
        
        # Store the security object
        $this->security = $security;
        
        # Create a new local cache
        $this->localCache = CacheFactory::getLocalCache();
    }
    
    /**
     * Destructor -- Doesn't do anything at the moment
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     */
    public function __destruct()
    {
        // Nothing to clean-up at the moment
    }
    
    
    /***************************************************************************
     * PUBLIC PROPERTIES AND METHODS
     **************************************************************************/

    /**
     * Binds all the services in the given object. This call can be made as
     * many times as as necessary to bind all necessary services.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     * 2013.10.16:
     *   (AT)  Fix clobbering issue where calling the method a second time
     *         deletes previous bindings
     * 2014.02.26:
     *   (AT)  Extract annotations using extractFromObjectWithInheritance()
     *         method and enable inheritance from interfaces
     * 2014.03.10:
     *   (AT)  Add ^/ to the path regex to match explicitly on the beginning of
     *         the path
     * 2014.03.12:
     *   (AT)  Require a URI parameter to contain at least one character
     *   (AT)  Add the number of URI parameters and literals to the binding
     * 2014.03.14:
     *   (AT)  Fix matching of root paths (those are simply /)
     *   (AT)  Add $ to end of regex to be more exact on path matching
     *   (AT)  Make sure to set the parameter name when the parameter contains a
     *         regular expression
     * 2014.08.05:
     *   (AT)  Internally convert the Returns and Accepts annotations to
     *         lowercase to improve and simplify mimetype detection
     *
     * @version 2014.08.05
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param object $object_reference
     *   Reference to the object that will be bound
     * @throws \Cougar\Exceptions\Exception;
     */
    public function bindFromObject(&$object_reference)
    {
        # Make sure this is an object
        if (! is_object($object_reference))
        {
            throw new Exception("Object reference must be an object");
        }
        
        # Get the class name
        $class = get_class($object_reference);
        
        # Skip if this class is already in the object list
        if (array_key_exists($class, $this->objects))
        {
            throw new Exception("You have attempted to bind an object twice " .
                "or bind two objects of the same class; please verify your " .
                "object bindings");
        }
        
        # Create our own cache key
        $cache_key = Annotations::$annotationsCachePrefix . $class .
            ".AnnotatedRestService.Bindings";

        # Get the annotations
        $annotations = Annotations::extractFromObjectWithInheritance(
            $object_reference, array(), true, true);
        
        # See if we have pre-parsed bindings
        $bindings = false;
        if ($annotations->cached)
        {
            $bindings = $this->localCache->get($cache_key);
        }

        # See if we need to extract the bindings from the annotations
        if ($bindings === false)
        {
            # Start a blank bindings list
            $bindings = array();

            # Go through the object's methods
            foreach($annotations->methods as $method => $annotations)
            {
                # Create the binding and initialize the paths and methods array
                $binding = new Binding();
                $paths = array();
                
                # Add the class and method information about the binding
                $binding->object = $class;
                $binding->method = $method;
                $binding->http_methods = array("GET", "POST", "PUT", "DELETE");
                
                # Extract the property's annotations
                foreach($annotations as $annotation)
                {
                    switch($annotation->name)
                    {
                        case "Path":
                            $paths[] = $annotation->value;
                            break;
                        case "Methods":
                            $binding->http_methods = preg_split('/\s+/u',
                                mb_strtoupper($annotation->value));
                            break;
                        case "Accepts":
                            $binding->accepts =
                                mb_strtolower($annotation->value);
                            break;
                        case "Returns":
                            $binding->returns =
                                mb_strtolower($annotation->value);
                            break;
                        case "XmlRootElement":
                        case "RootElement":
                            $binding->xmlRootElement = $annotation->value;
                            break;
                        case "XmlObjectName":
                        case "ObjectName":
                            $binding->xmlObjectName = $annotation->value;
                            break;
                        case "XmlObjectList":
                            $binding->xmlObjectList = $annotation->value;
                            break;
                        case "XSD":
                            $binding->xsd =
                                file_get_contents($annotation->value, true);
                            break;
                        case "XSL":
                            $binding->xsl =
                                file_get_contents($annotation->value, true);
                            break;
                        case "UriArray":
                            $parameter = new Parameter();
                            $parameter->source = "URI";
                            $parameter->index = 0;
                            $parameter->array = true;
                            $binding->parameters[$annotation->value] =
                                $parameter;
                            break;
                        case "GetArray":
                            $parameter = new Parameter();
                            $parameter->source = "GET";
                            $parameter->index = 0;
                            $parameter->array = true;
                            $binding->parameters[$annotation->value] =
                                $parameter;
                            break;
                        case "GetValue":
                            # Define the new entry
                            $parameter = new Parameter();
                            
                            # Split the values at word boundaries
                            $values = preg_split('/\s+/u', $annotation->value,
                                3);
                            
                            # See how many values we have
                            switch(count($values))
                            {
                                case 3:
                                    # type variable_name method_parameter_name
                                    $param_name = $values[2];
                                    $parameter->source = "GET";
                                    $parameter->index = $values[1];
                                    $parameter->type = $values[0];
                                    break;
                                case 2:
                                    # type get_variable_name
                                    $param_name = $values[1];
                                    $parameter->source = "GET";
                                    $parameter->index = $values[1];
                                    $parameter->type = $values[0];
                                    break;
                                case 1:
                                    # get_variable_name
                                    $param_name = $values[0];
                                    $parameter->source = "GET";
                                    $parameter->index = $values[0];
                                    $parameter->type = "string";
                                    break;
                                default:
                                    throw new InvalidAnnotationException(
                                        "Invalid GetValue: " .
                                        $annotation->value);
                            }
                            
                            # Add the parameter
                            $binding->parameters[$param_name] = $parameter;
                            break;
                        case "GetQuery":
                            # Define the new entry
                            $parameter = new Parameter();

                            # Split the values at word boundaries
                            $values = preg_split('/\s+/u', $annotation->value,
                                3);

                            # See how many values we have
                            switch(count($values))
                            {
                                case 1:
                                    # get_variable_name
                                    $param_name = $values[0];
                                    $parameter->source = "QUERY";
                                    $parameter->type = "array";
                                    break;
                                default:
                                    throw new InvalidAnnotationException(
                                        "Invalid GetQuery: " .
                                        $annotation->value);
                            }

                            # Add the parameter
                            $binding->parameters[$param_name] = $parameter;
                            break;
                        case "PostArray":
                            $parameter = new Parameter();
                            $parameter->source = "POST";
                            $parameter->index = 0;
                            $parameter->array = true;
                            $binding->parameters[$annotation->value] =
                                $parameter;
                            break;
                        case "PostValue":
                            # Define the new entry
                            $parameter = new Parameter();
                            
                            # Split the values at word boundaries
                            $values = preg_split('/\s+/u', $annotation->value,
                                3);
                            
                            # See how many values we have
                            switch(count($values))
                            {
                                case 3:
                                    # type variable_name method_parameter_name
                                    $param_name = $values[2];
                                    $parameter->source = "POST";
                                    $parameter->index = $values[1];
                                    $parameter->type = $values[0];
                                    break;
                                case 2:
                                    # type get_variable_name
                                    $param_name = $values[1];
                                    $parameter->source = "POST";
                                    $parameter->index = $values[1];
                                    $parameter->type = $values[0];
                                    break;
                                case 1:
                                    # get_variable_name
                                    $param_name = $values[0];
                                    $parameter->source = "POST";
                                    $parameter->index = $values[0];
                                    $parameter->type = "string";
                                    break;
                                default:
                                    throw new InvalidAnnotationException(
                                        "Invalid PostValue: " .
                                        $annotation->value);
                            }
                            
                            # Add the parameter
                            $binding->parameters[$param_name] = $parameter;
                            break;
                        case "Body":
                            # Define the new entry
                            $parameter = new Parameter();
            
                            # Split the values at word boundaries
                            $values = preg_split('/\s+/u', $annotation->value,
                                2);

                            # See how many values we have
                            switch(count($values))
                            {
                                case 2:
                                    # parameter_name type
                                    $param_name = $values[0];
                                    $parameter->source = "BODY";
                                    $parameter->type = $values[1];
                                    break;
                                case 1:
                                    $param_name = $values[0];
                                    $parameter->source = "BODY";
                                    break;
                                default:
                                    throw new InvalidAnnotationException(
                                        "Invalid Body: " . $annotations->value);
                            }

                            # Add the parameter
                            $binding->parameters[$param_name] = $parameter;
                            break;
                        case "Authentication":
                            if (mb_strtolower($annotation->value == "required"))
                            {
                                $binding->authentication = "required";
                            }
                            else
                            {
                                $binding->authentication = "optional";
                            }
                            break;
                    }
                }
                
                # Go through each path
                foreach($paths as $str_path)
                {
                    # Clone the binding
                    $real_binding = clone $binding;

                    # Remote leading and trailing slashes on the path
                    $str_path = preg_replace(':^/|/$:', "", $str_path);

                    # Separate the path elements by the backslash
                    $path = explode("/", $str_path);

                    # See if the first element is blank (usually the case)
                    if ($path[0] === "")
                    {
                        # Get rid of the element
                        array_shift($path);
                    }

                    # Check for root path (a simple / for the path)
                    if (count($path) == 1 && $path[0] === "")
                    {
                        # The number of URI parameters is 0
                        $real_binding->pathArgumentCount = 0;
                    }
                    else
                    {
                        # Add the number of URI parameters to the binding
                        $real_binding->pathArgumentCount = count($path);
                    }

                    # Go through each part of the path
                    foreach($path as $index => &$subpath)
                    {
                        # Split the subpath into its parts
                        $subpath_parts = explode(":", $subpath, 4);
                        $subpath_count = count($subpath_parts);

                        # See if this is a literal argument
                        if ($subpath_count < 2)
                        {
                            # See if we had a value (useful for root path)
                            # Increase the literal argument count
                            $real_binding->literalPathArgumentCount++;

                            # Go on to the next argument
                            continue;
                        }

                        # Define the new parameter, its name and regex
                        # expression, and add the number of path parameters
                        $parameter = new Parameter();
                        $param_name = "";
                        if (mb_substr($subpath, -1) == "+")
                        {
                            $param_regex = ".*";

                            # Declare the number of arguments to be 1000
                            $real_binding->pathArgumentCount = 1000;
                        }
                        else
                        {
                            $param_regex = "[^/]+";
                        }

                        # See how many parts we have
                        switch ($subpath_count)
                        {
                            case 4:
                                # :param_name:type:regex
                                $param_name = $subpath_parts[1];
                                $parameter->source = "URI";
                                $parameter->index = $index;
                                $parameter->type = $subpath_parts[2];
                                $param_regex = $subpath_parts[3];
                                break;
                            case 3:
                                # :param_name:type
                                $param_name = $subpath_parts[1];
                                $parameter->source = "URI";
                                $parameter->index = $index;
                                $parameter->type = $subpath_parts[2];
                                break;
                            case 2:
                                # :param_name
                                $param_name = $subpath_parts[1];
                                $parameter->source = "URI";
                                $parameter->index = $index;
                                $parameter->type = "string";
                                break;
                        }

                        # See if the have a + at the end of this parameter
                        if (mb_substr($param_name, -1) == "+")
                        {
                            # Get rid of the +
                            $param_name = mb_substr($param_name, 0, -1);

                            # Set the _array flag
                            $parameter->array = true;
                        }

                        # Add the parameter
                        $real_binding->parameters[$param_name] = $parameter;

                        # Replace the value of the parameter with its regular
                        # expression
                        $subpath = $param_regex;
                    }

                    # Reconstruct the path from the new regex values
                    $new_path = "^/" . implode("/", $path) . "$";
                    
                    # Add the binding
                    $bindings[$new_path][] = $real_binding;
                }
            }
            
            # Store the parsed bindings
            $this->localCache->set($cache_key, $bindings,
                Annotations::$cacheTime);
        }

        # Add the bindings to our bindings list
        $this->bindings = array_merge($this->bindings, $bindings);
        
        # Store the object reference
        $this->objects[$class] = $object_reference;
    }
    
    /**
     * Handles the incoming request with one of the bound objects. This is a
     * terminal call, meaning that the proper method will be called and will
     * automatically send the data to the browser. If an error occurs, it will
     * be caught and sent to the browser.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     * 2013.11.21:
     *   (AT)  Add __toHtml() and __toXml() support when converting method
     *         response to HTML or XML
     * 2014.02.26:
     *   (AT)  Fix bug where continue would only exit case statement rather than
     *         going to the next binding when evaluating content-type
     * 2014.03.12:
     *   (AT)  Sort bindings by number of parameters, number of literal
     *         parameters and finally by pattern; improves binding accuracy
     * 2014.03.14:
     *   (AT)  Remove single trailing slashes from the URI path
     *   (AT)  Use the backtick (`) as the regex delimiter to allow the colon
     *         to be used in regex classes
     * 2014.03.24:
     *   (AT)  Access path and method variables directly from the object
     * 2014.08.05:
     *   (AT)  Handle JSON, XML and PHP Returns types properly
     *
     * @version 2014.08.05
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @throws \Cougar\Exceptions\Exception
     * @throws \Cougar\Exceptions\AuthenticationRequiredException
     * @throws \Cougar\Exceptions\BadRequestException
     * @throws \Cougar\Exceptions\MethodNotAllowedException
     * @throws \Cougar\Exceptions\NotAcceptableException
     */
    public function handleRequest()
    {
        # Remove trailing slash from the path
        if (mb_strlen($this->path) > 1)
        {
            $this->path = preg_replace(':/$:', "", $this->path);
        }

        # Sort the bindings from most specific to least specific
        $path_argument_counts = array();
        $literal_path_argument_counts = array();
        $patterns = array();
        foreach($this->bindings as $pattern => $method_bindings)
        {
            $path_argument_counts[$pattern] =
                $method_bindings[0]->pathArgumentCount;
            $literal_path_argument_counts[$pattern] =
                $method_bindings[0]->literalPathArgumentCount;
            $patterns[$pattern] = $pattern;
        }
        array_multisort($path_argument_counts, SORT_DESC,
            $literal_path_argument_counts, SORT_DESC,
            $patterns, SORT_NATURAL, $this->bindings);

        # Go through the bindings and find those which match the URI pattern and
        # HTTP method
        $method_list = array();
        $http_method_mismatch = false;
        foreach($this->bindings as $pattern => $method_bindings)
        {
            # See if the pattern matches
            if (preg_match("`" . $pattern . "`u", $this->path))
            {
                # See if we are dealing with an OPTIONS request
                if ($this->method == "OPTIONS")
                {
                    /* Either we need to continue to iterate through all methods
                     * or we need to gather all the information and generate the
                     * response after the loop. Since this was originally
                     * written for CORS support, we will simply return the list
                     * of all methods for now. */
                    
                    /*
                    # Get the list of methods the end point will support
                    $methods = array();
                    foreach($method_bindings as $binding)
                    {
                        foreach($binding->http_methods as $method)
                        {
                            if (! in_array($method, $methods))
                            {
                                $methods[] = $method;
                            }
                        }
                    }
                    */
                    
                    # Return all basic methods for now
                    $methods = array("GET", "POST", "PUT", "DELETE");
                    
                    # Return the response
                    $this->sendResponse(204, null,
                        array("Allow" => implode(", ", $methods)));
                }
                else
                {
                    # Go through each binding and get potential candidates
                    foreach($method_bindings as $binding)
                    {
                        # See if this binding can handle this method
                        if (! in_array($this->method, $binding->http_methods))
                        {
                            $http_method_mismatch = true;
                            continue;
                        }

                        # See if there is a specific mimetype this method
                        # accepts
                        switch($binding->accepts)
                        {
                            case "json":
                                if ($this->header("Content-type") !=
                                    "application/json")
                                {
                                    # This binding doesn't accept this type;
                                    # go to the next binding
                                    continue 2;
                                }
                                break;
                            case "xml":
                                if ($this->header("Content-type") !=
                                    "application/xml" &&
                                    $this->header("Content-type") != "text/xml")
                                {
                                    # This binding doesn't accept this type;
                                    # go to the next binding
                                    continue 2;
                                }
                                break;
                            case "php":
                                if ($this->header("Content-type") !=
                                    "application/vnd.php.serialized")
                                {
                                    # This binding doesn't accept this type;
                                    # go to the next one
                                    continue 2;
                                }
                                break;
                            case "":
                                # No binding specified; allow binding
                                break;
                            default:
                                # Check the content type directly
                                if (! $binding->accepts !=
                                    $this->header("Content-type"))
                                {
                                    # This binding doesn't accept this type;
                                    # go to the next binding
                                    continue 2;
                                }
                                break;
                        }

                        $method_list[] = $binding;
                    }
                }
            }
        }
        
        # See if we have any methods that can respond to our request
        if (count($method_list) == 0)
        {
            # See if we had methods that matched the pattern but couldn't
            # support the HTTP method
            if ($http_method_mismatch)
            {
                throw new MethodNotAllowedException(
                    "The resource does not support " . $this->method .
                        " operations");
            }
            else
            {
                # Return a 400 error
                throw new BadRequestException(
                    "Your request could not be mapped to a known resource");
            }
        }
                
        # Go through the potential method bindings and extract the response
        # type; if none is defined, do JSON, XML, HTML
        # TODO: add version information once version methodology is defined
        $response_types = array();
        foreach($method_list as $binding)
        {
            if ($binding->returns)
            {
                switch($binding->returns)
                {
                    case "json":
                        $response_types[] = "application/json";
                        break;
                    case "xml":
                        $response_types[] = "application/xml";
                        $response_types[] = "text/xml";
                        break;
                    case "php":
                        $response_types[] = "application/vnd.php.serialized";
                        break;
                    default:
                        $response_types[] = $binding->returns;
                        break;
                }
            }
            else
            {
                if (! in_array("application/json", $response_types))
                {
                    $response_types[] = "application/json";
                }

                if (! in_array("application/vnd.php.serialized",
                    $response_types))
                {
                    $response_types[] =
                        "application/vnd.php.serialized";
                }

                if (! in_array("application/xml", $response_types))
                {
                    $response_types[] = "application/xml";
                }

                if (! in_array("text/html", $response_types))
                {
                    $response_types[] = "text/html";
                }
            }
        }

        # Negotiate the response
        $output_response_types =
            $this->negotiateResponseType($response_types);

        # Find the binding that best fits
        # TODO improve detection
        $binding = null;
        foreach($output_response_types as $response_type)
        {
            foreach($method_list as $potential_binding)
            {
                if ($potential_binding->returns == $response_type)
                {
                    $binding = $potential_binding;
                    break 2;
                }
                else if ($potential_binding->returns == "json" &&
                    $response_type == "application/json")
                {
                    $binding = $potential_binding;
                    break 2;
                }
                else if ($potential_binding->returns == "xml" &&
                    $response_type == "application/xml")
                {
                    $binding = $potential_binding;
                    break 2;
                }
                else if ($potential_binding->returns == "xml" &&
                    $response_type == "text/xml")
                {
                    $binding = $potential_binding;
                    break 2;
                }
                else if (! $potential_binding->returns)
                {
                    $binding = $potential_binding;
                    break 2;
                }
            }
        }

        # If we don't have a binding, send a NotAcceptable exception
        if (! $binding)
        {
            throw new NotAcceptableException(
                "The requested resource cannot be represented by any " .
                "of the acceptable representations requested by the client");
        }
        
        # If we've made it this far, we have found our optimal binding

        # Get the object associated with the binding
        if (! array_key_exists($binding->object, $this->objects))
        {
            throw new Exception("Could not find object!");
        }
        $object = $this->objects[$binding->object];
        $r_object = new \ReflectionClass($object);

        # Get the method associated with the binding
        if (! $r_object->hasMethod($binding->method))
        {
            throw new Exception("Could not find method!");
        }
        $r_method = $r_object->getMethod($binding->method);

        # Assemble the method parameters into an array
        $params = array();

        foreach($r_method->getParameters() as $r_param)
        {
            # Get the default value of the parameter (if it has one)
            $default_param_value = null;
            if ($r_param->isOptional())
            {
                $default_param_value = $r_param->getDefaultValue();
            }

            # See if we have a binding for this parameter
            if (array_key_exists($r_param->name, $binding->parameters))
            {
                # Get the parameter information
                $param_info = $binding->parameters[$r_param->name];

                # See where the value is coming from
                switch($param_info->source)
                {
                    case "URI":
                        # See if we need to make an array with the remaining
                        # parameters
                        if ($param_info->array)
                        {
                            $params[] = array_slice($this->uri,
                                $param_info->index);
                        }
                        else
                        {
                            $params[] = $this->uriValue($param_info->index,
                                $param_info->type, $default_param_value);
                        }
                        break;
                    case "GET":
                        if ($param_info->array)
                        {
                            $params[] = $_GET;
                        }
                        else
                        {
                            $params[] = $this->getValue($param_info->index,
                                $param_info->type, $default_param_value);
                        }
                        break;
                    case "POST":
                        if ($param_info->array)
                        {
                            $params[] = $_POST;
                        }
                        else
                        {
                            $params[] = $this->postValue($param_info->index,
                                $param_info->type, $default_param_value);
                        }
                        break;
                    case "BODY":
                        $params[] = $this->body($param_info->type);
                        break;
                    case "QUERY":
                        $params[] = $this->getQuery();
                        break;
                    case "IDENTITY":
                        break;
                    default:
                        throw new Exception(
                            "Invalid parameter source");
                }
            }
            else
            {
                # We don't have a binding; pass the default value
                $params[] = $default_param_value;
            }
        }

        # See if the call requires authentication
        switch($binding->authentication)
        {
            case "required":
                $auth_success = $this->security->authenticate();
                if (! $auth_success)
                {
                    throw new AuthenticationRequiredException();
                }
                break;
            case "optional":
                $this->security->authenticate();
                break;
            default:
                # No need to do anything
                break;
        }

        # Call the method
        $data = call_user_func_array(array($object, $binding->method), $params);
        
        # Send the data in the appropriate data type
        if ($data !== null)
        {
            switch ($response_type)
            {
                case "application/json":
                    $this->sendResponse(200, json_encode($data), array(),
                        $response_type);
                    break;
                case "application/vnd.php.serialized":
                    $this->sendResponse(200, serialize($data), array(),
                        $response_type);
                    break;
                case "application/xml":
                case "text/xml":
                    # TODO: Implement XSD
                    # See if we have an object
                    if (is_object($data))
                    {
                        # See if this is a SimpleXMLElement
                        if ($data instanceof \SimpleXMLElement)
                        {
                            $xml = $data->asXML();
                        }
                        # See if the object has the __toXml() method
                        else if (method_exists($data, "__toXml"))
                        {
                            $xml = $data->__toXml();
                        }
                        # Convert data to XML
                        else
                        {
                            $xml = Xml::toXml($data, $binding->xmlRootElement,
                                $binding->xmlObjectName,
                                $binding->xmlObjectList);
                        }
                    }
                    else
                    {
                        # Convert data to XML
                        $xml = Xml::toXml($data, $binding->xmlRootElement,
                            $binding->xmlObjectName, $binding->xmlObjectList);
                    }

                    if (is_object($xml))
                    {
                        if ($xml instanceof \SimpleXMLElement)
                        {
                            $xml = $xml->asXML();
                        }
                    }

                    # Send the response
                    $this->sendResponse(200, $xml, array(), $response_type);
                    break;
                case "text/html":
                    # See if this is an object
                    $html = null;
                    $xml = null;
                    if (is_object($data))
                    {
                        # See if object has __toHtml() method
                        if (method_exists($data, "__toHtml"))
                        {
                            $html = $data->__toHtml();
                        }
                        # See if this is a SimpleXMLElement
                        else if ($data instanceof \SimpleXMLElement)
                        {
                            $xml = $data->asXML();
                        }
                        # See if the object has the __toXml() method
                        else if (method_exists($data, "__toXml"))
                        {
                            $xml = $data->__toXml();
                        }
                    }

                    if ($html === null && $xml === null)
                    {
                        # Convert data to XML
                        $xml = Xml::toXml($data, $binding->xmlRootElement,
                            $binding->xmlObjectName, $binding->xmlObjectList);

                        # See if we have an XSL transform
                        if ($binding->xsl)
                        {
                            $xsl = new \SimpleXMLElement($binding["xsl"]);
                            $xslt = new \XSLTProcessor();
                            $xslt->importStylesheet($xsl);

                            $html = $xslt->transformToXml($xml);
                        }
                    }

                    # See which kind of response we have
                    if ($html !== null)
                    {
                        $this->sendResponse(200, $html, array(),
                            $response_type);
                    }
                    else
                    {
                        if (is_object($xml))
                        {
                            if ($xml instanceof \SimpleXMLElement)
                            {
                                $xml = $xml->asXML();
                            }
                        }

                        $this->sendResponse(200, $xml, array(), "text/xml");
                    }
                    break;
                default:
                    $this->sendResponse(200, $data, array(), $response_type);
            }
        }
        else
        {
            $this->sendResponse(204, null, array(), $response_type);
        }
    }


    /***************************************************************************
     * PROTECTED PROPERTIES AND METHODS
     **************************************************************************/
    
    /**
     * @var \Cougar\Cache\iCache Cache object
     */
    protected $localCache = null;
    
    /**
     * @var \Cougar\Security\iSecurity Reference to Security object
     */
    protected $security = null;
    
    /**
     * @var array Bindings
     */
    protected $bindings = array();
    
    /**
     * @var array Objects
     */
    protected $objects = array();
}
?>
