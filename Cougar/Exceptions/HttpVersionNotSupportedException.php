<?php

namespace Cougar\Exceptions;

/**
 * The requested HTTP version is not supported by the server.
 * 
 * HTTP Status Code: 505
 * 
 * @package Cougar
 * @license MIT
 * @copyright 2014 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class HttpVersionNotSupportedException extends Exception
{
    protected $httpStatusCode = 505;
}
?>
