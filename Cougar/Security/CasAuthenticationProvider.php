<?php

namespace Cougar\Security;

use phpCAS;

# Initialize the framework
require_once("cougar.php");

/**
 * Attempts to authenticate the current user using CAS if a CAS session exists.
 * This provider will not force CAS authentication.
 *
 * This provider only works with the official phpCAS client from JSIG.
 *
 * Because the phpCAS client uses static classes, there are no references to a
 * CAS object that are required. However, you *must* set up the phpCAS client
 * and initialize it via the phpCAS::client() method *before* you attempt to
 * authenticate.
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
class CasAuthenticationProvider implements iAuthenticationProvider
{
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
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @return iIdentity Identity object
	 */
	public function authenticate()
	{
		# Check for CAS authentication
		\phpCAS::setCacheTimesForAuthRecheck(0);
		if (\phpCAS::checkAuthentication())
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
}
?>
