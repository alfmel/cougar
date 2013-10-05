<?php

namespace Cougar\Util;

/**
 * Loads configuration files and provides methods to extract individual values.
 * The config files need to be in name = value format. Names and values may
 * contain spaces, but they will be trimmed to remove leading and trailing
 * whitespace. Lines will be ignored after a hash mark (#) and those will
 * be considered comments.
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
interface iConfig
{
    /**
     * Loads the given config file
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string $config_file
     *   Filename of the config file to load; may include full or relative path
     */
    public function __construct($config_file);
    
    /**
     * Returns an array with all name/value pairs.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @return array All configuration values in the file
     */
    public function values();
    
    /**
     * Returns the value associated with the given name. If the name does not
     * exists, null will be returned
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param string $name The setting name
     * @return string Value of the config setting
     */
    public function value($name);
}
?>
