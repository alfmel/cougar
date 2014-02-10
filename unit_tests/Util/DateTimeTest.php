<?php

namespace Cougar\UnitTests\Util;

use Cougar\Util\DateTime;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2013-09-03 at 13:39:48.
 */
class DateTimeTest extends \PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass()
    {
        require_once(__DIR__ . "/../../cougar.php");
    }

    /**
     * @covers Cougar\Util\DateTime::__toString
     */
    public function test__toStringDefault() {
        $object = new DateTime();
        $this->assertEquals(date(DateTime::$defaultDateTimeFormat),
            (string) $object);
    }

    /**
     * @covers Cougar\Util\DateTime::__toString
     */
    public function test__toStringDateTime() {
        $object = new DateTime();
        $object->format = "DateTime";
        $this->assertEquals(date(DateTime::$defaultDateTimeFormat),
            (string) $object);
    }

    /**
     * @covers Cougar\Util\DateTime::__toString
     */
    public function test__toStringDate() {
        $object = new DateTime();
        $object->format = "Date";
        $this->assertEquals(date(DateTime::$defaultDateFormat),
            (string) $object);
    }

    /**
     * @covers Cougar\Util\DateTime::__toString
     */
    public function test__toStringTime() {
        $object = new DateTime();
        $object->format = "Time";
        $this->assertEquals(date(DateTime::$defaultTimeFormat),
            (string) $object);
    }

    /**
     * @covers Cougar\Util\DateTime::__toString
     */
    public function test__toStringCustom() {
        $format = "Y-m-d H:i:s";
        $object = new DateTime();
        $object->format = $format;
        $this->assertEquals(date($format), (string) $object);
    }
    
    /**
     * @covers Cougar\Util\DateTime::__toString
     */
    public function testObjectToJson() {
        $object = new DateTimeExportUnitTest();
        $object->dateTimeProperty = new DateTime();
        $this->assertEquals('{"dateTimeProperty":"' . 
                date(DateTime::$defaultDateTimeFormat) . '"}',
            json_encode($object));
    }
}

class DateTimeExportUnitTest
{
    public $dateTimeProperty;
}
