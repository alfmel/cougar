<?php

namespace Cougar\Util;

use Cougar\Cache\iCache;

/**
 * Defines the Annotations interface used for retreiving and caching file
 * annotations
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
interface iAnnotations
{
    /**
     * Returns the annotations for the class and public methods and properties
     * from the given object. Annotations in the interfaces the object may
     * implement are ignored.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param \Cougar\Cache\iCache $local_cache
     *   Local cache object
     * @param mixed $object
     *   Object to extract annotations from
     * @param array $exclude_class_list
     *   List of classes to exclude
     * @return \Cougar\Util\ClassAnnotations
     *   ClassAnnotations object with annotations
     */
    public static function extract(iCache $local_cache, $object,
        array $exclude_class_list = array());
}
?>
