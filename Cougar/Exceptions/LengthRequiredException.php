<?php

namespace Cougar\Exceptions;

/**
 * Length must be specified.
 * 
 * HTTP Status Code: 411
 * 
 * @package Cougar
 * @license MIT
 * @copyright 2014 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class LengthRequiredException extends Exception
{
    protected $httpStatusCode = 411;
}
