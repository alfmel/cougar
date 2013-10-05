<?php

namespace Cougar\Security;

/**
 * The AuthenticationProvider interface defines what an authentication provider
 * must provide in order to interoperate within the security framework. This
 * interface is very simple: it must provide the authenticate() method call.
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
interface iAuthenticationProvider
{
    /**
     * Authenticates the client. If authentication is successful, the method
     * will return an object that implements the iIdentity object. If
     * authentication fails, the method should return a null.
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
    public function authenticate();
}
?>
