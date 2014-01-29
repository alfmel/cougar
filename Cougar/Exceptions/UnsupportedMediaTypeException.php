<?php

namespace Cougar\Exceptions;

/**
 *
 * 
 * HTTP Status Code: 415
 * 
 * @package Cougar
 * @license MIT
 * @copyright 2014 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class UnsupportedMediaTypeException extends Exception
{
    protected $httpStatusCode = 415;
}
