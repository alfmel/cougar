<?php

namespace Cougar\UnitTests\RestService;

use Cougar\Security\Security;
use Cougar\RestService\AnnotatedRestService;
use Cougar\Util\Xml;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2013-03-18 at 12:50:03.
 */
class AnnotatedRestServiceTestPost extends \PHPUnit_Framework_TestCase {

    /**
     * @var AnnotatedRestService
     */
    protected $object;

    public static function setUpBeforeClass()
    {
        require_once(__DIR__ . "/../../cougar.php");
    }

    /**
     * @covers \Cougar\RestService\AnnotatedRestService::bindFromObject
     * @covers \Cougar\RestService\AnnotatedRestService::handleRequest
     */
    public function testSimpleCase() {
        $_SERVER["SERVER_PROTOCOL"] = "HTTP/1.1";
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["REQUEST_URI"] = "/post/SimpleCase";
        $_SERVER["PHP_SELF"] = "/request_handler";
        $_SERVER["HTTP_HOST"] = "localhost";
        $_SERVER["HTTP_ACCEPT"] = "application/vnd.php.serialized";
        
        $object = new AnnotatedRestServicePostTests();
        $this->expectOutputString(serialize($object->simpleCase()));
        
        $service = new AnnotatedRestService(new Security());
        $service->bindFromObject($object);
        $service->handleRequest();
    }

    /**
     * @covers \Cougar\RestService\AnnotatedRestService::bindFromObject
     * @covers \Cougar\RestService\AnnotatedRestService::handleRequest
     */
    public function testSingleUriArgument() {
        $_SERVER["SERVER_PROTOCOL"] = "HTTP/1.1";
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["REQUEST_URI"] = "/post/SingleUriArgument/uri1";
        $_SERVER["PHP_SELF"] = "/request_handler";
        $_SERVER["HTTP_HOST"] = "localhost";
        $_SERVER["HTTP_ACCEPT"] = "application/vnd.php.serialized";
        
        $object = new AnnotatedRestServicePostTests();
        $this->expectOutputString(serialize(
            $object->singleUriArgument("uri1")));
        
        $service = new AnnotatedRestService(new Security());
        $service->bindFromObject($object);
        $service->handleRequest();
    }

    /**
     * @covers \Cougar\RestService\AnnotatedRestService::bindFromObject
     * @covers \Cougar\RestService\AnnotatedRestService::handleRequest
     */
    public function testMultiUriArgument() {
        $_SERVER["SERVER_PROTOCOL"] = "HTTP/1.1";
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["REQUEST_URI"] = "/post/MultiUriArgument/uri1/uri2/uri3";
        $_SERVER["PHP_SELF"] = "/request_handler";
        $_SERVER["HTTP_HOST"] = "localhost";
        $_SERVER["HTTP_ACCEPT"] = "application/vnd.php.serialized";
        
        $object = new AnnotatedRestServicePostTests();
        $this->expectOutputString(serialize(
            $object->multiUriArgument("uri1", "uri2", "uri3")));
        
        $service = new AnnotatedRestService(new Security());
        $service->bindFromObject($object);
        $service->handleRequest();
    }

    /**
     * @covers \Cougar\RestService\AnnotatedRestService::bindFromObject
     * @covers \Cougar\RestService\AnnotatedRestService::handleRequest
     */
    public function testMultiUriArgumentPlus() {
        $_SERVER["SERVER_PROTOCOL"] = "HTTP/1.1";
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["REQUEST_URI"] = "/post/MultiUriArgument/plus/uri1/uri2/uri3";
        $_SERVER["PHP_SELF"] = "/request_handler";
        $_SERVER["HTTP_HOST"] = "localhost";
        $_SERVER["HTTP_ACCEPT"] = "application/vnd.php.serialized";
        
        $object = new AnnotatedRestServicePostTests();
        $this->expectOutputString(serialize(
            $object->multiUriArgumentPlus(array("uri1", "uri2", "uri3"))));
        
        $service = new AnnotatedRestService(new Security());
        $service->bindFromObject($object);
        $service->handleRequest();
    }
    
