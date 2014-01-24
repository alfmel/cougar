<?php

namespace Cougar\RestClient;

use Cougar\Security\iHttpCredentialProvider;
use Cougar\Exceptions\Exception;

# Initialize the framework
require_once("cougar.php");

/**
 * The CurlWrapper provides an object-oriented way of accessing and using the
 * cURL functions within PHP. The main idea is to simplify calls to REST-like
 * services with cURL. Not all features of cURL are implemented, so please
 * contribute as necessary.
 *
 * Since it uses cURL, you need to have cURL support in PHP with a proper
 * certificate list. The path to the certificate list can be set using the 3rd
 * argument in the constructor.
 *
 * @history
 * 2013.09.30:
 *   (AT)  Initial release
 * 2014.01.24:
 *   (AT)  Add support for credential provider
 *
 * @version 2014.01.24
 * @package Cougar
 * @license MIT
 *
 * @copyright 2013-2014 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */

class CurlWrapper
{
    /**
     * Initializes the cURL session
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param string $content_type
     *   (optional, default text/plain)
     * @param int $timeout
     *   Optional, 900 seconds by default
     * @param string $ssl
     *   Path to SSL certificates, path to PEM file, false to turn off SSL
     *   verification
     */
    public function __construct($content_type = "text/plain", $timeout = 900,
        $ssl = "/etc/ssl")
    {
        # Set the default values
        if ($content_type === null)
        {
            $content_type = "text/plain";
        }
        
        if ($timeout === null)
        {
            $timeout = 900;
        }
        
        if ($ssl === null)
        {
            $ssl = "/etc/ssl";
        }
        
        # Set the timeout and ca_path properties
        $this->defaultContentType = $content_type;
        $this->timeout = (int) $timeout;
        $this->ssl = (string) $ssl;
        
        # Initialize the cURL session
        $this->curlInit();
    }

    /**
     * Closes the cURL session
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
        # See if we have a valid cURL session
        if (is_resource($this->curl))
        {
            if (get_resource_type($this->curl) == "curl")
            {
                curl_close($this->curl);
            }
        }
    }

    /**
     * Creates and reconfigures the cURL resource after deserializing
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     */
    public function __wakeup()
    {
        $this->curlInit();
    }

    /***************************************************************************
     * PUBLIC PROPERTIES AND METHODS
     **************************************************************************/

    /**
     * Prepares the object for a new request by resetting the method, URL,
     * URLFields, GetFields, Body, PostMultiPart and OutputPath
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     */
    public function newRequest()
    {
        # Reset the cURL object by specifying a GET request
        curl_setopt($this->curl, CURLOPT_HTTPGET, true);
        
        # Reset the request properties
        $this->method = "GET";
        $this->url = null;
        $this->urlFields = array();
        $this->getFields = array();
        $this->body = null;
        $this->contentType = null;
        $this->PostMultiPart = false;
        $this->outputPath = null;
        $this->executed = false;
        $this->requestInfo = null;
        $this->rawHeader = null;
        $this->responseHeaders = null;
        $this->responseHeaderMap = null;
        $this->response = null;
    }

    /**
     * Sets the time out for the request
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param int $timeout Timeout in seconds
     */
    public function setTimeout($timeout)
    {
        $this->timeout = (int) $timeout;
        curl_setopt($this->curl, CURLOPT_TIMEOUT, $this->timeout);
    }

    /**
     * Sets the HTTP request method
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param string $method
     *   One of GET, HEAD, POST, PUT, DELETE, TRACE or CONNECT
     * @throws \Cougar\Exceptions\Exception
     */
    public function setMethod($method)
    {
        $method = strtoupper($method);
        
        # See what method we have and handle it
        switch(strtoupper($method))
        {
            case "GET":
            case "HEAD":
            case "POST":
            case "PUT":
            case "DELETE":
            case "TRACE":
            case "CONNECT":
                $this->method = $method;
                break;
            default:
                throw new Exception("Invalid HTTP method: " . $method);
                break;
        }
    }

    /**
     * Sets the URL for the request
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param string $url
     */
    public function setURL($url)
    {
        $this->url = $url;
    }

    /**
     * Sets custom HTTP headers
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param array $headers
     *   Associative array of header (header name as key)
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * Sets the cookies that will be sent with the request
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param array $cookies
     *   Associative array of cookies (cookie name as key)
     */
    public function setCookies(array $cookies)
    {
        $this->cookies = $cookies;
    }

    /**
     * Sets the content type for the data being sent
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param string $content_type
     *   Content type
     */
    public function setContentType($content_type)
    {
        $this->contentType = (string) $content_type;
    }

