<?php

namespace Cougar\Exceptions;

/**
 * Turns all errors into ErrorExceptions as specified in the PHP Documentation.
 * See http://php.net/manual/en/class.errorexception.php for details.
 * 
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class Errors
{
    /**
     * @var bool Whether the error handler has been set
     */
    public static $errorHandlerSet = false;

    /**
     * Handles errors and turns them into ErrorExceptions
     *
     * @history:
     * 2013.09.30:
     *   (AT)  Initial implementation
     *
     * @version 2013.09.30
     * @package Cougar
     * @license MIT
     *
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param int $errno
     *   The level of the error raised
     * @param string $errstr
     *   The error message
     * @param string $errfile
     *   The filename that the error was raised in
     * @param int $errline
     *   The line number the error was raised at
     * @throws \ErrorException
     */
    public static function exceptionErrorHandler($errno, $errstr, $errfile,
        $errline)
    {
        throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
    }

    /**
     * Sets the error handler to the exceptionErrorHandler in this class.
     *
     * @history:
     * 2013.09.30:
     *   (AT)  Initial implementation
     *
     * @version 2013.09.30
     * @package Cougar
     * @license MIT
     *
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     */
    public static function setErrorHandler()
    {
        if (! self::$errorHandlerSet)
        {
            set_error_handler(__NAMESPACE__ .
                "\\Errors::exceptionErrorHandler");
            self::$errorHandlerSet = true;
        }
    }
}
?>
