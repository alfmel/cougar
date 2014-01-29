<?php

namespace Cougar\Exceptions;

/**
 * Specifies that a request took too long to respond.
 * 
 * HTTP Status Code: 408
 * 
 * @package Cougar
 * @license MIT
 * @copyright 2014 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class TimeoutException extends Exception
{
    protected $httpStatusCode = 408;
}
