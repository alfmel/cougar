<?php

namespace Cougar;

/**
 * This include file initializes the Cougar Framework. Initialization consists
 * of the following:
 *
 *   - Setting up class autoloading for the framework
 *   - Defining the environment if it has not been defined in hidef or another
 *     mechanisms
 *   - Converting all PHP errors into Exceptions as demonstrated in the PHP
 *     ErrorException documentation
 *
 * This file is safe to include multiple times. However, using require_once() is
 * recommended. That means you may safely include the following line in all
 * files that make some use features of the framework:
 *
 *   require_once("cougar.php");
 *
 * @history
 * 2013.09.30:
 *   (AT)  Initial release
 *
 * @version 2013.09.30
 * @package Cougar
 * @licence MIT
 *
 * @copyright 2013 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 *
 * @example examples/init.php
 *   Example on how to initialize the framework
 */

# Define the ENVIRONMENT constant if it has not been defined
if (! defined("ENVIRONMENT"))
{
    define("ENVIRONMENT", "local");
}

# Include the Autoload class file
require_once(__DIR__ . "/Cougar/Autoload/Autoload.php");
if (! Autoload\Autoload::$registered)
{
    Autoload\Autoload::register(__DIR__);
}

# Turn all errors into ErrorExceptions
if (! Exceptions\Errors::$errorHandlerSet)
{
    Exceptions\Errors::setErrorHandler();
}
?>
