<?php

namespace Cougar\Exceptions;

/**
 * Specifies that a required configuration file was not found
 * 
 * HTTP Status Code: 503
 * 
 * @package Cougar
 * @license MIT
 * @copyright 2013 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class ConfigurationFileNotFoundException extends ServiceUnavailableException
{
    protected $httpStatusCode = 503;
}
?>
