<?php

namespace Cougar\Exceptions;

/**
 * Specifies that the call could not generate an acceptable response based on
 * the requested document type (no real mapping to non-service class)
 * 
 * HTTP Status Code: 406
 * 
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class NotAcceptableException extends Exception
{
	protected $httpStatusCode = 406;
}
?>
