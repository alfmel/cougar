<?php

namespace Cougar\Exceptions;

/**
 * An intermediary gateway has timed out.
 * 
 * HTTP Status Code: 504
 * 
 * @package Cougar
 * @license MIT
 * @copyright 2014 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class GatewayTimeoutException extends Exception
{
    protected $httpStatusCode = 504;
}
?>
