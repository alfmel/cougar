<?php

namespace Cougar\UnitTests;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2012-11-06 at 16:26:53.
 */
class LoadFrameworkTests extends \PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass()
    {
        require_once(__DIR__ . "/../cougar.php");
    }

    /**
     * Make sure autoload works
     */
    public function testAutoload()
    {
        # Get a value from a known class
        $value = \Cougar\Autoload\FlexAutoload::$cacheTime;
        $this->assertNotNull($value);
    }

    /**
     * Make sure error handling works
     *
     * @expectedException \ErrorException
     */
    public function testErrorHandling()
    {
        $fh = fopen("/path/to/some/non/existent/file", "r");
        fclose($fh);
        $this->fail("Expected error was raised");
    }
}
