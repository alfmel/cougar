<?php

namespace Cougar\Exceptions;

/**
 * Request failure because of a bad gateway.
 * 
 * HTTP Status Code: 502
 * 
 * @package Cougar
 * @license MIT
 * @copyright 2014 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class BadGatewayException extends Exception
{
    protected $httpStatusCode = 502;
}
?>
