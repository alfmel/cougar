<?php

namespace Cougar\RestClient;

/**
 * The REST Client extends the CurlWrapper class to provide an easy-to-use REST
 * client. The client support GET, POST, PUT and DELETE operations.
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
interface iRestClient
{
    /**
     * Initializes the client.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param string $response_type
     *   The response type to expect - one of RAW, XML, JSON, PHP or null (raw)
     * @param string $content_type
     *   The content type being sent
     * @param int $timeout (optional)
     * @param string $ssl (optional - See CurlWrapper constructor for options)
     */
    public function __construct($response_type = null, $content_type = null,
        $timeout = null, $ssl = null);
    
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
     */
    public function get($url, array $url_fields = null,
        array $get_fields = null, $body = null, $content_type = null);

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
     */
    public function post($url, array $url_fields = null,
        array $get_fields = null, $body = null, $content_type = null);
    
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
        array $get_fields = null, $body = null, $content_type = null);
    
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
        array $get_fields = null, $body = null, $content_type = null);
    
    /**
     * Returns the HTTP status of the last request
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @return int HTTP status code
     */
    public function getStatus();

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
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param \Cougar\Security\iClientAuthenticationProvider $authentication_provider
     *   Authentication provider to add
     */
    public function addClientAuthenticationProvider(
        \Cougar\Security\iClientAuthenticationProvider $authentication_provider);
}
?>
