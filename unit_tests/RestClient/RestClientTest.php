<?php

namespace Cougar\UnitTests\RestClient;

use Cougar\RestClient\RestClient;
use Cougar\Security\BasicHttpCredentialProvider;
use Cougar\Security\CookieHttpCredentialProvider;

/**
 * Test class for RestClient.
 * Generated by PHPUnit on 2012-07-28 at 19:16:45.
 *
 * Note: This test connects to localhost. Please create a symlink from your HTTP
 * directory to the web directory for full testing.
 */
class RestClientTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var \Cougar\RestClient\RestClient Rest client
     */
    protected $rest;

    /**
     * TODO: See if we can do this with an external service
     *
     * @var string Base URL
     */
    protected $restUrl = "http://localhost/RestClientTest/";

    /**
     * Creates the RestClient
     */
    protected function setup()
    {
        $this->rest = new RestClient("json");
    }

    public static function setUpBeforeClass()
    {
        require_once(__DIR__ . "/../../cougar.php");
    }

    /**
     * @covers \Cougar\RestClient\RestClient::get
     * @covers \Cougar\RestClient\RestClient::makeRequest
     */
    public function test__get()
    {
        $object = $this->rest->get($this->restUrl);
        $this->assertArrayHasKey("method", $object);
        $this->assertEquals("GET", $object["method"]);
        $this->assertArrayHasKey("url", $object);
        $this->assertCount(0, $object["url"]);
        $this->assertArrayHasKey("get", $object);
        $this->assertCount(0, $object["get"]);
        $this->assertArrayHasKey("post", $object);
        $this->assertCount(0, $object["post"]);
        $this->assertArrayHasKey("body", $object);
        $this->assertEquals("", $object["body"]);
        $this->assertArrayHasKey("headers", $object);
        $this->assertArrayNotHasKey("Content-Type", $object["headers"]);
    }

    /**
     * @covers \Cougar\RestClient\RestClient::get
     * @covers \Cougar\RestClient\RestClient::makeRequest
     */
    public function test__getWithUrlFields()
    {
        $object = $this->rest->get($this->restUrl,
            array("abc", "def", "ghi"));
        $this->assertArrayHasKey("method", $object);
        $this->assertEquals("GET", $object["method"]);
        $this->assertArrayHasKey("url", $object);
        $this->assertCount(3, $object["url"]);
        $this->assertEquals("abc", $object["url"][0]);
        $this->assertEquals("def", $object["url"][1]);
        $this->assertEquals("ghi", $object["url"][2]);
        $this->assertArrayHasKey("get", $object);
        $this->assertCount(0, $object["get"]);
        $this->assertArrayHasKey("post", $object);
        $this->assertCount(0, $object["post"]);
        $this->assertArrayHasKey("body", $object);
        $this->assertEquals("", $object["body"]);
        $this->assertArrayHasKey("headers", $object);
        $this->assertArrayNotHasKey("Content-Type", $object["headers"]);
    }
    
    /**
     * @covers \Cougar\RestClient\RestClient::get
     * @covers \Cougar\RestClient\RestClient::makeRequest
     */
    public function test__getWithGetFields()
    {
        $object = $this->rest->get($this->restUrl, null,
            array("value1" => "one",
                "value2" => "two",
                "value3" => "three"));
        $this->assertArrayHasKey("method", $object);
        $this->assertEquals("GET", $object["method"]);
        $this->assertArrayHasKey("url", $object);
        $this->assertCount(0, $object["url"]);
        $this->assertArrayHasKey("get", $object);
        $this->assertCount(3, $object["get"]);
        $this->assertArrayHasKey("value1", $object["get"]);
        $this->assertEquals("one", $object["get"]["value1"]);
        $this->assertArrayHasKey("value2", $object["get"]);
        $this->assertEquals("two", $object["get"]["value2"]);
        $this->assertArrayHasKey("value3", $object["get"]);
        $this->assertEquals("three", $object["get"]["value3"]);
        $this->assertArrayHasKey("post", $object);
        $this->assertCount(0, $object["post"]);
        $this->assertArrayHasKey("body", $object);
        $this->assertEquals("", $object["body"]);
        $this->assertArrayHasKey("headers", $object);
        $this->assertArrayNotHasKey("Content-Type", $object["headers"]);
    }
    
    /**
     * @covers \Cougar\RestClient\RestClient::get
     * @covers \Cougar\RestClient\RestClient::makeRequest
     */
    public function test__getWithUrlFieldsAndGetFields()
    {
        $object = $this->rest->get($this->restUrl,
            array("abc", "def", "ghi"),
            array("value1" => "one",
                "value2" => "two",
                "value3" => "three"));
        $this->assertArrayHasKey("method", $object);
        $this->assertEquals("GET", $object["method"]);
        $this->assertArrayHasKey("url", $object);
        $this->assertCount(3, $object["url"]);
        $this->assertEquals("abc", $object["url"][0]);
        $this->assertEquals("def", $object["url"][1]);
        $this->assertEquals("ghi", $object["url"][2]);
        $this->assertArrayHasKey("get", $object);
        $this->assertCount(3, $object["get"]);
        $this->assertArrayHasKey("value1", $object["get"]);
        $this->assertEquals("one", $object["get"]["value1"]);
        $this->assertArrayHasKey("value2", $object["get"]);
        $this->assertEquals("two", $object["get"]["value2"]);
        $this->assertArrayHasKey("value3", $object["get"]);
        $this->assertEquals("three", $object["get"]["value3"]);
        $this->assertArrayHasKey("post", $object);
        $this->assertCount(0, $object["post"]);
        $this->assertArrayHasKey("body", $object);
        $this->assertEquals("", $object["body"]);
        $this->assertArrayHasKey("headers", $object);
        $this->assertArrayNotHasKey("Content-Type", $object["headers"]);
    }
    
    /**
     * @covers \Cougar\RestClient\RestClient::get
     * @covers \Cougar\RestClient\RestClient::makeRequest
     */
    public function test__getWithStringBody()
    {
        $object = $this->rest->get($this->restUrl, null, null,
            "String body", "text/plain");
        $this->assertArrayHasKey("method", $object);
        $this->assertEquals("GET", $object["method"]);
        $this->assertArrayHasKey("url", $object);
        $this->assertCount(0, $object["url"]);
        $this->assertArrayHasKey("get", $object);
        $this->assertCount(0, $object["get"]);
        $this->assertArrayHasKey("post", $object);
        $this->assertCount(0, $object["post"]);
        $this->assertArrayHasKey("body", $object);
        $this->assertEquals("String body", $object["body"]);
        $this->assertArrayHasKey("headers", $object);
        $this->assertArrayHasKey("Content-Type", $object["headers"]);
        $this->assertEquals("text/plain", $object["headers"]["Content-Type"]);
    }
    
    /**
     * @covers \Cougar\RestClient\RestClient::get
     * @covers \Cougar\RestClient\RestClient::makeRequest
     */
    public function test__getWithArrayBody()
    {
        $object = $this->rest->get($this->restUrl, null, null,
            array("abc" => "123"));
        $this->assertArrayHasKey("method", $object);
        $this->assertEquals("GET", $object["method"]);
        $this->assertArrayHasKey("url", $object);
        $this->assertCount(0, $object["url"]);
        $this->assertArrayHasKey("get", $object);
        $this->assertCount(0, $object["get"]);
        $this->assertArrayHasKey("post", $object);
        $this->assertCount(0, $object["post"]);
        $this->assertArrayHasKey("body", $object);
        $this->assertEquals("abc=123", $object["body"]);
        $this->assertArrayHasKey("headers", $object);
        $this->assertArrayHasKey("Content-Type", $object["headers"]);
        $this->assertEquals("application/x-www-form-urlencoded",
            $object["headers"]["Content-Type"]);
    }
    
    /**
     * @covers \Cougar\RestClient\RestClient::post
     * @covers \Cougar\RestClient\RestClient::makeRequest
     */
    public function test__post()
    {
        $object = $this->rest->post($this->restUrl);
        $this->assertArrayHasKey("method", $object);
        $this->assertEquals("POST", $object["method"]);
        $this->assertArrayHasKey("url", $object);
        $this->assertCount(0, $object["url"]);
        $this->assertArrayHasKey("get", $object);
        $this->assertCount(0, $object["get"]);
        $this->assertArrayHasKey("post", $object);
        $this->assertCount(0, $object["post"]);
        $this->assertArrayHasKey("body", $object);
        $this->assertEquals("", $object["body"]);
        $this->assertArrayHasKey("headers", $object);
        $this->assertArrayNotHasKey("Content-Type", $object["headers"]);
    }
    
    /**
     * @covers \Cougar\RestClient\RestClient::post
     * @covers \Cougar\RestClient\RestClient::makeRequest
     */
    public function test__postWithArrayBody()
    {
        $object = $this->rest->post($this->restUrl, null, null,
            array("value1" => "one",
                "value2" => "two",
                "value3" => "three"));
        $this->assertArrayHasKey("method", $object);
        $this->assertEquals("POST", $object["method"]);
        $this->assertArrayHasKey("url", $object);
        $this->assertCount(0, $object["url"]);
        $this->assertArrayHasKey("get", $object);
        $this->assertCount(0, $object["get"]);
        $this->assertArrayHasKey("post", $object);
        $this->assertCount(3, $object["post"]);
        $this->assertArrayHasKey("value1", $object["post"]);
        $this->assertEquals("one", $object["post"]["value1"]);
        $this->assertArrayHasKey("value2", $object["post"]);
        $this->assertEquals("two", $object["post"]["value2"]);
        $this->assertArrayHasKey("value3", $object["post"]);
        $this->assertEquals("three", $object["post"]["value3"]);
        $this->assertArrayHasKey("body", $object);
        $this->assertEquals("value1=one&value2=two&value3=three",
            $object["body"]);
        $this->assertArrayHasKey("headers", $object);
        $this->assertArrayHasKey("Content-Type", $object["headers"]);
        $this->assertEquals("application/x-www-form-urlencoded",
            $object["headers"]["Content-Type"]);
    }
    
    /**
     * @covers \Cougar\RestClient\RestClient::post
     * @covers \Cougar\RestClient\RestClient::makeRequest
     */
    public function test__postWithArrayBodyMultipart()
    {
        $object = $this->rest->post($this->restUrl, null, null,
            array("value1" => "one",
                "value2" => "two",
                "value3" => "three"),
            "multipart/form-data");
        $this->assertArrayHasKey("method", $object);
        $this->assertEquals("POST", $object["method"]);
        $this->assertArrayHasKey("url", $object);
        $this->assertCount(0, $object["url"]);
        $this->assertArrayHasKey("get", $object);
        $this->assertCount(0, $object["get"]);
        $this->assertArrayHasKey("post", $object);
        $this->assertCount(3, $object["post"]);
        $this->assertArrayHasKey("value1", $object["post"]);
        $this->assertEquals("one", $object["post"]["value1"]);
        $this->assertArrayHasKey("value2", $object["post"]);
        $this->assertEquals("two", $object["post"]["value2"]);
        $this->assertArrayHasKey("value3", $object["post"]);
        $this->assertEquals("three", $object["post"]["value3"]);
        $this->assertArrayHasKey("body", $object);
        $this->assertEquals("", $object["body"]);
        $this->assertArrayHasKey("headers", $object);
        $this->assertArrayHasKey("Content-Type", $object["headers"]);
        $this->assertContains("multipart/form-data",
            $object["headers"]["Content-Type"]);
    }
    
    /**
     * @covers \Cougar\RestClient\RestClient::post
     * @covers \Cougar\RestClient\RestClient::makeRequest
     */
    public function test__postWithTextBody()
    {
        $object = $this->rest->post($this->restUrl, null, null,
            "This is the post body", "text/plain");
        $this->assertArrayHasKey("method", $object);
        $this->assertEquals("POST", $object["method"]);
        $this->assertArrayHasKey("url", $object);
        $this->assertCount(0, $object["url"]);
        $this->assertArrayHasKey("get", $object);
        $this->assertCount(0, $object["get"]);
        $this->assertArrayHasKey("post", $object);
        $this->assertCount(0, $object["post"]);
        $this->assertArrayHasKey("body", $object);
        $this->assertEquals("This is the post body", $object["body"]);
        $this->assertArrayHasKey("headers", $object);
        $this->assertArrayHasKey("Content-Type", $object["headers"]);
        $this->assertEquals("text/plain", $object["headers"]["Content-Type"]);
    }
    
    /**
     * @covers \Cougar\RestClient\RestClient::put
     * @covers \Cougar\RestClient\RestClient::makeRequest
     */
    public function test__put()
    {
        $object = $this->rest->put($this->restUrl);
        $this->assertArrayHasKey("method", $object);
        $this->assertEquals("PUT", $object["method"]);
        $this->assertArrayHasKey("url", $object);
        $this->assertCount(0, $object["url"]);
        $this->assertArrayHasKey("get", $object);
        $this->assertCount(0, $object["get"]);
        $this->assertArrayHasKey("post", $object);
        $this->assertCount(0, $object["post"]);
        $this->assertArrayHasKey("body", $object);
        $this->assertEquals("", $object["body"]);
        $this->assertArrayHasKey("headers", $object);
        $this->assertArrayNotHasKey("Content-Type", $object["headers"]);
    }
    
    /**
     * @covers \Cougar\RestClient\RestClient::put
     * @covers \Cougar\RestClient\RestClient::makeRequest
     */
    public function test__putWithArrayBody()
    {
        $object = $this->rest->put($this->restUrl, null, null,
            array("value1" => "one",
                "value2" => "two",
                "value3" => "three"));
        $this->assertArrayHasKey("method", $object);
        $this->assertEquals("PUT", $object["method"]);
        $this->assertArrayHasKey("url", $object);
        $this->assertCount(0, $object["url"]);
        $this->assertArrayHasKey("get", $object);
        $this->assertCount(0, $object["get"]);
        $this->assertArrayHasKey("post", $object);
        $this->assertCount(0, $object["post"]);
        $this->assertArrayHasKey("body", $object);
        $this->assertEquals("value1=one&value2=two&value3=three",
            $object["body"]);
        $this->assertArrayHasKey("headers", $object);
        $this->assertArrayHasKey("Content-Type", $object["headers"]);
        $this->assertEquals("application/x-www-form-urlencoded",
            $object["headers"]["Content-Type"]);
    }
    
    /**
     * @covers \Cougar\RestClient\RestClient::put
     * @covers \Cougar\RestClient\RestClient::makeRequest
     */
    public function test__putWithTextBody()
    {
        $object = $this->rest->put($this->restUrl, null, null,
            "This is the PUT body", "text/plain");
        $this->assertArrayHasKey("method", $object);
        $this->assertEquals("PUT", $object["method"]);
        $this->assertArrayHasKey("url", $object);
        $this->assertCount(0, $object["url"]);
        $this->assertArrayHasKey("get", $object);
        $this->assertCount(0, $object["get"]);
        $this->assertArrayHasKey("post", $object);
        $this->assertCount(0, $object["post"]);
        $this->assertArrayHasKey("body", $object);
        $this->assertEquals("This is the PUT body", $object["body"]);
        $this->assertArrayHasKey("headers", $object);
        $this->assertArrayHasKey("Content-Type", $object["headers"]);
        $this->assertEquals("text/plain", $object["headers"]["Content-Type"]);
    }

    /**
     * @covers \Cougar\RestClient\RestClient::put
     * @covers \Cougar\RestClient\RestClient::makeRequest
     */
    public function test__putWithFile()
    {
        $object = $this->rest->put($this->restUrl, null, null, "@" . __FILE__);
        $this->assertArrayHasKey("method", $object);
        $this->assertEquals("PUT", $object["method"]);
        $this->assertArrayHasKey("url", $object);
        $this->assertCount(0, $object["url"]);
        $this->assertArrayHasKey("get", $object);
        $this->assertCount(0, $object["get"]);
        $this->assertArrayHasKey("post", $object);
        $this->assertCount(0, $object["post"]);
        $this->assertArrayHasKey("body", $object);
        $this->assertEquals(filesize(__FILE__), strlen($object["body"]));
        $this->assertArrayHasKey("headers", $object);
        $this->assertArrayNotHasKey("Content-Type", $object["headers"]);
    }

    /**
     * @covers \Cougar\RestClient\RestClient::put
     * @covers \Cougar\RestClient\RestClient::makeRequest
     */
    public function test__putWithFileAndMimeType()
    {
        $object = $this->rest->put($this->restUrl, null, null, "@" . __FILE__,
            "text/plain");
        $this->assertArrayHasKey("method", $object);
        $this->assertEquals("PUT", $object["method"]);
        $this->assertArrayHasKey("url", $object);
        $this->assertCount(0, $object["url"]);
        $this->assertArrayHasKey("get", $object);
        $this->assertCount(0, $object["get"]);
        $this->assertArrayHasKey("post", $object);
        $this->assertCount(0, $object["post"]);
        $this->assertArrayHasKey("body", $object);
        $this->assertEquals(filesize(__FILE__), strlen($object["body"]));
        $this->assertArrayHasKey("headers", $object);
        $this->assertArrayHasKey("Content-Type", $object["headers"]);
        $this->assertEquals("text/plain", $object["headers"]["Content-Type"]);
    }

    /**
     * @covers \Cougar\RestClient\RestClient::delete
     * @covers \Cougar\RestClient\RestClient::makeRequest
     */
    public function test__delete()
    {
        $object = $this->rest->delete($this->restUrl);
        $this->assertArrayHasKey("method", $object);
        $this->assertEquals("DELETE", $object["method"]);
        $this->assertArrayHasKey("url", $object);
        $this->assertCount(0, $object["url"]);
        $this->assertArrayHasKey("get", $object);
        $this->assertCount(0, $object["get"]);
        $this->assertArrayHasKey("post", $object);
        $this->assertCount(0, $object["post"]);
        $this->assertArrayHasKey("body", $object);
        $this->assertEquals("", $object["body"]);
        $this->assertArrayHasKey("headers", $object);
        $this->assertArrayNotHasKey("Content-Type", $object["headers"]);
    }

    /**
     * @covers \Cougar\RestClient\RestClient::delete
     * @covers \Cougar\RestClient\RestClient::makeRequest
     */
    public function test__deleteWithArrayBody()
    {
        $object = $this->rest->delete($this->restUrl, null, null,
            array("value1" => "one",
                "value2" => "two",
                "value3" => "three"));
        $this->assertArrayHasKey("method", $object);
        $this->assertEquals("DELETE", $object["method"]);
        $this->assertArrayHasKey("url", $object);
        $this->assertCount(0, $object["url"]);
        $this->assertArrayHasKey("get", $object);
        $this->assertCount(0, $object["get"]);
        $this->assertArrayHasKey("post", $object);
        $this->assertCount(0, $object["post"]);
        $this->assertArrayHasKey("body", $object);
        $this->assertEquals("value1=one&value2=two&value3=three",
            $object["body"]);
        $this->assertArrayHasKey("headers", $object);
        $this->assertArrayHasKey("Content-Type", $object["headers"]);
        $this->assertEquals("application/x-www-form-urlencoded",
            $object["headers"]["Content-Type"]);
    }
    
    /**
     * @covers \Cougar\RestClient\RestClient::delete
     * @covers \Cougar\RestClient\RestClient::makeRequest
     */
    public function test__deleteWithTextBody()
    {
        $object = $this->rest->delete($this->restUrl, null, null,
            "This is the DELETE body", "text/plain");
        $this->assertArrayHasKey("method", $object);
        $this->assertEquals("DELETE", $object["method"]);
        $this->assertArrayHasKey("url", $object);
        $this->assertCount(0, $object["url"]);
        $this->assertArrayHasKey("get", $object);
        $this->assertCount(0, $object["get"]);
        $this->assertArrayHasKey("post", $object);
        $this->assertCount(0, $object["post"]);
        $this->assertArrayHasKey("body", $object);
        $this->assertEquals("This is the DELETE body", $object["body"]);
        $this->assertArrayHasKey("headers", $object);
        $this->assertArrayHasKey("Content-Type", $object["headers"]);
        $this->assertEquals("text/plain", $object["headers"]["Content-Type"]);
    }

    /**
     * @covers \Cougar\RestClient\RestClient::get
     * @covers \Cougar\RestClient\RestClient::addCredentialProvider
     * @covers \Cougar\RestClient\RestClient::makeRequest
     */
    public function testGetWithBasicHttpAuthentication()
    {
        // Set the username and password
        $username = "some_user";
        $password = "some_password";
        $credential_provider =
            new BasicHttpCredentialProvider($username, $password);

        // Add the credential provider
        $this->rest->addCredentialProvider($credential_provider);

        $object = $this->rest->get($this->restUrl);
        $this->assertArrayHasKey("method", $object);
        $this->assertEquals("GET", $object["method"]);
        $this->assertArrayHasKey("url", $object);
        $this->assertCount(0, $object["url"]);
        $this->assertArrayHasKey("get", $object);
        $this->assertCount(0, $object["get"]);
        $this->assertArrayHasKey("post", $object);
        $this->assertCount(0, $object["post"]);
        $this->assertArrayHasKey("body", $object);
        $this->assertEquals("", $object["body"]);
        $this->assertArrayHasKey("headers", $object);
        $this->assertArrayNotHasKey("Content-Type", $object["headers"]);
        $this->assertArrayHasKey("Authorization", $object['headers']);
        $this->assertEquals(
            "Basic " . base64_encode($username . ":" . $password),
            $object["headers"]["Authorization"]);
    }

    /**
     * @covers \Cougar\RestClient\RestClient::get
     * @covers \Cougar\RestClient\RestClient::addCredentialProvider
     * @covers \Cougar\RestClient\RestClient::makeRequest
     */
    public function testGetWithCookieHttpAuthentication()
    {
        // Set the username and password
        $session_cookie = array("SESSIONID" => "abc123");
        $credential_provider =
            new CookieHttpCredentialProvider($session_cookie);

        // Add the credential provider
        $this->rest->addCredentialProvider($credential_provider);

        $object = $this->rest->get($this->restUrl);
        $this->assertArrayHasKey("method", $object);
        $this->assertEquals("GET", $object["method"]);
        $this->assertArrayHasKey("url", $object);
        $this->assertCount(0, $object["url"]);
        $this->assertArrayHasKey("get", $object);
        $this->assertCount(0, $object["get"]);
        $this->assertArrayHasKey("post", $object);
        $this->assertCount(0, $object["post"]);
        $this->assertArrayHasKey("body", $object);
        $this->assertEquals("", $object["body"]);
        $this->assertArrayHasKey("headers", $object);
        $this->assertArrayNotHasKey("Content-Type", $object["headers"]);
        $this->assertArrayHasKey("cookies", $object);
        $this->assertArrayHasKey("SESSIONID", $object['cookies']);
        $this->assertEquals("abc123", $object["cookies"]["SESSIONID"]);
    }
}
?>
