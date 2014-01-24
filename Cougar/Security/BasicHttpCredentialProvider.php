<?php

namespace Cougar\Security;

use Cougar\Util\StringObfuscator;

// Initialize the framework
require_once("cougar.php");

/**
 * Provides HTTP Basic Authentication for client HTTP requests.
 *
 * @history
 * 2014.01.23:
 *   (AT)  Initial release
 *
 * @version 2014.01.23
 * @package Cougar
 * @license MIT
 *
 * @copyright 2014 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class BasicHttpCredentialProvider implements iHttpCredentialProvider
{
    /**
     * Stores the username and password to use in Basic HTTP Authentication.
     * The username and/or password may be provided as obfuscated strings.
     *
     * @history:
     * 2014.01.23:
     *   (AT)  Initial implementation
     *
     * @version 2014.01.23
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string $username Username
     * @param string $password Password
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }


    /***************************************************************************
     * PUBLIC PROPERTIES AND METHODS
     **************************************************************************/

    /**
     * Sets the username and password. If a previous username and/or password
     * was set, it will override them with the newly provided values.
     *
     * @history
     * 2014.01.23:
     *   (AT)  Initial release
     *
     * @version 2014.01.23
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string $username Username
     * @param string $password Password
     */
    public function setCredentials($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Adds a Basic authorization header to the request. The header consists of
     * the keyword "Basic" followed by the username and password, separated by
     * a colon, and encoded as base64.
     *
     * @history
     * 2014.01.23:
     *   (AT)  Initial release
     *
     * @version 2014.01.23
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
     */
    public function addCredentials(&$url, array &$headers, array &$cookies,
        &$body)
    {
        // Add the authorization header
        $headers["Authorization"] = "Basic " . base64_encode(
            StringObfuscator::decode($this->username) . ":" .
            StringObfuscator::decode($this->password));
    }


    /***************************************************************************
     * PROTECTED PROPERTIES AND METHODS
     **************************************************************************/

    /**
     * @var string Username
     */
    protected $username;

    /**
     * @var string Password
     */
    protected $password;
}
?>
