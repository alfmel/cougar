<?php

namespace Cougar\Exceptions;

/**
 * The specified range is not valid.
 * 
 * HTTP Status Code: 416
 * 
 * @package Cougar
 * @license MIT
 * @copyright 2014 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class RequestedRangeNotSatisfiableException extends Exception
{
    protected $httpStatusCode = 416;
}
