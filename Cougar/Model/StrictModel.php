<?php

namespace Cougar\Model;

use Cougar\Exceptions;

# Initialize the framework (disabled; should have been done by application)
#require_once(__DIR__ . "/../../cougar.php");

/**
 * The StrictModel trait implements the Model interface by using the RealStruct
 * class rather than the struct class. It is therefore a more proper but slower
 * implementation.
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
abstract class StrictModel implements iModel
{
    use tStrictModel;
}
?>
