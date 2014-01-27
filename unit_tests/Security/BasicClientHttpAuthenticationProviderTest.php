<?php

namespace Cougar\UnitTests\Security;

use Cougar\Security\BasicHttpCredentialProvider;

require_once(__DIR__ . "/../../../cougar.php");

class BasicClientHttpAuthenticationProviderTest
    extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Cougar\Security\BasicHttpCredentialProvider::__construct
     * @covers \Cougar\Security\BasicHttpCredentialProvider::addCredentials
     */
    public function testAuthenticateRequest()
    {
        $username = "some_user";
        $password = "some_password";

        $url = "https://service.example.com/path/to/resource";
        $headers = array("Accepts" => "application/json");
        $cookies = array("SESSIONID", "abc123");
        $body = null;
        $content_type = "application/json";

        $expected_url = $url;
        $expected_headers = $headers;
        $expected_headers["Authorization"] =
            "Basic " . base64_encode($username . ":" . $password);
        $expected_cookies = $cookies;
        $expected_body = $body;

        $object = new BasicHttpCredentialProvider($username, $password);
        $object->addCredentials($url, $headers, $cookies, $body);
        $expected_content_type = $content_type;

        $this->assertEquals($expected_url, $url);
        $this->assertEquals($expected_headers, $headers);
        $this->assertEquals($expected_cookies, $cookies);
        $this->assertEquals($expected_body, $body);
        $this->assertEquals($expected_content_type, $content_type);
    }
}
?>
