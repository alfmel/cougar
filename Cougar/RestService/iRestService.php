<?php

namespace Cougar\RestService;

/**
 * Defines the base RestService class, which helps deal with REST service calls.
 * The class provides functions for reading GET, POST and URL parameters, for
 * obtaining the body or request method, etc.
 *
 * @history
 * 2013.09.30:
 *   (AT)  Initial release
 * 2014.02.05:
 *   (AT)  Added uri() and url() methods
 *
 * @version 2014.02.05
 * @package Cougar
 * @license MIT
 *
 * @copyright 2013-2014 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
interface iRestService
{
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
     * @return string HTTP Method
     */
    public function method();

    /**
     * Returns the entire request URL.
     *
     * @history
     * 2014.02.05:
     *   (AT)  Initial release
     *
     * @version 2014.02.05
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @return string Request URL
     */
    public function url();

    /**
     * Returns the entire request URI, including the GET query. You may remove
     * the query parameters by setting the first argument to false.
     *
     * @history
     * 2014.02.05:
     *   (AT)  Initial release
     *
     * @version 2014.02.05
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param bool $include_query Whether to include the query in the URI
     * @return string Request URL
     */
    public function uri($include_query = true);

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
     * @return array Associative array with HTTP request headers
     */
    public function headers();
    
    /**
     * Returns the specified header. If it does not exist, return the default
     * value (null by default). As specified in RFC 2616 (HTTP), the header name
     * is case-insensitive.
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
    public function header($header, $type = "string", $default = null);
    
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
    public function uriValue($index, $type = "string", $default = null);
    
    /**
     * Returns the value of the given GET variable. If it does not exist, return
     * the default value (null by default).
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param int $variable GET variable name
     * @param string $type Cast to the specified type (string|int|float|bool)
     * @param mixed $default Default value
     * @return mixed GET variable value
     */
    public function getValue($variable, $type = "string", $default = null);
    
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
    public function getQuery();
        
    /**
     * Returns the value of the given POST variable. If it does not exist,
     * return the default value (null by default).
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
    public function postValue($variable, $type = "string", $default = null);
    
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
    public function body($parse_type = null);
    
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
     */
    public function negotiateResponseType(array $type_list, $strict = false);

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
     *   401 Authentication Required
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
     * Once this script ends, it will stop further execution of the script.
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
     *   The response to send out; if response is a string, it will be sent
     *   as-is. If it is an array or object, it will be converted to the output
     *   mimetype
     * @param array $headers
     *   An associative array of headers to send
     * @param string $mimetype
     *   Override the output mimetype
     */
    public function sendResponse($http_status_code, $response = null,
        array $headers = array(), $mimetype = null);
    
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
    public function rawRequest();
    
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
     * @param object $exception Thrown exception
     */
    public function exceptionHandler($exception);
}
?>
