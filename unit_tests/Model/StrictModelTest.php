<?php

namespace Cougar\UnitTests\Model;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2013-01-23 at 12:28:52.
 */
class StrictModelTest extends \PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass()
    {
        require_once(__DIR__ . "/../../cougar.php");
    }

    /**
     * @covers \Cougar\Model\StrictModel::__construct
     * @covers \Cougar\Model\StrictModel::__isset
     * @covers \Cougar\Model\StrictModel::__set
     * @covers \Cougar\Model\StrictModel::__get
     */
    public function testNewObject()
    {
        $object = new StrictModelUnitTest();
        $this->assertNull($object->userId);
        $this->assertNull($object->lastName);
        $this->assertNull($object->firstName);
        $this->assertNull($object->email);
        $this->assertNull($object->phone);
        $this->assertNull($object->birthDate);
        $this->assertTrue($object->active);
    }
    
    /**
     * @covers \Cougar\Model\StrictModel::__construct
     * @covers \Cougar\Model\StrictModel::__isset
     * @covers \Cougar\Model\StrictModel::__set
     * @covers \Cougar\Model\StrictModel::__get
     */
    public function testNewObjectFromExecutionCache()
    {
        $object1 = new StrictModelUnitTest();
        $object2 = new StrictModelUnitTest();
        $this->assertEquals($object1, $object2);
    }
    
    /**
     * @covers \Cougar\Model\StrictModel::__construct
     * @covers \Cougar\Model\StrictModel::__isset
     * @covers \Cougar\Model\StrictModel::__set
     * @covers \Cougar\Model\StrictModel::__get
     */
    public function testNewObjectSetProperties()
    {
        $object = new StrictModelUnitTest();
        $object->userId = "12345";
        $object->lastName = "Cougar";
        $object->firstName = "Cosmo";
        $object->email = "cosmo@byu.edu";
        $object->phone = "555-1212";
        $object->birthDate = "01 JUN 1960";
        
        $this->assertEquals(12345, $object->userId);
        $this->assertEquals("Cougar", $object->lastName);
        $this->assertEquals("Cosmo", $object->firstName);
        $this->assertEquals("cosmo@byu.edu", $object->email);
        $this->assertEquals("555-1212", $object->phone);
        $this->assertInstanceOf("Cougar\\Util\\DateTime", $object->birthDate);
        $this->assertEquals("1960-06-01", (string) $object->birthDate);
        $this->assertTrue($object->active);
    }
    
    /**
     * @covers \Cougar\Model\StrictModel::__construct
     * @covers \Cougar\Model\StrictModel::__isset
     * @covers \Cougar\Model\StrictModel::__set
     * @covers \Cougar\Model\StrictModel::__get
     */
    public function testNewObjectSetPropertiesWithAlias()
    {
        $object = new StrictModelUnitTest();
        $object->userId = "12345";
        $object->lastName = "Cougar";
        $object->firstName = "Cosmo";
        $object->emailAddress = "cosmo@byu.edu";
        $object->phone = "555-1212";
        
        $this->assertEquals(12345, $object->userId);
        $this->assertEquals("Cougar", $object->lastName);
        $this->assertEquals("Cosmo", $object->firstName);
        $this->assertEquals("cosmo@byu.edu", $object->email);
        $this->assertEquals("cosmo@byu.edu", $object->emailAddress);
        $this->assertEquals("555-1212", $object->phone);
        $this->assertTrue($object->active);
    }
    
    /**
     * @covers \Cougar\Model\StrictModel::__construct
     * @covers \Cougar\Model\StrictModel::__isset
     * @covers \Cougar\Model\StrictModel::__set
     * @expectedException \Cougar\Exceptions\Exception
     */
    public function testNewObjectSetInvalidProperty()
    {
        $object = new StrictModelUnitTest();
        $object->badProperty = "junk";
        $this->fail("Expected exception was not thrown");
    }
    
    /**
     * @covers \Cougar\Model\StrictModel::__construct
     * @covers \Cougar\Model\StrictModel::__isset
     * @covers \Cougar\Model\StrictModel::__set
     * @expectedException \Cougar\Exceptions\Exception
     */
    public function testNewObjectBadConstraint()
    {
        $object = new StrictModelUnitTest();
        $object->email = "bad email address";
        $this->fail("Expected exception was not thrown");
    }
    
    /**
     * @covers \Cougar\Model\StrictModel::__construct
     * @covers \Cougar\Model\StrictModel::__isset
     * @covers \Cougar\Model\StrictModel::__set
     * @covers \Cougar\Model\StrictModel::__get
     * @covers \Cougar\Model\StrictModel::toArray
     */
    public function testJsonSerializeWihtoutOptional()
    {
        $object = new StrictModelUnitTest();
        $object->lastName = "Cougar";
        $object->firstName = "Cosmo";
        $object->email = "cosmo@byu.edu";
        
        $this->assertEquals(array(
            "lastName" => "Cougar",
            "firstName" => "Cosmo",
            "email" => "cosmo@byu.edu",
            "birthDate" => null,
            "active" => true),
            json_decode(json_encode($object), true));
    }
    
    /**
     * @covers \Cougar\Model\StrictModel::__construct
     * @covers \Cougar\Model\StrictModel::__isset
     * @covers \Cougar\Model\StrictModel::__set
     * @covers \Cougar\Model\StrictModel::__get
     * @covers \Cougar\Model\StrictModel::toArray
     */
    public function testJsonSerialize()
    {
        $object = new StrictModelUnitTest();
        $object->userId = "12345";
        $object->lastName = "Cougar";
        $object->firstName = "Cosmo";
        $object->email = "cosmo@byu.edu";
        $object->phone = "555-1212";
        $object->birthDate = "01 JUN 1960";
        
        $this->assertEquals(array(
            "userId" => 12345,
            "lastName" => "Cougar",
            "firstName" => "Cosmo",
            "email" => "cosmo@byu.edu",
            "phone" => "555-1212",
            "birthDate" => "1960-06-01",
            "active" => true),
            json_decode(json_encode($object), true));
    }
    
    /**
     * @covers \Cougar\Model\StrictModel::__construct
     * @covers \Cougar\Model\StrictModel::__isset
     * @covers \Cougar\Model\StrictModel::__set
     * @covers \Cougar\Model\StrictModel::__get
     * @covers \Cougar\Model\StrictModel::toArray
     */
    public function testJsonSerializeWithAltView()
    {
        $object = new StrictModelUnitTest();
        $object->__setView("alt");
        $object->userId = "12345";
        $object->lastName = "Cougar";
        $object->firstName = "Cosmo";
        $object->email = "cosmo@byu.edu";
        $object->phone = "555-1212";
        
        $this->assertEquals(array(
            "userId" => 12345,
            "lastName" => "Cougar",
            "firstName" => "Cosmo",
            "emailAddress" => "cosmo@byu.edu",
            "phoneNumber" => "555-1212",
            "birthDate" => null,
            "active" => true),
            json_decode(json_encode($object), true));
    }
    
    /**
     * @covers \Cougar\Model\StrictModel::__construct
     * @covers \Cougar\Model\StrictModel::__isset
     * @covers \Cougar\Model\StrictModel::__set
     * @covers \Cougar\Model\StrictModel::__get
     * @covers \Cougar\Model\StrictModel::toArray
     */
    public function testJsonSerializeWithAliasesWithoutOptional()
    {
        $object = new StrictModelUnitTest();
        $object->__setView("alt");
        $object->lastName = "Cougar";
        $object->firstName = "Cosmo";
        $object->email = "cosmo@byu.edu";
        $object->birthDate = "01 JUN 1960";
        
        $this->assertEquals(array(
            "lastName" => "Cougar",
            "firstName" => "Cosmo",
            "emailAddress" => "cosmo@byu.edu",
            "birthDate" => "1960-06-01",
            "active" => true),
            json_decode(json_encode($object), true));
    }
    
    /**
     * @covers \Cougar\Model\StrictModel::__construct
     * @covers \Cougar\Model\StrictModel::__isset
     * @covers \Cougar\Model\StrictModel::__set
     * @covers \Cougar\Model\StrictModel::__get
     * @covers \Cougar\Model\StrictModel::__toArray
     */
    public function testToArrayWithDefaultValues()
    {
        $object = new StrictModelUnitTest();
        
        $this->assertEquals(array(
            "lastName" => null,
            "firstName" => null,
            "email" => null,
            "birthDate" => null,
            "active" => true), $object->__toArray());
    }
    
    /**
     * @covers \Cougar\Model\StrictModel::__construct
     * @covers \Cougar\Model\StrictModel::__isset
     * @covers \Cougar\Model\StrictModel::__set
     * @covers \Cougar\Model\StrictModel::__get
     * @covers \Cougar\Model\StrictModel::toArray
     */
    public function testToArray()
    {
        $object = new StrictModelUnitTest();
        $object->userId = "12345";
        $object->lastName = "Cougar";
        $object->firstName = "Cosmo";
        $object->email = "cosmo@byu.edu";
        $object->phone = "555-1212";
        $object->birthDate = "01 JUN 1960";
        
        $this->assertEquals(array(
            "userId" => 12345,
            "lastName" => "Cougar",
            "firstName" => "Cosmo",
            "email" => "cosmo@byu.edu",
            "phone" => "555-1212",
            "birthDate" => "1960-06-01",
            "active" => true), $object->__toArray());
    }
    
    /**
     * @covers \Cougar\Model\StrictModel::__construct
     * @covers \Cougar\Model\StrictModel::__isset
     * @covers \Cougar\Model\StrictModel::__set
     * @covers \Cougar\Model\StrictModel::__get
     * @covers \Cougar\Model\StrictModel::toArray
     */
    public function testToArrayWithoutOptional()
    {
        $object = new StrictModelUnitTest();
        $object->lastName = "Cougar";
        $object->firstName = "Cosmo";
        $object->email = "cosmo@byu.edu";
        
        $this->assertEquals(array(
            "lastName" => "Cougar",
            "firstName" => "Cosmo",
            "email" => "cosmo@byu.edu",
            "birthDate" => null,
            "active" => true), $object->__toArray());
    }
    
    /**
     * @covers \Cougar\Model\StrictModel::__construct
     * @covers \Cougar\Model\StrictModel::__isset
     * @covers \Cougar\Model\StrictModel::__set
     * @covers \Cougar\Model\StrictModel::__get
     * @covers \Cougar\Model\StrictModel::toArray
     */
    public function testToArrayWithAliases()
    {
        $object = new StrictModelUnitTest();
        $object->__setView("alt");
        $object->userId = "12345";
        $object->lastName = "Cougar";
        $object->firstName = "Cosmo";
        $object->email = "cosmo@byu.edu";
        $object->phone = "555-1212";
        
        $this->assertEquals(array(
            "userId" => 12345,
            "lastName" => "Cougar",
            "firstName" => "Cosmo",
            "emailAddress" => "cosmo@byu.edu",
            "phoneNumber" => "555-1212",
            "birthDate" => null,
            "active" => true), $object->__toArray());
    }
    
    /**
     * @covers \Cougar\Model\StrictModel::__construct
     * @covers \Cougar\Model\StrictModel::__isset
     * @covers \Cougar\Model\StrictModel::__set
     * @covers \Cougar\Model\StrictModel::__get
     * @covers \Cougar\Model\StrictModel::toArray
     */
    public function testIteratable()
    {
        $object = new StrictModelUnitTest();
        $object->userId = "12345";
        $object->lastName = "Cougar";
        $object->firstName = "Cosmo";
        $object->email = "cosmo@byu.edu";
        $object->phone = "555-1212";
        
        $iteratable = array();
        foreach($object as $key => $value)
        {
            $iteratable[$key] = $value;
        }
        
        $this->assertEquals(array(
            "userId" => 12345,
            "lastName" => "Cougar",
            "firstName" => "Cosmo",
            "email" => "cosmo@byu.edu",
            "phone" => "555-1212",
            "birthDate" => null,
            "active" => true), $iteratable);
    }
    
    /**
     * @covers \Cougar\Model\StrictModel::__construct
     * @covers \Cougar\Model\StrictModel::__isset
     * @covers \Cougar\Model\StrictModel::__set
     * @covers \Cougar\Model\StrictModel::__get
     * @covers \Cougar\Model\StrictModel::toArray
     */
    public function testIteratableWithAliases()
    {
        $object = new StrictModelUnitTest();
        $object->__setView("alt");
        $object->userId = "12345";
        $object->lastName = "Cougar";
        $object->firstName = "Cosmo";
        $object->emailAddress = "cosmo@byu.edu";
        $object->phone = "555-1212";
        $object->birthDate = "01 JUN 1960";
        
        $iteratable = array();
        foreach($object as $key => $value)
        {
            $iteratable[$key] = $value;
        }
        
        $this->assertEquals(array(
            "userId" => 12345,
            "lastName" => "Cougar",
            "firstName" => "Cosmo",
            "emailAddress" => "cosmo@byu.edu",
            "phoneNumber" => "555-1212",
            "birthDate" => "1960-06-01",
            "active" => true), $iteratable);
    }
    
    /**
     * @covers \Cougar\Model\StrictModel::__construct
     * @covers \Cougar\Model\StrictModel::__isset
     * @covers \Cougar\Model\StrictModel::__set
     * @covers \Cougar\Model\StrictModel::__get
     * @covers \Cougar\Model\StrictModel::toArray
     */
    public function testIteratableWithoutOptional()
    {
        $object = new StrictModelUnitTest();
        $object->lastName = "Cougar";
        $object->firstName = "Cosmo";
        $object->email = "cosmo@byu.edu";
        
        $iteratable = array();
        foreach($object as $key => $value)
        {
            $iteratable[$key] = $value;
        }
        
        $this->assertEquals(array(
            "lastName" => "Cougar",
            "firstName" => "Cosmo",
            "email" => "cosmo@byu.edu",
            "birthDate" => null,
            "active" => true), $iteratable);
    }
    
    /**
     * @covers \Cougar\Model\StrictModel::__construct
     * @covers \Cougar\Model\StrictModel::__isset
     * @covers \Cougar\Model\StrictModel::__set
     * @covers \Cougar\Model\StrictModel::__get
     */
    public function testNewObjectFromArray()
    {
        $array = array(
            "userId" => 12345,
            "lastName" => "Cougar",
            "firstName" => "Cosmo",
            "email" => "cosmo@byu.edu",
            "phone" => "555-1212",
            "birthDate" => "01 JUN 1960"
        );
        $object = new StrictModelUnitTest($array);
        
        $this->assertEquals(12345, $object->userId);
        $this->assertEquals("Cougar", $object->lastName);
        $this->assertEquals("Cosmo", $object->firstName);
        $this->assertEquals("cosmo@byu.edu", $object->email);
        $this->assertEquals("555-1212", $object->phone);
        $this->assertEquals("1960-06-01", $object->birthDate);
        $this->assertTrue($object->active);
    }
    
    /**
     * @covers \Cougar\Model\StrictModel::__construct
     * @covers \Cougar\Model\StrictModel::__isset
     * @covers \Cougar\Model\StrictModel::__set
     * @covers \Cougar\Model\StrictModel::__get
     */
    public function testNewObjectFromPartialArray()
    {
        $array = array(
            "lastName" => "Cougar",
            "firstName" => "Cosmo",
            "email" => "cosmo@byu.edu"
        );
        $object = new StrictModelUnitTest($array);
        
        $this->assertNull($object->userId);
        $this->assertEquals("Cougar", $object->lastName);
        $this->assertEquals("Cosmo", $object->firstName);
        $this->assertEquals("cosmo@byu.edu", $object->email);
        $this->assertNull($object->phone);
        $this->assertNull($object->birthDate);
        $this->assertTrue($object->active);
    }
    
    /**
     * @covers \Cougar\Model\StrictModel::__construct
     * @covers \Cougar\Model\StrictModel::__isset
     * @covers \Cougar\Model\StrictModel::__set
     * @covers \Cougar\Model\StrictModel::__get
     */
    public function testNewObjectFromObject()
    {
        $source_object = new \StdClass();
        $source_object->userId = "12345";
        $source_object->lastName = "Cougar";
        $source_object->firstName = "Cosmo";
        $source_object->email = "cosmo@byu.edu";
        $source_object->phone = "555-1212";
        $source_object->birthDate = "01 JUN 1960";
        $object = new StrictModelUnitTest($source_object);
        
        $this->assertEquals(12345, $object->userId);
        $this->assertEquals("Cougar", $object->lastName);
        $this->assertEquals("Cosmo", $object->firstName);
        $this->assertEquals("cosmo@byu.edu", $object->email);
        $this->assertEquals("555-1212", $object->phone);
        $this->assertInstanceOf("Cougar\\Util\\DateTime", $object->birthDate);
        $this->assertEquals("1960-06-01", (string) $object->birthDate);
        $this->assertTrue($object->active);
    }
    
    /**
     * @covers \Cougar\Model\StrictModel::__construct
     * @covers \Cougar\Model\StrictModel::__isset
     * @covers \Cougar\Model\StrictModel::__set
     * @covers \Cougar\Model\StrictModel::__get
     */
    
    public function testNewObjectWithChild()
    {
        $object = new StrictModelWithChildUnitTest();
        $this->assertNull($object->id);
        $this->assertNull($object->object);
    }
    
    /**
     * @covers \Cougar\Model\StrictModel::__construct
     * @covers \Cougar\Model\StrictModel::__isset
     * @covers \Cougar\Model\StrictModel::__set
     * @covers \Cougar\Model\StrictModel::__get
     */
    public function testNewObjectWithChildSetProperties()
    {
        $object = new StrictModelWithChildUnitTest();
        $object->id = "12345";
        $object->object = new \stdClass();
        
        $this->assertEquals(12345, $object->id);
        $this->assertInstanceOf("\StdClass", $object->object);
    }
    
    /**
     * @covers \Cougar\Model\StrictModel::__construct
     * @covers \Cougar\Model\StrictModel::__isset
     * @covers \Cougar\Model\StrictModel::__set
     * @covers \Cougar\Model\StrictModel::__get
     * @expectedException \Cougar\Exceptions\Exception
     */
    public function testNewObjectWithChildSetPropertiesWithInvalidObject()
    {
        $object = new StrictModelWithChildUnitTest();
        $object->id = "12345";
        $object->object = new StrictModelWithChildUnitTest();
        $this->fail("Expected exception was not thrown");
    }
    
    /**
     * @covers \Cougar\Model\StrictModel::__construct
     * @covers \Cougar\Model\StrictModel::__isset
     * @covers \Cougar\Model\StrictModel::__set
     * @covers \Cougar\Model\StrictModel::__get
     */
    public function testNewObjectWithChildFromObject()
    {
        $source_object = new \stdClass();
        $source_object->id = "12345";
        $source_object->object = new \stdClass();
        $object = new StrictModelWithChildUnitTest($source_object);
        
        $this->assertEquals(12345, $object->id);
        $this->assertInstanceOf("\StdClass", $object->object);
    }
    
    /**
     * @covers \Cougar\Model\StrictModel::__construct
     * @covers \Cougar\Model\StrictModel::__isset
     * @covers \Cougar\Model\StrictModel::__set
     * @covers \Cougar\Model\StrictModel::__get
     */
    public function testNewObjectWithChildFromArray()
    {
        $array = array("id" => "12345", "object" => new \stdClass());
        $object = new StrictModelWithChildUnitTest($array);
        
        $this->assertEquals(12345, $object->id);
        $this->assertInstanceOf("\StdClass", $object->object);
    }
    
    /**
     * @covers \Cougar\Model\StrictModel::__construct
     * @covers \Cougar\Model\StrictModel::__isset
     * @covers \Cougar\Model\StrictModel::__set
     * @covers \Cougar\Model\StrictModel::__get
     * @expectedException \Cougar\Exceptions\Exception
     */
    public function testNewObjectWithChildFromObjectWithWrongObject()
    {
        $source_object = new \stdClass();
        $source_object->id = "12345";
        $source_object->object = new StrictModelWithChildUnitTest();
        $object = new StrictModelWithChildUnitTest($source_object);
        $this->fail("Expected exception was not thrown");
    }
    
    /**
     * @covers \Cougar\Model\StrictModel::__construct
     * @covers \Cougar\Model\StrictModel::__isset
     * @covers \Cougar\Model\StrictModel::__set
     * @covers \Cougar\Model\StrictModel::__get
     * @expectedException \Cougar\Exceptions\Exception
     */
    public function testNewObjectWithChildFromArrayWithWrongObject()
    {
        $array = array("id" => "12345",
            "object" => new StrictModelWithChildUnitTest());
        $object = new StrictModelWithChildUnitTest($array);
        $this->fail("Expected exception was not thrown");
    }
}

