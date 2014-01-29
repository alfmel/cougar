<?php

namespace Cougar\RestClient;

use Cougar\Exceptions\BadGatewayException;
use Cougar\Exceptions\ConflictException;
use Cougar\Exceptions\ExpectationFailed;
use Cougar\Exceptions\GatewayTimeoutException;
use Cougar\Exceptions\GoneException;
use Cougar\Exceptions\HttpVersionNotSupportedException;
use Cougar\Exceptions\LengthRequiredException;
use Cougar\Exceptions\PreconditionFailedException;
use Cougar\Exceptions\ProxyAuthenticationRequiredException;
use Cougar\Exceptions\RequestedRangeNotSatisfiableException;
use Cougar\Exceptions\RequestEntityTooLargeException;
use Cougar\Exceptions\RequestUriTooLargeException;
use Cougar\Exceptions\RequestUriTooLongException;
use Cougar\Exceptions\TimeoutException;
use Cougar\Exceptions\UnsupportedMediaTypeException;
use Cougar\Security\iHttpCredentialProvider;
use Cougar\Exceptions\Exception;
use Cougar\Exceptions\AccessDeniedException;
use Cougar\Exceptions\AuthenticationRequiredException;
use Cougar\Exceptions\BadRequestException;
use Cougar\Exceptions\HttpRequestParseException;
use Cougar\Exceptions\NotAcceptableException;
use Cougar\Exceptions\NotFoundException;
use Cougar\Exceptions\NotImplementedException;
use Cougar\Exceptions\MethodNotAllowedException;
use Cougar\Exceptions\ServiceUnavailableException;

# Initialize the framework
require_once("cougar.php");

/**
 * The REST Client extends the CurlWrapper class to provide an easy-to-use REST
 * client. The client support GET, POST, PUT and DELETE operations. The most
 * important feature in this client is its native support for WsAuth with both
 * URL-Encoded and Nonce-Encoded HMACs. Since the class extends the CurlWrapper
 * class, all CurlWrapper methods are still available.
 *
 * @history
 * 2013.09.30:
 *   (AT)  Initial release
 * 2014.01.28:
 *   (AT)  Add HTTP credential provider support
 * 2014.01.29:
 *   (AT)  Improve exception handling by adding all 4xx and 5xx HTTP errors
 *         specified in RFC 2616.
 *
 * @version 2014.01.29
 * @package Cougar
 * @license MIT
 *
 * @copyright 2013 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 *
 * @todo: Implement client authorization providers
 */

class RestClient extends CurlWrapper implements iRestClient
{
    /**
     * Initializes the client
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string default_response_type [RAW|XML|JSON|PHP]
     * @param string default_content_type
     * @param int timeout (optional)
     * @param string ssl (optional - See CurlWrapper constructor for options)
     */
    public function __construct($response_type = null, $content_type = null,
        $timeout = null, $ssl = null)
    {
        # Set the response type
        $this->setResponseType($response_type);
        
        # Run the parent constructor
        parent::__construct($content_type, $timeout, $ssl);
    }
    
    /**
     * Clears authentication
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
        parent::__destruct();
    }

    /***************************************************************************
     * PUBLIC PROPERTIES AND METHODS
     **************************************************************************/

