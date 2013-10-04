<?php

namespace Cougar\UnitTests\Autoload\TestClass;

/**
 * Defines a basic class to test autoloading directives
 */
class AutoloadTestClass implements iAutoloadTestClass
{
	use AutoloadTestTrait;
	
	public function __construct()
	{
		
	}

	public function __destruct()
	{

	}
}
?>
