<?php

namespace Cougar\UnitTests\Model;

use Cougar\Model\PdoEnumeration;

require_once(__DIR__ . "/../../../cougar.php");

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2013-07-25 at 14:55:47.
 */
class PdoEnumerationMySQLTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var PdoEnumeration
	 */
	protected $object;

    /**
     * @var \PDO Database connection
     */
    protected $pdo;

	/**
	 * Create the necessary tables
	 */
	protected function setUp() { 
		$this->pdo = new \PDO("mysql:host=localhost;dbname=UnitTest",
			"root", "");

		$this->pdo->exec("
			CREATE TEMPORARY TABLE IF NOT EXISTS tbl1 (
				col_one VARCHAR(10),
				col_two SMALLINT)");
		$this->pdo->exec("INSERT INTO tbl1 VALUES('hello!', 10)");
		$this->pdo->exec("INSERT INTO tbl1 VALUES('goodbye', 20)");
		
		$this->pdo->exec("CREATE TEMPORARY TABLE tbl2 (
			f1 VARCHAR(30) PRIMARY KEY,
			f2 VARCHAR(15),
			f3 DOUBLE)");
		$this->pdo->exec("INSERT INTO tbl2 VALUES('foo', 'bar', 5.5)");
		$this->object = new PdoEnumeration($this->pdo);
	}

	/**
	 * Remove the tables
	 */
	protected function tearDown() {
		$this->pdo->exec("DROP TABLE tbl1");
		$this->pdo->exec("DROP TABLE tbl2");
	}

	/**
	 * @covers \Cougar\Model\PdoEnumeration::getTables
	 */
	public function testGetTable() {
		$tables = $this->object->getTables();	
		// Verify both tables are listed
	}

	/**
	 * @covers \Cougar\Model\PdoEnumeration::getColumns
	 */
	public function testGetColumnName() {
		$columns_tbl1 = $this->object->getColumns("tbl1");
		// Verify all the columns from tbl1 are here
		
		$columns_tbl2 = $this->object->getColumns("tbl2");
		// Verify all the columns from tbl2 are here
	}
}
