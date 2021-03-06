<?php

namespace Cougar\UnitTests\Exceptions;

use Cougar\Exceptions\Errors;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2012-11-06 at 16:26:53.
 */
class ErrorExceptionTest extends \PHPUnit_Framework_TestCase {

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testErrorHandling()
    {
        $fh = fopen("/path/to/some/non/existent/file", "r");
        fclose($fh);
        $this->fail("Expected error was raised");
    }

    /**
     * @covers Cougar\Exceptions\Errors::setErrorHandler
     * @covers Cougar\Exceptions\Errors::exceptionErrorHandler
     * @expectedException \ErrorException
     * @depends testErrorHandling
     */
    public function testSetErrorHandler()
    {
        require_once(__DIR__ . "/../../Cougar/Exceptions/Errors.php");
        Errors::setErrorHandler();
        $fh = fopen("/path/to/some/non/existent/file", "r");
        fclose($fh);
        $this->fail("No exception was thrown");
    }
}
