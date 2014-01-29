<?php

namespace Cougar\Exceptions;

/**
 * The expectation in the Expect request header could not be met.
 * 
 * HTTP Status Code: 417
 * 
 * @package Cougar
 * @license MIT
 * @copyright 2014 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class ExpectationFailed extends Exception
{
    protected $httpStatusCode = 417;
}
