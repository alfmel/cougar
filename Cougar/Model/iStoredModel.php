<?php

namespace Cougar\Model;

/**
 * Stored Models provide object binding to data stored in any type of resource
 * (files, databases, Web Services, etc.) Individual data fields are exposed as
 * object properties. Loading records is done via the constructor. Modifying
 * data fields is as simple as updating the property.
 * 
 * The interface describes the methods for querying, saving and deleting the
 * Stored Models. The resource-specific implementations will define their
 * own constructors and annotations.
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
interface iStoredModel extends iModel
{
	/**
	 * Saves the record, either by creating a new record or by updating the
	 * existing one.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 */
	public function save();
	
	/**
	 * Deletes the existing record.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 */
	public function delete();

	/**
	 * Returns a list of records with the given values.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 *
	 * @param array $parameters
     *   List of query parameters
	 * @param string $class_name
     *   Use array to return list as an array, or class name to return objects
	 * @param array $ctoargs
     *   Constructor arguments if returning objects
	 * @return array Record list
	 */
	public function query(array $parameters = array(), $class_name = "array",
		array $ctorargs = array());
}
?>
