<?php

namespace Cougar\Model;

use PDO;
use Cougar\Util\Format;
use Cougar\Exceptions\Exception;

# Initialize the framework (disabled; should have been done by application)
#require_once(__DIR__ . "/../../cougar.php");

/**
 * @history
 * 2013.09.30:
 *   (JPK) Initial release
 * 2013.11.19:
 *   (AT)  Fix issue with Oracle driver not supporting column metadata
 *
 * @version 2013.11.19
 * @package Cougar
 * @license MIT
 *
 * @copyright 2013 Brigham Young University
 *
 * @author (JPK) Jillian Koontz, Brigham Young Univ. <jpkoontz@gmail.com>
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class PdoEnumeration
{
    /**
     * Stores the database connection (PDO)
     *
     * @history
     * 2013.09.30:
     *   (JPK) Initial release
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
     *   (JPK) Initial release
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
     *   (JPK) Initial release
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
     *   (JPK) Initial release
     * 2013.11.19:
     *   (AT)  Work around OCI driver limitations to obtain column information
     *
     * @version 2013.11.19
     * @author (JPK) Jillian Koontz, Brigham Young Univ. <jpkoontz@gmail.com>
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
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
                    "SELECT COLUMN_NAME, DATA_TYPE, DATA_PRECISION, DATA_SCALE,
							NULLABLE
						FROM dba_tab_columns WHERE table_name = :table_name");
                $statement->execute(array(":table_name" => $table));
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

				foreach($result as $raw_column)
				{
					$column = array("name" => "",
						"flags" => array(),
						"native_type" => ""
					);

					$column["name"] = $raw_column["COLUMN_NAME"];
					switch($raw_column["DATA_TYPE"])
					{
						case "NUMBER":
						case "LONG":
						case "LONG RAW":
							if ($raw_column["DATA_SCALE"] > 0)
							{
								$column["native_type"] = "float";
							}
							else
							{
								$column["native_type"] = "int";
							}
							break;
						case "FLOAT":
							$column["native_type"] = "float";
							break;
						case "DATE":
						case "TIMESTAMP":
							$column["native_type"] = "datetime";
							break;
						default:
							if (substr($raw_column["DATA_TYPE"], 0, 9) ==
								"TIMESTAMP")
							{
								$column["native_type"] = "datetime";
							}
							else
							{
								$column["native_type"] = "string";
							}
							break;
					}

					Format::strToBool($raw_column["NULLABLE"]);
					if (! $raw_column["NULLABLE"])
					{
						$column["floags"][] = "not_null";
					}

					$columns[] = $column;
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
