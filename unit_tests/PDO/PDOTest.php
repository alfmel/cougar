<?php

namespace Cougar\UnitTests\PDO;

use Cougar\PDO\PDO;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2013-01-17 at 15:30:09.
 * 
 * Note: this unit test does not use the DbUnit extension because we are testing
 * the database library, not the database results.
 */
class PDOTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var PDO
     */
    protected $object;
    
    /**
     * @var dsn used for testing
     */
    protected $dsn = "mysql:host=localhost;dbname=UnitTest";
    
    /**
     * @var username to use
     */
    protected $username = "root";

    /**
     * @var password
     */
    protected $password = "";
    
    /**
     * @var data to use during tests
     */
    protected $data = array(
        array("binaryValue" => "Binary value 1",
            "stringValue" => "String Value 1",
            "integerValue" => 1,
            "floatValue" => 1.1,
            "blobValue" => "Blob 1",
            "dateTimeValue" => "2013-01-01 13:01:00"),
        array("binaryValue" => "Binary value 2",
            "stringValue" => "String Value 2",
            "integerValue" => 2,
            "floatValue" => 2.2,
            "blobValue" => "Blob 2",
            "dateTimeValue" => "2013-02-02 14:02:00"),
        array("binaryValue" => "Binary value 3",
            "stringValue" => "String Value 3",
            "integerValue" => 3,
            "floatValue" => 3.3,
            "blobValue" => "Blob 3",
            "dateTimeValue" => "2013-03-03 15:03:00"),
        array("binaryValue" => "Binary value 4",
            "stringValue" => "String Value 4",
            "integerValue" => 4,
            "floatValue" => 4.4,
            "blobValue" => "Blob 4",
            "dateTimeValue" => "2013-04-04 16:04:00"),
        array("binaryValue" => "Binary value 5",
            "stringValue" => "String Value 5",
            "integerValue" => 5,
            "floatValue" => 5.5,
            "blobValue" => "Blob 5",
            "dateTimeValue" => "2013-05-05 17:05:00")
    );

    public static function setUpBeforeClass()
    {
        require_once(__DIR__ . "/../../cougar.php");
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     * 
     * @covers \Cougar\PDO\PDO::__construct
     * @covers \Cougar\PDO\PDO::establishConnection
     * @covers \Cougar\PDO\PDO::exec
     * @covers \Cougar\PDO\PDO::prepare
     * @covers \Cougar\PDO\PDO::commit
     */
    protected function setUp() {
        # Connect to the database
        $this->object = new PDO($this->dsn, $this->username, $this->password);
        
        # Create the temporary table
        $this->object->exec("CREATE TEMPORARY TABLE UnitTest (
            RowID int(10) unsigned NOT NULL AUTO_INCREMENT,
            BinaryValue varbinary(12) DEFAULT NULL,
            StringValue varchar(255) DEFAULT NULL,
            IntegerValue int(11) DEFAULT NULL,
            FloatValue double DEFAULT NULL,
            BlobValue mediumblob,
            DateTimeValue datetime DEFAULT NULL,
            LastModified timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (RowID))");
        
        # Insert some records
        $statement = $this->object->prepare("INSERT INTO UnitTest
            (BinaryValue, StringValue, IntegerValue, FloatValue, BlobValue,
                DateTimeValue)
            VALUES(:binaryValue, :stringValue, :integerValue, :floatValue,
                :blobValue, :dateTimeValue)");
        foreach($this->data as $row)
        {
            $statement->execute($row);
        }
        
        $this->object->commit();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     * 
     * @covers \Cougar\PDO\PDO::__destruct
     */
    protected function tearDown() {
        unset($this->object);
    }

    /**
     * @covers \Cougar\PDO\PDO::queryFetchAll
     * @covers \Cougar\PDO\PDO::establishConnection
     */
    public function testqueryFetchAll() {
        $data = $this->object->queryFetchAll("SELECT * FROM UnitTest");
        $this->assertCount(5, $data);
        $this->assertCount(8, $data[0]);
    }

    /**
     * @covers \Cougar\PDO\PDO::queryFetchAll
     * @covers \Cougar\PDO\PDO::establishConnection
     */
    public function testqueryFetchAllWithParameters() {
        $data = $this->object->queryFetchAll(
            "SELECT RowID, IntegerValue, DateTimeValue, LastModified
            FROM UnitTest WHERE IntegerValue > :IntegerValue",
            array("IntegerValue" => 2));
        $this->assertCount(3, $data);
        $this->assertCount(4, $data[0]);
        
        $data = $this->object->queryFetchAll(
            "SELECT RowID, IntegerValue, DateTimeValue, LastModified
            FROM UnitTest WHERE StringValue LIKE :StringValue",
            array("StringValue" => 'String Value%'));
        $this->assertCount(5, $data);
        $this->assertCount(4, $data[0]);
    }

    /**
     * @covers \Cougar\PDO\PDO::queryFetchAll
     * @covers \Cougar\PDO\PDO::establishConnection
     */
    public function testqueryFetchAllClass() {
        $data = $this->object->queryFetchAll("SELECT RowID, StringValue,
            IntegerValue FROM UnitTest", null, \PDO::FETCH_CLASS,
            "Cougar\UnitTests\PDO\Stuff");
        $this->assertCount(5, $data);
        $this->assertInstanceOf("Cougar\\UnitTests\\PDO\\Stuff", $data[0]);
    }

    /**
     * @covers \Cougar\PDO\PDO::queryFetchAll
     * @covers \Cougar\PDO\PDO::establishConnection
     */
    public function testqueryFetchAllNoResult() {
        $data = $this->object->queryFetchAll("SELECT * FROM UnitTest
            WHERE RowID = 'abc'");
        $this->assertCount(0, $data);
    }
    
    /**
     * @covers \Cougar\PDO\PDO::queryFetchAll
     * @covers \Cougar\PDO\PDO::queryFetchRow
     * @covers \Cougar\PDO\PDO::establishConnection
     */
    public function testqueryFetchRow() {
        $data = $this->object->queryFetchAll("SELECT * FROM UnitTest");
        $this->assertCount(5, $data);
        $this->assertCount(8, $data[0]);
        $row_id = $data[0]["RowID"];
        
        $row = $this->object->queryFetchRow("SELECT * FROM UnitTest
            WHERE RowID = :row_id",
            array("row_id" => $row_id));
        $this->assertEquals($data[0], $row);
    }

    /**
     * @covers \Cougar\PDO\PDO::queryFetchRow
     * @covers \Cougar\PDO\PDO::establishConnection
     */
    public function testqueryFetchRowClass() {
        $data = $this->object->queryFetchRow("SELECT RowID, StringValue,
            IntegerValue FROM UnitTest WHERE RowID = 1", null,
            \PDO::FETCH_CLASS, "Cougar\\UnitTests\\PDO\\Stuff");
        $this->assertInstanceOf("Cougar\\UnitTests\\PDO\\Stuff", $data);
    }

    /**
     * @covers \Cougar\PDO\PDO::queryFetchRow
     * @covers \Cougar\PDO\PDO::establishConnection
     */
    public function testqueryFetchRowNoResult() {
        $data = $this->object->queryFetchAll("SELECT * FROM UnitTest
            WHERE RowID = 'abc'");
        $this->assertCount(0, $data);
    }

    /**
     * @covers \Cougar\PDO\PDO::queryFetchRow
     * @covers \Cougar\PDO\PDO::establishConnection
     */
    public function testqueryFetchRowObjNoResult() {
        $data = $this->object->queryFetchRow("SELECT * FROM UnitTest
            WHERE RowID = 'abc'", null, \PDO::FETCH_OBJ);
        $this->assertNull($data);
    }

    /**
     * @covers \Cougar\PDO\PDO::queryFetchRow
     * @covers \Cougar\PDO\PDO::establishConnection
     */
    public function testqueryFetchRowInto() {
        $stuff = new Stuff();
        $this->object->queryFetchRow("SELECT RowID, StringValue,
            IntegerValue FROM UnitTest WHERE RowID = 1", null,
            \PDO::FETCH_INTO, $stuff);
        $this->assertEquals(1, (int) $stuff->IntegerValue);
    }

    /**
     * @covers \Cougar\PDO\PDO::queryFetchRow
     * @covers \Cougar\PDO\PDO::establishConnection
     */
    public function testqueryFetchRowObj() {
        $stuff = new Stuff();
        $data = $this->object->queryFetchRow("SELECT RowID, StringValue,
            IntegerValue FROM UnitTest WHERE RowID = 1", null,
            \PDO::FETCH_OBJ);
        $this->assertInstanceOf("stdClass", $data);
    }

    /**
     * @covers \Cougar\PDO\PDO::queryFetchAll
     * @covers \Cougar\PDO\PDO::queryFetchRow
     * @covers \Cougar\PDO\PDO::establishConnection
     * @expectedException \Cougar\Exceptions\Exception
     */
    public function testqueryFetchRowMultipleRows() {
        $data = $this->object->queryFetchAll("SELECT * FROM UnitTest");
        $this->assertCount(5, $data);
        $this->assertCount(8, $data[0]);
        $row_id = $data[1]["RowID"];
        
        $row = $this->object->queryFetchRow("SELECT * FROM UnitTest
            WHERE RowID <= :row_id",
            array("row_id" => $row_id));
        $this->fail("Should not have received a row");
    }
    
    /**
     * @covers \Cougar\PDO\PDO::queryFetchAll
     * @covers \Cougar\PDO\PDO::queryFetchColumn
     * @covers \Cougar\PDO\PDO::establishConnection
     */
    public function testqueryFetchColumn() {
        $data = $this->object->queryFetchAll("SELECT * FROM UnitTest");
        $this->assertCount(5, $data);
        $this->assertCount(8, $data[0]);
        $row_id = $data[0]["RowID"];
        
        $value = $this->object->queryFetchColumn("SELECT LastModified
            FROM UnitTest WHERE RowID = :row_id",
            array("row_id" => $row_id));
        $this->assertEquals($data[0]["LastModified"], $value);
    }

    /**
     * @covers \Cougar\PDO\PDO::queryFetchAll
     * @covers \Cougar\PDO\PDO::queryFetchColumn
     * @covers \Cougar\PDO\PDO::establishConnection
     * @expectedException \Cougar\Exceptions\Exception
     */
    public function testqueryFetchColumnMultipleRows() {
        $data = $this->object->queryFetchAll("SELECT * FROM UnitTest");
        $this->assertCount(5, $data);
        $this->assertCount(8, $data[0]);
        $row_id = $data[1]["RowID"];
        
        $row = $this->object->queryFetchColumn("
            SELECT LastModified FROM UnitTest
            WHERE RowID <= :row_id",
            array("row_id" => $row_id));
        $this->fail("Should not have received a value");
    }
    

    /**
     * @covers \Cougar\PDO\PDO::queryFetchAll
     * @covers \Cougar\PDO\PDO::queryFetchColumn
     * @covers \Cougar\PDO\PDO::establishConnection
     * @expectedException \Cougar\Exceptions\Exception
     */
    public function testqueryFetchColumnMultipleColumns() {
        $data = $this->object->queryFetchAll("SELECT * FROM UnitTest");
        $this->assertCount(5, $data);
        $this->assertCount(8, $data[0]);
        $row_id = $data[1]["RowID"];
        
        $row = $this->object->queryFetchColumn("SELECT RowID, LastModified
            FROM UnitTest WHERE RowID = :row_id",
            array("row_id" => $row_id));
        $this->fail("Should not have received a value");
    }

    /**
     * @covers \Cougar\PDO\PDO::queryFetchColumn
     * @covers \Cougar\PDO\PDO::establishConnection
     */
    public function testqueryFetchColumnNoResult() {
        $data = $this->object->queryFetchColumn("SELECT RowID FROM UnitTest
            WHERE RowID = 'abc'");
        $this->assertNull($data);
        
        $data = $this->object->queryFetchColumn("SELECT RowID FROM UnitTest
            WHERE RowID = 'abc'", null, \PDO::FETCH_OBJ);
        $this->assertNull($data);
    }
    
    /**
     * @covers \Cougar\PDO\PDO::exec
     * @covers \Cougar\PDO\PDO::queryFetchAll
     * @covers \Cougar\PDO\PDO::queryFetchRow
     * @covers \Cougar\PDO\PDO::queryFetchColumn
     * @covers \Cougar\PDO\PDO::lastInsertId
     * @covers \Cougar\PDO\PDO::establishConnection
     */
    public function testExec() {
        # Update
        $rows = $this->object->exec("UPDATE UnitTest SET BlobValue = 'Blob'");
        $this->assertEquals(5, $rows);

        # Get data
        $data = $this->object->queryFetchAll("SELECT * FROM UnitTest");
        $this->assertCount(5, $data);
        $this->assertCount(8, $data[0]);
        $row_id = $data[0]["RowID"];
        
        # Update with parameters
        $rows = $this->object->exec("UPDATE UnitTest
            SET FloatValue = :float_value WHERE RowID = :row_id",
            array("float_value" => 12.12, "row_id" => $row_id));
        $this->assertEquals(1, $rows);
        
        # Insert with parameters
        $date = date("Y-m-d H:i:s");
        $rows = $this->object->exec("INSERT INTO UnitTest
            (BinaryValue, StringValue, IntegerValue, FloatValue, BlobValue,
                DateTimeValue)
            VALUES(:binaryValue, :stringValue, :integerValue, :floatValue,
                :blobValue, :dateTimeValue)",
            array("binaryValue" => "Binary",
                "stringValue" => "String Value",
                "integerValue" => 15,
                "floatValue" => 15.15,
                "blobValue" => str_repeat("Xx", 10000),
                "dateTimeValue" => $date));
        $this->assertEquals(1, $rows);
        $row_id = $this->object->lastInsertId();
        
        $row = $this->object->queryFetchRow("SELECT * FROM UnitTest
            WHERE RowID = :row_id",
            array("row_id" => $row_id));
        $this->assertCount(8, $row);
        $this->assertEquals("Binary", $row["BinaryValue"]);
        $this->assertEquals("String Value", $row["StringValue"]);
        $this->assertEquals(15, $row["IntegerValue"]);
        $this->assertEquals(15.15, $row["FloatValue"]);
        $this->assertEquals(str_repeat("Xx", 10000), $row["BlobValue"]);
        $this->assertEquals($date, $row["DateTimeValue"]);
        
        # Delete with parameters
        $rows = $this->object->exec("DELETE FROM UnitTest
            WHERE RowID = :row_id",
            array("row_id" => $row_id));
        $this->assertEquals(1, $rows);
        $this->assertEquals(5, $this->object->queryFetchColumn(
            "SELECT COUNT(*) FROM UnitTest"));
    }
    
    /**
     * @covers \Cougar\PDO\PDO::exec
     * @covers \Cougar\PDO\PDO::lastInsertId
     * @covers \Cougar\PDO\PDO::queryFetchRow
     * @covers \Cougar\PDO\PDO::commit
     * @covers \Cougar\PDO\PDO::establishConnection
     */
    public function testCommit() {
        # Insert a record
        $date = date("Y-m-d H:i:s");
        $rows = $this->object->exec("INSERT INTO UnitTest
            (BinaryValue, StringValue, IntegerValue, FloatValue, BlobValue,
                DateTimeValue)
            VALUES(:binaryValue, :stringValue, :integerValue, :floatValue,
                :blobValue, :dateTimeValue)",
            array("binaryValue" => "Binary",
                "stringValue" => "String Value",
                "integerValue" => 15,
                "floatValue" => 15.15,
                "blobValue" => str_repeat("Xx", 10000),
                "dateTimeValue" => $date));
        $this->assertEquals(1, $rows);
        $row_id = $this->object->lastInsertId();
        
        # Commit
        $this->object->commit();
        
        # Roll back
        $this->object->rollBack();
        
        $row = $this->object->queryFetchRow("SELECT * FROM UnitTest
            WHERE RowID = :row_id",
            array("row_id" => $row_id));
        $this->assertCount(8, $row);
    }
    
    /**
     * @covers \Cougar\PDO\PDO::exec
     * @covers \Cougar\PDO\PDO::lastInsertId
     * @covers \Cougar\PDO\PDO::queryFetchRow
     * @covers \Cougar\PDO\PDO::rollBack
     * @covers \Cougar\PDO\PDO::establishConnection
     */
    public function testRollBack() {
        # Insert a record
        $date = date("Y-m-d H:i:s");
        $rows = $this->object->exec("INSERT INTO UnitTest
            (BinaryValue, StringValue, IntegerValue, FloatValue, BlobValue,
                DateTimeValue)
            VALUES(:binaryValue, :stringValue, :integerValue, :floatValue,
                :blobValue, :dateTimeValue)",
            array("binaryValue" => "Binary",
                "stringValue" => "String Value",
                "integerValue" => 15,
                "floatValue" => 15.15,
                "blobValue" => str_repeat("Xx", 10000),
                "dateTimeValue" => $date));
        $this->assertEquals(1, $rows);
        $row_id = $this->object->lastInsertId();
        
        # Roll back
        $this->object->rollBack();
        
        $row = $this->object->queryFetchRow("SELECT * FROM UnitTest
            WHERE RowID = :row_id",
            array("row_id" => $row_id));
        $this->assertCount(0, $row);
    }
    
    /**
     * @covers \Cougar\PDO\PDO::exec
     * @expectedException PDOException
     */
    public function testExecException() {
        $rows = $this->object->exec("INSERT INTO UnitTest VALUES(", array());
        $this->fail("Exception was not thrown");
    }
}

class Stuff
{
    public $RowID;
    public $StringValue;
    public $IntegerValue;
}