    /**
     * @covers \Cougar\RestService\AnnotatedRestService::bindFromObject
     * @covers \Cougar\RestService\AnnotatedRestService::handleRequest
     */
    public function testMultiUriArgumentWithLiteral() {
        $_SERVER["SERVER_PROTOCOL"] = "HTTP/1.1";
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["REQUEST_URI"] = "/post/MultiUriArgument/uri1/literal/uri2";
        $_SERVER["PHP_SELF"] = "/request_handler";
        $_SERVER["HTTP_HOST"] = "localhost";
        $_SERVER["HTTP_ACCEPT"] = "application/vnd.php.serialized";
        
        $object = new AnnotatedRestServicePostTests();
        $this->expectOutputString(serialize(
            $object->multiUriArgumentWithLiteral("uri1", "uri2")));
        
        $service = new AnnotatedRestService(new Security());
        $service->bindFromObject($object);
        $service->handleRequest();
    }

    /**
     * @covers \Cougar\RestService\AnnotatedRestService::bindFromObject
     * @covers \Cougar\RestService\AnnotatedRestService::handleRequest
     */
    public function testEmptyResponse() {
        $_SERVER["SERVER_PROTOCOL"] = "HTTP/1.1";
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["REQUEST_URI"] = "/post/EmptyResponse";
        $_SERVER["PHP_SELF"] = "/request_handler";
        $_SERVER["HTTP_HOST"] = "localhost";
        $_SERVER["HTTP_ACCEPT"] = "application/vnd.php.serialized";
        
        $object = new AnnotatedRestServicePostTests();
        $this->expectOutputString("");
        
        $service = new AnnotatedRestService(new Security());
        $service->bindFromObject($object);
        $service->handleRequest();
    }
    
    /**
     * @covers \Cougar\RestService\AnnotatedRestService::bindFromObject
     * @covers \Cougar\RestService\AnnotatedRestService::handleRequest
     */
    public function testGetValueOneArgumentSpaceAtEnd()
    {
        $_SERVER["SERVER_PROTOCOL"] = "HTTP/1.1";
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["REQUEST_URI"] = "/post/GetValue/OneArg/Space/At/End";
        $_SERVER["PHP_SELF"] = "/request_handler";
        $_SERVER["HTTP_HOST"] = "localhost";
        $_SERVER["HTTP_ACCEPT"] = "application/vnd.php.serialized";
        $_GET["something"] = "foo";
        
        $object = new AnnotatedRestServicePostTests();
        $this->expectOutputString(serialize(
            $object->getValueOneArgumentSpaceAtEnd("foo")));
        
        $service = new AnnotatedRestService(new Security());
        $service->bindFromObject($object);
        $service->handleRequest();
    }
    
    /**
     * @covers \Cougar\RestService\AnnotatedRestService::bindFromObject
     * @covers \Cougar\RestService\AnnotatedRestService::handleRequest
     */
    public function testSingleGetArgumentFloat() {
        $_SERVER["SERVER_PROTOCOL"] = "HTTP/1.1";
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["REQUEST_URI"] = "/post/GetValue/OneArg/float";
        $_SERVER["PHP_SELF"] = "/request_handler";
        $_SERVER["HTTP_HOST"] = "localhost";
        $_SERVER["HTTP_ACCEPT"] = "application/vnd.php.serialized";
        $_GET["float"] = "3.5";
        
        $object = new AnnotatedRestServicePostTests();
        $this->expectOutputString(serialize(
            $object->getValueOneArgumentFloat(3.5)));
        
        $service = new AnnotatedRestService(new Security());
        $service->bindFromObject($object);
        $service->handleRequest();
    }
    
