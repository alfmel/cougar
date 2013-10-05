<?php

namespace Cougar\Exceptions;

/**
 * Extends the standard exception by adding an HTTP Status Code and an
 * getter method. When this exception is used with the RestService, the response
 * handler will know which HTTP Status to send based on the given value.
 * 
 * HTTP Status Code: 500
 * 
 * @package Cougar
 * @license MIT
 * @copyright 2013 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class Exception extends \Exception
{
    protected $httpStatusCode = 500;
    
    public function getHttpStatusCode()
    {
        return $this->httpStatusCode;
    }
}
?>