    /**
     * Makes the specified request. If the response type is set on the object,
     * the method will try to parse the object based on the Content-Type header
     * sent back by the server.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     * 2014.01.28:
     *   (AT)  Add HTTP credential provider support
     * 2014.01.29:
     *   (AT)  Improve exception handling by adding all 4xx and 5xx HTTP errors
     *         specified in RFC 2616.
     *
     * @version 2014.01.29
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string $method
     *   Method to use [GET|POST|PUT|DELETE]
     * @param string $url
     *   The base URL to call
     * @param array $url_fields
     * @param array $get_fields
     *   Array with name/value pairs to pass as GET params
     * @param mixed $body
     *   Array, string or @/path/to/file for body
     * @param string $content_type
     *   Content type of the body being sent
     * @return mixed response
     * @throws \Cougar\Exceptions\NotImplementedException
     * @throws \Cougar\Exceptions\ProxyAuthenticationRequiredException
     * @throws \Cougar\Exceptions\BadRequestException
     * @throws \Cougar\Exceptions\UnsupportedMediaTypeException
     * @throws \Cougar\Exceptions\ExpectationFailed
     * @throws \Cougar\Exceptions\AuthenticationRequiredException
     * @throws \Cougar\Exceptions\PreconditionFailedException
     * @throws \Cougar\Exceptions\RequestUriTooLongException
     * @throws \Cougar\Exceptions\BadGatewayException
     * @throws \Cougar\Exceptions\NotFoundException
     * @throws \Cougar\Exceptions\RequestedRangeNotSatisfiableException
     * @throws \Cougar\Exceptions\LengthRequiredException
     * @throws \Cougar\Exceptions\TimeoutException
     * @throws \Cougar\Exceptions\ServiceUnavailableException
     * @throws \Cougar\Exceptions\Exception
     * @throws \Cougar\Exceptions\HttpRequestParseException
     * @throws \Cougar\Exceptions\ConflictException
     * @throws \Cougar\Exceptions\GoneException
     * @throws \Cougar\Exceptions\GatewayTimeoutException
     * @throws \Cougar\Exceptions\RequestEntityTooLargeException
     * @throws \Cougar\Exceptions\NotAcceptableException
     * @throws \Cougar\Exceptions\AccessDeniedException
     * @throws \Cougar\Exceptions\MethodNotAllowedException
     * @throws \Cougar\Exceptions\HttpVersionNotSupportedException
     */
    public function makeRequest($method, $url, array $url_fields = null,
        array $get_fields = null, $body = null, $content_type = null)
    {
        $this->newRequest();
        $this->setMethod($method);
        $this->setURL($url);
        if (is_array($url_fields))
        {
            $this->setURLFields($url_fields);
        }
        if (is_array($get_fields))
        {
            $this->setGetFields($get_fields);
        }
        if ($body)
        {
            $this->setBody($body);
            if ($content_type)
            {
                $this->contentType = $content_type;
                if ($content_type == "multipart/form-data")
                {
                    $this->multiPart = true;
                }
            }
            else
            {
                if (is_array($body) || is_object($body))
                {
                    $this->contentType =
                        "application/x-www-form-urlencoded";
                }
            }
        }

        # Request the appropriate data type
        $reset_accept_header = false;
        if (! array_key_exists("Accept", $this->headers))
        {
            switch(strtolower($this->responseType))
            {
                case "xml":
                    $this->headers["Accept"] = "application/xml";
                    $reset_accept_header = true;
                    break;
                case "json":
                    $this->headers["Accept"] = "application/json";
                    $reset_accept_header = true;
                    break;
                case "php":
                    $this->headers["Accept"] = "application/vnd.php.serialized";
            }
        }

        # Get the full URL
        $url = $this->generateURL();

        # See if we need to add credentials
        if ($this->credentialProvider)
        {
            # Add the credentials
            $this->credentialProvider->addCredentials($method, $url,
                $this->headers, $this->cookies, $body, $this->contentType);
        }

        # Make the request
        $http_code = $this->Exec($url);

        # Get the response
        $response = $this->getResponse();

        # If the Accept header was not part of the headers defined by the user,
        #  remove it to avoid polluting them
        if ($reset_accept_header)
        {
            unset($this->headers["Accept"]);
        }

        # Parse the response
        try
        {
            # See what the content-type headers says
            $content_type = $this->getHeader("Content-Type");

            # The JSON case includes the ability to parse text as JSON because
            #  in debug, I tend to tell it to return text so that I can see in
            #  a browser
            if (strpos($content_type, "json") !== false ||
                ($content_type == "text/plain" &&
                    $this->responseType == "json"))
            {
                $tmp_response = json_decode($response, true);
                if ($tmp_response === null)
                {
                    throw new Exception("JSON decode error");
                }
                $response = $tmp_response;
            }
            else if (strpos($content_type, "xml") !== false)
            {
                $response = new \SimpleXMLElement($response);
            }
            else if (strpos($content_type, "php") !== false)
            {
                $response = unserialize($response);
            }
        }
        catch (\Exception $e)
        {
            throw new HttpRequestParseException("Could not parse response as " .
                $content_type, $http_code, $response, $e->getMessage());
        }

        # Validate the return code
        switch($http_code)
        {
            case 400:
                throw new BadRequestException("HTTP Status 400: Bad Request");
                break;
            case 401:
                throw new AuthenticationRequiredException(
                    "HTTP Status 401: Authentication Required");
                break;
            case 403:
                throw new AccessDeniedException(
                    "HTTP Status 403: Access Denied");
                break;
            case 404:
                throw new NotFoundException("HTTP Status 404: Not Found");
                break;
            case 405:
                throw new MethodNotAllowedException(
                    "HTTP Status 405: Method Not Allowed");
                break;
            case 406:
                throw new NotAcceptableException(
                    "HTTP Status 406: Request Not Acceptable");
                break;
            case 407:
                throw new ProxyAuthenticationRequiredException(
                    "HTTP Status 407: Proxy Authentication Required");
                break;
            case 408:
                throw new TimeoutException(
                    "HTTP Status 408: Request Timeout");
                break;
            case 409:
                throw new ConflictException("HTTP Status 409: Conflict");
                break;
            case 410:
                throw new GoneException("HTTP Status 410: Gone");
                break;
            case 411:
                throw new LengthRequiredException(
                    "HTTP Status 411: Length Required");
                break;
            case 412:
                throw new PreconditionFailedException(
                    "HTTP Status 412: Precondition Failed");
                break;
            case 413:
                throw new RequestEntityTooLargeException(
                    "HTTP Status 413: Request Entity Too Large");
                break;
            case 414:
                throw new RequestUriTooLongException(
                    "HTTP Status 414: Request URI Too Long");
                break;
            case 415:
                throw new UnsupportedMediaTypeException(
                    "HTTP Status 415: Unsupported Media Type");
                break;
            case 416:
                throw new RequestedRangeNotSatisfiableException(
                    "HTTP Status 416: Request Range Not Satisfiable");
                break;
            case 417:
                throw new ExpectationFailed(
                    "HTTP Status 417: Expectation Failed");
                break;
            case 500:
                throw new ServerErrorException(
                    "HTTP Status 500: Internal Server Error");
                break;
            case 501:
                throw new NotImplementedException(
                    "HTTP Status 501: Not Implemented");
                break;
            case 502:
                throw new BadGatewayException("HTTP Status 502: Bad Gateway");
                break;
            case 503:
                throw new ServiceUnavailableException(
                    "HTTP Status 503: Service Unavailable");
                break;
            case 504:
                throw new GatewayTimeoutException(
                    "HTTP Status 504: Gateway Timeout");
                break;
            case 505:
                throw new HttpVersionNotSupportedException(
                    "HTTP Status 505: HTTP Version Not Supported");
                break;
            default:
                if ($http_code >= 400 && $http_code < 500)
                {
                    throw new BadRequestException("HTTP Status " . $http_code);
                }
                else if ($http_code >= 500)
                {
                    throw new ServerErrorException("HTTP Status " . $http_code);
                }
                break;
        }

        # Return the response
        return $response;
    }
    
