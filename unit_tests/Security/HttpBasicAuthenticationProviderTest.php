<?php
/**
 * Created by PhpStorm.
 * User: alberto
 * Date: 3/24/14
 * Time: 11:46 AM
 */

namespace Cougar\UnitTests\Security;

use Cougar\Security\HttpBasicAuthenticationProvider;
use Cougar\Security\Identity;

class HttpBasicAuthenticationProviderTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        require_once(__DIR__ . "/../../cougar.php");
    }

    /**
     * @var \Cougar\RestService\RestService
     */
    protected $restService;

    /**
     * @var \Cougar\Security\iBasicCredentialValidator
     */
    protected $basicCredentialValidator;

    /**
     * @var \Cougar\Security\HttpBasicAuthenticationProvider
     */
    protected $object;

    /**
     * Sets up the mock dependencies and object being tested
     */
    protected function setUp()
    {
        $this->restService = $this->getMockBuilder(
                '\Cougar\RestService\RestService')
            ->disableOriginalConstructor()
            ->getMock();

        $this->basicCredentialValidator = $this->getMock(
            '\Cougar\Security\iBasicCredentialValidator');

        $this->object = new HttpBasicAuthenticationProvider($this->restService,
            $this->basicCredentialValidator);
    }

    /**
     * @covers \Cougar\Security\HttpBasicAuthenticationProvider::__construct
     * @covers \Cougar\Security\HttpBasicAuthenticationProvider::authenticate
     */
    public function testAuthenticateNoAuthorizationHeader()
    {
        $this->restService->expects($this->once())
            ->method("authorizationHeader")
            ->with()
            ->will($this->returnValue(null));

        $this->basicCredentialValidator->expects($this->never())
            ->method("validate");

        $this->object->authenticate();
    }

    /**
     * @covers \Cougar\Security\HttpBasicAuthenticationProvider::__construct
     * @covers \Cougar\Security\HttpBasicAuthenticationProvider::authenticate
     */
    public function testAuthenticateNonBasicAuthentication()
    {
        $auth_header = array("raw_header" => "Other Some,crazy,parameter",
            "scheme" => "Other",
            "parameter" => "Some,crazy,parameter");

        $this->restService->expects($this->once())
            ->method("authorizationHeader")
            ->with()
            ->will($this->returnValue($auth_header));

        $this->basicCredentialValidator->expects($this->never())
            ->method("validate");

        $this->object->authenticate();
    }

    /**
     * @covers \Cougar\Security\HttpBasicAuthenticationProvider::__construct
     * @covers \Cougar\Security\HttpBasicAuthenticationProvider::authenticate
     */
    public function testAuthenticateValidCredentials()
    {
        $auth_header = array("raw_header" => "Basic " .
                base64_encode("Aladdin:open sesame"),
            "scheme" => "Basic",
            "parameter" => base64_encode("Aladdin:open sesame"),
            "username" => "Aladdin",
            "password" => "open sesame");

        $identity = new Identity();

        $this->restService->expects($this->once())
            ->method("authorizationHeader")
            ->with()
            ->will($this->returnValue($auth_header));

        $this->basicCredentialValidator->expects($this->once())
            ->method("validate")
            ->with("Aladdin", "open sesame")
            ->will($this->returnValue($identity));

        $this->object->authenticate();
    }

    /**
     * @covers \Cougar\Security\HttpBasicAuthenticationProvider::__construct
     * @covers \Cougar\Security\HttpBasicAuthenticationProvider::authenticate
     * @expectedException \Cougar\Exceptions\AuthenticationRequiredException
     */
    public function testAuthenticateBadCredentials()
    {
        $auth_header = array("raw_header" => "Basic " .
            base64_encode("Aladdin:bad password"),
            "scheme" => "Basic",
            "parameter" => base64_encode("Aladdin:bad password"),
            "username" => "Aladdin",
            "password" => "bad password");

        $this->restService->expects($this->once())
            ->method("authorizationHeader")
            ->with()
            ->will($this->returnValue($auth_header));

        $this->basicCredentialValidator->expects($this->once())
            ->method("validate")
            ->with("Aladdin", "bad password")
            ->will($this->returnValue(null));

        $this->object->authenticate();
    }

    /**
     * @covers \Cougar\Security\HttpBasicAuthenticationProvider::__construct
     * @covers \Cougar\Security\HttpBasicAuthenticationProvider::authenticate
     * @expectedException \Cougar\Exceptions\Exception
     */
    public function testAuthenticateBadReplyFromValidator()
    {
        $auth_header = array("raw_header" => "Basic " .
            base64_encode("Aladdin:bad password"),
            "scheme" => "Basic",
            "parameter" => base64_encode("Aladdin:bad password"),
            "username" => "Aladdin",
            "password" => "bad password");

        $bad_reply = array("id" => "Aladdin");

        $this->restService->expects($this->once())
            ->method("authorizationHeader")
            ->with()
            ->will($this->returnValue($auth_header));

        $this->basicCredentialValidator->expects($this->once())
            ->method("validate")
            ->with("Aladdin", "bad password")
            ->will($this->returnValue($bad_reply));

        $this->object->authenticate();
    }
}
?>
