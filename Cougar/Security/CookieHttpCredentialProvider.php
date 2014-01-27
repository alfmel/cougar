<?php

namespace Cougar\Security;

use Cougar\Util\StringObfuscator;

// Initialize the framework
require_once("cougar.php");

/**
 * Provides HTTP Authentication via a session cookie. Note that this class only
 * adds the cookie to the request; it does not establish the session. You must
 * establish the session beforehand.
 *
 * @history
 * 2014.01.24:
 *   (AT)  Initial release
 * 2014.01.27:
 *   (AT)  Added $content_type parameter to match interface
 *
 * @version 2014.01.27
 * @package Cougar
 * @license MIT
 *
 * @copyright 2014 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class CookieHttpCredentialProvider implements iHttpCredentialProvider
{
    /**
     * Stores the cookies that will be sent out with the request.
     *
     * @history:
     * 2014.01.24:
     *   (AT)  Initial implementation
     *
     * @version 2014.01.24
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param array $cookies
     *   Assoc. array with cookies (key/value pair)
     */
    public function __construct(array $cookies)
    {
        $this->cookies = $cookies;
    }


    /***************************************************************************
     * PUBLIC PROPERTIES AND METHODS
     **************************************************************************/

    /**
     * Sets up a new set of cookies for credentials. If previous cookies were
     * defined, they will be erased and replaced with this new set.
     *
     * @history
     * 2014.01.24:
     *   (AT)  Initial release
     *
     * @version 2014.01.24
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param array $cookies
     *   Assoc. array with cookies (key/value pair)
     */
    public function setCookies(array $cookies)
    {
        $this->cookies = $cookies;
    }

    /**
     * Adds a Basic authorization header to the request. The header consists of
     * the keyword "Basic" followed by the username and password, separated by
     * a colon, and encoded as base64.
     *
     * @history
     * 2014.01.24:
     *   (AT)  Initial release
     * 2014.01.27:
     *   (AT)  Added $content_type parameter to match interface
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
        &$body, &$content_type = null)
    {
        // Add the session cookies
        $cookies = array_merge($cookies, $this->cookies);
    }


    /***************************************************************************
     * PROTECTED PROPERTIES AND METHODS
     **************************************************************************/

    /**
     * @var array Cookies as key/value pairs
     */
    protected $cookies;
}
?>