    /**
     * @covers \Cougar\RestService\AnnotatedRestService::bindFromObject
     * @covers \Cougar\RestService\AnnotatedRestService::handleRequest
     */
    public function testSingleGetArgumentString() {
        $_SERVER["SERVER_PROTOCOL"] = "HTTP/1.1";
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["REQUEST_URI"] = "/post/GetValue/OneArg/string";
        $_SERVER["PHP_SELF"] = "/request_handler";
        $_SERVER["HTTP_HOST"] = "localhost";
        $_SERVER["HTTP_ACCEPT"] = "application/vnd.php.serialized";
        $_GET["string"] = "three";
        
        $object = new AnnotatedRestServicePostTests();
        $this->expectOutputString(serialize(
            $object->getValueOneArgumentString('three')));
        
        $service = new AnnotatedRestService(new Security());
        $service->bindFromObject($object);
        $service->handleRequest();
    }
    
    /**
     * @covers \Cougar\RestService\AnnotatedRestService::bindFromObject
     * @covers \Cougar\RestService\AnnotatedRestService::handleRequest
     */
    public function testSingleGetArgumentInt() {
        $_SERVER["SERVER_PROTOCOL"] = "HTTP/1.1";
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["REQUEST_URI"] = "/post/GetValue/OneArg/int";
        $_SERVER["PHP_SELF"] = "/request_handler";
        $_SERVER["HTTP_HOST"] = "localhost";
        $_SERVER["HTTP_ACCEPT"] = "application/vnd.php.serialized";
        $_GET["int"] = "3";
        
        $object = new AnnotatedRestServicePostTests();
        $this->expectOutputString(serialize(
            $object->getValueOneArgumentInt(3)));
        
        $service = new AnnotatedRestService(new Security());
        $service->bindFromObject($object);
        $service->handleRequest();
    }
    
    /**
     * @covers \Cougar\RestService\AnnotatedRestService::bindFromObject
     * @covers \Cougar\RestService\AnnotatedRestService::handleRequest
     */
    public function testSingleGetArgumentBool() {
        $_SERVER["SERVER_PROTOCOL"] = "HTTP/1.1";
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["REQUEST_URI"] = "/post/GetValue/OneArg/bool";
        $_SERVER["PHP_SELF"] = "/request_handler";
        $_SERVER["HTTP_HOST"] = "localhost";
        $_SERVER["HTTP_ACCEPT"] = "application/vnd.php.serialized";
        $_GET["bool"] = "true";
        
        $object = new AnnotatedRestServicePostTests();
        $this->expectOutputString(serialize(
            $object->getValueOneArgumentBool(true)));
        
        $service = new AnnotatedRestService(new Security());
        $service->bindFromObject($object);
        $service->handleRequest();
    }
    
    /**
     * @covers \Cougar\RestService\AnnotatedRestService::bindFromObject
     * @covers \Cougar\RestService\AnnotatedRestService::handleRequest
     */
    public function testGetValueOnePostArgumentOnePostArgument()
    {
        $_SERVER["SERVER_PROTOCOL"] = "HTTP/1.1";
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["REQUEST_URI"] = "/post/GetValue/OneArg/Space/At/End";
        $_SERVER["PHP_SELF"] = "/request_handler";
        $_SERVER["HTTP_HOST"] = "localhost";
        $_SERVER["HTTP_ACCEPT"] = "application/vnd.php.serialized";
        $_GET["something"] = "foo";
        $_POST["somethingelse"] = "bar";
        
        $object = new AnnotatedRestServicePostTests();
        $this->expectOutputString(serialize(
            $object->getValueOneArgumentSpaceAtEnd("foo")));
        
        $service = new AnnotatedRestService(new Security());
        $service->bindFromObject($object);
        $service->handleRequest();
    }
    
