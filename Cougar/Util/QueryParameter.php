<?php

namespace Cougar\Util;

use Cougar\Model\Struct;
use Cougar\Exceptions\Exception;

# Initialize the framework
require_once("cougar.php");

/**
 * The Query Parameter struct stores the attributes of a query parameter.
 *
 * @history
 * 2013.09.30:
 *   (AT)  Initial release
 * 2013.11.25:
 *   (AT)  Add support for _limit, _count, _offset and _skip to toSql() method
 *
 * @version 2013.11.25
 * @package Cougar
 * @license MIT
 *
 * @copyright 2013 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
class QueryParameter extends Struct
{
    /**
     * @var mixed Property name or array of query parameters
     */
    public $property;
    
    /**
     * @var string Query value
     */
    public $value;
    
    /**
     * @var string Query operator (= != < > <= >= ** =* *=)
     */
    public $operator;
    
    /**
     * @var string Append mode (AND or OR)
     */
    public $mode = "AND";
    
    /**
     * @var bool Whether to add option for null value
     */
    public $orNull = false;

    /**
     * Populates the object's values.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param mixed $property
     *   Property name or list of query parameters
     * @param mixed $value
     *   Property value
     * @param string $operator
     *   Comparison operator (= != < > <= >= ** =* *=)
     * @param string $mode
     *   AND or OR
     * @param bool $orNull
     *   Whether to include option for null value in SQL statement
     * @throws \Cougar\Exceptions\Exception
     */
    public function __construct($property = null, $value = null,
        $operator = "=", $mode = "AND", $or_null = false)
    {
        # Validate and store the values
        if (is_array($property))
        {
            foreach($property as $query)
            {
                if (! $query instanceof QueryParameter)
                {
                    throw new Exception("Property array entry must be " .
                        "instance of Cougar\Util\QueryParameter");
                }
            }
        }
        $this->property = $property;
        
        $this->value = $value;
        
        switch($operator)
        {
            case "=":
            case "!=":
            case "<":
            case ">":
            case "<=":
            case ">=":
            case "**":
            case "=*":
            case "*=":
                $this->operator = $operator;
                break;
            default:
                throw new Exception("Invalid query type: " . $operator);
        }
        
        switch(strtolower($mode))
        {
            case "and":
                $this->mode = "AND";
                break;
            case "or":
                $this->mode = "OR";
                break;
            default:
                throw new Exception("Operator must be AND or OR");
        }
        
        $this->orNull = (bool) $or_null;        
    }
    
    /**
     * Returns the list of queries as a SQL expression
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @todo Support parentheses
     * 
     * @param string $uri_query
     *   URI query part (including the ?)
     * @return array List of QueryParameters with the information in the query
     */
    public static function fromUri($uri_query)
    {
        # Initialize the list
        $parameters = array();
        
        # Make sure all %xx sequences are converted to their respective
        # characters
        $tmp_uri = "";
        while ($uri_query != $tmp_uri)
        {
            $tmp_uri = $uri_query;
            $uri_query = rawurldecode($uri_query);
        }
        
        # Make sure we have the leading ?
        if (substr($uri_query, 0, 1) !== "?")
        {
            $uri_query = "?" . $uri_query;
        }
        
        # Separate the different key/value pairs
        $matches = array();
        preg_match_all("/([?&|])([^&|]+)/", $uri_query, $matches,
            PREG_SET_ORDER);
        
        # Go through the matches
        foreach($matches as $match)
        {
            # Get the parameter mode
            switch($match[1])
            {
                case "&":
                case "?":
                default:
                    $mode = "AND";
                    break;
                case "|":
                    $mode = "OR";
            }
            
            # Get the property name, operator and value
            $param_parts = array();
            preg_match_all("/([A-Za-z0-9_]+)(!=|<=|>=|<|>|\*\*|=\*|\*=|=)(.*)/",
                $match[2], $param_parts, PREG_SET_ORDER);
            
            if (count($param_parts) >= 1)
            {
                if (count($param_parts[0]) >= 4)
                {
                    # Add a new parameter to the list
                    $parameters[] = new QueryParameter($param_parts[0][1],
                        $param_parts[0][3], $param_parts[0][2], $mode);
                }
            }
        }
        
        return $parameters;
    }
    
    /**
     * Returns the list of queries as an extended HTML query string
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     * 
     * @param array $query_parameter_list
     *   List of query parameters
     * @return string HTML query string
     */
    public static function toHtml(array $query_parameter_list)
    {
        # Initialize the query string
        $query = "";
        
        # Go through each parameter in the list
        $first = true;
        foreach($query_parameter_list as $parameter)
        {
            # Add the append mode
            if (! $first)
            {
                switch ($parameter->mode)
                {
                    case "AND":
                        $query .= "&";
                        break;
                    case "OR";
                        $query .= "|";
                        break;
                }
            }
            else
            {
                $first = false;
            }
            
            # Append the property name, operator and value
            $query .= $parameter->property . $parameter->operator .
                $parameter->value;
        }
        
        # Return the qery
        return $query;
    }

    /**
     * Returns the list of queries as a SQL expression
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     * 2013.11.25:
     *   (AT)  Add limit and offset parameters (by reference)
     *
     * @version 2013.11.25
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param array $query_parameter_list
     *   List of query parameters
     * @param array $column_map
     *   Assoc. array of properties to column names
     * @param array $aliases
     *   Assoc. array of property aliases
     * @param bool $case_insensitive
     *   Whether property names are case-insensitive
     * @param array $values
     *   Reference to array of property values
     * @param array $used_parameters
     *   Used internally during recursion to avoid parameter naming conflicts
     * @param int $limit
     *   Number of records to fetch (reference)
     * @param int $offset
     *   Number of records to skip (reference)
     * @return string SQL expression
     * @throws \Cougar\Exceptions\Exception
     */
    public static function toSql(array $query_parameter_list, array $column_map,
        array $aliases, $case_insensitive, array &$values,
        array &$used_parameters = array(), &$limit = null, &$offset = null)
    {
        # Initialize the sql statement
        $sql = "";
        $first = true;
        
        # Go through the values
        foreach($query_parameter_list as $index => $param)
        {
            # See if the parameter is an array
            if (is_array($param->property))
            {
                $inner_sql = self::toSql($param->property, $column_map,
                    $aliases, $case_insensitive, $values, $used_parameters);
                
                if ($inner_sql)
                {
                    if (! $first)
                    {
                        $sql .= " " . $param->mode;
                    }
                    $sql .= " (" . $inner_sql . ")";
                }
            }
            else
            {
                # See if the parameter is one of _count, _limit, _offset, or
                #  _skip
                switch(strtolower($param->property))
                {
                    case "_limit":
                    case "_count":
                        if ((int) $param->value > 0)
                        {
                            $limit = (int) $param->value;
                        }

                        # Go to the next parameter
                        continue;
                        break;
                    case "_offset":
                    case "_skip":
                        $offset = (int) $param->value;

                        # Go to the next parameter
                        continue;
                        break;
                }

                # Figure out the actual value of the property name
                if ($case_insensitive)
                {
                    $property = strtolower($param->property);
                }
                else
                {
                    $property = $param->property;
                }
                if (array_key_exists($property, $aliases))
                {
                    $property = $aliases[$property];
                    $column = $column_map[$property];
                }
                else
                {
                    # Skip this property 
                    continue;
                }
                
                # See if this is the first entry
                if (! $first)
                {
                    # Add the operator
                    $sql .= " " . $param->mode;
                }

                # See if we have the orNull option
                if ($param->orNull)
                {
                    $sql .= " (";
                }
                else
                {
                    $sql .= " ";
                }
                
                # Figure out the name of the parameter
                $param_name = $property;
                while (in_array($param_name, $used_parameters))
                {
                    $param_name .= "_" . $index;
                }
                $used_parameters[] = $param_name;
                
                # See which type of operator we have
                switch ($param->operator)
                {
                    case "=":
                    case "!=":
                    case "<":
                    case ">":
                    case "<=":
                    case ">=":
                        $sql .= $column . " " . $param->operator . " :" .
                            $param_name;
                        $values[$param_name] = $param->value;
                        break;
                    case "**":
                        $sql .= $column . " LIKE" . " :" . $param_name;
                        $values[$param_name] = "%" . $param->value . "%";
                        break;
                    case "=*":
                        $sql .= $column . " LIKE" . " :" . $param_name;
                        $values[$param_name] = $param->value . "%";
                        break;
                    case "*=":
                        $sql .= $column . " LIKE" . " :" . $param_name;
                        $values[$param_name] = "%" . $param->value;
                        break;
                    default:
                        throw new Exception("Invalid comparison operator: " .
                            $param->operator);
                }
                
                # See if we need to finish orNull option
                if ($param->orNull)
                {
                    $sql .= " OR " . $column . " IS NULL)";
                }
            }
            
            # This is no longer the first property
            $first = false;
        }
    
        return trim($sql);
    }
}
?>
