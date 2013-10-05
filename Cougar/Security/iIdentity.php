<?php

namespace Cougar\Security;

/**
 * Defines the iIdentity interface which identifies a client identity (or user).
 * 
 * The identity concept is simple: the record will have an ID which corresponds
 * to the primary ID of the user. It will also have a set of attributes (such
 * as name, secondary ID, etc.) which can be queried at any time. The attributes
 * are defined by the authentication provider, and may vary from provider to
 * provider.
 * 
 * Additionally, the identity may define a parent identity, which can be used
 * to create chained credentials. Parent identities can also have a parent.
 * There is no limit on how long the identity chain can be.
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
interface iIdentity
{
    /**
     * Initializes the identity. The parameters are purposefully optional so
     * that a NULL identity may be created.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param string $id The primary ID of the identity
     * @param array $attributes The identity attributes (associative array)
     * @param iIdentity $parent The parent identity (optional)
     */
    public function __construct($id = null, $attributes = null,
        iIdentity $parent = null);

    /**
     * Returns true if the identity has a parent.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @return bool True if identity has parent, false otherwise
     */
    public function hasParent();
    
    /**
     * Returns the root identity. The root identity is the last identity up the
     * identity chain.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @return iIdentity Root identity
     */
    public function root();
    
    /**
     * Returns all the identity attributes
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @return array Identity attributes
     */
    public function getAttributes();
}
?>
