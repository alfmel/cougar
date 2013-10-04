<?php

namespace Cougar\Exceptions;

/**
 * Specifies that a dependent service (such as a database) was not available
 * (if it timed out, for example)
 * 
 * HTTP Status Code: 503
 * 
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class ServiceUnavailableException extends Exception
{
	protected $httpStatusCode = 503;
}
?>
