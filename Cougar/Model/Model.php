<?php

namespace Cougar\Model;

# Initialize the framework
require_once("cougar.php");

/**
 * The Model abstract class implements the Model interface in a less strict but
 * much faster implementation by using the Struct class rather than the
 * StrictStruct class.
 *
 * Since the implementation takes some shortcuts to benefit speed, you should
 * always call the __validate() method to enforce all property behavior.
 *
 * For full information about what a Model does, see the documentation for
 * iModel, the interface it implements.
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
abstract class Model implements iModel
{
    use tModel;
}
?>
