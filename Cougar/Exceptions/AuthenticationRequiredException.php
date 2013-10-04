<?php

namespace Cougar\Exceptions;

/**
 * Specifies that authentication is required. This means the default calling
 * identity is not enough.
 * 
 * HTTP Status Code: 401
 * 
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class AuthenticationRequiredException extends Exception
{
	protected $httpStatusCode = 401;
}
?>