require_once(__DIR__ . "/../../Cougar/Model/iArrayExportable.php");
require_once(__DIR__ . "/../../Cougar/Model/tArrayExportable.php");
require_once(__DIR__ . "/../../Cougar/Model/iAnnotatedClass.php");
require_once(__DIR__ . "/../../Cougar/Model/tAnnotatedClass.php");
require_once(__DIR__ . "/../../Cougar/Model/iStruct.php");
require_once(__DIR__ . "/../../Cougar/Model/tStruct.php");
require_once(__DIR__ . "/../../Cougar/Model/Struct.php");
require_once(__DIR__ . "/../../Cougar/Model/iModel.php");
require_once(__DIR__ . "/../../Cougar/Model/tModel.php");
require_once(__DIR__ . "/../../Cougar/Model/tStrictModel.php");
require_once(__DIR__ . "/../../Cougar/Model/StrictModel.php");

/**
 * Example AnnotatedRealStruct extension
 * 
 * @Views alt
 */
class StrictModelUnitTest extends \Cougar\Model\StrictModel
{
    /**
     * This property is optional to make sure the first value is skipped
     * properly
     * 
     * @Optional
     * @var int User ID
     */
    public $userId;
    
    /**
     * @var string User's last name
     */
    public $lastName;
    
    /**
     * @var string User's first name
     */
    public $firstName;
    
    /**
     * @Alias emailAddress
     * @View alt emailAddress
     * @Regex /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z0-9-]+$/i
     * @var string User's email address
     */
    public $email;
    
    /**
     * @Optional
     * @Alias phoneNumber
     * @View alt phoneNumber
     * @Regex /^[0-9]{3}-[0-9]{3}-[0-9]{4}$/
     * @Regex /^[0-9]{3}-[0-9]{4}$/
     * @var string User's phone number
     */
    public $phone;
    
    /**
     * @DateTimeFormat Date
     * @var DateTime
     */
    public $birthDate;
    
    /**
     * This is marked read-only to test read-only status.
     * 
     * Note: the ReadOnly tag is not used in this class, but in child classes
     * 
     * @ReadOnly
     * @var bool Whether record is active
     */
    public $active = true;
}

/**
 * Example AnnotatedRealStruct extension with child object
 */
class StrictModelWithChildUnitTest extends StrictModelUnitTest
{
    /**
     * @var int id
     */
    public $id;
    
    /**
     * @var \StdClass object
     */
    public $object;
}
?>