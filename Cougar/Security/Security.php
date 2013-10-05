<?php

namespace Cougar\Security;

use Cougar\Exceptions\Exception;

# Initialize the framework
require_once("cougar.php");

/**
 * Implements the security context object, which works as follows:
 * 
 * At instantiation, the security object will create a null identity, meaning,
 * no user has been authenticated. Once instantiated, developers need to add
 * appropriate authorization and authentication providers.
 * 
 * Once everything is set up, the developer can ask the security object to
 * authenticate. The authentication identity can be retrieved using the
 * getIdentity() method.
 * 
 * At any time (with or without authentication) developers may pass a query to
 * the authorize() method to determine if the identity is authorized to perform
 * a certain action. The query depends on the provider, so make sure to read the
 * provider's documentation on how the query should be made.
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
class Security implements iSecurity
{
    /**
     * Initializes the security object by creating a null (default) identity.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     */
    public function __construct()
    {
        # Create the null (default) identity
        $this->identity = new Identity();
    }
    
    /**
     * Cleans up the object.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     */
    public function __destruct()
    {
        // Nothing to do for now
    }
    
    
    /***************************************************************************
     * PUBLIC PROPERTIES AND METHODS
     **************************************************************************/
    
    /**
     * Returns the identity.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @return Identity Identity object
     */
    public function getIdentity()
    {
        return $this->identity;
    }
    
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
        iAuthenticationProvider $authentication_provider)
    {
        $this->authenticationProviders[] = $authentication_provider;
    }
    
    /**
     * Authenticates the user against the given identity providres.
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
    public function authenticate()
    {
        # Go through the authentication providers
        foreach($this->authenticationProviders as $provider)
        {
            # Assk the provider to authenticate
            $identity = $provider->authenticate();
            
            # See if got back an identity
            if ($identity instanceof iIdentity)
            {
                # Save the identity
                $this->identity = $identity;
                
                # Mark the identity as authenticated
                $this->authenticated = true;
                
                # Exit the loop
                break;
            }
        }
        
        # Return the result
        return $this->authenticated;
    }
    
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
     * @return bool True if client has authenticated, false if not
     */
    public function isAuthenticated()
    {
        return $this->authenticated;
    }
    
    /**
     * Adds the provided authorization provider to the list. An optional alias
     * may be provided. If no alias is given, the provider's name will be
     * used. Any providers added with the same class name or with the same alias
     * will replace the existing provider.
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
        iAuthorizationProvider $authorization_provider, $alias = null)
    {
        # If we don't have an alias, use the class name
        if (! $alias)
        {
            $alias = get_class($authorization_provider);
        }
        
        # Add the provider to the list
        $this->authorizationProviders[$alias] = $authorization_provider;
    }

    /**
     * Authorizes the identity against the given authorization provider. The
     * format of the query will depend on the provider.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string $provider
     *   Authorization provider to use
     * @param array $query
     *   Authorization query
     * @return mixed Authorization response
     * @throws \Cougar\Exceptions\Exception
     */
    public function authorize($provider, $query)
    {
        # See if we have this provider
        if (array_key_exists($provider, $this->authorizationProviders))
        {
            $provider = $this->authorizationProviders[$provider];
        }
        else
        {
            throw new Exception("Authorization provider has not been added");
        }
        
        # Perform the query and return the results
        return $provider->authorize($this->identity, $query);
    }
    
    
    /***************************************************************************
     * PROTECTED PROPERTIES AND METHODS
     **************************************************************************/
    
    /**
     * @var iIdentity Identity object
     */
    protected $identity = null;
    
    /**
     * @var bool Whether the identity has been authenticated
     */
    protected $authenticated = false;
    
    /**
     * @var array Authentication providers
     */
    protected $authenticationProviders = array();
    
    /**
     * @var array Authorization providers
     */
    protected $authorizationProviders = array();
}
?>
