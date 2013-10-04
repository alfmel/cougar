<?php

namespace Cougar\Exceptions;

/**
 * Specifies that a wrong HTTP method has been used (no real mapping to
 * non-service class)
 * 
 * HTTP Status Code: 405
 * 
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class MethodNotAllowedException extends Exception
{
	protected $httpStatusCode = 405;
}
?>
