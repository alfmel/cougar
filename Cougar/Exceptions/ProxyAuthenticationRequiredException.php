<?php

namespace Cougar\Exceptions;

/**
 * Specifies that authentication to an intermediary proxy is required.
 *
 * HTTP Status Code: 407
 * 
 * @package Cougar
 * @license MIT
 * @copyright 2014 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class ProxyAuthenticationRequiredException extends Exception
{
    protected $httpStatusCode = 407;
}
?>
