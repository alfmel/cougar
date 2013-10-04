<?php
namespace Cougar\PDO;

/**
 * Extends the PHP PDO class so that all commands are wrapped in a transaction.
 * It also extends the PDO::exec() method so that it can accept a list of bound
 * parameters.
 * 
 * It also adds the queryFetchAll(), queryFetchRow() and queryFetchColumn()
 * methods which allow the execution of queries that automatically return the
 * data without needing create a statement or iterate through a result set.
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
interface iPDO
{
	/**
     * Store the connections values so we can connect when we really need to.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @param string $dsn
     *   Data Source Name
	 * @param string $username
     *   The user name for the DSN string.
	 * @param string $password
     *   The password for the DSN string.
	 * @param array $driver_options
     *   A key=>value array of driver-specific connection options.
	 */
	public function __construct($dsn, $username = null, $password = null,
		array $driver_options = null);
	
	/**
	 * Rolls back any outstanding transactions.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 */
	public function __destruct();
	
	/**
	 * Commits the transaction and starts the next one. This avoids the behavior
	 * of turning on autocommit on the commit.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @return bool Returns TRUE on success or FALSE on failure
	 */
	public function commit();
	
	/**
	 * Rolls back the transaction and starts the next one. This avoids the
	 * behavior of turning on autocommit on rollback.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @return bool Returns TRUE on success or FALSE on failure
	 */
	public function rollBack();
	
	/**
	 * Executes the given statement. If input parameters are passed, then the
	 * parameters are bound to the statement.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @param string $statement
     *   The SQL statement to prepare and execute
	 * @param array $input_parameters
     *   An array of values with as many elements as there are bound parameters
     *   in the SQL statement being executed
	 * @return int Number of rows affected
	 */
	public function exec($statement, array $input_parameters = null);
	
	/**
	 * Executes the given query statement and returns the result as an array.
	 * If you use PDO::FETCH_CLASS the method will return an array of objects of
	 * the given class. PDO::FETCH_OBJECT is not supported .
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @param string $statement
     *   The SQL statement to prepare and execute
	 * @param array $input_parameters
     *   An array of values with as many elements as there are bound parameters
     *   in the SQL statement being executed
	 * @param int $fetch_mode
     *   The fetch mode must be one PDO::FETCH_ASSOC (default), PDO::FETCH_BOTH,
     *   PDO::FETCH_NUM or PDO::FETCH_CLASS
	 * @param string $classname
     *   The name of the class to fetch into
	 * @param array $ctorargs
     *   Constructor arugments for PDO::FETCH_CLASS
	 * @return array The result set
	 */
	public function queryFetchAll($statement, array $input_parameters = null,
		$fetch_mode = \PDO::FETCH_ASSOC, $classname = null,
		array $ctorargs = null);
	
	/**
	 * Executes the given query statement and returns the single row of the
	 * result set. If the result set returns more than one row, an exception
     * will be thrown.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @param string $statement
     *   The SQL statement to prepare and execute
	 * @param array $input_parameters
     *   An array of values with as many elements as there are bound parameters
     *   in the SQL statement being executed
	 * @param int $fetch_mode
     *   The fetch mode must be one PDO::FETCH_ASSOC (default), PDO::FETCH_BOTH,
     *   PDO::FETCH_NUM, PDO::FETCH_CLASS or PDO::FETCH_INTO
	 * @param mixed $classname_object
     *   The name of the class OR object to fetch into
	 * @param array $ctorargs
     *   Constructor arguments for PDO::FETCH_CLASS
	 * @return mixed An array or object with the result set
	 */
	public function queryFetchRow($statement, array $input_parameters = null,
		$fetch_mode = \PDO::FETCH_ASSOC, $classname_object = null,
		array $ctorargs = null);
	
	/**
	 * Executes the given query statement and returns the single value in the
	 * result set. This is useful for COUNT() queries or queries that only
     * return one value. If the result set contains more than one row or value,
     * an exception will be thrown.
	 * 
	 * Please note some databases always return strings as their values. You
	 * must therefore perform any necessary casts for the value.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 * 
	 * @param string $statement
     *   The SQL statement to prepare and execute
	 * @param array $input_parameters
     *   An array of values with as many elements as there are bound parameters
     *   in the SQL statement being executed
	 * @return mixed Value of the result set
	 */
	public function queryFetchColumn($statement, array $input_parameters = null);
}
?>