    /**
     * @covers \Cougar\RestService\AnnotatedRestService::bindFromObject
     * @covers \Cougar\RestService\AnnotatedRestService::handleRequest
     * @expectedException \Cougar\Exceptions\MethodNotAllowedException
     */
    public function testPostWithGet() {
        $_SERVER["SERVER_PROTOCOL"] = "HTTP/1.1";
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/post/SimpleCase";
        $_SERVER["PHP_SELF"] = "/request_handler";
        $_SERVER["HTTP_HOST"] = "localhost";
        $_SERVER["HTTP_ACCEPT"] = "application/vnd.php.serialized";
        
        $object = new AnnotatedRestServicePostTests();
        $service = new AnnotatedRestService(new Security());
        $service->bindFromObject($object);
        $service->handleRequest();
        $this->fail("Expected exception was not thrown");
    }
    
    /**
     * @covers \Cougar\RestService\AnnotatedRestService::bindFromObject
     * @covers \Cougar\RestService\AnnotatedRestService::handleRequest
     * @expectedException \Cougar\Exceptions\BadRequestException
     */
    public function testNonExistantEndPoint() {
        $_SERVER["SERVER_PROTOCOL"] = "HTTP/1.1";
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["REQUEST_URI"] = "/whatever";
        $_SERVER["PHP_SELF"] = "/request_handler";
        $_SERVER["HTTP_HOST"] = "localhost";
        $_SERVER["HTTP_ACCEPT"] = "application/vnd.php.serialized";
        
        $object = new AnnotatedRestServicePostTests();
        $service = new AnnotatedRestService(new Security());
        $service->bindFromObject($object);
        $service->handleRequest();
        $this->fail("Expected exception was not thrown");
    }
    
    /**
     * @covers \Cougar\RestService\AnnotatedRestService::bindFromObject
     * @covers \Cougar\RestService\AnnotatedRestService::handleRequest
     */
    public function testPostBody() {
        $_SERVER["SERVER_PROTOCOL"] = "HTTP/1.1";
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["REQUEST_URI"] = "/post/with/body";
        $_SERVER["PHP_SELF"] = "/request_handler";
        $_SERVER["HTTP_HOST"] = "localhost";
        $_SERVER["HTTP_ACCEPT"] = "application/vnd.php.serialized";
        global $_BODY;
        $_BODY = "Sample Body";
        
        $object = new AnnotatedRestServicePostTests();
        $this->expectOutputString(serialize($object->PostBody($_BODY)));
        
        $service = new AnnotatedRestService(new Security());
        $service->bindFromObject($object);
        $service->handleRequest();
    }

    /**
     * @covers \Cougar\RestService\AnnotatedRestService::bindFromObject
     * @covers \Cougar\RestService\AnnotatedRestService::handleRequest
     * @expectedException \Cougar\Exceptions\BadRequestException
     */
    public function testOptionsWithPost() {
        $_SERVER["SERVER_PROTOCOL"] = "HTTP/1.1";
        $_SERVER["REQUEST_METHOD"] = "OPTIONS";
        $_SERVER["REQUEST_URI"] = "/post/SimpleCase";
        $_SERVER["PHP_SELF"] = "/request_handler";
        $_SERVER["HTTP_HOST"] = "localhost";
        $_SERVER["HTTP_ACCEPT"] = "application/vnd.php.serialized";
        
        $object = new AnnotatedRestServicePostTests();
        $service = new AnnotatedRestService(new Security());
        $service->bindFromObject($object);
        $service->handleRequest();
        $this->fail("Expected exception was not thrown");
    }

    /**
     * @covers \Cougar\RestService\AnnotatedRestService::bindFromObject
     * @covers \Cougar\RestService\AnnotatedRestService::handleRequest
     */
    public function testJson() {
        $_SERVER["SERVER_PROTOCOL"] = "HTTP/1.1";
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["REQUEST_URI"] = "/post/SimpleCase";
        $_SERVER["PHP_SELF"] = "/request_handler";
        $_SERVER["HTTP_HOST"] = "localhost";
        $_SERVER["HTTP_ACCEPT"] = "application/json";
        
        $object = new AnnotatedRestServicePostTests();
        $this->expectOutputString(json_encode($object->simpleCase()));
        
        $service = new AnnotatedRestService(new Security());
        $service->bindFromObject($object);
        $service->handleRequest();
    }
    
