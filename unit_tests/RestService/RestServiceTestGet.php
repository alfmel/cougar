<?php

namespace Cougar\UnitTests\RestService;

use Cougar\RestService\RestService;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2012-12-10 at 12:02:16.
 */
class RestServiceTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var RestService
     */
    protected $object;

    public static function setUpBeforeClass()
    {
        require_once(__DIR__ . "/../../cougar.php");
    }

    /**
     * Sets up a fake GET request and create the object
     * @covers \Cougar\RestService\RestService::parseAcceptHeaderString
     * @covers \Cougar\RestService\RestService::buildAcceptString
     */
    protected function setUp() {
        # Set the parameters we want to test
        $_SERVER["SERVER_PROTOCOL"] = "HTTP/1.1";
        $_SERVER["SERVER_PORT"] = "80";
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] =
            "/path/to/resource/true?name1=value1&name2=value2&true=true";
        $_SERVER["PHP_SELF"] = "/request_handler";
        $_SERVER["HTTP_HOST"] = "localhost";
        $_SERVER["HTTP_USER_AGENT"] = "Sample user agent";
        $_SERVER["HTTP_ACCEPT"] = "application/json;version=2," .
            "application/json,application/xml;q=0.8,text/html;q=0.5";
        $_GET["name1"] = "value1";
        $_GET["name2"] = "value2";
        $_GET["true"] = "true";
        $_POST["name3"] = "value3";
        $_POST["name4"] = "value4";
        $_POST["name5"] = "true";
        
        # Create the object
        $this->object = new RestService();
    }

    /**
     * Tests the values that should be set by the constructor
     * @covers \Cougar\RestService\RestService::__construct
     */
    public function test__construct() {
        $this->assertArrayHasKey("_METHOD", $GLOBALS);
        $this->assertEquals("GET", $GLOBALS["_METHOD"]);
        $this->assertArrayHasKey("_PATH", $GLOBALS);
        $this->assertEquals("/path/to/resource/true", $GLOBALS["_PATH"]);
        $this->assertArrayHasKey("_URI", $GLOBALS);
        $this->assertEquals(array("path", "to", "resource", "true"),
            $GLOBALS["_URI"]);
    }

    /**
     * @covers \Cougar\RestService\RestService::method
     */
    public function testMethod() {
        $this->assertEquals("GET", $this->object->Method());
    }

    /**
     * @covers \Cougar\RestService\RestService::url
     */
    public function testUrl()
    {
        $this->assertEquals(
            "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"],
            $this->object->url());

        // Add SSL
        $_SERVER["HTTPS"] = "on";
        $this->assertEquals(
            "https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"],
            $this->object->url());
        unset($_SERVER["HTTPS"]);

        // Set a different port
        $_SERVER["SERVER_PORT"] = "8080";
        $this->assertEquals("http://" . $_SERVER["HTTP_HOST"] . ":8080" .
            $_SERVER["REQUEST_URI"], $this->object->url());
    }

    /**
     * @covers \Cougar\RestService\RestService::uri
     */
    public function testUri()
    {
        $this->assertEquals($_SERVER["REQUEST_URI"], $this->object->uri());
        $this->assertEquals(substr($_SERVER["REQUEST_URI"], 0,
            strpos($_SERVER["REQUEST_URI"], "?")), $this->object->uri(false));
    }

    /**
     * @covers \Cougar\RestService\RestService::headers
     */
    public function testHeaders() {
        $this->assertEquals(array("Host" => "localhost",
            "User-Agent" => "Sample user agent",
            #"Accept" => "text/html,application/xhtml+xml," .
            #    "application/xml;q=0.9,*/*;q=0.8"),
            "Accept" => "application/json;version=2,application/json," .
                "application/xml;q=0.8,text/html;q=0.5"),
            $this->object->Headers());
    }

    /**
     * @covers \Cougar\RestService\RestService::header
     */
    public function testHeader() {
        $this->assertEquals("localhost", $this->object->header("Host"));
        $this->assertEquals("Sample user agent",
            $this->object->header("User-Agent"));
        $this->assertEquals("Sample user agent",
            $this->object->header("USER-AGENT"));
        $this->assertEquals("Sample user agent",
            $this->object->header("user-agent"));
        $this->assertEquals("Sample user agent",
            $this->object->header("user-Agent"));
        #$this->assertEquals("text/html,application/xhtml+xml," .
        #        "application/xml;q=0.9,*/*;q=0.8",
        #    $this->object->header("Accept"));
        $this->assertEquals("application/json;version=2,application/json," .
                "application/xml;q=0.8,text/html;q=0.5",
            $this->object->header("Accept"));
        $this->assertEquals(0, $this->object->header("Host", "int"));
        $this->assertEquals(0.0, $this->object->header("Host", "float"));
        $this->assertEquals(false, $this->object->header("Host", "bool"));
        $this->assertEquals("localhost",
            $this->object->header("Host", "string", "no value"));
        $this->assertEquals("no value",
            $this->object->header("does not exist", "string", "no value"));
    }

    /**
     * @covers \Cougar\RestService\RestService::uriValue
     * @todo   Implement testUriValue().
     */
    public function testUriValue() {
        $this->assertEquals("path", $this->object->uriValue(0));
        $this->assertEquals("to", $this->object->uriValue(1));
        $this->assertEquals("resource", $this->object->uriValue(2));
        $this->assertNull($this->object->uriValue(4));
        $this->assertEquals(0, $this->object->uriValue(0, "int"));
        $this->assertEquals(0.0, $this->object->uriValue(0, "float"));
        $this->assertEquals(false, $this->object->uriValue(0, "bool"));
        $this->assertEquals("path",
            $this->object->uriValue(0, "string", "no value"));
        $this->assertEquals(true, $this->object->uriValue(3, "bool"));
        $this->assertEquals("no value",
            $this->object->uriValue(4, "string", "no value"));
    }

    /**
     * @covers \Cougar\RestService\RestService::getValue
     */
    public function testGetValue() {
        $this->assertEquals("value1", $this->object->getValue("name1"));
        $this->assertEquals("value2", $this->object->getValue("name2"));
        $this->assertNull($this->object->getValue("name3"));
        $this->assertEquals(0, $this->object->getValue("name1", "int"));
        $this->assertEquals(0.0, $this->object->getValue("name1", "float"));
        $this->assertEquals(false, $this->object->getValue("name1", "bool"));
        $this->assertEquals(true, $this->object->getValue("true", "bool"));
        $this->assertEquals("value1",
            $this->object->getValue("name1", "string", "no value"));
        $this->assertEquals("no value",
            $this->object->getValue("name3", "string", "no value"));
        $this->assertTrue($this->object->getValue("name1", "set"));
        $this->assertFalse($this->object->getValue("nameX", "set"));
    }

    /**
     * @covers \Cougar\RestService\RestService::getQuery
     */
    public function testGetQuery() {
        $params = $this->object->getQuery();
        $this->assertCount(3, $params);
        foreach($params as $param)
        {
            $this->assertInstanceOf("\\Cougar\\Util\\QueryParameter", $param);
        }
    }

    /**
     * @covers \Cougar\RestService\RestService::postValue
     */
    public function testPostValue() {
        $this->assertEquals("value3", $this->object->postValue("name3"));
        $this->assertEquals("value4", $this->object->postValue("name4"));
        $this->assertNull($this->object->postValue("name1"));
        $this->assertEquals(0, $this->object->postValue("name3", "int"));
        $this->assertEquals(0.0, $this->object->postValue("name3", "float"));
        $this->assertEquals(false, $this->object->postValue("name3", "bool"));
        $this->assertEquals(true, $this->object->postValue("name5", "bool"));
        $this->assertEquals("value3",
            $this->object->postValue("name3", "string", "no value"));
        $this->assertEquals("no value",
            $this->object->postValue("name1", "string", "no value"));
        $this->assertTrue($this->object->postValue("name3", "set"));
        $this->assertFalse($this->object->postValue("nameX", "set"));
    }

    /**
     * @covers \Cougar\RestService\RestService::body
     */
    public function testBody() {
        global $_BODY;
        $_BODY = "Sample text body";
        
        $this->assertEquals($_BODY, $this->object->body());
    }

    /**
     * @covers \Cougar\RestService\RestService::body
     */
    public function testBodyXml() {
        global $_BODY;
        
        $xml = new \SimpleXMLElement("<unit_test/>");
        $xml->addChild("key", "value");
        $_BODY = $xml->asXML();
        
        $this->assertEquals($xml, $this->object->body("xml"));
    }

    /**
     * @covers \Cougar\RestService\RestService::negotiateResponseType
     */
    public function testNegotiateResponseType() {
        $this->assertEquals(array("application/json"),
            $this->object->negotiateResponseType(array("application/json")));
        $this->assertEquals(array("application/json", "application/xml"),
            $this->object->negotiateResponseType(array("application/xml",
                "application/json")));
        $this->assertEquals(array("application/json;version=2"),
            $this->object->negotiateResponseType(
                array("application/json;version=2")));
        
        # Set up a new header
        # TODO: Add more test cases
    }

    /**
     * @covers \Cougar\RestService\RestService::sendResponse
     */
    public function testSendResponseBaisc() {
        $this->expectOutputString("Hello World!");
        $this->object->sendResponse(200, "Hello World!");
    }

    /**
     * @covers \Cougar\RestService\RestService::addTransactionCoordinator
     * @covers \Cougar\RestService\RestService::sendResponse
     */
    public function testSendResponseWithTransactionCoordinatorCommit() {
        $pdo = $this->getMock("\PDO",
            array("commit", "rollBack"),
            array("mysql:"));
        $pdo->expects($this->once())
            ->method("commit")
            ->will($this->returnValue(true));
        $pdo->expects($this->never())
            ->method("rollBack");
        
        $this->object->addTransactionCoordinator($pdo);
        
        $this->expectOutputString("Hello World!");
        $this->object->sendResponse(200, "Hello World!");
    }

    /**
     * @covers \Cougar\RestService\RestService::addTransactionCoordinator
     * @covers \Cougar\RestService\RestService::sendResponse
     */
    public function testSendResponseWithTransactionCoordinatorRollback() {
        $pdo = $this->getMock("\PDO",
            array("commit", "rollBack"),
            array("mysql:"));
        $pdo->expects($this->never())
            ->method("commit");
        $pdo->expects($this->once())
            ->method("rollBack")
            ->will($this->returnValue(true));
        
        $this->object->addTransactionCoordinator($pdo);
        
        $this->expectOutputString("500 World epxloded!");
        $this->object->sendResponse(500, "500 World epxloded!");
    }

    /**
     * @covers \Cougar\RestService\RestService::rawRequest
     * @todo   Implement testRawRequest().
     */
    public function testRawRequest() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
