<?php

namespace Cougar\Model;

use Cougar\Model\tStruct;

# Initialize the framework
require_once("cougar.php");

/**
 * The Struct abstract class implements the __get() and __set() magic methods to
 * ensure class properties cannot be added. Thus creating a struct-like data
 * object.
 *
 * @history
 * 2013.09.30:
 *   (AT)  Initial release
 *
 * @version 2013.09.30
 * @package Cougar
 * @license MIT
 *
 * @copyright 2013 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
abstract class Struct implements iStruct
{
    use tStruct;
}
?>