    /**
     * @covers \Cougar\RestService\AnnotatedRestService::bindFromObject
     * @covers \Cougar\RestService\AnnotatedRestService::handleRequest
     */
    public function testXML() {
        $_SERVER["SERVER_PROTOCOL"] = "HTTP/1.1";
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["REQUEST_URI"] = "/post/SimpleCase";
        $_SERVER["PHP_SELF"] = "/request_handler";
        $_SERVER["HTTP_HOST"] = "localhost";
        $_SERVER["HTTP_ACCEPT"] = "application/xml";
        
        $object = new AnnotatedRestServicePostTests();
        $this->expectOutputString(Xml::toXml($object->simpleCase())->asXML());
        
        $service = new AnnotatedRestService(new Security());
        $service->bindFromObject($object);
        $service->handleRequest();
    }
        
    /**
     * @covers \Cougar\RestService\AnnotatedRestService::bindFromObject
     * @covers \Cougar\RestService\AnnotatedRestService::handleRequest
     */
    public function testHTML() {
        $_SERVER["SERVER_PROTOCOL"] = "HTTP/1.1";
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["REQUEST_URI"] = "/post/SimpleCase";
        $_SERVER["PHP_SELF"] = "/request_handler";
        $_SERVER["HTTP_HOST"] = "localhost";
        $_SERVER["HTTP_ACCEPT"] = "text/html";
        
        $object = new AnnotatedRestServicePostTests();
        $this->expectOutputString(Xml::toXml($object->simpleCase())->asXML());
        
        $service = new AnnotatedRestService(new Security());
        $service->bindFromObject($object);
        $service->handleRequest();
    }
    
    /**
     * @covers \Cougar\RestService\AnnotatedRestService::bindFromObject
     * @covers \Cougar\RestService\AnnotatedRestService::handleRequest
     * 
     * @todo FIX ME
     */
    public function testJsonContentType() {
        $_SERVER["SERVER_PROTOCOL"] = "HTTP/1.1";
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["REQUEST_URI"] = "/post/ContentType";
        $_SERVER["PHP_SELF"] = "/request_handler";
        $_SERVER["HTTP_HOST"] = "localhost";
        $_SERVER["HTTP_ACCEPT"] = "application/json";
        $_SERVER["HTTP_CONTENT_TYPE"] = "application/json";
        
        $object = new \stdClass();
        $object->foo = "bar";
        $json_object = json_encode($object);
        
        global $_BODY;
        $_BODY = $json_object;
        
        $object = new AnnotatedRestServicePostTests();
        $this->expectOutputString(json_encode(
                $object->PostBodyJson($json_object)));
        
        $service = new AnnotatedRestService(new Security());
        $service->bindFromObject($object);
        $service->handleRequest();
    }

    
    /**
     * @covers \Cougar\RestService\AnnotatedRestService::bindFromObject
     * @covers \Cougar\RestService\AnnotatedRestService::handleRequest
     * 
     * @todo FIX ME
     */
    public function testXmlContentType() {
        $_SERVER["SERVER_PROTOCOL"] = "HTTP/1.1";
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["REQUEST_URI"] = "/post/ContentType";
        $_SERVER["PHP_SELF"] = "/request_handler";
        $_SERVER["HTTP_HOST"] = "localhost";
        $_SERVER["HTTP_ACCEPT"] = "application/xml";
        $_SERVER["HTTP_CONTENT_TYPE"] = "application/xml";
        
        $xml = new \SimpleXMLElement("<foo><bar id=\"1\"/></foo>");
        $xml_text = trim($xml->asXML());
        
        global $_BODY;
        $_BODY = $xml_text;
        
        $object = new AnnotatedRestServicePostTests();
        $this->expectOutputString(
            XML::toXml($object->PostBodyXml($xml_text))->asXML());
        
        $service = new AnnotatedRestService(new Security());
        $service->bindFromObject($object);
        $service->handleRequest();
    }
}

