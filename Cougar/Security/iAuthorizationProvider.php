<?php

namespace Cougar\Security;

/**
 * The Authorization Provider interface defines how providers will speak to the
 * Security module.
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
interface iAuthorizationProvider
{
    /**
     * The authorize method asks the authorization provider to authorize the
     * given identity with the given authorization query.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param iIdentity $identity
     *   Identity object
     * @param mixed $query
     *   Authorization query (query depends on provider)
     * @return mixed Authorization response (response depends on provider)
     */
    public function authorize(iIdentity $identity, $query);
}
?>
