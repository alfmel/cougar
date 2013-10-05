<?php

namespace Cougar\Exceptions;

/**
 * Specifies that a wrong HTTP method has been used (no real mapping to
 * non-service class)
 * 
 * HTTP Status Code: 405
 * 
 * @package Cougar
 * @license MIT
 * @copyright 2013 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class MethodNotAllowedException extends Exception
{
    protected $httpStatusCode = 405;
}
?>
