<?php

namespace Cougar\Model;

/**
 * Structs provide a way to create strict data objects that enforce structure
 * and consistency. Models extend Structs to provide additional flexibility.
 * They are created by extending the abstract Model class or by including the
 * tModel trait.
 * 
 * Models support views. Views allow the developer to change how the public
 * properties of the Struct are presented by assigning an alias, making them
 * read-only, making them optional (not exported if value is null) or hiding
 * them completely. Properties can also be specified as case-insensitive, or
 * have alternate aliases.
 * 
 * To provide their added functionality, Models implement the
 * JsonSerializable, Iterator and ArrayExportable interfaces. They also
 * allow values to be imported from other objects or associative arrays via
 * the constructor or the special __import() method.
 * 
 * To create a Model extend the Cougar\Model\Model abstract class or use the
 * tModel trait in your class. You can then add properties directly to your
 * class or via a trait. You may then use any of the following annotations in
 * the the class and/or property document blocks to affect their behavior:
 * 
 * Class Annotations:
 *   @CaseInsensitive
 *   Specifies that property names should be treated as case-insensitive. If
 *   this annotation is not specified, setting or getting a property will behave
 *   in a case-sensitive manner, consistent with PHP's default behavior. When
 *   specified, any property can be set or read in a case-insensitive manner.
 * 
 *   @Views
 *   A space-separated list of views supported by the record. View names can be
 *   any non-white space character and are case-insensitive.
 * 
 * Property Annotations:
 *   @Alias name
 *   If the property can be known by another name, you may specify its alias
 *   here. That means this alias name can be used when setting or getting the
 *   property. Multiple aliases may be specified by repeating the tag.
 * 
 *   @Column name
 *   For the purposes of the Model, a column name is an alias. However, PDO
 *   extensions will use it to determine the actual column name in the
 *   database.
 * 
 *   @NotNull
 *   Specifies that the properly cannot have a null value. A null value will be
 *   cast to the parameter's default type.
 * 
 *   @Regex regular_expression
 *   A properly formated (e.g. /^[::alpha::]{8}$/) regular expression that
 *   verifies the property's value. You may provide multiple regex values with
 *   multiple tags.
 * 
 *   @Optional
 *   If the value is set to null, it will not be exported when the object is
 *   iterated, converted to an array or encoded to JSON.
 * 
 *   @View view_name [export_alias_name] [hidden] [optional]
 *   Specifies the behavior of the property under the given view. If the
 *   export_alias_name is specified, the given alias will be used when the
 *   record is exported (and also creates a new alias for the property). If
 *   hidden is specified, the record will not be exported. If optional is
 *   specified the property will behave in the same way as if the @Optional
 *   annotation was specified. You may also use the reserved view name of
 *   __default__ to set default view options.
 * 
 *   @var string|int|integer|float|double|bool|boolean|array|object name [comment] 
 *   An optional type. For scalar types, values will be cast when they are
 *   exported. For objects, the values will be verified to be instances of the
 *   object. Note that this annotation is shared with PHPDocumentor; so if you
 *   set the @var annotation for documentation purposes, the Model class will
 *   use it as well.
 * 
 * Properties may be defined with a default value associated to them, just as
 * you would in any class.
 * 
 * Note that there is a magic method __validate() that verifies that all the
 * values are correct. You may call the method at any time. You may also add
 * __preValidate() and __postValidate methods to your class to do additional
 * processing before and after validation.
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
interface iModel
extends iStruct, iAnnotatedClass, \Iterator, \JsonSerializable
{
    /**
     * Sets the view of the object to the specified view. If the view does not
     * exist, this method will throw an Exception.
     * 
     * To reset the view to the default, omit the view parameter or set it to
     * null.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string $view
     *  View name
     * @throws \Cougar\Exceptions\Exception
     */
    public function __setView($view = null);
    
    /**
     * Imports values from an object or array. 
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param mixed $object
     *   Object or associative array to import values from
     * @param bool $strict
     *   Whether to perform strict property checking (on by default). If strict,
     *   any values in the incoming object that are not part of the Model will
     *   throw an Exception. If not strict, the method will simply ignore them.
     * @throws \Cougar\Exceptions\Exception
     */
    public function __import($object, $strict = true);
    
    /**
     * Ensures all properties are of the given type and conform to their
     * specified behavior.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     */
    public function __validate();
}
?>
