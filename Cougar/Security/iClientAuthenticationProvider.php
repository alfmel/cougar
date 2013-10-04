<?php

namespace Cougar\Security;

/**
 * The AuthenticationProvider interface defines what a client authentication
 * provider must provide in order to interoperate within the security framework.
 * This interface is very simple: it must provide the provideCredentials()
 * method call.
 * 
 * The constructor is not defined here, since each constructor will require
 * different parameters based on its needs.
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
interface iClientAuthenticationProvider
{
	/**
	 * Provides credentials, signatures, tokens or whatever the transport may
     * need to validate the client request.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 */
	public function provideCredentials();
}
?>
