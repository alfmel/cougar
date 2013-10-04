<?php

namespace Cougar\Model;

use PDO;
use Cougar\Exceptions\Exception;

# Initialize the framework
require_once("cougar.php");

/**
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
 * @author (JPK) Jillian Koontz, Brigham Young Univ. <jpkoontz@gmail.com>
 */
class PdoEnumeration
{
	/**
	 * Stores the database connection (PDO)
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (JPK) Jillian Koontz, Brigham Young Univ. <jpkoontz@gmail.com>
	 *
     * @var PDO
     *   Reference to PDO (database connection)
	 */
	final public function __construct(PDO $pdo)
	{
		$this->pdo = $pdo;

	}

	/**
	 * Cleans-up the object.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (JPK) Jillian Koontz, Brigham Young Univ. <jpkoontz@gmail.com>
	 */
	final public function __destruct()
	{
		# Nothing to do at this time
	}


	/***************************************************************************
	 * PUBLIC PROPERTIES
	 **************************************************************************/

	/**
	 * Gets the list of tables from the current schema.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (JPK) Jillian Koontz, Brigham Young Univ. <jpkoontz@gmail.com>
     *
     * @return array List of tables
     * @throws \Cougar\Exceptions\Exception
	 */
	public function getTables()
	{
		$tableNames = array();

		switch ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME))
		{
			case "sqlite":
				$statement = $this->pdo->prepare(
					"select * from sqlite_master");
				$statement->execute();
				$results = $statement->fetchAll(PDO::FETCH_NUM);

				foreach($results as $result)
				{
					$tableNames[] = $result["tbl_name"];
				}
				break;
			case "mysql":
				$statement = $this->pdo->prepare("SHOW TABLES");
				$statement->execute();
				$results = $statement->fetchAll(PDO::FETCH_NUM);

				foreach($results as $result)
				{
					$tableNames[] = $result[0];
				}
				break;
			case "oci":
                $statement = $this->pdo->prepare(
                    "SELECT table_name FROM dba_tables");
                $statement->execute();
                $results = $statement->fetchAll(PDO::FETCH_NUM);

                foreach($results as $result)
                {
                    $tableNames[] = $result[0];
                }
				break;
			default:
				throw new Exception("Unsupported driver");
		}

		return $tableNames;
	}

    /**
     * Gets information on the columns in the given table.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (JPK) Jillian Koontz, Brigham Young Univ. <jpkoontz@gmail.com>
     *
     * @param string $table Table name
     * @throws \Cougar\Exceptions\Exception
     * @return array List of column properties
     */
	public function getColumns($table)
	{
		$columns = array();    

		switch ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME))
		{    
            case "sqlite":
                # TODO: SQLite driver doesn't support getColumnMeta
                $statement = $this->pdo->prepare(
                    "SELECT * FROM " . $table . " LIMIT 1");
                $statement->execute();
                $result = $statement->fetch();

                for ($i = 0; $i < count($result); $i++)
                {
                    $columns[] = $statement->getColumnMeta($i);
                }
				break;
			case "mysql":
				$statement = $this->pdo->prepare(
                    "SELECT * FROM " . $table . " LIMIT 1");
				$statement->execute();
				$result = $statement->fetch();

				for ($i = 0; $i < count($result); $i++)
				{
					$columns[] = $statement->getColumnMeta($i);
				}
				break;
			case "oci":
                $statement = $this->pdo->prepare(
                    "SELECT * FROM " . $table . " WHERE ROWNUM = 1");
                $statement->execute();
                $result = $statement->fetch();

                for ($i = 0; $i < count($result); $i++)
                {
                    $columns[] = $statement->getColumnMeta($i);
                }
				break;
			default:
				throw new Exception("Unsupported driver");
		}

		return $columns;
	}

	
	/***************************************************************************
	 * PROTECTED PROPERTIES AND METHODS
	 **************************************************************************/

    /**
     * @var PDO Database connection
     */
    protected $pdo = null;
}
?>