class AnnotatedRestServicePostTests
{
    /**
     * @Path /post/SimpleCase
     * @Methods POST
     */
    public function simpleCase()
    {
        return array("method" => __FUNCTION__,
            "arguments" => func_get_args());
    }
    
    /**
     * @Path /post/SingleUriArgument/:argument
     */
    public function singleUriArgument($argument)
    {
        return array("method" => __FUNCTION__,
            "arguments" => func_get_args());
    }
    
    /**
     * @Path /post/MultiUriArgument/:argument1/:argument2:/:argument3
     */
    public function multiUriArgument($argument1, $argument2, $argument3)
    {
        return array("method" => __FUNCTION__,
            "arguments" => func_get_args());
    }
        
    /**
     * @Path /post/MultiUriArgument/plus/:arguments+
     */
    public function multiUriArgumentPlus(array $arguments)
    {
        return array("method" => __FUNCTION__,
            "arguments" => func_get_args());
    }
    
    /**
     * @Path /post/MultiUriArgument/:argument1/literal/:argument2
     */
    public function multiUriArgumentWithLiteral($argument1, $argument2)
    {
        return array("method" => __FUNCTION__,
            "arguments" => func_get_args());
    }

    /**
     * @Path /post/EmptyResponse
     */
    public function emptyResponse()
    {
        # Don't return anything; response should be blank
    }
    
    /**
     * @Path /post/GetValue/OneArg/Space/At/End
     * @GetValue something  
     */
    public function getValueOneArgumentSpaceAtEnd($something)
    {
        return array("method" => __FUNCTION__,
            "arguments" => func_get_args());
    }
        
    /**
     * @Path /post/GetValue/OneArg/float
     * @GetValue float float  
     */
    public function getValueOneArgumentFloat($float)
    {
        return array("method" => __FUNCTION__,
            "arguments" => func_get_args());
    }
    
    /**
     * @Path /post/GetValue/OneArg/string
     * @GetValue string string  
     */
    public function getValueOneArgumentString($string)
    {
        return array("method" => __FUNCTION__,
            "arguments" => func_get_args());
    }
    
    /**
     * @Path /post/GetValue/OneArg/int
     * @GetValue int int  
     */
    public function getValueOneArgumentInt($int)
    {
        return array("method" => __FUNCTION__,
            "arguments" => func_get_args());
    }
    
    /**
     * @Path /post/GetValue/OneArg/bool
     * @GetValue bool bool  
     */
    public function getValueOneArgumentBool($bool)
    {
        return array("method" => __FUNCTION__,
            "arguments" => func_get_args());
    }
        
    /**
     * @Path /post/GetValue/OneArg/Space/At/End
     * @GetValue something  
     * @PostValue somethingelse
     */
    public function getValueOnePostArgumentOnePostArgument($something, $somethingelse)
    {
        return array("method" => __FUNCTION__,
            "arguments" => func_get_args());
    }
    
    /**
     * @Path /post/with/body
     * @Methods POST
     * @Body body
     */
    public function postBody($body)
    {
        return array("method" => __FUNCTION__,
            "arguments" => func_get_args());
    }
    
    /**
     * @Path /post/ContentType
     * @Methods POST
     * @Body body
     * @Accepts JSON
     */
    public function postBodyJson($body)
    {
        $json = json_decode($body);
        return array("method" => __FUNCTION__,
            "arguments" => func_get_args());
    }
    
    /**
     * @Path /post/ContentType
     * @Methods POST
     * @Body body
     * @Accepts XML
     */
    public function postBodyXml($body)
    {
        $xml = new \SimpleXMLElement($body);
        return array("method" => __FUNCTION__,
            "arguments" => func_get_args());
    }
}
