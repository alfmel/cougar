<?php

namespace Cougar\Model;

use Cougar\Util\Annotations;
use Cougar\Cache\CacheFactory;

# Initialize the framework (disabled; should have been done by application)
#require_once(__DIR__ . "/../../cougar.php");

/**
 * The Annotated Class trait provides functionality to extract, cache and store
 * class annotations. Annotations are stored in the protected __annotations
 * property.
 *
 * @history
 * 2013.09.30:
 *   (AT)  Initial release
 * 2014.02.26:
 *   (AT)  Extract annotations with extractFromObjectWithInheritance()
 *
 * @version 2014.02.26
 * @package Cougar
 * @license MIT
 *
 * @copyright 2013 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
trait tAnnotatedClass
{
    /**
     * Extracts the annotations from the class, property and method blocks and
     * stores them in the protected __annotations property.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     * 2014.02.26:
     *   (AT)  Extract annotations with extractFromObjectWithInheritance()
     *
     * @version 2014.02.26
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     */
    public function __construct()
    {
        $this->__annotations = Annotations::extractFromObjectWithInheritance(
            $this, array(), true, false);
    }


    /***************************************************************************
     * PROTECTED PROPERTIES AND METHODS
     **************************************************************************/
    
    /**
     * @var \Cougar\Util\Annotations Annotations extracted from the class
     */
    protected $__annotations;
}
?>
