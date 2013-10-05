<?php

namespace Cougar\UnitTests\Model;

require_once(__DIR__ . "/../../../cougar.php");

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2013-08-05 at 07:50:44.
 */
class RealStructTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers Cougar\Model\tRealStruct::__get
     */
    public function testGet() {
        $object = new RealStructUnitTest();
        $this->assertEquals("Value A", $object->propertyA);
        $this->assertEquals("Value B", $object->propertyB);
    }

    /**
     * @covers Cougar\Model\RealStruct::__get
     * @expectedException \Cougar\Exceptions\Exception
     */
    public function testGetError() {
        $object = new RealStructUnitTest();
        $object->propertyC;
    }

    /**
     * @covers Cougar\Model\RealStruct::__set
     */
    public function testSet() {
        $object = new RealStructUnitTest();
        $object->propertyA = "Some other Value A";
        $object->propertyB = "Some other Value B";
        $this->assertEquals("Some other Value A", $object->propertyA);
        $this->assertEquals("Some other Value B", $object->propertyB);
    }
    
    /**
     * @covers Cougar\Model\RealStruct::__set
     * @expectedException \Cougar\Exceptions\Exception
     */
    public function testSetError() {
        $object = new RealStructUnitTest();
        $object->propertyC = "Some new Value C";
    }

    /**
     * @covers Cougar\Model\RealStruct::__unset
     * @expectedException \Cougar\Exceptions\Exception
     */
    public function testUnsetValidProperty()
    {
        $object = new RealStructUnitTest();
        unset($object->propertyA);
    }

    /**
     * @covers Cougar\Model\RealStruct__unset
     * @expectedException \Cougar\Exceptions\Exception
     */
    public function testUnsetInvalidProperty()
    {
        $object = new RealStructUnitTest();
        unset($object->propertyC);
    }
    
    /**
     * @covers Cougar\Model\RealStruct::__isset
     */
    public function testIsset()
    {
        $object = new RealStructUnitTest();
        $this->assertEquals(true, isset($object->propertyA));
        $this->assertEquals(true, isset($object->propertyB));
        $this->assertEquals(false, isset($object->propertyC));
    }

    /**
     * @covers Cougar\Model\tRealStruct::__get
     */
    public function testTraitGet() {
        $object = new RealStructUnitTestViaTrait();
        $this->assertEquals("Value A", $object->propertyA);
        $this->assertEquals("Value B", $object->propertyB);
    }

    /**
     * @covers Cougar\Model\tRealStruct::__get
     * @expectedException \Cougar\Exceptions\Exception
     */
    public function testTraitGetError() {
        $object = new RealStructUnitTestViaTrait();
        $object->propertyC;
    }

    /**
     * @covers Cougar\Model\tRealStruct::__set
     */
    public function testTraitSet() {
        $object = new RealStructUnitTestViaTrait();
        $object->propertyA = "Some other Value A";
        $object->propertyB = "Some other Value B";
        $this->assertEquals("Some other Value A", $object->propertyA);
        $this->assertEquals("Some other Value B", $object->propertyB);
    }

    /**
     * @covers Cougar\Model\tRealStruct::__set
     * @expectedException \Cougar\Exceptions\Exception
     */
    public function testTraitSetError() {
        $object = new RealStructUnitTestViaTrait();
        $object->propertyC = "Some new Value C";
    }
    
    /**
     * @covers Cougar\Model\RealStruct__unset
     * @expectedException \Cougar\Exceptions\Exception
     */
    public function testTraitUnsetValidProperty()
    {
        $object = new RealStructUnitTestViaTrait();
        unset($object->propertyA);
    }

    /**
     * @covers Cougar\Model\RealStruct__unset
     * @expectedException \Cougar\Exceptions\Exception
     */
    public function testTraitUnsetInvalidProperty()
    {
        $object = new RealStructUnitTestViaTrait();
        unset($object->propertyC);
    }
    
    /**
     * @covers Cougar\Model\tRealStruct::__isset
     */
    public function testTraitIsset()
    {
        $object = new RealStructUnitTestViaTrait();
        $this->assertEquals(true, isset($object->propertyA));
        $this->assertEquals(true, isset($object->propertyB));
        $this->assertEquals(false, isset($object->propertyC));
    }
}


/* Implementations of class and struct to be tested */

class RealStructUnitTestViaTrait
implements \Cougar\Model\iStruct, \Iterator, \JsonSerializable
{
    use \Cougar\Model\tRealStruct;
    
    public $propertyA = "Value A";
    public $propertyB = "Value B";
}

class RealStructUnitTest extends \Cougar\Model\RealStruct
{
    public $propertyA = "Value A";
    public $propertyB = "Value B";
}
?>
