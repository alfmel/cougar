<?php

namespace Cougar\Exceptions;

/**
 * Specifies that the call could not generate an acceptable response based on
 * the requested document type (no real mapping to non-service class)
 * 
 * HTTP Status Code: 406
 * 
 * @package Cougar
 * @license MIT
 * @copyright 2013 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class NotAcceptableException extends Exception
{
    protected $httpStatusCode = 406;
}
?>
