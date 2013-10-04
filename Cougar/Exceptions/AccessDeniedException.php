<?php

namespace Cougar\Exceptions;

/**
 * Specifies that the identity does not have access to the resource.
 * 
 * HTTP Status Code: 403
 * 
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class AccessDeniedException extends Exception
{
	protected $httpStatusCode = 403;
}
?>
