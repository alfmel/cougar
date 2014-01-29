<?php

namespace Cougar\Exceptions;

/**
 * Specifies a conflict with the current state of a resource.
 * 
 * HTTP Status Code: 409
 * 
 * @package Cougar
 * @license MIT
 * @copyright 2014 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class ConflictException extends Exception
{
    protected $httpStatusCode = 409;
}
