<?php

namespace Cougar\Model;

# Initialize the framework
require_once("cougar.php");

/**
 * The Annotated Class, when extended, will extract, cache and store any
 * annotations you may have in the class comment, public methods and public
 * properties. The Annotations will be stored in the protected __annotations
 * property.
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
abstract class AnnotatedClass implements iAnnotatedClass
{
    use tAnnotatedClass;
}
?>
