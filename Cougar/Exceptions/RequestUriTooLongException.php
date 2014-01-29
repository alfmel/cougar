<?php

namespace Cougar\Exceptions;

/**
 * The request UIR is too long.
 * 
 * HTTP Status Code: 414
 * 
 * @package Cougar
 * @license MIT
 * @copyright 2014 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class RequestUriTooLongException extends Exception
{
    protected $httpStatusCode = 414;
}
