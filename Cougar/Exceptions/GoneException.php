<?php

namespace Cougar\Exceptions;

/**
 * Specifies the resource is permanently gone.
 * 
 * HTTP Status Code: 410
 * 
 * @package Cougar
 * @license MIT
 * @copyright 2014 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class GoneException extends Exception
{
    protected $httpStatusCode = 410;
}
