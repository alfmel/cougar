<?php

namespace Cougar\Util;

/**
 * Defines the interface for the encoding/decoding of string information. This
 * prevents sensitive string information from being stored in plain text. This
 * is a generic interface, meaning that there can be multiple implementations of
 * it.
 * 
 * The interface only defines the encode and decode methods. Each implementation
 * may have additional methods to configure it depending on its implementation.
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
interface iSecureString
{
    /**
     * Encodes the given string.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param string $string
     *   The string to encode
     * @return string Encoded string
     */
    
    static function encode($string);
    /**
     * Decodes the given string.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param string $encoded_string
     *   The string to decode
     * @return string Decoded string
     */
    static function decode($encoded_string);
}
?>
