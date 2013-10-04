<?php

namespace Cougar\Util;

use Cougar\Model\Struct;

# Initialize the framework
require_once("cougar.php");

/**
 * The ClassAnnotation strcut stores the annotations for a class and its
 * public properties and methods.
 *
 * @history
 * 2013.09.30:
 *   (AT)  Initial release
 *
 * @version 2013.09.30
 * @package Cougar
 * @licence MIT
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class ClassAnnotations extends Struct
{
	/**
	 * @var array List of annotations for the class
	 */
	public $class = array();
	
	/**
	 * @var array Associative array of properties and their annotations
	 */
	public $properties = array();
	
	/**
	 * @var array Associative array of properties and their annotations
	 */
	public $methods = array();
	
	/**
	 * @var bool Whether the annotations were retrieved from the cache
	 */
	public $cached = false;
}
?>
