<?php

namespace Cougar\Exceptions;

/**
 * Specifies that a bad request has been made, for example, with bad parameters.
 * In the case of a web service, this also means the URI was not mapped
 * to a known service.
 * 
 * HTTP Status Code: 400
 * 
 * @package Cougar
 * @license MIT
 * @copyright 2013 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class BadRequestException extends Exception
{
    protected $httpStatusCode = 400;
}
