<?php

namespace Cougar\UnitTests\Model;

use PDO;
use Cougar\Model\PdoModel;
use Cougar\Security\Security;
use Cougar\Util\QueryParameter;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2013-01-23 at 12:28:52.
 */
class PdoModelNoCacheTest extends \PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass()
    {
        require_once(__DIR__ . "/../../cougar.php");
    }

    /**
     * @covers \Cougar\Model\PdoModel::__construct
     * @covers \Cougar\Model\PdoModel::getRecord
     */
    public function testLoad() {
        $security = new Security();
        
        $cache = $this->getMock("\\Cougar\\Cache\\Cache");
        $cache->expects($this->never())
            ->method("get");
        $cache->expects($this->never())
            ->method("set")
            ->will($this->returnValue(false));
        
        $pdo_statement = $this->getMock("\\PDOStatement");
        $pdo_statement->expects($this->once())
            ->method("execute")
            ->with($this->equalTo(array("userId" => 12345)))
            ->will($this->returnValue(true));
        $pdo_statement->expects($this->at(2))
            ->method("fetch")
            ->will($this->returnValue(array(
                "userId" => "12345",
                "lastName" => "Trevino",
                "firstName" => "Alberto",
                "emailAddress" => "alberto@byu.edu",
                "phone" => "801-555-1212")));
        $pdo_statement->expects($this->at(3))
            ->method("fetch")
            ->will($this->returnValue(false));
        
        $pdo = $this->getMock("\\PDO",
            array("prepare"),
            array("mysql:"));
        $pdo->expects($this->once())
            ->method("prepare")
            ->with($this->equalTo(
                "SELECT userId, lastName, firstName, emailAddress AS email, " .
                    "phone " .
                "FROM user WHERE userId = :userId"))
            ->will($this->returnValue($pdo_statement));
        
        $object = new PdoModelNoCacheUnitTest($security, $cache, $pdo,
            array("userId" => "12345"));
        $this->assertEquals(12345, $object->userId);
    }

    /**
     * @covers \Cougar\Model\PdoModel::__construct
     * @covers \Cougar\Model\PdoModel::getRecord
     * @expectedException \Cougar\Exceptions\RecordNotFoundException
     */
    public function testLoadRecordNotFound() {
        $security = new Security();
        
        $cache = $this->getMock("\\Cougar\\Cache\\Cache");
        $cache->expects($this->never())
            ->method("get");
        
        $pdo_statement = $this->getMock("\\PDOStatement");
        $pdo_statement->expects($this->once())
            ->method("execute")
            ->with($this->equalTo(array("userId" => 12345)))
            ->will($this->returnValue(true));
        $pdo_statement->expects($this->once())
            ->method("fetch")
            ->will($this->returnValue(false));
        
        $pdo = $this->getMock("\\PDO",
            array("prepare"),
            array("mysql:"));
        $pdo->expects($this->once())
            ->method("prepare")
            ->with($this->equalTo(
                "SELECT userId, lastName, firstName, emailAddress AS email, " .
                    "phone " .
                "FROM user WHERE userId = :userId"))
            ->will($this->returnValue($pdo_statement));
        
        $object = new PdoModelNoCacheUnitTest($security, $cache, $pdo,
            array("userId" => "12345"));
    }

    /**
     * @covers \Cougar\Model\PdoModel::__construct
     * @covers \Cougar\Model\PdoModel::__get
     * @covers \Cougar\Model\PdoModel::__set
     * @covers \Cougar\Model\PdoModel::save
     */
    public function testSaveInsert() {        
        $security = new Security();
        
        $cache = $this->getMock("\\Cougar\\Cache\\Cache");
        $cache->expects($this->never())
            ->method("get");
        $cache->expects($this->never())
            ->method("set");
        
        $pdo_statement = $this->getMock("\\PDOStatement");
        $pdo_statement->expects($this->once())
            ->method("execute")
            ->with($this->equalTo(array(
                "userId" => null,
                "lastName" => "Trevino",
                "firstName" => "Alberto",
                "email" => "alberto@byu.edu",
                "phone" => "801-555-1212")))
            ->will($this->returnValue(true));
        $pdo_statement->expects($this->once())
            ->method("rowCount")
            ->will($this->returnValue(1));

        $pdo = $this->getMock("\\PDO",
            array("prepare"),
            array("mysql:"));
        $pdo->expects($this->once())
            ->method("prepare")
            ->with($this->equalTo(
                "INSERT INTO user " .
                "(userId, lastName, firstName, emailAddress, phone) " .
                "VALUES(:userId, :lastName, :firstName, :email, :phone)"))
            ->will($this->returnValue($pdo_statement));
        
        # Test set
        $object = new PdoModelNoCacheUnitTest($security, $cache, $pdo);
        $object->firstName = "Alberto";
        $object->lastName = "Trevino";
        $object->email = "alberto@byu.edu";
        $object->phone = "801-555-1212";
        
        # Test get
        $this->assertEquals("Alberto", $object->firstName);
        $this->assertEquals("Trevino", $object->lastName);
        $this->assertEquals("alberto@byu.edu", $object->email);
        $this->assertEquals("801-555-1212", $object->phone);
    
        # Test save with insert
        $object->save();
    }

    /**
     * @covers \Cougar\Model\PdoModel::__construct
     * @covers \Cougar\Model\PdoModel::getRecord
     * @covers \Cougar\Model\PdoModel::buildWhereClause
     * @covers \Cougar\Model\PdoModel::getWhereParameters
     * @covers \Cougar\Model\PdoModel::save
     */
    public function testSaveUpdate() {
        $security = new Security();
        
        $cache = $this->getMock("\\Cougar\\Cache\\Cache");
        $cache->expects($this->never())
            ->method("get");
        $cache->expects($this->never())
            ->method("set");
        
        $pdo_statement_select = $this->getMock("\\PDOStatement");
        $pdo_statement_select->expects($this->once())
            ->method("execute")
            ->with($this->equalTo(array("userId" => 12345)))
            ->will($this->returnValue(true));
        $pdo_statement_select->expects($this->at(2))
            ->method("fetch")
            ->will($this->returnValue(array(
                "userId" => "12345",
                "lastName" => "",
                "firstName" => "",
                "emailAddress" => "",
                "phone" => "")));
        $pdo_statement_select->expects($this->at(3))
            ->method("fetch")
            ->will($this->returnValue(false));
        
        $pdo_statement_update = $this->getMock("\\PDOStatement");
        $pdo_statement_update->expects($this->once())
            ->method("execute")
            ->with($this->equalTo(array(
                "lastName" => "Trevino",
                "firstName" => "Alberto",
                "email" => "alberto@byu.edu",
                "phone" => "801-555-1212",
                "userId" => 12345)))
            ->will($this->returnValue(true));
        $pdo_statement_update->expects($this->once())
            ->method("rowCount")
            ->will($this->returnValue(1));

        $pdo = $this->getMock("\\PDO",
            array("prepare"),
            array("mysql:"));
        $pdo->expects($this->at(0))
            ->method("prepare")
            ->with($this->equalTo(
                "SELECT userId, lastName, firstName, emailAddress AS email, " .
                    "phone " .
                "FROM user WHERE userId = :userId"))
            ->will($this->returnValue($pdo_statement_select));
        $pdo->expects($this->at(1))
            ->method("prepare")
            ->with($this->equalTo(
                "UPDATE user " .
                "SET lastName = :lastName, " .
                    "firstName = :firstName, " .
                    "emailAddress = :email, " .
                    "phone = :phone " .
                "WHERE userId = :userId"))
            ->will($this->returnValue($pdo_statement_update));
        
        $object = new PdoModelNoCacheUnitTest($security, $cache, $pdo,
            array("userId" => "12345"));
        $object->firstName = "Alberto";
        $object->lastName = "Trevino";
        $object->email = "alberto@byu.edu";
        $object->phone = "801-555-1212";
        $object->save();
    }

    /**
     * @covers \Cougar\Model\PdoModel::__construct
     * @covers \Cougar\Model\PdoModel::getRecord
     * @covers \Cougar\Model\PdoModel::buildWhereClause
     * @covers \Cougar\Model\PdoModel::getWhereParameters
     * @covers \Cougar\Model\PdoModel::save
     * @expectedException \Cougar\Exceptions\Exception
     */
    public function testUpdateReadOnlyProperty() {
        $security = new Security();
        
        $cache = $this->getMock("\\Cougar\\Cache\\Cache");
        $cache->expects($this->never())
            ->method("get");
        
        $pdo_statement_select = $this->getMock("\\PDOStatement");
        $pdo_statement_select->expects($this->once())
            ->method("execute")
            ->with($this->equalTo(array("userId" => 12345)))
            ->will($this->returnValue(true));
        $pdo_statement_select->expects($this->at(2))
            ->method("fetch")
            ->will($this->returnValue(array(
                "userId" => "12345",
                "lastName" => "",
                "firstName" => "",
                "emailAddress" => "",
                "phone" => "")));
        $pdo_statement_select->expects($this->at(3))
            ->method("fetch")
            ->will($this->returnValue(false));
        
        $pdo = $this->getMock("\\PDO",
            array("prepare"),
            array("mysql:"));
        $pdo->expects($this->at(0))
            ->method("prepare")
            ->with($this->equalTo(
                "SELECT userId, lastName, firstName, emailAddress AS email, " .
                    "phone " .
                "FROM user WHERE userId = :userId"))
            ->will($this->returnValue($pdo_statement_select));
        
        $object = new PdoModelNoCacheUnitTest($security, $cache, $pdo,
            array("userId" => "12345"));
        $object->userId = "54321";
        $object->save();
    }

    /**
     * @covers \Cougar\Model\PdoModel::__construct
     * @covers \Cougar\Model\PdoModel::getRecord
     * @covers \Cougar\Model\PdoModel::buildWhereClause
     * @covers \Cougar\Model\PdoModel::getWhereParameters
     * @covers \Cougar\Model\PdoModel::delete
     */
    public function testDelete() {
        $security = new Security();
        
        $cache = $this->getMock("\\Cougar\\Cache\\Cache");
        $cache->expects($this->never())
            ->method("get");
        $cache->expects($this->never())
            ->method("clear");
        
        $pdo_statement_select = $this->getMock("\\PDOStatement");
        $pdo_statement_select->expects($this->once())
            ->method("execute")
            ->with($this->equalTo(array("userId" => 12345)))
            ->will($this->returnValue(true));
        $pdo_statement_select->expects($this->at(2))
            ->method("fetch")
            ->will($this->returnValue(array(
                "userId" => "12345",
                "lastName" => "Trevino",
                "firstName" => "Alberto",
                "emailAddress" => "alberto@byu.edu",
                "phone" => "801-555-1212")));
        $pdo_statement_select->expects($this->at(3))
            ->method("fetch")
            ->will($this->returnValue(false));
        
        $pdo_statement_delete = $this->getMock("\\PDOStatement");
        $pdo_statement_delete->expects($this->once())
            ->method("execute")
            ->with($this->equalTo(array("userId" => 12345)))
            ->will($this->returnValue(true));
        $pdo_statement_delete->expects($this->exactly(2))
            ->method("rowCount")
            ->will($this->returnValue(1));

        $pdo = $this->getMock("\\PDO",
            array("prepare"),
            array("mysql:"));
        $pdo->expects($this->at(0))
            ->method("prepare")
            ->with($this->equalTo(
                "SELECT userId, lastName, firstName, emailAddress AS email, " .
                    "phone " .
                "FROM user WHERE userId = :userId"))
            ->will($this->returnValue($pdo_statement_select));
        $pdo->expects($this->at(1))
            ->method("prepare")
            ->with($this->equalTo(
                "DELETE FROM user " .
                "WHERE userId = :userId"))
            ->will($this->returnValue($pdo_statement_delete));
        
        $object = new PdoModelNoCacheUnitTest($security, $cache, $pdo,
            array("userId" => "12345"));
        $object->delete();
    }
    
    /**
     * @covers \Cougar\Model\PdoModel::__construct
     * @covers \Cougar\Model\PdoModel::query
     */
    public function testQuery() {
        $parameters = array(
            new QueryParameter("firstName", "Alberto", "**"),
            new QueryParameter("lastName", "Trevino", "**")
        );
        
        $security = new Security();
        
        $cache = $this->getMock("\\Cougar\\Cache\\Cache");
        $cache->expects($this->never())
            ->method("get");
    $cache->expects($this->never())
            ->method("set")
            ->will($this->returnValue(false));
        
        $pdo_statement = $this->getMock("\\PDOStatement");
        $pdo_statement->expects($this->once())
            ->method("execute")
            ->with($this->equalTo(array(
                "firstName" => "%Alberto%",
                "lastName" => "%Trevino%")))
            ->will($this->returnValue(true));
        $pdo_statement->expects($this->at(1))
            ->method("fetchAll")
            ->will($this->returnValue(array(array(
                "userId" => "12345",
                "lastName" => "Trevino",
                "firstName" => "Alberto",
                "emailAddress" => "alberto@byu.edu",
                "phone" => "801-555-1212"))));
        
        $pdo = $this->getMock("\\PDO",
            array("prepare"),
            array("mysql:"));
        $pdo->expects($this->once())
            ->method("prepare")
            ->with($this->equalTo(
                "SELECT userId, lastName, firstName, emailAddress AS email, " .
                    "phone " .
                "FROM user  WHERE firstName LIKE :firstName AND " .
                    "lastName LIKE :lastName " .
                "LIMIT 10000 OFFSET 0"))
            ->will($this->returnValue($pdo_statement));
        
        $object = new PdoModelNoCacheUnitTest($security, $cache, $pdo);
        $query_result = $object->query($parameters);
        $this->assertCount(1, $query_result);
        $this->assertArrayHasKey("userId", $query_result[0]);
        $this->assertEquals("12345", $query_result[0]["userId"]);
    }
    
    /**
     * @covers \Cougar\Model\PdoModel::__construct
     * @covers \Cougar\Model\PdoModel::getRecord
     * @covers \Cougar\Model\PdoModel::current
     * @covers \Cougar\Model\PdoModel::key
     * @covers \Cougar\Model\PdoModel::next
     * @covers \Cougar\Model\PdoModel::rewind
     * @covers \Cougar\Model\PdoModel::valid
     */
    public function testIteratorMethods() {
        $security = new Security();
        
        $cache = $this->getMock("\\Cougar\\Cache\\Cache");
        
        $pdo = new PDO("mysql:");
        
        $object = new PdoModelNoCacheUnitTest($security, $cache, $pdo);
        $object->userId = "userId value";
        $object->lastName = "lastName value";
        $object->firstName = "firstName value";
        $object->email = "email value";
        $object->phone = "phone value";
        
        $array = array();
        foreach($object as $key => $value)
        {
            $array[$key] = $value;
        }
        $this->assertEquals($object->__toArray(), $array);
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
require_once(__DIR__ . "/../../Cougar/Model/iPersistentModel.php");
require_once(__DIR__ . "/../../Cougar/Model/tPdoModel.php");
require_once(__DIR__ . "/../../Cougar/Model/PdoModel.php");

/**
 * Example PdoModel extension
 * 
 * @Table user
 * @Allow SELECT INSERT UPDATE DELETE QUERY
 * @PrimaryKey userId
 * @NoCache
 */
class PdoModelNoCacheUnitTest extends PdoModel
{
    /**
     * @ReadOnly
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
     * @Column emailAddress
     * @var string User's email address
     */
    public $email;
    
    /**
     * @var string User's phone number
     */
    public $phone;
}
