<?php

namespace Cougar\Security;

use Cougar\Exceptions\Exception;
use Cougar\RestService\iRestService;
use Cougar\Exceptions\AuthenticationRequiredException;

/**
 * Performs HTTP Basic Authentication via the provided credential validator
 * object.
 *
 * @history
 * 2014.03.24:
 *   (AT)  Initial implementation
 *
 * @version 2014.01.24
 * @package Cougar
 * @license MIT
 *
 * @copyright 2014 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class HttpBasicAuthenticationProvider implements iAuthenticationProvider
{
    /**
     * Accepts the reference to the REST Service object and a credential
     * validator which will validate the credentials when the authenticate()
     * method is called.
     *
     * @history:
     * 2014.03.24:
     *   (AT)  Initial implementation
     *
     * @version 2014.03.24
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param iRestService $rest_service
     *   REST Service object for extracting the Authorization header
     * @param iBasicCredentialValidator $credential_validator
     *   Credential validator object
     */
    public function __construct(iRestService $rest_service,
        iBasicCredentialValidator $credential_validator)
    {
        // Store the reference to the REST service and credential validator
        // objects
        $this->restService = $rest_service;
        $this->credentialValidator = $credential_validator;
    }


    /***************************************************************************
     * PUBLIC PROPERTIES AND METHODS
     **************************************************************************/

    /**
     * Authenticates the client. If authentication is successful, the method
     * will return the client's Identity object. If the credentials are invalid
     * the object will throw an AuthenticationRequiredException (HTTP 401). If
     * the authentication scheme is not Basic, the object will simply return a
     * null.
     *
     * @history:
     * 2014.03.24:
     *   (AT)  Initial implementation
     *
     * @version 2014.03.24
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @throws \Cougar\Exceptions\Exception
     * @throws \Cougar\Exceptions\AuthenticationRequiredException
     * @return iIdentity Identity object
     */
    public function authenticate()
    {
        // Get the authentication information from the REST Service object
        $auth = $this->restService->authorizationHeader();

        // See if the request is using Basic authentication
        if (strtolower($auth["scheme"]) == "basic")
        {
            // Validate the credentials and get the identity
            $identity = $this->credentialValidator->validate($auth["username"],
                $auth["password"]);

            // See if we have an identity
            if ($identity instanceof Identity)
            {
                // Authentication was successful; return the identity
                return $identity;
            }
            else if ($identity)
            {
                // We got something else; throw an Exception
                throw new Exception("Credential validator must return " .
                    "an Identity object");
            }
            else
            {
                // Credential validation failed; return a 401 status code
                throw new AuthenticationRequiredException(
                    "Invalid username or password");
            }
        }
    }


    /***************************************************************************
     * PROTECTED PROPERTIES AND METHODS
     **************************************************************************/

    /**
     * @var \Cougar\RestService\iRestService
     */
    protected $restService;

    /**
     * @var \Cougar\Security\iBasicCredentialValidator
     */
    protected $credentialValidator;
}
?>
