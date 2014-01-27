<?php

namespace Cougar\UnitTests\Security;

use Cougar\Security\CookieHttpCredentialProvider;

require_once(__DIR__ . "/../../../cougar.php");

class BasicClientHttpAuthenticationProviderTest
    extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Cougar\Security\CookieHttpCredentialProvider::__construct
     * @covers \Cougar\Security\CookieHttpCredentialProvider::addCredentials
     */
    public function testAuthenticateRequest()
    {
        $method = "GET";
        $url = "https://service.example.com/path/to/resource";
        $headers = array("Accepts" => "application/json");
        $cookies = array("Yummy", "Cocholate Chip");
        $body = null;
        $content_type = "application/json";

        $expected_method = $method;
        $expected_url = $url;
        $expected_headers = $headers;
        $new_cookie = array("SESSIONID" => "abc123");
        $expected_cookies = array_merge($cookies, $new_cookie);
        $expected_body = $body;
        $expected_content_type = $content_type;

        $object = new CookieHttpCredentialProvider($new_cookie);
        $object->addCredentials($method, $url, $headers, $cookies, $body,
            $content_type);

        $this->assertEquals($expected_method, $method);
        $this->assertEquals($expected_url, $url);
        $this->assertEquals($expected_headers, $headers);
        $this->assertEquals($expected_cookies, $cookies);
        $this->assertEquals($expected_body, $body);
        $this->assertEquals($expected_content_type, $content_type);
    }
}
?>
