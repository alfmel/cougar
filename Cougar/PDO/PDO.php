<?php

namespace Cougar\PDO;

use Cougar\Exceptions\Exception;

# Initialize the framework (disabled; should have been done by application)
#require_once(__DIR__ . "/../../cougar.php");

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
 * 2014.03.27:
 *   (AT)  Handle PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE fetch modes
 *
 * @version 2014.03.27
 * @package Cougar
 * @license MIT
 *
 * @copyright 2013 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class PDO extends \PDO implements iPDO
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
        array $driver_options = null)
    {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = base64_encode(str_rot13(strrev($password)));
        $this->driver_options = $driver_options;
    }
    
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
    public function __destruct()
    {
        if ($this->pdo !== null)
        {
            # See if we are in a transaction
            if ($this->pdo->inTransaction())
            {
                # Roll back the transaction
                $this->pdo->rollBack();
            }
        }
    }
    
    
    /***************************************************************************
     * PUBLIC PROPERTIES AND METHODS
     **************************************************************************/

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
     * @throws \Cougar\Exceptions\Exception
     */
    public function exec($statement, array $input_parameters = null)
    {
        # Make sure we have a connection
        if ($this->pdo === null)
        {
            $this->establishConnection();
        }
        
        # See if we have input parameters
        if (is_array($input_parameters))
        {
            # Prepare the statement
            $result = $statement = $this->pdo->prepare($statement);
            
            if ($result === false)
            {
                $error = $this->pdo->errorInfo();
                throw new Exception("(" . $error[0] . ") " . $error[2],
                    $error[1]);
            }
            
            # Execute the statement with the input parameters
            $result = $statement->execute($input_parameters);
            if ($result === false)
            {
                $error = $this->pdo->errorInfo();
                throw new Exception("(" . $error[0] . ") " . $error[2],
                    $error[1]);
            }
            
            # Return the number of rows
            return $statement->rowCount();
        }
        else
        {
            # Execute the statement normally
            $result = $this->pdo->exec($statement);
            if ($result === false)
            {
                $error = $this->pdo->errorInfo();
                throw new Exception("(" . $error[0] . ") " . $error[2],
                    $error[1]);
            }
            
            return $result;
        }
    }
    
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
    public function commit()
    {
        # Make sure we have a connection
        if ($this->pdo !== null)
        {
            # Commit the records and start a new transaction
            $this->pdo->commit();
            $this->pdo->beginTransaction();
        }
    }
    
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
    public function rollBack()
    {
        # Make sure we have a connection
        if ($this->pdo !== null)
        {
            # Roll back the records and start a new transaction
            $this->pdo->rollBack();
            $this->pdo->beginTransaction();
        }
    }

    /**
     * Executes an SQL statement, returning a result set as a PDOStatement
     * object
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
     * @param int $fetch_mode
     *   One of the \PDO::FETCH_* constants
     * @param mixed $classname_object
     *   The name of the class OR object to fetch into
     * @param array $ctorargs
     *   Constructor arugments for PDO::FETCH_CLASS
     * @return \PDOStatement Result set
     * @throws \Cougar\Exceptions\Exception
     */
    public function query($statement, $fetch_mode = \PDO::FETCH_ASSOC,
        $classname_object = null, array $ctorargs = null)
    {
        # Make sure we have a connection
        if ($this->pdo === null)
        {
            $this->establishConnection();
        }
        
        # See how many arguments we have
        switch(func_num_args())
        {
            case 0:
            case 1:
            case 2:
                $result = $this->pdo->query($statement, $fetch_mode);
                break;
            case 3:
                $result = $this->pdo->query($statement, $fetch_mode,
                    $classname_object);
                break;
            case 4:
                $result = $this->pdo->query($statement, $fetch_mode,
                    $classname_object, $ctorargs);
            default:
                break;
        }
        
        # Check for errors
        if ($result === false)
        {
            $error = $this->pdo->errorInfo();
            throw new Exception("(" . $error[0] . ") " . $error[2], $error[1]);
        }
        
        # Return the statement
        return $result;
    }
    
    /**
     * Executes the given query statement and returns the result as an array.
     * If you use PDO::FETCH_CLASS the method will return an array of objects of
     * the given class. PDO::FETCH_OBJECT is not supported .
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     * 2014.03.27:
     *   (AT)  Make sure we can handle a fetch mode of FETCH_CLASS ORed with
     *         FETCH_PROPS_LATE
     *   (AT)  Add default option to case statement to make sure we fetch
     *         results
     *
     * @version 2014.03.27
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string $statement
     *   The SQL statement to prepare and execute
     * @param array $input_parameters
     *   An array of values with as many elements as there are bound parameters
     *   in the SQL statement being executed
     * @param int $fetch_mode
     *   The fetch mode must be one PDO::FETCH_ASSOC (default), PDO::FETCH_BOTH,
     *   PDO::FETCH_NUM, PDO::FETCH_CLASS or PDO::FETCH_CLASS ORed with
     *   PDO::FETCH_PROPS_LATE
     * @param string $classname
     *   The name of the class to fetch into
     * @param array $ctorargs
     *   Constructor arguments for PDO::FETCH_CLASS
     * @return array The result set
     * @throws \Cougar\Exceptions\Exception
     */
    public function queryFetchAll($statement, array $input_parameters = null,
        $fetch_mode = \PDO::FETCH_ASSOC, $classname = null,
        array $ctorargs = null)
    {
        # Make sure we have a connection
        if ($this->pdo === null)
        {
            $this->establishConnection();
        }
        
        # Prepare the statement
        $result = $statement = $this->pdo->prepare($statement);
        if ($result === false)
        {
            $error = $this->pdo->errorInfo();
            throw new Exception("(" . $error[0] . ") " . $error[2], $error[1]);
        }
        
        # Execute
        $result = $statement->execute($input_parameters);
        if ($result === false)
        {
            $error = $this->pdo->errorInfo();
            throw new Exception("(" . $error[0] . ") " . $error[2], $error[1]);
        }
        
        # See how we are returning data
        switch($fetch_mode)
        {
            case \PDO::FETCH_CLASS:
            case \PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE:
                $rows = $statement->fetchAll($fetch_mode, $classname,
                    $ctorargs);
                break;
            case \PDO::FETCH_ASSOC:
            case \PDO::FETCH_BOTH:
            case \PDO::FETCH_NUM:
            default:
                $rows = $statement->fetchAll($fetch_mode);
                break;
        }
        
        # See if we got any data
        if ($rows === false)
        {
            # Return an empty array
            return array();
        }
        else
        {
            return $rows;
        }
    }
    
    /**
     * Executes the given query statement and returns the single row of the
     * result set. If the result set returns more than one row, an exception
     * will be thrown.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     * 2014.03.27:
     *   (AT)  Add support for PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE fetch
     *         mode
     *
     * @version 2014.03.27
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
     * @throws \Cougar\Exceptions\Exception
     */
    public function queryFetchRow($statement, array $input_parameters = null,
        $fetch_mode = \PDO::FETCH_ASSOC, $classname_object = null,
        array $ctorargs = null)
    {
        # Make sure we have a connection
        if ($this->pdo === null)
        {
            $this->establishConnection();
        }
        
        # Prepare the statement
        $result = $statement = $this->pdo->prepare($statement);
        if ($result === false)
        {
            $error = $this->pdo->errorInfo();
            throw new Exception("(" . $error[0] . ") " . $error[2], $error[1]);
        }
        
        # Execute
        $result = $statement->execute($input_parameters);
        if ($result === false)
        {
            $error = $this->pdo->errorInfo();
            throw new Exception("(" . $error[0] . ") " . $error[2], $error[1]);
        }
        
        # Note: PDOStatement::rowCount() does not always return the number of
        # rows in the result set. To work around it, we will fetch the first
        # row. If the result is false, we have no rows in the result set. If we
        # get a row, we will fetch the second row. If it is false, we only had
        # one row. If it's another result, then we had more than one row.
        
        # Get the first row
        switch($fetch_mode)
        {
            case \PDO::FETCH_ASSOC:
            case \PDO::FETCH_BOTH:
            case \PDO::FETCH_NUM:
            case \PDO::FETCH_OBJ:
            default:
                $row = $statement->fetch($fetch_mode);
                break;
            case \PDO::FETCH_CLASS:
            case \PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE:
                $statement->setFetchMode($fetch_mode, $classname_object,
                    $ctorargs);
                $row = $statement->fetch();
                break;
            case \PDO::FETCH_INTO:
                $statement->setFetchMode($fetch_mode,
                    $classname_object);
                $row = $statement->fetch();
                break;
        }
        
        # See if we have a row
        if ($row === false)
        {
            # We don't have a row; return an empty set
            switch($fetch_mode)
            {
                case \PDO::FETCH_ASSOC:
                case \PDO::FETCH_BOTH:
                case \PDO::FETCH_NUM:
                    return array();
                    break;
                case \PDO::FETCH_OBJ:
                case \PDO::FETCH_CLASS:
                case \PDO::FETCH_INTO:
                    return null;
                    break;
            }
        }
        
        # See if we have more than one row
        $statement->setFetchMode(\PDO::FETCH_NUM);
        if ($statement->fetch() !== false)
        {
            throw new Exception("Query returned more than one row " .
                "(only one expected)");
        }
        
        # Return the row
        return $row;
    }
    
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
     * @throws \Cougar\Exceptions\Exception
     */
    public function queryFetchColumn($statement, array $input_parameters = null)
    {
        # Make sure we have a connection
        if ($this->pdo === null)
        {
            $this->establishConnection();
        }
        
        # Prepare the statement
        $result = $statement = $this->pdo->prepare($statement);
        if ($result === false)
        {
            $error = $this->pdo->errorInfo();
            throw new Exception("(" . $error[0] . ") " . $error[2], $error[1]);
        }
        
        # Execute
        $result = $statement->execute($input_parameters);
        if ($result === false)
        {
            $error = $this->pdo->errorInfo();
            throw new Exception("(" . $error[0] . ") " . $error[2], $error[1]);
        }
        
        # See if we have more than one column
        switch($statement->columnCount())
        {
            case 0:
                return null;
            case 1:
                # Get the value
                $value = $statement->fetchColumn();
                
                # Some databases still return a positive columnCount even if
                # there are no rows; check for a false value
                if ($value === false)
                {
                    $value = null;
                }
                
                # Make sure we don't have another row
                # NOTE: rowCount() does not always work; next row should return
                #       false if there are no other rows
                if ($statement->fetch() !== false)
                {
                    throw new Exception("Query returned more than one row " .
                        "(only one expected)");
                }
                
                return $value;
            default:
                throw new Exception("Query returned " .
                    $statement->columnCount() . " columns (only one expected)");
        }
    }
    
    /**
     * Ensures the connection has been established before calling the method.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function beginTransaction()
    {
        # Make sure we have a connection
        if ($this->pdo === null)
        {
            $this->establishConnection();
        }
        
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Ensures the connection has been established before calling the method.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @return mixed SQLSTATE
     */
    public function errorCode()
    {
        # Make sure we have a connection
        if ($this->pdo === null)
        {
            $this->establishConnection();
        }
        
        return $this->pdo->errorCode();
    }
    
    /**
     * Ensures the connection has been established before calling the method.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @return array Error information
     */
    public function errorInfo()
    {
        # Make sure we have a connection
        if ($this->pdo === null)
        {
            $this->establishConnection();
        }
        
        return $this->pdo->errorInfo();
    }
    
    /**
     * Ensures the connection has been established before calling the method.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param int $attribute
     *   One of the PDR::ATTR_* constants
     * @return mixed Attribute
     */
    public function getAttribute($attribute)
    {
        # Make sure we have a connection
        if ($this->pdo === null)
        {
            $this->establishConnection();
        }
        
        return $this->pdo->getAttribute($attribute);
    }
    
    /**
     * Ensures the connection has been established before calling the method.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @return bool TRUE if a transaction is currently active, and FALSE if not
     */
    public function inTransaction()
    {
        # Make sure we have a connection
        if ($this->pdo === null)
        {
            return false;
        }
        else
        {
            return $this->pdo->inTransaction();
        }
    }
    
    /**
     * Ensures the connection has been established before calling the method.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param string $name
     *   Name of the sequence object from which the ID should be returned
     * @return string Value
     */
    public function lastInsertId($name = null)
    {
        # Make sure we have a connection
        if ($this->pdo === null)
        {
            $this->establishConnection();
        }
        
        return $this->pdo->lastInsertId($name);
    }
    
    /**
     * Ensures the connection has been established before calling the method.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param string $statement
     * @param array $driver_options
     * @return PDOStatement
     */
    public function prepare($statement, $driver_options = null)
    {
        # Make sure we have a connection
        if ($this->pdo === null)
        {
            $this->establishConnection();
        }
        
        if (is_array($driver_options))
        {
            return $this->pdo->prepare($statement, $driver_options);
        }
        else
        {
            return $this->pdo->prepare($statement);
        }
    }
    
    /**
     * Ensures the connection has been established before calling the method.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param string $string
     *   The string to be quoted
     * @param int $parameter_type
     *   Provides a data type hint for drivers that have alternate quoting
     *   styles
     * @return string Quoted string
     */
    public function quote($string, $parameter_type = \PDO::PARAM_STR)
    {
        # Make sure we have a connection
        if ($this->pdo === null)
        {
            $this->establishConnection();
        }
        
        return $this->pdo->quote($string, $parameter_type);
    }
    
    /**
     * Ensures the connection has been established before calling the method.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param int $attribute
     *   One of the PDR::ATTR_* constants
     * @param mixed $value
     * @return bool Returns TRUE on success or FALSE on failure
     */
    public function setAttribute($attribute, $value)
    {
        # Make sure we have a connection
        if ($this->pdo === null)
        {
            $this->establishConnection();
        }
        
        return $this->pdo->setAttribute($attribute, $value);
    }

    
    /***************************************************************************
     * PROTECTED PROTPERTIES AND METHODS
     **************************************************************************/
    
    /**
     * @var PDO The actual PDO object
     */
    protected $pdo = null;
    
    /**
     * @var string DSN connection string
     */
    protected $dsn = "";
    
    /**
     * @var string Username
     */
    protected $username = "";
    
    /**
     * @var string Password
     */
    protected $password = "";
    
    /**
     * @var array Driver options
     */
    protected $driver_options = "";


    /**
     * Establish the PDO connection.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     */
    protected function establishConnection()
    {
        # Make sure the connection has not been established
        if ($this->pdo !== null)
        {
            return;
        }
        
        # Run the parent constructor
        $this->pdo = new \PDO($this->dsn, $this->username,
            strrev(str_rot13(base64_decode($this->password))),
            $this->driver_options);
        
        # Null out the connection parameters (for improved security
        $this->dsn = null;
        $this->username = null;
        $this->password = null;
        $this->driver_options = null;
        
        # Set some useful attributes
        $this->pdo->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_NATURAL);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        # Change the date/time format in Oracle
        if ($this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME) == "oci")
        {
            $this->pdo->exec(
                "ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD HH24:MI:SS'");
        }
        
        # Disable autocommit
        try
        {
            $this->pdo->setAttribute(\PDO::ATTR_AUTOCOMMIT, false);
        }
        catch (\Exception $e)
        {
            # Some database drivers don't allow this option to be changed;
            # Ignore the error in those cases
        }
        
        # Start a new transaction
        $this->pdo->beginTransaction();
    }
}
?>
