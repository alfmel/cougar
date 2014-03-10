<?php

namespace Cougar\UnitTests\PDO;

use Cougar\PDO\PDOFactory;
use Cougar\Util\Arc4;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2013-02-20 at 13:16:44.
 */
class PDOFactoryTest extends \PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass()
    {
        require_once(__DIR__ . "/../../cougar.php");
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        # Set up the encryption parameters
        Arc4::setKey("0123456789abcdeffedcba9876543210");
        Arc4::setMagic("MAGIC");
        Arc4::useCompression("true");
    }

    /**
     * @covers \Cougar\PDO\PDOFactory::createConnectionFile
     */
    public function testCreateConnectionFile() {
        PDOFactory::createConnectionFile("UnitTest", "unit_test",
            "mysql:host=localhost;dbname=UnitTest", "root", "");
        $this->assertTrue(file_exists("unittest.unit_test.conf"));
    }

    /**
     * @covers \Cougar\PDO\PDOFactory::getConnection
     * @todo   Implement testGetConnection().
     */
    public function testGetConnection() {
        /*
        $pdo_mock = $this->getMockBuilder("\Cougar\PDO\PDO")
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->getMock();
        $pdo_mock->expects($this->once())
            ->method("__construct")
            ->with($this->equalTo("mysql:host=localhost;dbname=UnitTest"),
                $this->equalTo("root"),
                $this->equalTo(""));
         */
        
        # Requires a database to test against
        $this->assertInstanceOf("Cougar\\PDO\\PDO",
            PDOFactory::getConnection("UnitTest", "unit_test"));
        
        unlink("unittest.unit_test.conf");
    }
}
?>
