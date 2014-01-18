<?php

namespace Cougar\Security;

use phpCAS;

# Initialize the framework
require_once("cougar.php");

/**
 * Attempts to authenticate the current user using CAS if a CAS session exists.
 * This provider will not force CAS authentication.
 *
 * This provider only works with the official phpCAS client from JASIG.
 *
 * Because the phpCAS client uses static classes, there are no references to a
 * CAS object that are required. However, you *must* set up the phpCAS client
 * and initialize it via the phpCAS::client() method *before* you attempt to
 * authenticate.
 *
 * @history
 * 2013.09.30:
 *   (AT)  Initial release
 * 2013.11.21:
 *   (AT)  Add ability to pass setup() callable during construction so that
 *         setup can occur only when authenticate() is called
 * 2014.01.17:
 *   (AT)  Added previous_session parameter to constructor
 *   (AT)  Documentation updates
 *
 * @version 2014.01.17
 * @package Cougar
 * @license MIT
 *
 * @copyright 2013 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class CasAuthenticationProvider implements iAuthenticationProvider
{
    /**
     * Accepts a callable to set up the CAS client before authenticating. By
     * using a call back we don't have to set up CAS unless we absolutely have
     * to.
     *
     * Setting the previous_session parameter to true will only authenticate if
     * there is evidence of previous CAS setup in the PHP session.
     *
     * @history:
     * 2013.11.21:
     *   (AT)  Initial implementation
     * 2014.01.17:
     *   (AT)  Added previous_session parameter
     *
     * @param callable $setup_callable CAS setup function
     * @param boolean $previous_session_only Authenticate only if previous session exists
     */
    public function __construct(callable $setup_callable = null,
        $previous_session_only = false)
    {
        // Store the callable
        if ($setup_callable)
        {
            $this->setup = $setup_callable;
            $this->callSetup = true;
        }

        // Store the previous session only flag value
        $this->previousSessionOnly = (bool) $previous_session_only;
    }


    /***************************************************************************
     * PUBLIC PROPERTIES AND METHODS
     **************************************************************************/
    
    /**
     * Authenticates the client. If authentication is successful, the method
     * will return an object that implements the iIdentity object. The primary
     * identity (id) will be the identity returned from the getUser() call.
     * Additional attributes (of available) will be passed as the identity
     * object's attributes. If authentication fails, the method will return a
     * null.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     * 2013.11.21:
     *   (AT)  Call setup function if one exists
     *
     * @version 2013.11.21
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @return iIdentity Identity object
     */
    public function authenticate()
    {
        // See if we need to call the setup function
        if ($this->callSetup)
        {
            $setup = $this->setup;
            $setup();
            $this->callSetup = false;
        }

        // See if a session must exist
        if ($this->previousSessionOnly)
        {
            $session_exists = false;

            // See if we have a session
            if (session_status() == PHP_SESSION_ACTIVE)
            {
                if (array_key_exists("phpCAS", $_SESSION))
                {
                    if (array_key_exists("user", $_SESSION["phpCAS"]))
                    {
                        $session_exists = true;
                    }
                }
            }

            // If we don't have a session consider it as no authentication
            if (! $session_exists)
            {
                return null;
            }
        }

        // Check for CAS authentication
        phpCAS::setCacheTimesForAuthRecheck(0);
        if (phpCAS::checkAuthentication())
        {
            # Get the ID
            $id = phpCAS::getUser();

            # Get the attributes
            $attributes = phpCAS::getAttributes();

            # Return a new identity
            return new Identity($id, $attributes);
        }
        else
        {
            return null;
        }
    }

    /***************************************************************************
     * PROTECTED PROPERTIES AND METHODS
     **************************************************************************/

    /**
     * @var callable Setup function
     */
    protected $setup;

    /**
     * @var bool Whether to call the setup function
     */
    protected $callSetup = false;

    /**
     * @var bool Whether we authenticate onlyu if we have a previous session
     */
    protected $previousSessionOnly = false;
}
?>
