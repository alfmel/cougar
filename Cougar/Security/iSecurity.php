<?php

namespace Cougar\Security;

/**
 * The Security interface defines the Security class, which handles the client
 * identity, authorization providers, and autherization mechanisms.
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
 */
interface iSecurity
{
    /**
     * Returns the identity object
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @return PrincipalIdentity Identity object
     */
    public function getIdentity();
    
    /**
     * Adds the given authentication provider to the list. Providers should be
     * added in the preferred order. If the first provider fails, then it will
     * move to the second, etc. Once a provider has returned an identity, no
     * other identity provides will be used.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param iAuthenticationProvider $authentication_provider
     *   The authentication provider to add
     */
    public function addAuthenticationProvider(
        iAuthenticationProvider $authentication_provider);
    
    /**
     * Authenticates the user against the given identity provider.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @return bool True if user authenticated, false if not
     */
    public function authenticate();
    
    /**
     * Returns true if the identity has been authenticated.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @return bool True if user authenticated, false if not
     */
    public function isAuthenticated();
    
    /**
     * Adds the provided authorization provider to the list. An optional alias
     * may be provided. If no alias is given, the alias will be set by the
     * object's providerAlias property if it exists. Otherwise, the provider's
     * class name (without namespace) will be used. Any providers added with the
     * same alias will replace any existing providers.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param iAuthorizationProvider $authorization_provider
     *   The authorization provider to add
     * @param string $alias
     *   Alias to give the authorization provider
     */
    public function addAuthorizationProvider(
        iAuthorizationProvider $authorization_provider, $alias = null);
    
    /**
     * Authorizes the identity against the given authorization provider.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param string provider Authorization provider to use
     * @param array query Authorization query
     * @return mixed Authorization response
     */
    public function authorize($provider, $query);
}
?>