    /**
     * Sets the user agent; by default, the user agent is:
     * 
     *   cURL (PHP_COUGAR_CLIENT)
     * 
     * You can use this method to set a new agent, or clear it by setting it to
     * null or a blank string
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param string $user_agent
     *   "Browser" agent ID
     */
    public function setUserAgent($user_agent)
    {
        # Set the user agent
        $this->userAgent = (string) $user_agent;
        curl_setopt($this->curl, CURLOPT_USERAGENT, (string) $this->userAgent);
    }
    
    /**
     * Sets the location of the SSL client certificate (in PEM format, including
     * the private key) and an optional password if one is required to unlock
     * the private key.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param string $cert_filename
     *   Full path and filename of certificate file
     * @param string $password
     *   Optional password
     * @throws \Cougar\Exceptions\Exception
     */
    public function setClientSSLCertificate($cert_filename, $password = null)
    {
        # Make sure the file exists
        if (!file_exists($cert_filename))
        {
            throw new Exception("Client certificate does not exist: " .
                $cert_filename);
        }
        
        # Set the options in cURL
        curl_setopt($this->curl, CURLOPT_SSLCERT, $cert_filename);
        if ($password)
        {
            curl_setopt($this->curl, CURLOPT_SSLCERTPASSWD, $password);
        }
        
        # Store the values
        $this->sslCertFile = $cert_filename;
        $this->sslCertPassword = $password;
    }

    /**
     * Sets the URL fields; these will be added to the URL separated by slashes
     * (/param1/param2/param3) in the order they are listed in the array.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param array $fields
     *   Array with URL fields (keys are ignored)
     */
    public function setURLFields(array $fields)
    {
        $this->urlFields = $fields;
    }

    /**
     * Sets the GET fields; these will be url-encoded and added to the URL
     * after the ? character.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param array $fields
     *   Associative array of key/value pairs
     */
    public function setGetFields(array $fields)
    {
        $this->getFields = $fields;
    }
    
    /**
     * Sets the body of the request. The body can be a string, an array, object,
     * or a file (described in a string as @/path/to/file). Strings will be sent
     * directly without any modification. Arrays and objects will be sent as
     * multipart form data unless the url_encoded parameter is set to true.  If
     * the request method is PUT and the string starts with @, then the value
     * if the string will be considered a file and the file will be sent with
     * the request.
     * 
     * To send files using POST, the value of the field must start with a @ and
     * may optionally contain the mime type as follows:
     *   file_upload => @/path/to/file;type=text/plain
     * 
     * To send a file using PUT, the body needs to be a string in @/path/to/file
     * format. 
     * 
     * When uploading a file, the content type will automatically be set to
     * multipart/form-data as specified in RFC 1867
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * \
     * @param mixed $body
     *   Contents of request body
     * @param bool $use_multipart
     *   Whether to use multipart body (defaults to false)
     */
    public function setBody($body, $use_multipart = false)
    {
        $this->body = $body;
        $this->multiPart = (bool) $use_multipart;
        
        # Check if we have an array with a file pointer
        if (is_array($body))
        {
            foreach($body as $value)
            {
                if (substr($value, 0, 1) == "@")
                {
                    # We have a file; use MultiPart
                    $this->multiPart = true;
                    break;
                }
            }
        }
    }

    /**
     * When set, the output of the request will be written to a file with the
     * specified filename. This is useful for downloading files.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param array $filename
     *   Write output to the given file
     * @throws \Cougar\Exceptions\Exception
     */
    public function setOutputFilename($filename)
    {
        # Make sure the directory is writeable
        if (! is_writeable(dirname($filename)))
        {
            throw new Exception("Unable to write to directory " .
                dirname($filename));
        }
        
        $this->outputPath = $filename;
    }

    /**
     * Generates the URL using the URL and GET fields
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @return string url
     */
    public function generateURL()
    {
        # Add the URL fields to the array
        $url_fields = "";
        if (count($this->urlFields) > 0)
        {
            if (substr($this->url, -1) != "/")
            {
                $url_fields .= "/";
            }
            
            # Go through each parameter and make them url-safe
            foreach($this->urlFields as &$value)
            {
                $value = rawurlencode($value);
            }
            
            # Add the parameters to the URL
            $url_fields .= implode("/", $this->urlFields);
        }

        # Add the GET fields
        $get_fields = "";
        if (count($this->getFields) > 0)
        {
            if (strpos($this->url, "?") === false)
            {
                # We don't have any parameters; start the get query
                $get_fields = "?";
            }
            else
            {
                # We have parameters; append them
                $get_fields = "&";
            }

            $get_fields .= $this->arrayToUrlEncoding($this->getFields);
        }
        
        # Set the URL with any URL and GET parameters
        return $this->url . $url_fields . $get_fields;
    }
    
