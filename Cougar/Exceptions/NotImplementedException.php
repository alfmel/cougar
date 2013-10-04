<?php

namespace Cougar\Exceptions;

/**
 * Specifies that the requested functionality has not been implemented
 * 
 * HTTP Status Code: 501
 * 
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class NotImplementedException extends Exception
{
	protected $httpStatusCode = 501;
}
?>
