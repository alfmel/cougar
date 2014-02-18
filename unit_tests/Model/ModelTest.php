<?php

namespace Cougar\UnitTests\Model;

use Cougar\Model\Model;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2013-01-23 at 12:28:52.
 */
class ModelTest extends \PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass()
    {
        require_once(__DIR__ . "/../../cougar.php");
    }

    /**
     * @covers \Cougar\Model\Model::__construct
     * @covers \Cougar\Model\Model::__isset
     * @covers \Cougar\Model\Model::__set
     * @covers \Cougar\Model\Model::__get
     */
    public function testNewObject()
    {
        $object = new ModelUnitTest();
        $this->assertNull($object->userId);
        $this->assertNull($object->lastName);
        $this->assertNull($object->firstName);
        $this->assertNull($object->email);
        $this->assertNull($object->phone);
        $this->assertNull($object->birthDate);
        $this->assertTrue($object->active);
        $this->assertCount(0, $object->attributes);
    }
    
    /**
     * @covers \Cougar\Model\Model::__construct
     * @covers \Cougar\Model\Model::__isset
     * @covers \Cougar\Model\Model::__set
     * @covers \Cougar\Model\Model::__get
     */
    public function testNewObjectFromExecutionCache()
    {
        $object1 = new ModelUnitTest();
        $object2 = new ModelUnitTest();
        $this->assertEquals($object1, $object2);
    }
    
    /**
     * @covers \Cougar\Model\Model::__construct
     * @covers \Cougar\Model\Model::__isset
     * @covers \Cougar\Model\Model::__set
     * @covers \Cougar\Model\Model::__get
     * @covers \Cougar\Model\Model::validate();
     */
    public function testNewObjectSetProperties()
    {
        $object = new ModelUnitTest();
        $object->userId = "12345";
        $object->lastName = "Cougar";
        $object->firstName = "Cosmo";
        $object->email = "cosmo@byu.edu";
        $object->phone = "801-555-1212";
        $object->birthDate = "01 JUN 1960";
        $object->attributes = array("a" => 1, "b" => 2);
        $object->__validate();
        
        $this->assertEquals(12345, $object->userId);
        $this->assertEquals("Cougar", $object->lastName);
        $this->assertEquals("Cosmo", $object->firstName);
        $this->assertEquals("cosmo@byu.edu", $object->email);
        $this->assertEquals("801-555-1212", $object->phone);
        $this->assertInstanceOf("Cougar\Util\DateTime", $object->birthDate);
        $this->assertEquals("1960-06-01", (string) $object->birthDate);
        $this->assertTrue($object->active);
        $this->assertCount(2, $object->attributes);
        $this->assertArrayHasKey("a", $object->attributes);
        $this->assertEquals(1, $object->attributes["a"]);
        $this->assertArrayHasKey("b", $object->attributes);
        $this->assertEquals(2, $object->attributes["b"]);
    }

    /**
     * @covers \Cougar\Model\Model::__construct
     * @covers \Cougar\Model\Model::__isset
     * @covers \Cougar\Model\Model::__set
     * @covers \Cougar\Model\Model::__get
     * @covers \Cougar\Model\Model::validate();
     */
    public function testNewObjectSetPropertiesJsonEncoedArray()
    {
        $object = new ModelUnitTest();
        $object->userId = "12345";
        $object->lastName = "Cougar";
        $object->firstName = "Cosmo";
        $object->email = "cosmo@byu.edu";
        $object->phone = "801-555-1212";
        $object->birthDate = "01 JUN 1960";
        $object->attributes = json_encode(array("a" => 1, "b" => 2));
        $object->__validate();

        $this->assertEquals(12345, $object->userId);
        $this->assertEquals("Cougar", $object->lastName);
        $this->assertEquals("Cosmo", $object->firstName);
        $this->assertEquals("cosmo@byu.edu", $object->email);
        $this->assertEquals("801-555-1212", $object->phone);
        $this->assertInstanceOf("Cougar\Util\DateTime", $object->birthDate);
        $this->assertEquals("1960-06-01", (string) $object->birthDate);
        $this->assertTrue($object->active);
        $this->assertCount(2, $object->attributes);
        $this->assertArrayHasKey("a", $object->attributes);
        $this->assertEquals(1, $object->attributes["a"]);
        $this->assertArrayHasKey("b", $object->attributes);
        $this->assertEquals(2, $object->attributes["b"]);
    }

    /**
     * @covers \Cougar\Model\Model::__construct
     * @covers \Cougar\Model\Model::__isset
     * @covers \Cougar\Model\Model::__set
     * @covers \Cougar\Model\Model::__get
     */
    public function testNewObjectGetPropertiesWithAlias()
    {
        $object = new ModelUnitTest();
        $this->assertNull($object->email);
        $object->email = "cosmo@byu.edu";
        $this->assertEquals("cosmo@byu.edu", $object->emailAddress);
        $object->emailAddress = "cosmo.cougar@byu.edu";
        $this->assertEquals("cosmo.cougar@byu.edu", $object->email);
    }

    /**
     * @covers \Cougar\Model\Model::__construct
     * @covers \Cougar\Model\Model::__isset
     * @covers \Cougar\Model\Model::__set
     * @covers \Cougar\Model\Model::__get
     */
    public function testNewObjectSetPropertiesWithAlias()
    {
        $object = new ModelUnitTest();
        $object->userId = "12345";
        $object->lastName = "Cougar";
        $object->firstName = "Cosmo";
        $object->emailAddress = "cosmo@byu.edu";
        $object->phone = "555-1212";
        $object->birthDate = "01 JUN 1960";
        $object->__validate();

        $this->assertEquals(12345, $object->userId);
        $this->assertEquals("Cougar", $object->lastName);
        $this->assertEquals("Cosmo", $object->firstName);
        $this->assertEquals("cosmo@byu.edu", $object->email);
        $this->assertEquals("cosmo@byu.edu", $object->emailAddress);
        $this->assertEquals("555-1212", $object->phone);
        $this->assertInstanceOf("Cougar\Util\DateTime", $object->birthDate);
        $this->assertEquals("1960-06-01", (string) $object->birthDate);
        $this->assertTrue($object->active);
    }

    /**
     * @covers \Cougar\Model\Model::__construct
     * @covers \Cougar\Model\Model::__isset
     * @covers \Cougar\Model\Model::__set
     * @expectedException \Cougar\Exceptions\Exception
     */
    public function testNewObjectSetInvalidProperty()
    {
        $object = new ModelUnitTest();
        $object->badProperty = "junk";
        $this->fail("Expected exception was not thrown");
    }
    
    /**
     * @covers \Cougar\Model\Model::__construct
     * @covers \Cougar\Model\Model::__isset
     * @covers \Cougar\Model\Model::__set
     * @expectedException \Cougar\Exceptions\Exception
     */
    public function testNewObjectBadConstraint()
    {
        $object = new ModelUnitTest();
        $object->email = "bad email address";
        $object->__validate();
        $this->fail("Expected exception was not thrown");
    }
    
    /**
     * @covers \Cougar\Model\Model::__construct
     * @covers \Cougar\Model\Model::__isset
     * @covers \Cougar\Model\Model::__set
     * @covers \Cougar\Model\Model::__get
     * @covers \Cougar\Model\Model::toArray
     */
    public function testJsonSerializeWihtoutOptional()
    {
        $object = new ModelUnitTest();
        $object->lastName = "Cougar";
        $object->firstName = "Cosmo";
        $object->email = "cosmo@byu.edu";
        
        $this->assertEquals(array(
            "lastName" => "Cougar",
            "firstName" => "Cosmo",
            "email" => "cosmo@byu.edu",
            "birthDate" => null,
            "active" => true,
            "attributes" => array()),
            json_decode(json_encode($object), true));
    }
    
    /**
     * @covers \Cougar\Model\Model::__construct
     * @covers \Cougar\Model\Model::__isset
     * @covers \Cougar\Model\Model::__set
     * @covers \Cougar\Model\Model::__get
     * @covers \Cougar\Model\Model::toArray
     */
    public function testJsonSerialize()
    {
        $object = new ModelUnitTest();
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
            "active" => true,
            "attributes" => array()),
            json_decode(json_encode($object), true));
    }
    
    /**
     * @covers \Cougar\Model\Model::__construct
     * @covers \Cougar\Model\Model::__isset
     * @covers \Cougar\Model\Model::__set
     * @covers \Cougar\Model\Model::__get
     * @covers \Cougar\Model\Model::toArray
     */
    public function testJsonSerializeWithAltView()
    {
        $object = new ModelUnitTest();
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
            "active" => true,
            "attributes" => array()),
            json_decode(json_encode($object), true));
    }
    
    /**
     * @covers \Cougar\Model\Model::__construct
     * @covers \Cougar\Model\Model::__isset
     * @covers \Cougar\Model\Model::__set
     * @covers \Cougar\Model\Model::__get
     * @covers \Cougar\Model\Model::toArray
     */
    public function testJsonSerializeWithAliasesWithoutOptional()
    {
        $object = new ModelUnitTest();
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
            "active" => true,
            "attributes" => array()),
            json_decode(json_encode($object), true));
    }
    
    /**
     * @covers \Cougar\Model\Model::__construct
     * @covers \Cougar\Model\Model::__isset
     * @covers \Cougar\Model\Model::__set
     * @covers \Cougar\Model\Model::__get
     * @covers \Cougar\Model\Model::__toArray
     */
    public function testToArrayWithDefaultValues()
    {
        $object = new ModelUnitTest();
        
        $this->assertEquals(array(
            "lastName" => null,
            "firstName" => null,
            "email" => null,
            "birthDate" => null,
            "active" => true,
            "attributes" => array()), $object->__toArray());
    }
    
    /**
     * @covers \Cougar\Model\Model::__construct
     * @covers \Cougar\Model\Model::__isset
     * @covers \Cougar\Model\Model::__set
     * @covers \Cougar\Model\Model::__get
     * @covers \Cougar\Model\Model::__toArray
     */
    public function testToArray()
    {
        $object = new ModelUnitTest();
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
            "active" => true,
            "attributes" => array()), $object->__toArray());
    }
    
    /**
     * @covers \Cougar\Model\Model::__construct
     * @covers \Cougar\Model\Model::__isset
     * @covers \Cougar\Model\Model::__set
     * @covers \Cougar\Model\Model::__get
     * @covers \Cougar\Model\Model::toArray
     */
    public function testToArrayWithoutOptional()
    {
        $object = new ModelUnitTest();
        $object->lastName = "Cougar";
        $object->firstName = "Cosmo";
        $object->email = "cosmo@byu.edu";
        
        $this->assertEquals(array(
            "lastName" => "Cougar",
            "firstName" => "Cosmo",
            "email" => "cosmo@byu.edu",
            "birthDate" => null,
            "active" => true,
            "attributes" => array()), $object->__toArray());
    }
    
    /**
     * @covers \Cougar\Model\Model::__construct
     * @covers \Cougar\Model\Model::__isset
     * @covers \Cougar\Model\Model::__set
     * @covers \Cougar\Model\Model::__get
     * @covers \Cougar\Model\Model::toArray
     */
    public function testToArrayWithAliases()
    {
        $object = new ModelUnitTest();
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
            "active" => true,
            "attributes" => array()), $object->__toArray());
    }
    
    /**
     * @covers \Cougar\Model\Model::__construct
     * @covers \Cougar\Model\Model::__isset
     * @covers \Cougar\Model\Model::__set
     * @covers \Cougar\Model\Model::__get
     * @covers \Cougar\Model\Model::toArray
     */
    public function testIteratable()
    {
        $object = new ModelUnitTest();
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
            "active" => true,
            "attributes" => array()), $iteratable);
    }
    
    /**
     * @covers \Cougar\Model\Model::__construct
     * @covers \Cougar\Model\Model::__isset
     * @covers \Cougar\Model\Model::__set
     * @covers \Cougar\Model\Model::__get
     * @covers \Cougar\Model\Model::toArray
     */
    public function testIteratableWithAliases()
    {
        $object = new ModelUnitTest();
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
            "active" => true,
            "attributes" => array()), $iteratable);
    }
    
    /**
     * @covers \Cougar\Model\Model::__construct
     * @covers \Cougar\Model\Model::__isset
     * @covers \Cougar\Model\Model::__set
     * @covers \Cougar\Model\Model::__get
     * @covers \Cougar\Model\Model::toArray
     */
    public function testIteratableWithoutOptional()
    {
        $object = new ModelUnitTest();
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
            "active" => true,
            "attributes" => array()), $iteratable);
    }
    
    /**
     * @covers \Cougar\Model\Model::__construct
     * @covers \Cougar\Model\Model::__isset
     * @covers \Cougar\Model\Model::__set
     * @covers \Cougar\Model\Model::__get
     */
    public function testNewObjectFromArray()
    {
        $array = array(
            "userId" => 12345,
            "lastName" => "Cougar",
            "firstName" => "Cosmo",
            "email" => "cosmo@byu.edu",
            "phone" => "555-1212",
            "birthDate" => "01 JUN 1960",
            "active" => false,
            "attributes" => array("a" => 1, "b" => 2)
        );
        $object = new ModelUnitTest($array);
        
        $this->assertEquals(12345, $object->userId);
        $this->assertEquals("Cougar", $object->lastName);
        $this->assertEquals("Cosmo", $object->firstName);
        $this->assertEquals("cosmo@byu.edu", $object->email);
        $this->assertEquals("555-1212", $object->phone);
        $this->assertEquals("1960-06-01", $object->birthDate);
        $this->assertFalse($object->active);
        $this->assertCount(2, $object->attributes);
        $this->assertArrayHasKey("a", $object->attributes);
        $this->assertEquals(1, $object->attributes["a"]);
        $this->assertArrayHasKey("b", $object->attributes);
        $this->assertEquals(2, $object->attributes["b"]);
    }
    
    /**
     * @covers \Cougar\Model\Model::__construct
     * @covers \Cougar\Model\Model::__isset
     * @covers \Cougar\Model\Model::__set
     * @covers \Cougar\Model\Model::__get
     */
    public function testNewObjectFromPartialArray()
    {
        $array = array(
            "lastName" => "Cougar",
            "firstName" => "Cosmo",
            "email" => "cosmo@byu.edu"
        );
        $object = new ModelUnitTest($array);
        
        $this->assertNull($object->userId);
        $this->assertEquals("Cougar", $object->lastName);
        $this->assertEquals("Cosmo", $object->firstName);
        $this->assertEquals("cosmo@byu.edu", $object->email);
        $this->assertNull($object->phone);
        $this->assertNull($object->birthDate);
        $this->assertTrue($object->active);
        $this->assertCount(0, $object->attributes);
    }
    
    /**
     * @covers \Cougar\Model\Model::__construct
     * @covers \Cougar\Model\Model::__isset
     * @covers \Cougar\Model\Model::__set
     * @covers \Cougar\Model\Model::__get
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
        $source_object->attributes = array("a" => 1, "b" => 2);
        $object = new ModelUnitTest($source_object);
        
        $this->assertEquals(12345, $object->userId);
        $this->assertEquals("Cougar", $object->lastName);
        $this->assertEquals("Cosmo", $object->firstName);
        $this->assertEquals("cosmo@byu.edu", $object->email);
        $this->assertEquals("555-1212", $object->phone);
        $this->assertInstanceOf("Cougar\\Util\\DateTime", $object->birthDate);
        $this->assertEquals("1960-06-01", (string) $object->birthDate);
        $this->assertTrue($object->active);
        $this->assertCount(2, $object->attributes);
        $this->assertArrayHasKey("a", $object->attributes);
        $this->assertEquals(1, $object->attributes["a"]);
        $this->assertArrayHasKey("b", $object->attributes);
        $this->assertEquals(2, $object->attributes["b"]);
    }
    
    /**
     * @covers \Cougar\Model\Model::__construct
     * @covers \Cougar\Model\Model::__isset
     * @covers \Cougar\Model\Model::__set
     * @covers \Cougar\Model\Model::__get
     */
    
    public function testNewObjectWithChild()
    {
        $object = new ModelWithChildUnitTest();
        $this->assertNull($object->id);
        $this->assertNull($object->object);
    }
    
    /**
     * @covers \Cougar\Model\Model::__construct
     * @covers \Cougar\Model\Model::__isset
     * @covers \Cougar\Model\Model::__set
     * @covers \Cougar\Model\Model::__get
     */
    public function testNewObjectWithChildSetProperties()
    {
        $object = new ModelWithChildUnitTest();
        $object->id = "12345";
        $object->object = new \stdClass();
        
        $this->assertEquals(12345, $object->id);
        $this->assertInstanceOf("\StdClass", $object->object);
    }
    
    /**
     * @covers \Cougar\Model\Model::_
     */
    
    /**
     * @covers \Cougar\Model\Model::__construct
     * @covers \Cougar\Model\Model::__isset
     * @covers \Cougar\Model\Model::__set
     * @covers \Cougar\Model\Model::__get
     * @expectedException \Cougar\Exceptions\Exception
     */
    public function testNewObjectWithChildSetPropertiesWithInvalidObject()
    {
        $object = new ModelWithChildUnitTest();
        $object->id = "12345";
        $object->object = new ModelWithChildUnitTest();
        $object->__validate();
        $this->fail("Expected exception was not thrown");
    }
    
    /**
     * @covers \Cougar\Model\Model::__construct
     * @covers \Cougar\Model\Model::__isset
     * @covers \Cougar\Model\Model::__set
     * @covers \Cougar\Model\Model::__get
     */
    public function testNewObjectWithChildFromObject()
    {
        $source_object = new \stdClass();
        $source_object->id = "12345";
        $source_object->object = new \stdClass();
        $object = new ModelWithChildUnitTest($source_object);
        
        $this->assertEquals(12345, $object->id);
        $this->assertInstanceOf("\\StdClass", $object->object);
    }
    
    /**
     * @covers \Cougar\Model\Model::__construct
     * @covers \Cougar\Model\Model::__isset
     * @covers \Cougar\Model\Model::__set
     * @covers \Cougar\Model\Model::__get
     */
    public function testNewObjectWithChildFromArray()
    {
        $array = array("id" => "12345", "object" => new \stdClass());
        $object = new ModelWithChildUnitTest($array);
        
        $this->assertEquals(12345, $object->id);
        $this->assertInstanceOf("\StdClass", $object->object);
    }
    
    /**
     * @covers \Cougar\Model\Model::__construct
     * @covers \Cougar\Model\Model::__isset
     * @covers \Cougar\Model\Model::__set
     * @covers \Cougar\Model\Model::__get
     * @expectedException \Cougar\Exceptions\Exception
     */
    public function testRegexValidationFailure()
    {
        $object = new ModelUnitTest();
        $object->email = "";
        $object->__validate();
    }
    
    /**
     * @covers \Cougar\Model\Model::__construct
     * @covers \Cougar\Model\Model::__isset
     * @covers \Cougar\Model\Model::__set
     * @covers \Cougar\Model\Model::__get
     * @expectedException \Cougar\Exceptions\Exception
     */
    public function testNewObjectWithChildFromObjectWithWrongObject()
    {
        $source_object = new \stdClass();
        $source_object->id = "12345";
        $source_object->object = new ModelWithChildUnitTest();
        $object = new ModelWithChildUnitTest($source_object);
        $this->fail("Expected exception was not thrown");
    }
    
    /**
     * @covers \Cougar\Model\Model::__construct
     * @covers \Cougar\Model\Model::__isset
     * @covers \Cougar\Model\Model::__set
     * @covers \Cougar\Model\Model::__get
     * @expectedException \Cougar\Exceptions\Exception
     */
    public function testNewObjectWithChildFromArrayWithWrongObject()
    {
        $array = array("id" => "12345",
            "object" => new ModelWithChildUnitTest());
        $object = new ModelWithChildUnitTest($array);
        $this->fail("Expected exception was not thrown");
    }
}

require_once(__DIR__ . "/../../Cougar/Model/iArrayExportable.php");
require_once(__DIR__ . "/../../Cougar/Model/tArrayExportable.php");
require_once(__DIR__ . "/../../Cougar/Model/iAnnotatedClass.php");
require_once(__DIR__ . "/../../Cougar/Model/tAnnotatedClass.php");
require_once(__DIR__ . "/../../Cougar/Model/iStruct.php");
require_once(__DIR__ . "/../../Cougar/Model/tStruct.php");
require_once(__DIR__ . "/../../Cougar/Model/iModel.php");
require_once(__DIR__ . "/../../Cougar/Model/tModel.php");
require_once(__DIR__ . "/../../Cougar/Model/Model.php");

/**
 * Example AnnotatedStruct extension
 * 
 * @Views alt
 */
class ModelUnitTest extends \Cougar\Model\Model
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
     * @ReadOnly
     * @var bool Whether record is active
     */
    public $active = true;

    /**
     * @var array User's attributes
     */
    public $attributes = array();
}

/**
 * Example AnnotatedStruct extension with child object
 */
class ModelWithChildUnitTest extends ModelUnitTest
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