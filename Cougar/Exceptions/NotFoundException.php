<?php

namespace Cougar\Exceptions;

/**
 * Specifies that the resource was not found.
 * 
 * HTTP Status Code: 404
 * 
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class NotFoundException extends Exception
{
	protected $httpStatusCode = 404;
}
?>
