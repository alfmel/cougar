<?php

namespace Cougar\Model;

# Load the framework foundation
require_once("cougar.php");

/**
 * The RealStruct abstract class implements the __get() and __set() magic
 * methods to ensure class properties cannot be added. Thus creating a
 * strcut-like data object.
 *
 * The RealStruct prevents the addition of new properties and the unsetting of
 * existing ones. This Struct is slow because it forces all get and set
 * operations to pass through the __get() and __set() magic methods. It also
 * can't be cast into an array, and therefore provides its own __toArray()
 * method. It also implements the Iteratable and JsonSerializable interfaces for
 * exporting.
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
abstract class RealStruct implements iStruct, \Iterator, \JsonSerializable
{
	use tRealStruct;
}
?>
