<?php

namespace Cougar\UniTests\Model;

use PDO;
use Cougar\Model\PdoModel;
use Cougar\Security\Security;
use Cougar\Util\QueryParameter;

require_once(__DIR__ . "/../../../cougar.php");

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2013-01-23 at 12:28:52.
 */
class PdoModelTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @covers \Cougar\Model\PdoModel::__construct
	 * @covers \Cougar\Model\PdoModel::getRecord
	 * @covers \Cougar\Model\PdoModel::getCacheKey
	 */
	public function testLoad() {
		$security = new Security();
		
		$cache = $this->getMock("\\Cougar\\Cache\\Cache");
		$cache->expects($this->once())
			->method("get")
			->with("unittest.model.12345")
			->will($this->returnValue(false));
		$cache->expects($this->once())
			->method("set")
			->will($this->returnValue(false));
		
		$pdo_statement = $this->getMock("\PDOStatement");
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
				"phone" => "801-555-1212",
				"birthDate" => "01 JUN 1960")));
		$pdo_statement->expects($this->at(3))
			->method("fetch")
			->will($this->returnValue(false));
		
		$pdo = $this->getMock("\PDO",
			array("prepare"),
			array("mysql:"));
		$pdo->expects($this->once())
			->method("prepare")
			->with($this->equalTo(
				"SELECT userId, lastName, firstName, emailAddress, phone, " .
					"birthDate " .
				"FROM user WHERE userId = :userId"))
			->will($this->returnValue($pdo_statement));
		
		$object = new PdoModelUnitTest($security, $cache, $pdo,
			array("userId" => 12345));
		$this->assertEquals(12345, $object->userId);
	}

	/**
	 * @covers \Cougar\Model\PdoModel::__construct
	 * @covers \Cougar\Model\PdoModel::getRecord
	 * @covers \Cougar\Model\PdoModel::getCacheKey
	 * @expectedException \Cougar\Exceptions\RecordNotFoundException
	 */
	public function testLoadRecordNotFound() {
		$security = new Security();
		
		$cache = $this->getMock("\\Cougar\\Cache\\Cache");
		$cache->expects($this->once())
			->method("get")
			->with("unittest.model.12345")
			->will($this->returnValue(false));
		
		$pdo_statement = $this->getMock("\PDOStatement");
		$pdo_statement->expects($this->once())
			->method("execute")
			->with($this->equalTo(array("userId" => 12345)))
			->will($this->returnValue(true));
		$pdo_statement->expects($this->once())
			->method("fetch")
			->will($this->returnValue(false));
		
		$pdo = $this->getMock("\PDO",
			array("prepare"),
			array("mysql:"));
		$pdo->expects($this->once())
			->method("prepare")
			->with($this->equalTo(
				"SELECT userId, lastName, firstName, emailAddress, phone, " .
					"birthDate " .
				"FROM user WHERE userId = :userId"))
			->will($this->returnValue($pdo_statement));
		
		$object = new PdoModelUnitTest($security, $cache, $pdo,
			array("userId" => "12345"));
	}

	/**
	 * @covers \Cougar\Model\PdoModel::__construct
	 * @covers \Cougar\Model\PdoModel::__get
	 * @covers \Cougar\Model\PdoModel::__set
	 * @covers \Cougar\Model\PdoModel::save
	 * @covers \Cougar\Model\PdoModel::getCacheKey
	 */
	public function testSaveInsert() {		
		$security = new Security();
		
		$cache = $this->getMock("\\Cougar\\Cache\\Cache");
		$cache->expects($this->never())
			->method("get");
		$cache->expects($this->once())
			->method("set")
			->will($this->returnValue(false));
		
		$pdo_statement = $this->getMock("\PDOStatement");
		$pdo_statement->expects($this->once())
			->method("execute")
			->with($this->equalTo(array(
				"userId" => null,
				"lastName" => "Trevino",
				"firstName" => "Alberto",
				"email" => "alberto@byu.edu",
				"phone" => "801-555-1212",
				"birthDate" => "1960-06-01")))
			->will($this->returnValue(true));
		$pdo_statement->expects($this->once())
			->method("rowCount")
			->will($this->returnValue(1));

		$pdo = $this->getMock("\PDO",
			array("prepare"),
			array("mysql:"));
		$pdo->expects($this->once())
			->method("prepare")
			->with($this->equalTo(
				"INSERT INTO user " .
				"(userId, lastName, firstName, emailAddress, phone, birthDate) " .
				"VALUES(:userId, :lastName, :firstName, :email, :phone, " .
					":birthDate)"))
			->will($this->returnValue($pdo_statement));
		
		# Test set
		$object = new PdoModelUnitTest($security, $cache, $pdo);
		$object->firstName = "Alberto";
		$object->lastName = "Trevino";
		$object->email = "alberto@byu.edu";
		$object->phone = "801-555-1212";
		$object->birthDate = "01 JUN 1960";
	
		# Test save with insert
		$object->save();
		
		# Test get
		$this->assertEquals("Alberto", $object->firstName);
		$this->assertEquals("Trevino", $object->lastName);
		$this->assertEquals("alberto@byu.edu", $object->email);
		$this->assertEquals("801-555-1212", $object->phone);
		$this->assertInstanceOf("\\Cougar\\Util\\DateTime", $object->birthDate);
		$this->assertEquals("1960-06-01", (string) $object->birthDate);
		
		# Test getting the changed values
		$changes = $object->lastChanges();
		$this->assertArrayHasKey("firstName", $changes);
		$this->assertArrayHasKey("lastName", $changes);
		$this->assertArrayHasKey("email", $changes);
		$this->assertArrayHasKey("phone", $changes);
		$this->assertArrayHasKey("birthDate", $changes);
		
		$this->assertNull($changes["firstName"]);
		$this->assertNull($changes["lastName"]);
		$this->assertNull($changes["email"]);
		$this->assertNull($changes["phone"]);
		$this->assertNull($changes["birthDate"]);
	}

	/**
	 * @covers \Cougar\Model\PdoModel::__construct
	 * @covers \Cougar\Model\PdoModel::__get
	 * @covers \Cougar\Model\PdoModel::__set
	 * @covers \Cougar\Model\PdoModel::save
	 * @covers \Cougar\Model\PdoModel::getCacheKey
	 */
	public function testSaveInsertFromLoad() {		
		$security = new Security();
		
		$cache = $this->getMock("\\Cougar\\Cache\\Cache");
		$cache->expects($this->never())
			->method("get");
		$cache->expects($this->once())
			->method("set")
			->will($this->returnValue(false));
		
		$pdo_statement = $this->getMock("\PDOStatement");
		$pdo_statement->expects($this->once())
			->method("execute")
			->with($this->equalTo(array(
				"userId" => null,
				"lastName" => "Trevino",
				"firstName" => "Alberto",
				"email" => "alberto@byu.edu",
				"phone" => "801-555-1212",
				"birthDate" => "1960-06-01")))
			->will($this->returnValue(true));
		$pdo_statement->expects($this->once())
			->method("rowCount")
			->will($this->returnValue(1));

		$pdo = $this->getMock("\PDO",
			array("prepare"),
			array("mysql:"));
		$pdo->expects($this->once())
			->method("prepare")
			->with($this->equalTo(
				"INSERT INTO user " .
				"(userId, lastName, firstName, emailAddress, phone, birthDate) " .
				"VALUES(:userId, :lastName, :firstName, :email, :phone, " .
					":birthDate)"))
			->will($this->returnValue($pdo_statement));
		
		# Test set
		$object = new PdoModelUnitTest($security, $cache, $pdo,
			array("userId" => "",
				"firstName" => "Alberto",
				"lastName" => "Trevino",
				"email" => "alberto@byu.edu",
				"phone" => "801-555-1212",
				"birthDate" => "01 JUN 1960"));
	
		# Test save with insert
		$object->save();
		
		# Test get
		$this->assertEquals("Alberto", $object->firstName);
		$this->assertEquals("Trevino", $object->lastName);
		$this->assertEquals("alberto@byu.edu", $object->email);
		$this->assertEquals("801-555-1212", $object->phone);
		$this->assertInstanceOf("\\Cougar\\Util\\DateTime", $object->birthDate);
		$this->assertEquals("1960-06-01", (string) $object->birthDate);
		
		# Test getting the changed values
		$changes = $object->lastChanges();
		$this->assertArrayHasKey("firstName", $changes);
		$this->assertArrayHasKey("lastName", $changes);
		$this->assertArrayHasKey("email", $changes);
		$this->assertArrayHasKey("phone", $changes);
		$this->assertArrayHasKey("birthDate", $changes);
		
		$this->assertNull($changes["firstName"]);
		$this->assertNull($changes["lastName"]);
		$this->assertNull($changes["email"]);
		$this->assertNull($changes["phone"]);
		$this->assertNull($changes["birthDate"]);
	}

	/**
	 * @covers \Cougar\Model\PdoModel::__construct
	 * @covers \Cougar\Model\PdoModel::getRecord
	 * @covers \Cougar\Model\PdoModel::buildWhereClause
	 * @covers \Cougar\Model\PdoModel::getWhereParameters
	 * @covers \Cougar\Model\PdoModel::getCacheKey
	 * @covers \Cougar\Model\PdoModel::save
	 */
	public function testSaveUpdate() {
		$security = new Security();
		
		$cache = $this->getMock("\\Cougar\\Cache\\Cache");
		$cache->expects($this->once())
			->method("get")
			->with("unittest.model.12345")
			->will($this->returnValue(false));
		$cache->expects($this->exactly(2))
			->method("set")
			->will($this->returnValue(false));
		
		$pdo_statement_select = $this->getMock("\PDOStatement");
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
		
		$pdo_statement_update = $this->getMock("\PDOStatement");
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

		$pdo = $this->getMock("\PDO",
			array("prepare"),
			array("mysql:"));
		$pdo->expects($this->at(0))
			->method("prepare")
			->with($this->equalTo(
				"SELECT userId, lastName, firstName, emailAddress, phone, " .
					"birthDate " .
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
		
		$object = new PdoModelUnitTest($security, $cache, $pdo,
			array("userId" => "12345"));
		$object->firstName = "Alberto";
		$object->lastName = "Trevino";
		$object->email = "alberto@byu.edu";
		$object->phone = "801-555-1212";
		$object->save();
		
		# Test getting the changed values
		$changes = $object->lastChanges();
		$this->assertCount(4, $changes);
		$this->assertArrayHasKey("firstName", $changes);
		$this->assertArrayHasKey("lastName", $changes);
		$this->assertArrayHasKey("email", $changes);
		$this->assertArrayHasKey("phone", $changes);
		
		$this->assertEquals("", $changes["firstName"]);
		$this->assertEquals("", $changes["lastName"]);
		$this->assertEquals("", $changes["email"]);
		$this->assertEquals("", $changes["phone"]);
	}

	/**
	 * @covers \Cougar\Model\PdoModel::__construct
	 * @covers \Cougar\Model\PdoModel::getRecord
	 * @covers \Cougar\Model\PdoModel::buildWhereClause
	 * @covers \Cougar\Model\PdoModel::getWhereParameters
	 * @covers \Cougar\Model\PdoModel::getCacheKey
	 * @covers \Cougar\Model\PdoModel::save
	 */
	public function testSaveUpdateOnLoad() {
		$security = new Security();
		
		$cache = $this->getMock("\\Cougar\\Cache\\Cache");
		$cache->expects($this->once())
			->method("get")
			->with("unittest.model.12345")
			->will($this->returnValue(false));
		$cache->expects($this->exactly(2))
			->method("set")
			->will($this->returnValue(false));
		
		$pdo_statement_select = $this->getMock("\PDOStatement");
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
		
		$pdo_statement_update = $this->getMock("\PDOStatement");
		$pdo_statement_update->expects($this->once())
			->method("execute")
			->with($this->equalTo(array(
				"lastName" => "Trevino",
				"firstName" => "Alberto",
				"email" => "alberto@byu.edu",
				"userId" => 12345)))
			->will($this->returnValue(true));
		$pdo_statement_update->expects($this->once())
			->method("rowCount")
			->will($this->returnValue(1));

		$pdo = $this->getMock("\PDO",
			array("prepare"),
			array("mysql:"));
		$pdo->expects($this->at(0))
			->method("prepare")
			->with($this->equalTo(
				"SELECT userId, lastName, firstName, emailAddress, phone, " .
					"birthDate " .
				"FROM user WHERE userId = :userId"))
			->will($this->returnValue($pdo_statement_select));
		$pdo->expects($this->at(1))
			->method("prepare")
			->with($this->equalTo(
				"UPDATE user " .
				"SET lastName = :lastName, " .
					"firstName = :firstName, " .
					"emailAddress = :email " .
				"WHERE userId = :userId"))
			->will($this->returnValue($pdo_statement_update));
		
		$object = new PdoModelUnitTest($security, $cache, $pdo,
			array("userId" => "12345",
				"lastName" => "Trevino",
				"firstName" => "Alberto",
				"email" => "alberto@byu.edu"));
		$object->save();
		
		# Test getting the changed values
		$changes = $object->lastChanges();
		$this->assertCount(3, $changes);
		$this->assertArrayHasKey("firstName", $changes);
		$this->assertArrayHasKey("lastName", $changes);
		$this->assertArrayHasKey("email", $changes);
		
		$this->assertEquals("", $changes["firstName"]);
		$this->assertEquals("", $changes["lastName"]);
		$this->assertEquals("", $changes["email"]);
	}

	/**
	 * @covers \Cougar\Model\PdoModel::__construct
	 * @covers \Cougar\Model\PdoModel::getRecord
	 * @covers \Cougar\Model\PdoModel::buildWhereClause
	 * @covers \Cougar\Model\PdoModel::getWhereParameters
	 * @covers \Cougar\Model\PdoModel::getCacheKey
	 * @covers \Cougar\Model\PdoModel::save
	 * @expectedException \Cougar\Exceptions\Exception
	 */
	public function testUpdateReadOnlyProperty() {
		$security = new Security();
		
		$cache = $this->getMock("\\Cougar\\Cache\\Cache");
		$cache->expects($this->once())
			->method("get")
			->with("unittest.model.12345")
			->will($this->returnValue(false));
		
		$pdo_statement_select = $this->getMock("\PDOStatement");
		$pdo_statement_select->expects($this->once())
			->method("execute")
			->with($this->equalTo(array("userId" => 12345)))
			->will($this->returnValue(true));
		$pdo_statement_select->expects($this->at(1))
			->method("fetch")
			->will($this->returnValue(array(
				"userId" => "12345",
				"lastName" => "",
				"firstName" => "",
				"emailAddress" => "",
				"phone" => "")));
		$pdo_statement_select->expects($this->at(2))
			->method("fetch")
			->will($this->returnValue(false));
		
		$pdo = $this->getMock("\PDO",
			array("prepare"),
			array("mysql:"));
		$pdo->expects($this->at(0))
			->method("prepare")
			->with($this->equalTo(
				"SELECT userId, lastName, firstName, emailAddress, phone, " .
					"birthDate " .
				"FROM user WHERE userId = :userId"))
			->will($this->returnValue($pdo_statement_select));
		
		$object = new PdoModelUnitTest($security, $cache, $pdo,
			array("userId" => "12345"));
		$object->userId = "54321";
	}

	/**
	 * @covers \Cougar\Model\PdoModel::__construct
	 * @covers \Cougar\Model\PdoModel::getRecord
	 * @covers \Cougar\Model\PdoModel::buildWhereClause
	 * @covers \Cougar\Model\PdoModel::getWhereParameters
	 * @covers \Cougar\Model\PdoModel::getCacheKey
	 * @covers \Cougar\Model\PdoModel::delete
	 */
	public function testDelete() {
		$security = new Security();
		
		$cache = $this->getMock("\\Cougar\\Cache\\Cache");
		$cache->expects($this->once())
			->method("get")
			->with("unittest.model.12345")
			->will($this->returnValue(false));
		$cache->expects($this->once())
			->method("clear")
			->will($this->returnValue(false));
		
		$pdo_statement_select = $this->getMock("\PDOStatement");
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
		
		$pdo_statement_delete = $this->getMock("\PDOStatement");
		$pdo_statement_delete->expects($this->once())
			->method("execute")
			->with($this->equalTo(array("userId" => 12345)))
			->will($this->returnValue(true));
		$pdo_statement_delete->expects($this->exactly(2))
			->method("rowCount")
			->will($this->returnValue(1));

		$pdo = $this->getMock("\PDO",
			array("prepare"),
			array("mysql:"));
		$pdo->expects($this->at(0))
			->method("prepare")
			->with($this->equalTo(
				"SELECT userId, lastName, firstName, emailAddress, phone, " .
					"birthDate " .
				"FROM user WHERE userId = :userId"))
			->will($this->returnValue($pdo_statement_select));
		$pdo->expects($this->at(1))
			->method("prepare")
			->with($this->equalTo(
				"DELETE FROM user " .
				"WHERE userId = :userId"))
			->will($this->returnValue($pdo_statement_delete));
		
		$object = new PdoModelUnitTest($security, $cache, $pdo,
			array("userId" => "12345"));
		$object->delete();
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
		
		$object = new PdoModelUnitTest($security, $cache, $pdo);
		$object->userId = "userId value";
		$object->lastName = "lastName value";
		$object->firstName = "firstName value";
		$object->email = "email@somewhere.com";
		$object->phone = "phone value";
		
		$array = array();
		foreach($object as $key => $value)
		{
			$array[$key] = $value;
		}
		$this->assertEquals($object->__toArray(), $array);
	}
	
	/**
	 * @covers \Cougar\Model\PdoModel::__construct
	 * @covers \Cougar\Model\PdoModel::query
	 * 
	 * @todo Until the cache supports grouped clearing, caching of queries has
	 *       been disabled. Change the cache entries as necessary once it works.
	 */
	public function testQuery() {
		$parameters = array(
			new QueryParameter("firstName", "Alberto", "**"),
			new QueryParameter("lastName", "Trevino", "**")
		);
		
		$security = new Security();
		/*
		$cache = $this->getMock("\\Cougar\\Cache\\Cache");
		$cache->expects($this->once())
			->method("get")
			->with("unittest.model.query." .
				md5(serialize($parameters) . "array.__default__"))
			->will($this->returnValue(false));
		$cache->expects($this->once())
			->method("set")
			->will($this->returnValue(false));
		*/
		$cache = $this->getMock("\\Cougar\\Cache\\Cache");
		$cache->expects($this->never())
			->method("get");
		$cache->expects($this->never())
			->method("set");
		
		$pdo_statement = $this->getMock("\PDOStatement");
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
		
		$pdo = $this->getMock("\PDO",
			array("prepare"),
			array("mysql:"));
		$pdo->expects($this->once())
			->method("prepare")
			->with($this->equalTo(
				"SELECT userId, lastName, firstName, emailAddress AS email, " .
					"phone, birthDate " .
				"FROM user  WHERE firstName LIKE :firstName AND " .
					"lastName LIKE :lastName"))
			->will($this->returnValue($pdo_statement));
		
		$object = new PdoModelUnitTest($security, $cache, $pdo);
		$query_result = $object->query($parameters);
		$this->assertCount(1, $query_result);
		$this->assertArrayHasKey("userId", $query_result[0]);
		$this->assertEquals("12345", $query_result[0]["userId"]);
	}
	
	/**
	 * @covers \Cougar\Model\PdoModel::__construct
	 */
	public function testToArrayWithDefaultValues()
	{
		$security = new Security();
		
		$cache = $this->getMock("\\Cougar\\Cache\\Cache");
		
		$pdo = new PDO("mysql:");
		
		$object = new PdoModelUnitTest($security, $cache, $pdo);
		
		$this->assertEquals(array(
			"userId" => null,
			"lastName" => null,
			"firstName" => null,
			"email" => null,
			"phone" => null,
			"birthDate" => null), $object->__toArray());
	}
	
	/**
	 * @covers \Cougar\Model\PdoModel::__construct
	 * @expectedException \Cougar\Exceptions\BadRequestException
	 */
	public function testSaveWithDefaultValues()
	{
		$security = new Security();
		
		$cache = $this->getMock("\\Cougar\\Cache\\Cache");
		
		$pdo = new PDO("mysql:");
		
		$object = new PdoModelUnitTest($security, $cache, $pdo);
		$object->save();
	}
}

/**
 * Example PdoModel extension
 * 
 * Note: Annotations have inconsistent white space on purpose to validate
 * parsing routines.
 * 
 * @Table user
 *	@Allow  SELECT  INSERT UPDATE DELETE QUERY
 * @PrimaryKey userId
 * @CachePrefix unittest.model
 * @CacheTime	60
 */
class PdoModelUnitTest extends \Cougar\Model\PdoModel
{
	/**
	 * @ReadOnly
	 * @var int User ID
	 */
	public $userId;
	
	/**
	 * @NotNull
	 * @var string User's last name
	 */
	public $lastName;
	
	/**
	 * @NotNull
	 * @var string User's first name
	 */
	public $firstName;
	
	/**
	 * @Column emailAddress
	 * @Regex /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z0-9-]+$/i
	 * @NotNull
	 * @var string User's email address
	 */
	public $email;
	
	/**
	 * @var string User's phone number
	 */
	public $phone;
	
	/**
	 * @DateTimeFormat Date
	 * @var DateTime User's birth date
	 */
	public $birthDate;
}