    /**
     * URL-encodes the given array.
     * 
     * By default, the method will use RFC 3986 encoding in PHP 5.4 and above.
     * To force RFC 1738 encoding pass true for the second argument. PHP
     * versions before 5.4 RFC 1738 will be used instead
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param array $data
     *   Data to encode
     * @param bool $rfc1738_encoding
     *   Use older RFC 1738 encoding (false by default)
     * @return string URL-encoded data
     */
    public function arrayToUrlEncoding($data, $rfc1738_encoding = false)
    {
        if ($rfc1738_encoding)
        {
            return http_build_query($data);
        }
        else
        {
            return http_build_query($data, "", "&", PHP_QUERY_RFC3986);
        }
    }

    /**
     * Executes the HTTP request
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     * 2014.01.24:
     *   (AT)  Minor refactoring to support the credentials provider;
     *   (AT)  Call the credential provider if we have one
     *
     * @version 2014.01.24
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string $url
     *   Optional URL
     * @return int HTTP status code
     * @throws \Cougar\Exceptions\Exception
     */
    public function exec($url = null)
    {
        # See if we have already executed the request
        if ($this->executed == true)
        {
            throw new Exception("Cannot re-execute request");
        }
        
        # Initialize the headers array
        $headers = array();
        
        # Set the method
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $this->method);
        
        # Get the URL
        if ($url === null)
        {
            $url = $this->generateURL();
        }

        # Define the input and output file pointers
        $put_fp = null;
        $put_filesize = null;
        $output_fp = null;
        
        # Figure out the body, content type and size
        $body = null;
        $content_type = null;
        $content_length = null;
        
        if (is_array($this->body) || is_object($this->body))
        {
            if ($this->multiPart)
            {
                # Leave the body as an array
                $body = $this->body;
                $content_type = "multipart/form-data";
            }
            else
            {
                # Convert the array into a url-encoded string
                $body = $this->arrayToUrlEncoding($this->body);
                $content_type = "application/x-www-form-urlencoded";
                $content_length = strlen($body);
            }
        }
        else if ($this->method == "PUT" && substr($this->body, 0, 1) == "@")
        {
            # Extract the filename
            $filename = substr($this->body, 1);

            # Make sure the file exists
            if (! file_exists($filename))
            {
                throw new Exception("PUT file does not exist");
            }

            # Open the file and get its size
            $put_fp = fopen($filename, "r");
            $put_filesize = filesize($filename);
        }
        else if ($this->body)
        {
            $body = $this->body;
            if ($this->contentType === null)
            {
                $content_type = $this->defaultContentType;
            }
            else
            {
                $content_type = $this->contentType;
            }
            $content_length = strlen($body);
        }

        // Merge the headers and get the cookies
        $headers = array_merge($headers, $this->headers);
        $cookies = $this->cookies;

        // See if we have a credential provider
        if ($this->credentialProvider)
        {
            // Get the credentials
            $this->credentialProvider->addCredentials($url, $headers, $cookies,
                $body);
        }

        // Add the URL
        curl_setopt($this->curl, CURLOPT_URL, $url);

