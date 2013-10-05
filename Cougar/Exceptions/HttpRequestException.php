<?php

namespace Cougar\Exceptions;

/**
 * Extends the Cougar\Exceptions\Exception class to accept the HTTP response.
 * 
 * HTTP Status Code: Remote HTTP request status code
 * 
 * @package Cougar
 * @license MIT
 * @copyright 2013 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class HttpRequestException extends Exception
{
    protected $httpResponse;

    public function __construct($message, $http_code, $http_response,
        $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->httpStatusCode = (int) $http_code;
        $this->httpResponse = $http_response;
    }

    public function getHttpResponse()
    {
        return $this->httpResponse;
    }
}
