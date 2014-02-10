<?php

namespace Cougar\Model;

# Initialize the framework (disabled; should have been done by application)
#require_once(__DIR__ . "/../../cougar.php");

/**
 * The ArrayExportable abstract class provides an implementation of the
 * __toArray() method. This method allows an object to have its public
 * properties exported as an array. Protected and private properties are
 * ignored.
 *
 * @history
 * 2013.09.30:
 *   (AT)  Initial release
 *
 * @version 2013.09.30
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
abstract class ArrayExportable implements iArrayExportable
{
    use tArrayExportable;
}
?>
