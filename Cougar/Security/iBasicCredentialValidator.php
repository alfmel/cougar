<?php

namespace Cougar\Security;

/**
 * The Basic Credential Validator provides the foundation for creating a
 * username and password validator for HTTP Basic authentication.
 *
 * To use Cougar's built-in HTTP Basic Authentication Provider, you must supply
 * an object that implements this interface. Your implementation should
 * validate that the the given username and password are legitimate and return
 * the proper Identity object for the user.
 *
 * @history
 * 2014.03.24:
 *   (AT)  Initial definition
 *
 * @version 2014.03.24
 * @package Cougar
 * @license MIT
 *
 * @copyright 2014 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
interface iBasicCredentialValidator {

    /**
     * Validates that the provided credentials are valid. For basic, or other
     * plain-text authentication schemes, pass the plain-text username and
     * password and validate that they match.
     *
     * If the credentials are valid, the method must return an Identity object.
     * If the credentials are invalid, it should simply return a null and let
     * the authentication provider(s) throw any necessary exceptions.
     *
     * @history
     * 2014.03.24:
     *   (AT)  Initial release
     *
     * @version 2014.03.24
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string $username Username of identity to validate
     * @param string $password Identity's password in plain text (if available)
     * @return iIdentity Identity object if validation successful;
     *   null otherwise
     */
    public function validate($username, $password);
}
?>
