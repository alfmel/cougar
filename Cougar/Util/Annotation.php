<?php

namespace Cougar\Util;

use Cougar\Model\Struct;

# Initialize the framework
require_once("cougar.php");

/**
 * The Annotation struct stores the name and value of a given annotation.
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
class Annotation extends Struct
{
    /**
     * @var string Annotation name
     */
    public $name;
    
    /**
     * @var string Annotation value
     */
    public $value;
    
    /**
     * Populates optional values for the annotation
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param string $name Annotation name
     * @param string $value Annotation value
     */
    public function __construct($name = null, $value = null)
    {
        $this->name = trim($name);
        $this->value = trim($value);
    }
}
?>
