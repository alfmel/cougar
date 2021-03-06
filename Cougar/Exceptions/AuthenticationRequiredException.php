<?php

namespace Cougar\Exceptions;

/**
 * Specifies that authentication is required. This means the default calling
 * identity is not enough.
 * 
 * HTTP Status Code: 401
 * 
 * @package Cougar
 * @license MIT
 * @copyright 2013 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class AuthenticationRequiredException extends Exception
{
    protected $httpStatusCode = 401;
}
?>
