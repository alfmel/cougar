<?php

namespace Cougar\UnitTests\Model;

require_once(__DIR__ . "/../../../cougar.php");

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2013-08-05 at 07:50:44.
 */
class StructTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers Cougar\Model\tStruct::__get
     */
    public function testGet() {
        $object = new StructUnitTest();
        $this->assertEquals("Value A", $object->propertyA);
        $this->assertEquals("Value B", $object->propertyB);
    }

    /**
     * @covers Cougar\Model\Struct::__get
     * @expectedException \Cougar\Exceptions\Exception
     */
    public function testGetError() {
        $object = new StructUnitTest();
        $object->propertyC;
    }

    /**
     * @covers Cougar\Model\tStruct::__set
     */
    public function testSet() {
        $object = new StructUnitTest();
        $object->propertyA = "Some other Value A";
        $object->propertyB = "Some other Value B";
        $this->assertEquals("Some other Value A", $object->propertyA);
        $this->assertEquals("Some other Value B", $object->propertyB);
    }

    /**
     * @covers Cougar\Model\tStruct::__set
     * @expectedException \Cougar\Exceptions\Exception
     */
    public function testSetError() {
        $object = new StructUnitTest();
        $object->propertyC = "Some new Value C";
    }

    /**
     * @covers Cougar\Model\tStruct::__get
     */
    public function testTraitGet() {
        $object = new StructUnitTestViaTrait();
        $this->assertEquals("Value A", $object->propertyA);
        $this->assertEquals("Value B", $object->propertyB);
    }

    /**
     * @covers Cougar\Model\tStruct::__get
     * @expectedException \Cougar\Exceptions\Exception
     */
    public function testTraitGetError() {
        $object = new StructUnitTestViaTrait();
        $object->propertyC;
    }

    /**
     * @covers Cougar\Model\tStruct::__set
     */
    public function testTraitSet() {
        $object = new StructUnitTestViaTrait();
        $object->propertyA = "Some other Value A";
        $object->propertyB = "Some other Value B";
        $this->assertEquals("Some other Value A", $object->propertyA);
        $this->assertEquals("Some other Value B", $object->propertyB);
    }

    /**
     * @covers Cougar\Model\tStruct::__set
     * @expectedException \Cougar\Exceptions\Exception
     */
    public function testTraitSetError() {
        $object = new StructUnitTestViaTrait();
        $object->propertyC = "Some new Value C";
    }
}


/* Implementations of class and struct to be tested */

class StructUnitTestViaTrait implements \Cougar\Model\iStruct
{
    use \Cougar\Model\tStruct;
    
    public $propertyA = "Value A";
    public $propertyB = "Value B";
}

class StructUnitTest extends \Cougar\Model\Struct
{
    public $propertyA = "Value A";
    public $propertyB = "Value B";
}
?>
