<?php

namespace Cougar\Exceptions;

/**
 * Specifies that the requested functionality has not been implemented
 * 
 * HTTP Status Code: 501
 * 
 * @package Cougar
 * @license MIT
 * @copyright 2013 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class NotImplementedException extends Exception
{
    protected $httpStatusCode = 501;
}
?>