    /**
     * Makes a GET request
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param string URL
     * @param array URL fields
     * @param array GET parameters
     * @param string|array Body
     * @param string content_type
     * @return object response
     * 
     * @example RestClient_get.example.php GET example
     */
    public function get($url, array $url_fields = null,
        array $get_fields = null, $body = null, $content_type = null)
    {
        return $this->makeRequest("GET", $url, $url_fields, $get_fields, $body,
            $content_type);
    }
    
    /**
     * Makes a POST request
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param string URL
     * @param array URL fields
     * @param array GET parameters
     * @param string|array Body
     * @param string content_type
     * @return object response
     * 
     * @example RestClient_post.example.php POST example
     */
    public function post($url, array $url_fields = null,
        array $get_fields = null, $body = null, $content_type = null)
    {
        return $this->makeRequest("POST", $url, $url_fields, $get_fields,
            $body, $content_type);
    }
    
    /**
     * Makes a PUT request
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param string URL
     * @param array URL fields
     * @param array GET parameters
     * @param string|array Body
     * @param string content_type
     * @return object response
     * 
     * @example RestClient_put.example.php PUT example
     */
    public function put($url, array $url_fields = null,
        array $get_fields = null, $body = null, $content_type = null)
    {
        return $this->makeRequest("PUT", $url, $url_fields, $get_fields, $body,
            $content_type);
    }
    
    /**
     * Makes a DELETE request
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param string URL
     * @param array URL fields
     * @param array GET parameters
     * @param string|array Body
     * @param string content_type
     * @return object response
     * 
     * @example RestClient_delete.example.php DELETE example
     */
    public function delete($url, array $url_fields = null,
        array $get_fields = null, $body = null, $content_type = null)
    {
        return $this->makeRequest("DELETE", $url, $url_fields, $get_fields,
            $body, $content_type);
    }
    
    /**
     * Sets the default response type
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param string $type
     *   Parse type [XML|JSON|PHP]
     * @throws \Cougar\Exceptions\Exception
     */
    public function setResponseType($type)
    {
        $type = strtolower($type);
        switch($type)
        {
            case "xml":
            case "json":
            case "php":
                $this->responseType = $type;
                break;
            case "none":
            case "raw":
            case null:
                $this->responseType = null;
                break;
            default:
                throw new Exception("Unsupported response type: " . $type);
        }
    }

    /**
     * Adds a client authentication provider.
     *
     * Note that the library will only hold one authentication provider at a
     * time. If you need to make calls using two different authentication
     * schemes, you will ned two separate RestClient objects.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     * 2014.01.24:
     *   (AT)  Renamed from addClientAuthenticationProvider
     *
     * @version 2014.01.24
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param \Cougar\Security\iHttpCredentialProvider $credential_provider
     *   Credential provider
     */
    public function addCredentialProvider(
        iHttpCredentialProvider $credential_provider)
    {
        $this->credentialProvider = $credential_provider;
    }


    /***************************************************************************
     * PROTECTED PROPERTIES AND METHODS
     **************************************************************************/

    /**
     * @var string Default request parse type
     */
    protected $responseType = null;

    /**
     * @var \Cougar\Security\iHttpCredentialProvider Credential provider
     */
    protected $credentialProvider;
}
?>
