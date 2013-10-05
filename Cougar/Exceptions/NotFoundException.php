<?php

namespace Cougar\Exceptions;

/**
 * Specifies that the resource was not found.
 * 
 * HTTP Status Code: 404
 * 
 * @package Cougar
 * @license MIT
 * @copyright 2013 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class NotFoundException extends Exception
{
    protected $httpStatusCode = 404;
}
?>
