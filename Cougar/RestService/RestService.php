<?php

namespace Cougar\RestService;

use Cougar\Util\Format;
use Cougar\Util\QueryParameter;
use Cougar\Exceptions\Exception;
use Cougar\Exceptions\NotAcceptableException;

# Initialize the framework
require_once("cougar.php");

/**
 * Implements the base RestService class with WsAuth and CAS authentication
 * support.
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
class RestService implements iRestService
{
    /**
     * Analyzes the request and exports the following public variables:
     *   $_PATH - String with the URI without the prefix
     *   $_URI - Array with URI parameters
     *   $_METHOD - String with the HTTP method
     * 
     * It also sets sets the default exception handler to handle errors
     * properly.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @todo Make sure the code works in CGI and Windows environments
     */
    public function __construct()
    {
        # Set the default exception handler
        set_exception_handler(array($this, "exceptionHandler"));
        
        # See if we are in unit tests
        if (class_exists("\\PHPUnit_Framework_TestCase", false))
        {
            $this->__testMode = true;
        }
        
        # Enable output buffering
        ob_start();
        
        # Handle CORS headers (do early so all responses have it)
        $origin = $this->header("Origin");
        if ($origin)
        {
            header("Access-Control-Allow-Origin: " . $origin);
            header("Access-Control-Allow-Credentials: true");
        }
        
        # Get the request method and store it in the global $_METHOD variable
        $this->method = $_SERVER["REQUEST_METHOD"];
        global $_METHOD;
        $_METHOD = $this->method;
        
        # Get URL path
        $uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
        
        # See if the URI has a CAS ticket attached to it
        $cas_ticket_pos = strpos($uri, "&ticket=ST-");
        if ($cas_ticket_pos !== false)
        {
            # Remove the CAS ticket from the URI
            $uri = substr($uri, 0, $cas_ticket_pos);
        }
        
        # Make sure all %xx sequences are converted to their respective
        # characters
        $tmp_uri = "";
        while ($uri != $tmp_uri)
        {
            $tmp_uri = $uri;
            $uri = rawurldecode($uri);
        }
        
        # Determine the path prefix 
        $prefix = dirname($_SERVER["PHP_SELF"]);
        
        # Determine the path (uri without prefix);
        global $_PATH;
        if ($prefix == "/")
        {
            $_PATH = $uri;
        }
        else
        {
            $_PATH = str_replace($prefix, "", $uri);
        }
        
        # Split the parameters on / and store locally and in the global context
        $this->uri = explode("/", substr($_PATH, 1));
        global $_URI;
        $_URI = $this->uri;
        
        # See if the $_POST variable is empty
        if (count($_POST) == 0)
        {
            # See if the method was something other than a post
            if ($_METHOD != "POST")
            {
                # See if the Content-Type header is a url-encoded form
                if (strpos($this->header("Content-Type"),
                    "application/x-www-form-urlencoded") === 0)
                {
                    $body = $this->body();
                    if ($body)
                    {
                        parse_str($body, $_POST);
                    }
                }
            }
        }
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
     * Returns the HTTP method for the request. This can also be found in the
     * $_METHOD global variable.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @return array Request headers
     */
    public function method()
    {
        return $this->method;
    }
    
    /**
     * Returns an associative array with all headers.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @return array Request headers
     */
    public function headers()
    {
        if ($this->headers === null)
        {
            # Get the headers
            if (function_exists("apache_request_headers"))
            {
                $this->headers = apache_request_headers();
            }
            else
            {
                # Extraction method retrieved from PHP documentation
                $this->headers = array();
                foreach($_SERVER as $key => $value)
                {
                    if (substr($key, 0, 5) == "HTTP_")
                    {
                        $name = str_replace(" ", "-",
                            ucwords(str_replace("_", " ",
                                strtolower(substr($key, 5)))));
                        $this->headers[$name] = $value;
                    }
                }
            }
        }
        
        return $this->headers;
    }
    
    /**
     * Returns the specified header. If it does not exist, return the default
     * value (null by default).
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param string $header Header name
     * @param string $type Cast to the specified type (string|int|float|bool)
     * @param mixed $default Default value
     * @return mixed Header value
     */
    public function header($header, $type = "string", $default = null)
    {
        # Handle special headers that are not in the HTTP_* form
        switch(strtolower($header))
        {
            case "content-type":
                if (array_key_exists("CONTENT_TYPE", $_SERVER))
                {
                    return $_SERVER["CONTENT_TYPE"];
                }
                break;
        }
        
        # See if the header exists
        $array_key = "HTTP_" . str_replace("-", "_", strtoupper($header));
        if (array_key_exists($array_key, $_SERVER))
        {
            # See what we will be returning
            switch ($type)
            {
                case "string":
                default:
                    return $_SERVER[$array_key];
                    break;
                case "int":
                    return (int) $_SERVER[$array_key];
                    break;
                case "float":
                    return (float) $_SERVER[$array_key];
                    break;
                case "bool":
                    $value = $_SERVER[$array_key];
                    return Format::strToBool($value, true);
                    break;
            }
        }
        else
        {
            # Return the default value
            return $default;
        }
    }
    
    /**
     * Returns the value of the URI parameter specified by the given numeric
     * offset. If it does not exist, return the default value (null by default).
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param int $index Zero-based index (value order)
     * @param string $type Cast to the specified type (string|int|float|bool)
     * @param mixed $default Default value
     * @return mixed URI parameter value
     */
    public function uriValue($index, $type = "string", $default = null)
    {
        # See if the header exists
        if (array_key_exists($index, $this->uri))
        {
            # See what we will be returning
            switch ($type)
            {
                case "string":
                default:
                    return $this->uri[$index];
                    break;
                case "int":
                    return (int) $this->uri[$index];
                    break;
                case "float":
                    return (float) $this->uri[$index];
                    break;
                case "bool":
                    $value = $this->uri[$index];
                    Format::strToBool($value, true);
                    return $value;
                    break;
            }
        }
        else
        {
            # Return the default value
            return $default;
        }
    }
    
    /**
     * Returns the value of the given GET variable. If it does not exist, return
     * the default value (null by default).
     * 
     * The value can be cast to one of the PHP scalar values (string, integer,
     * float or boolean). Additionally, a "set" type can be used to return true
     * if the variable is defined irregardless of its value, and false if it is
     * not. This is useful when GET variables are used as flags to the request
     * (for example, http://server/path/to/service?documentation).
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param int $variable
     *   GET variable name
     * @param string $type
     *   Cast to the specified type (string|int|float|bool|defined)
     * @param mixed
     *   $default Default value
     * @return mixed GET variable value
     */
    public function getValue($variable, $type = "string", $default = null)
    {
        # See if the header exists
        if (array_key_exists($variable, $_GET))
        {
            # See what we will be returning
            switch ($type)
            {
                case "string":
                default:
                    return $_GET[$variable];
                    break;
                case "int":
                    return (int) $_GET[$variable];
                    break;
                case "float":
                    return (float) $_GET[$variable];
                    break;
                case "bool":
                    $value = $_GET[$variable];
                    Format::strToBool($value, true);
                    return $value;
                    break;
                case "set":
                    return true;
                    break;
            }
        }
        else
        {
            # Return the default value (always false for set type)
            if ($type == "set")
            {
                return false;
            }
            else
            {
                return $default;
            }
        }
    }
    
    /**
     * Returns the parsed GET query as a list of QueryParameters.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @return array List of QueryParameters with query information
     */
    public function getQuery()
    {
        # See if we have parsed the query
        if ($this->queryParameters === null)
        {
            # Parse the query
            $this->queryParameters = QueryParameter::fromUri(
                parse_url($_SERVER["REQUEST_URI"], PHP_URL_QUERY));
        }
        
        # Return the list of parsed query parameters
        return $this->queryParameters;
    }
    
    /**
     * Returns the value of the given POST variable. If it does not exist,
     * return the default value (null by default).
     * 
     * If the method is not post, and the request content type is application/
     * x-www-form-urlencoded, the body will be parsed and the key/value pairs
     * will be put into the $_POST array.
     * 
     * The value can be cast to one of the PHP scalar values (string, integer,
     * float or boolean). Additionally, a "set" type can be used to return true
     * if the variable is defined irregardless of its value, and false if it is
     * not. This is userful when GET variables are used as flags to the request
     * (for example, key=value&sort_asc).
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param int $variable POST variable name
     * @param string $type Cast to the specified type (string|int|float|bool)
     * @param mixed $default Default value
     * @return mixed POST variable value
     */
    public function postValue($variable, $type = "string", $default = null)
    {
        # See if the header exists
        if (array_key_exists($variable, $_POST))
        {
            # See what we will be returning
            switch ($type)
            {
                case "string":
                default:
                    return $_POST[$variable];
                    break;
                case "int":
                    return (int) $_POST[$variable];
                    break;
                case "float":
                    return (float) $_POST[$variable];
                    break;
                case "bool":
                    $value = $_POST[$variable];
                    Format::strToBool($value, true);
                    return $value;
                    break;
                case "set":
                    return true;
                    break;
            }
        }
        else
        {
            # Return the default value (always false for set type)
            if ($type == "set")
            {
                return false;
            }
            else
            {
                return $default;
            }
        }
    }
    
    /**
     * Returns the body of the request, optionally parsing it as a specified
     * type of object. These are:
     * 
     *   XML    - Parse the body as XML and return as a SimpleXML object
     *   OBJECT - Parse the body as a JSON object and return as an object
     *   ARRAY  - Parse the body as a JSON object and return as an assoc. array
     *   PHP    - Parse the body as a serialized PHP data
     * 
     * If no parse type is specified, the body will be returned as a string.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param string $parse_type xml|object|array|php
     * @return mixed Body
     */
    public function body($parse_type = null)
    {
        if ($this->body === null)
        {
            # Get the body
            if ($this->__testMode)
            {
                # In test mode, read the body from the $_BODY variable
                global $_BODY;
                $this->body = trim($_BODY);
            }
            else
            {
                $this->body = trim(file_get_contents("php://input"));
            }
        }
        
        # See if we will be parsing the data
        switch(strtolower($parse_type))
        {
            case "xml":
                return new \SimpleXMLElement($this->body);
                break;
            case "object":
                return json_decode($this->body);
                break;
            case "array":
                return json_decode($this->body, true);
                break;
            case "php":
                return unserialize($this->body);
                break;
            default:
                return $this->body;
                break;
        }
    }
    
    /**
     * Negotiates the mime type for the response based on the values of the
     * Accept header and the provided list of mime types which the service can
     * provide. If the Accept header is missing, then the first mime type will
     * be returned. If no negotation can be achieved and the strict flag is set,
     * a 406 response should be sent to the browser.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param array $type_list
     *   An array with the mime types the service can produce
     * @param bool $strict
     *   If no mutual mime types can be found and this value is set to true,
     *   send a 406 response to the client
     * @return array Negotiated mime types, in order of preference
     * @throws \Cougar\Exceptions\Exception
     * @throws \Cougar\Exceptions\NotAcceptableException
     */
    public function negotiateResponseType(array $type_list, $strict = false)
    {
        if (count($type_list) == 0)
        {
            throw new Exception("type_list cannot be empty");
        }
        
        # Parse the accept header
        $accept_list = $this->parseAcceptHeaderString(
            $this->header("Accept", "string", "*/*"));

        # Parse the type list
        $produceable_list = $this->parseAcceptHeaderString(
            implode(",", $type_list));
        
        # Initialize the results
        $negotiated_list = array();

        # Go through the list of acceptable outputs
        foreach($accept_list as $accept)
        {
            # Go throgh the list of produceables and see if one matches
            $best_match = null;
            foreach($produceable_list as &$produceable)
            {
                # Skip entries we have already used
                if (array_key_exists("used", $produceable))
                {
                    continue;
                }
                
                # See if the type is a wildcard
                if ($accept["type"] == "*")
                {
                    # Add this entry as is as go to the next one
                    $negotiated_list[] = $this->buildAcceptString($produceable);
                    $produceable["used"] = true;
                    continue;
                }
                else if ($accept["subtype"] == "*")
                {
                    if ($accept["subtype"] == $produceable["mimetype"])
                    {
                        # Add this entry as is and go to the next one
                        $negotiated_list[] =
                            $this->buildAcceptString($produceable);
                        $produceable["used"] = true;
                        continue;
                    }
                }
                else
                {
                    # See if the mimetype matches
                    if ($accept["mimetype"] == $produceable["mimetype"])
                    {
                        # If the mimetype matches, this works as the best
                        $best_match = $produceable;

                        # See if the parameters match
                        if ($accept["params"] == $produceable["params"])
                        {
                            # Add this one to the list and go to the next one
                            $negotiated_list[] =
                                $this->buildAcceptString($produceable);
                            $produceable["used"] = true;
                            $best_match = null;
                            break;
                        }
                    }
                }
            }
            
            # If we never found a perfect match, add the last best one
            if ($best_match !== null)
            {
                $negotiated_list[] = $this->buildAcceptString($best_match);
                $best_match["used"] = true;
            }
        }
        
        # See if we have something on the list
        if (count($negotiated_list) == 0)
        {
            if ($strict)
            {
                throw new NotAcceptableException(
                    "The service cannot generate an acceptable response");
            }
            else
            {
                # Add the first media type to the list as a fallback
                $negotiated_list[] = $type_list[0];
            }
        }
        
        # Remove duplicates (they do happen, unfortunately)
        # TODO: try to figure out why
        $negotiated_list = array_values(array_unique($negotiated_list));
        
        # Return the list of mimetypes
        return $negotiated_list;
    }
    
    /**
     * Adds a transaction coordinator object to the list of transaction
     * coordination mechanism. This mechanism is designed to either commit or
     * roll back a transaction in one or more data objects depending on the
     * result of the service call. If the sendResponse() method is called with
     * a status of 2xx, all transaction coordinators will asked to commit their
     * data. If sendResponse() contains a status code other than 2xx, all
     * coordinators will be asked to roll back their data and any rollback
     * errors will be ignored.
     * 
     * This method requires the object reference that is being added, and
     * optionally the names of the methods used to commit or rollback the
     * transaction. For PDO-like objects that contain commit() and rollBack()
     * methods, you can simply pass the object reference. Otherwise, use the
     * second and third argument to pass the name of the methods that correspond
     * to the commit and rollback actions respectively.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param object $object_reference
     *   Reference to the transaction object
     * @param string $commit_method
     *   Method name for the commit action
     * @param string $rollback_method
     *   Method name for the rollback action
     * @throws \Cougar\Exceptions\Exception
     */
    public function addTransactionCoordinator(&$object_reference,
        $commit_method = "commit", $rollback_method = "rollBack")
    {
        # Make sure this is an object
        if (! is_object($object_reference))
        {
            throw new Exception("Object reference must be an object");
        }
        
        # Make sure object has the methods specified
        if (! method_exists($object_reference, $commit_method))
        {
            throw new Exception("Object does not contain " . $commit_method .
                "method");
        }
        
        if (! method_exists($object_reference, $rollback_method))
        {
            throw new Exception("Object does not contain " . $rollback_method .
                "method");
        }
        
        # Store the reference
        $this->transactionCoordinators[] = array(
            "object" => $object_reference,
            "commit_method" => $commit_method,
            "rollback_method" => $rollback_method
        );
    }

    /**
     * Sends the given response to the client with the given status code.
     * Supported status codes are:
     *
     *   200 OK
     *   201 Created - Specify URI in the response parameter
     *   202 Accepted
     *   203 Non-Authoritative Information - sames 200, but cached or partial
     *   204 No Content - OK, but no data to return
     *   301 Moved Permanently - Specify new URI in the response parameter
     *   302 Found - Specify location URI in the response parameter
     *   400 Bad Request - Use this when request syntax is malformed
     *   403 Forbidden - Access denied
     *   404 Not Found
     *   405 Method Not Allowed
     *   406 Not Acceptable - Used when we can't generated requested doc type
     *   500 Internal Server Error - Used on an unexpected application error
     *   501 Not Implemented
     *   503 Service Unavailable
     *
     * If the response is a string, the response will be sent as is. Use this
     * for HTML, XML or other text types.
     *
     * If the response is given as an object and the negotiated response type is
     * JSON or PHP, then the object will be encoded as json or serialized before
     * being sent.
     *
     * If the response is a SimpleXML object, then the object will be converted
     * to XML using its asXML() method before being sent.
     *
     * It is also possible to send additional headers to the browser and to
     * override the mimetype of the data being sent.
     *
     * Once this method ends, it will stop further execution of the script.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param int $http_status_code
     *   Numeric HTTP status
     * @param mixed $response
     *   The response to send out; if response string, it  will be sent as-is.
     *   If it is an array or object, it will be converted to the output
     *   mimetype. XML must be given as a string or a SimpleXMLElement.
     * @param array $headers
     *   An associative array of headers to set
     * @param string $mimetype
     *   Override the output mimetype
     * @throws \Cougar\Exceptions\Exception
     */
    public function sendResponse($http_status_code, $response = null,
        array $headers = array(), $mimetype = null)
    {
        # Clean the buffer
        ob_clean();
        
        # Make sure the status code is one we support
        if (! array_key_exists($http_status_code, $this->httpMessages))
        {
            throw new Exception("Invalid HTTP status code");
        }
        
        # Send the headers
        if (! $this->__testMode)
        {
            # Set the status code
            header($_SERVER["SERVER_PROTOCOL"] . " " .
                $this->httpMessages[$http_status_code]);

            # Send the content type
            if ($mimetype)
            {
                header("Content-type: " . $mimetype);
            }
            
            # Add relevant CORS headers
            $origin = $this->header("Origin");
            if ($origin)
            {
                if (array_key_exists("Allow", $headers))
                {
                    header("Access-Control-Max-Age: 86400");
                    header("Access-Control-Allow-Methods: " .
                        $headers["Allow"]);
                }
            }

            # Set the headers
            foreach($headers as $name => $header)
            {
                header($name . ": " . $header);
            }
        }
        
        # Generate the content
        if (is_object($response) || is_array($response))
        {
            switch ($mimetype)
            {
                case "application/json":
                    $content = json_encode($response);
                    break;
                case "application/xml":
                case "text/xml":
                    if (! $response instanceof \SimpleXMLElement)
                    {
                        throw new Exception("Response must be an XML object");
                    }
                    $content = $response->asXML();
                case "application/vnd.php.serialized":
                    $content = serialize($response);
                    break;
                case "text/csv":
                    throw new Exception("CSV output must be provided as text");
                    break;
                case "text/plain":
                    throw new Exception(
                        "Plain text output must be provided as text");
                    break;
                default:
                    throw new Exception(
                        "Unable to generate " . $mimetype . " document");
            }
        }
        else
        {
            $content = $response;
        }
        
        # See if we need to commit or roll back the transaction
        if ($http_status_code >= 200 && $http_status_code < 300)
        {
            foreach($this->transactionCoordinators as $transaction_coordinator)
            {
                $transaction_coordinator["object"]->
                    $transaction_coordinator["commit_method"]();
            }
        }
        else
        {
            foreach($this->transactionCoordinators as $transaction_coordinator)
            {
                try
                {
                    $transaction_coordinator["object"]->
                        $transaction_coordinator["rollback_method"]();
                }
                catch (\Exception $e)
                {
                    # Ignore the error so that it doesn't clobber the original
                    # problem or exception
                }
            }
        }
        
        # Send the content
        echo($content);

        # See if we are in unit tests
        if (! $this->__testMode)
        {
            # Flush the buffer
            ob_flush();

            # Exit
            exit();
        }
    }
    
    /**
     * Returns the entire request, as a string, as received by the browser.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @return string Raw request
     */
    public function rawRequest()
    {
        # There is no way to get the raw request in PHP, so we will recreate it
        $raw_request = "";
        
        # Go through all the headers
        foreach($this->headers as $header => $value)
        {
            $raw_request .= $header . ": " . $value . "\n";
        }
        
        # Add the body and return the data
        return "\n" . $raw_request . file_get_contents("php://input");
    }
    
    /**
     * Handles exceptions so that they are sent to the browser with proper
     * HTTP status codes and not as stack dumps.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param object exception Thrown exception
     * 
     * @todo: create objects where appropriate so that response can be sent in
     *        the proper format; additionally, include stack traces on
     *        non-production environments
     */
    public function exceptionHandler($e = null)
    {
        # Log the exception
        error_log($e);
        
        # See if we have a status code
        if ($e instanceof Exception)
        {
            $status_code = $e->getHttpStatusCode();
        }
        else
        {
            $status_code = 500;
        }
        
        # Build the message
        $message = $e->getMessage();
        if ($message)
        {
            $message = $this->httpMessages[$status_code] . ": " . $message;
        }
        else
        {
            $message = $this->httpMessages[$status_code];
        }
        
        # Send the response
        $this->sendResponse($status_code, $message);
    }

    
    /***************************************************************************
     * PROTECTED PROPERTIES AND METHODS
     **************************************************************************/
    
    /**
     * Associative array with the request headers
     * @var array
     */
    protected $headers = null;
    
    /**
     * The request method, here for easy access
     * @var string
     */
    protected $method = null;
    
    /**
     * The URI parameters, here for easy access
     * @var array
     */
    protected $uri = array();
    
    /**
     * The body of the request
     * @var string
     */
    protected $body = null;
    
    /**
     * @var array List of query parameters
     */
    protected $queryParameters = null;
    
    /**
     * HTTP messages
     * @var array
     */
    protected $httpMessages = array(
        200 => "200 OK",
        201 => "201 Created",
        202 => "202 Accepted",
        203 => "203 Non-Authoritative Information",
        204 => "204 No Content",
        301 => "301 Moved Permanently",
        302 => "302 Found",
        400 => "400 Bad Request",
        401 => "401 Authentication Required",
        403 => "403 Forbidden",
        404 => "404 Not Found",
        405 => "405 Method Not Allowed",
        406 => "406 Not Acceptable",
        500 => "500 Internal Server Error",
        501 => "501 Not Implemented",
        503 => "503 Service Unavailable"
    );
    
    protected $transactionCoordinators = array();
    
    /**
     * Takes a parsed accept header array object and converts it to a string
     * value.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param array $parsed_accept_entry Mimetype accept header array object
     * @return string Mimetype
     */
    protected function buildAcceptString(array $parsed_accept_entry)
    {
        if (count($parsed_accept_entry["params"]) > 0)
        {
            # Insert the mimetype
            $entry = $parsed_accept_entry["mimetype"];
            
            # Add the parameters
            foreach($parsed_accept_entry["params"] as $token => $value)
            {
                $entry .= ";" . $token . "=" . $value;
            }
            
            # Return the entry
            return $entry;
        }
        else
        {
            return $parsed_accept_entry["mimetype"];
        }
    }
    
    /**
     * Parses the given accept header string as specified in RFC 2616 Section 14
     * and returns an array with the parsed mimetype and parameters (minus the
     * quality) in the order of preference (highest to lowest).
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param string $header_string The accept header string
     * @return array Mimetypes in order of preference
     */
    protected function parseAcceptHeaderString($header_string)
    {
        # Get the accept header and remove white space
        $header = preg_replace("/\s+/", "", $header_string);
        
        # Define the arrays that will hold the specified mime types, and sorting
        # preferences
        $types = array();
        $sort_quality = array();
        $sort_param_count = array();
        $sort_wild_cards = array();
        $sort_order = array();
        
        # Go through each entry
        foreach(explode(",", $header) as $raw_entry)
        {
            # Define the base entry
            $entry = array("mimetype" => null,
                "type" => null,
                "subtype" => null,
                "params" => array());
            $quality = 1.0;
            
            # See if we have parameters
            if (strpos($raw_entry, ";") !== false)
            {
                # Separate the parameters
                $params = explode(";", $raw_entry);
                
                # Extract the mime type
                $entry["mimetype"] = array_shift($params);
                
                # Go through the parameters
                foreach($params as $param)
                {
                    # Split on the =
                    $param_values = explode("=", $param, 2);
                    
                    # See if this is a quality parameter
                    if ($param_values[0] == "q")
                    {
                        # Save the quality
                        $quality = (float) $param_values[1];
                    }
                    else
                    {
                        # Save the entry as a named parameter
                        $entry["params"][$param_values[0]] = $param_values[1];
                    }
                }
            }
            else
            {
                # Extract the mime type andd define an empty array for params
                $entry["mimetype"] = $raw_entry;
            }
            
            # Break-up the mime type into its type and subtype
            $entry["type"] = substr($entry["mimetype"], 0,
                strpos($entry["mimetype"], "/"));
            $entry["subtype"] = substr($entry["mimetype"],
                strpos($entry["mimetype"], "/") + 1);
            
            # Add the entry to our list
            $types[] = $entry;
            
            /* RFC 2616 Section 14 specifies the order in which the mime types
             * should be considered. First, quality is considered. Second,
             * specificity. text/html;level=1 is more specific than text/html.
             * text/html is more specific than text/* and that in turn is more
             * specific than * /*. Finally we take the order in which they
             * appear, all things being equal.
             * 
             * To deal with the wildcard entries, we simply put in a value for
             * the number of wildcards in the mimetype.
             */
            $sort_quality[] = $quality;
            $sort_param_count[] = count($entry["params"]);
            if ($entry["type"] == "*")
            {
                if ($entry["subtype"] == "*")
                {
                    $sort_wild_cards[] = 2;
                }
                else
                {
                    $sort_wild_cards[] = 1;
                }
            }
            else
            {
                $sort_wild_cards[] = 0;
            }
            $sort_order[] = count($sort_order);
        }
            
        # Sort the entries
        array_multisort($sort_quality, SORT_DESC,
            $sort_param_count, SORT_DESC,
            $sort_wild_cards, SORT_ASC,
            $sort_order, SORT_ASC,
            $types);
        
        # Return the result
        return $types;
    }

    
    /***************************************************************************
     * PRIVATE PROPERTIES AND METHODS
     **************************************************************************/
    
    /**
     * @var bool Whether we are in test mode
     */
    private $__testMode = false;
}
?>
