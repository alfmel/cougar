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
 * 2014.02.21:
 *   (AT)  Added extractFromDocumentBlock() method
 *
 * @version 2014.02.21
 * @package Cougar
 * @licence MIT
 *
 * @copyright 2013-2014 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
interface iAnnotations
{
    /**
     * Extracts multi-line annotations from the given document block. Any
     * comments before the first annotation will be returned in the special
     * _comment annotation. If there are no comments, the _comment annotation
     * will not exist.
     *
     * These annotations will not be cached.
     *
     * @history
     * 2014.02.21:
     *   (AT)  Initial implementation
     *
     * @version 2014.02.21
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string $document_block Full document block
     * @return \Cougar\Util\Annotation[] Array of annotation objects
     */
    public static function extractFromDocumentBlock($document_block);

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
