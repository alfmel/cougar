<?php

namespace Cougar\Security;

/**
 * An HTTP Credential Provider allows you to provide authentication credentials
 * for client HTTP requests. The provider's addCredentials() method will be
 * passed references to the request's URL, headers, GET parameters and body. The
 * authentication can then add custom headers, GET parameters or even modify the
 * body to provide authentication credentials, whether it be a simple username
 * and password, or a complex cryptographic signature.
 *
 * The interface only defines the addCredentials() method which will be called
 * by the RestClient every time a request is made. Actual implementations can
 * define their own constructor, properties and auxiliary methods as needed.
 *
 * @history
 * 2014.01.24:
 *   (AT)  Initial release
 * 2014.01.27:
 *   (AT)  Add content type to the request being passed
 *
 * @version 2014.01.27
 * @package Cougar
 * @license MIT
 *
 * @copyright 2014 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
interface iHttpCredentialProvider
{
    /**
     * Adds the necessary authentication credentials to an HTTP request.
     *
     * All variables are passed by reference and may be modified as necessary.
     *
     * The url parameter contains the full URL of the request without GET fields
     * (the part after the ?).
     *
     * The headers parameter contains an associative array of header names and
     * values. For example, to add an HTTP Basic Authentication header you would
     * add the following element to the array:
     *
     *   $headers["Authorization"] = "Basic ABCdef123==";
     *
     * Cookies are also passed as an associative array. To set a cookie, simply
     * add the cookie name and value to the cookies array:
     *
     *   $cookies["SESSIONID"] = "abc123";
     *
     * If the body parameter is an array, it will contain POST parameters,
     * similar to get_fields. If the body is text, then the raw contents will be
     * passed.
     *
     * @history
     * 2014.01.24:
     *   (AT)  Initial definition
     * 2014.01.27:
     *   (AT)  Added content_type parameter (optional)
     *
     * @version 2014.01.27
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string $url
     *   Request URL
     * @param array $headers
     *   Request headers as key/value pairs
     * @param array $cookies
     *   Request cookies as key/value pairs
     * @param mixed $body
     *   Either an assoc. array of POST parameters or raw body content
     * @param string $content_type
     *   The body's content type
     */
    public function addCredentials(&$url, array &$headers, array &$cookies,
        &$body, &$content_type = null);
}
?>