        // Add the headers
        if (count($headers) > 0)
        {
            # Build the cURL headers array
            $curl_headers = array();
            foreach($headers as $header_name => $header_value)
            {
                $curl_headers[] = $header_name . ": " . $header_value;
            }
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $curl_headers);
        }

        # Add the cookies
        if (count($cookies) > 0)
        {
            # Build the cookie string
            $cookie_str = "";
            foreach($cookies as $cookie_name => $cookie_value)
            {
                $cookie_str .= $cookie_name . "=" . $cookie_value . "; ";
            }
            curl_setopt($this->curl, CURLOPT_COOKIE, $cookie_str);
        }

        # Set the body, if we have one
        if ($body)
        {
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $body);
            if ($content_type)
            {
                $headers["Content-Type"] = $content_type;
            }
            if ($content_length !== null)
            {
                $headers["Content-Length"] = $content_length;
            }
        }
        else if (is_resource($put_fp))
        {
            # Set the file parameters
            curl_setopt($this->curl, CURLOPT_PUT, true);
            curl_setopt($this->curl, CURLOPT_INFILE, $put_fp);
            curl_setopt($this->curl, CURLOPT_INFILESIZE, filesize($filename));
        }

        # See if we are saving to a file
        if ($this->outputPath !== null)
        {
            $output_fp = fopen($this->outputPath, "w");
            curl_setopt($this->curl, CURLOPT_HEADER, false);
            curl_setopt($this->curl, CURLOPT_FILE, $output_fp);
        }

        # Execute the request
        $raw_response = curl_exec($this->curl);
        
        # Close files (if necessary)
        if (isset($put_fp))
        {
            fclose($put_fp);
        }
        if (isset($output_fp))
        {
            fclose($output_fp);
            curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->curl, CURLOPT_HEADER, true);
        }

        # Check for errors
        if ($raw_response === false)
        {
            throw new Exception("Execution failed: " .
                curl_error($this->curl));
        }

        # Declare the request as executed
        $this->executed = true;

        # Get the information on the request
        $this->requestInfo = curl_getinfo($this->curl);

        # Split the header and response
        $this->rawHeader = substr($raw_response, 0,
            $this->requestInfo["header_size"]);
        $this->response = substr($raw_response,
            $this->requestInfo["header_size"]);

        # Return the http code
        return $this->requestInfo["http_code"];
    }
    
    /**
     * Returns the information of the last request. The information comes from
     * the curl_getinfo() function.
     *
     * @throws \Cougar\Exceptions\Exception
     * 
     * @return array Request information
     */
    public function getInfo()
    {
        if (! $this->executed)
        {
            throw new Exception("Request has not been executed yet");
        }

        return $this->requestInfo;
    }

    /**
     * @throws \Cougar\Exceptions\Exception
     * 
     * @return int HTTP status code
     */
    public function getStatus()
    {
        if (! $this->executed)
        {
            throw new Exception("Request has not been executed yet");
        }

        return $this->requestInfo["http_code"];
    }

    /**
     * Returns the HTTP header returned by the server
     *
     * @throws \Cougar\Exceptions\Exception
     * 
     * @return string HTTP header
     * @throws \Cougar\Exceptions\Exception
     */
    public function getRawHeader()
    {
        if (! $this->executed)
        {
            throw new Exception("Request has not been executed yet");
        }

        return $this->rawHeader;
    }

    /**
     * Returns an array with all the HTTP response headers. The array will be
     * indexed by the header name.
     *
     * @throws \Cougar\Exceptions\Exception
     * 
     * @return array Associative array of HTTP response headers
     */
    public function getHeaders()
    {
        if ($this->responseHeaders === null)
        {
            $raw_headers = $this->getRawHeader();
            $lines = explode("\n", trim($raw_headers));

            $first_line = true;
            $this->responseHeaders = array();
            $this->responseHeaderMap = array();
            foreach($lines as $line)
            {
                if ($first_line)
                {
                    $this->responseHeaders["Status"] = trim($line);
                    $first_line = false;
                }
                else
                {
                    # Split the name and value
                    $header = explode(":", $line, 2);
                    if (count($header) == 1)
                    {
                        # Add the header without a name (odd if it happens)
                        #   Perhaps multiline?
                        $this->responseHeaders[] = trim($header[0]);
                    }
                    else
                    {
                        $this->responseHeaders[trim($header[0])] =
                            trim($header[1]);
                        
                        # Create the mapping
                        $this->responseHeaderMap[strtolower(trim($header[0]))] =
                            trim($header[0]);
                    }
                    
                }
            }
        }
        
        return $this->responseHeaders;
    }

    /**
     * Returns the given header. If the header is not present, null will be
     * returned. As specified in section 4.2 of the HTTP specification
     * (RFC 2616), header (field) names are case-insensitive. This method will
     * do a case-insensitive search on the header name.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param string $header Header name
     * @return string HTTP header
     */
    public function getHeader($header)
    {
        # Make sure the headers have been parsed
        if ($this->responseHeaders === null)
        {
            $this->getHeaders();
        }
        
        # See if the header exists
        $header = strtolower($header);
        if (array_key_exists($header, $this->responseHeaderMap))
        {
            return $this->responseHeaders[$this->responseHeaderMap[$header]];
        }
        else
        {
            return null;
        }
    }
    
    /**
     * Returns the HTTP body of the response returned by the server
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @return string HTTP response body
     * @throws \Cougar\Exceptions\Exception
     */
    public function getResponse()
    {
        if (! $this->executed)
        {
            throw new Exception("Request has not been executed yet");
        }

        return $this->response;
    }

    /**
     * Returns the cookies sent by the server; can be used directly to set
     * cookies for future calls
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @return array Associative array of cookies
     */
    public function getCookies()
    {
        # Initialize the cookie array
        $cookies = array();

        # Go through each header
        foreach(explode("\n", trim($this->rawHeader)) as $header)
        {
            # Get the name of the header
            $header_split = strpos($header, ":");
            $header_name = trim(substr($header, 0, $header_split));

            # See if this is a Set-Cookie header
            if ($header_name == "Set-Cookie")
            {
                # Get header value
                $header_value = trim(substr($header, $header_split + 1));

                # Get the name and value of the cookie
                $cookie_split = strpos($header_value, "=");
                $cookie_end = strpos($header_value, ";", $cookie_split);
                $cookie_name = trim(substr($header_value, 0, $cookie_split));
                $cookie_value = trim(substr($header_value, $cookie_split + 1,
                    $cookie_end - $cookie_split - 1));

                # Save the cookie
                $cookies[$cookie_name] = $cookie_value;
            }
        }

        return $cookies;
    }


    /***************************************************************************
     * PROTECTED PROPERTIES AND METHODS
     **************************************************************************/

    /**
     * @var resource cURL resource
     */
    protected $curl = null;

    /**
     * @var int Timeout value in seconds
     */
    protected $timeout = 900;

    /**
     * @var string CA certificate path
     */
    protected $ssl = "/etc/ssl";

    /**
     * @var string Request method
     */
    protected $method = "GET";

    /**
     * @var string Request URL
     */
    protected $url = null;

    /**
     * @var array Cookies to send with the request
     */
    protected $cookies = array();

    /**
     * @var harray HTTP headers to send
     */
    protected $headers = array();

    /**
     * @var string Default content type
     */
    protected $defaultContentType = null;

    /**
     * @var string HTTP Content-Type
     */
    protected $contentType = null;

    /**
     * @var string User agent
     */
    protected $userAgent = "cURL (PHP_COUGAR_CLIENT)";

    /**
     * @var string SSL client certificate PEM file
     */
    protected $sslCertFile = null;

    /**
     * @var string SSL client certificate password
     */
    protected $sslCertPassword = null;

    /**
     * @var array URL slash parameters to send
     */
    protected $urlFields = array();

    /**
     * @var array HTTP GET parameters to send
     */
    protected $getFields = array();

    /**
     * @var mixed HTTP POST body content
     */
    protected $body = null;

    /**
     * @var bool Send POST as multi-part
     */
    protected $multiPart = false;

    /**
     * @var string Where the response will be saved
     */
    protected $outputPath = null;

    /**
     * @var bool Whether the call has ben executed
     */
    protected $executed = false;

    /**
     * @var array Information on the request
     */
    protected $requestInfo = null;

    /**
     * @var string The response header
     */
    protected $rawHeader = null;

    /**
     * @var array Array with response headers
     */
    protected $responseHeaders = null;

    /**
     * @var array Maps header names to lowercase
     */
    protected $responseHeaderMap = null;

    /**
     * @var string The full HTTP response
     */
    protected $response = null;

    /**
     * @var \Cougar\Security\iHttpCredentialProvider Credential provider
     */
    protected $credentialProvider;

    /**
     * Initializes the cURL session; called in construct or wakeup event
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     */
    protected function curlInit()
    {
        # Initialize the curl session
        $this->curl = curl_init();

        # See if we have any errors
        if ($this->curl === false)
        {
            throw new Exception("Unable to initialize cURL session");
        }

        # Set the cURL options we will use
        curl_setopt($this->curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_HEADER, true);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($this->curl, CURLOPT_USERAGENT, (string) $this->userAgent);
        
        # Set the proper SSL option
        if (! $this->ssl)
        {
            curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
        }
        else if (is_dir($this->ssl))
        {
            curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($this->curl, CURLOPT_CAPATH, $this->ssl);
        }
        else if (file_exists ($this->ssl))
        {
            curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($this->curl, CURLOPT_CAINFO, $this->ssl);
        }
        else
        {
            throw new Exception("Invalid SSL path: " . $this->ssl . ". " .
                "Please specify a valid path to a directory where SSL " .
                "certificates are stored (like /etc/ssl) or to a PEM file " .
                "with the CA cerfiticates. You may also set the SSL value to " .
                "false to skip SSL peer verification (not recommended)");
        }
        
        # Set the client SSL certificate, if we have one
        if ($this->sslCertFile)
        {
            curl_setopt($this->curl, CURLOPT_SSLCERT, $this->sslCertFile);
        }
        if ($this->sslCertPassword)
        {
            curl_setopt($this->curl, CURLOPT_SSLCERTPASSWD,
                $this->sslCertPassword);
        }
    }
}
?>
