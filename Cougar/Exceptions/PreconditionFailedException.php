<?php

namespace Cougar\Exceptions;

/**
 * A required precondition was not met.
 * 
 * HTTP Status Code: 412
 * 
 * @package Cougar
 * @license MIT
 * @copyright 2014 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class PreconditionFailedException extends Exception
{
    protected $httpStatusCode = 412;
}
