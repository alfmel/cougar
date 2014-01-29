<?php

namespace Cougar\Exceptions;

/**
 * The request is too large.
 * 
 * HTTP Status Code: 413
 * 
 * @package Cougar
 * @license MIT
 * @copyright 2014 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class RequestEntityTooLargeException extends Exception
{
    protected $httpStatusCode = 413;
}
