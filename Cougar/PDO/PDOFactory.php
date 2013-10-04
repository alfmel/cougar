<?php

namespace Cougar\PDO;

use Cougar\Util\Config;
use Cougar\Util\Arc4;
use Cougar\Util\StringObfuscator;
use Cougar\Exceptions\Exception;
use Cougar\Exceptions\ConfigurationFileNotFoundException;

# Initialize the framework
require_once("cougar.php");

/**
 * The abstract method getConnection will return a Cougar\PDO\PDO object for the
 * given database. This method of obtaining connections makes it simple for
 * developers to connect to databases without knowing the hostname, username or
 * password required to connect. Additionally, the method can figure out the
 * environment the code is running under, and automatically decide which
 * database to connect to.
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
class PDOFactory
{
	/***************************************************************************
	 * STATIC PROPERTIES AND METHODS
	 **************************************************************************/

    /**
     * Returns a database connection to the named database.
     *
     * The information on the database is stored in individual Config files. The
     * place were these files are stored is configured via Config's static
     * properties.
     *
     * The config file should contain the following values:
     *
     *   dsn      - The PDO dsn connection string
     *   username - The username to use
     *   password - The password used for connection
     *   arc4     - Config file to ARC4 encryption parameters (optional)
     *
     * The username and passwords can be stored in either ARC4 encrypted form
     * (hex) or as obfuscated strings.
     *
     * The configuration files need to named using the following convention:
     *
     *   connection_name.environment.conf
     *
     * Because Unix filenames are case sensitive, the database name and the
     * environment will be converted to lowercase. Since Oracle schema names
     * are case-insensitive, and MySQL should be configured to be
     * case-insensitive, this should be of little difference. If case-
     * sensitivity is required, the DSN will preserve the casing of the schema
     * name.
     *
     * If no suitable environment is found, the method will do a final attempt
     * to find the connection information in a file named connection_name.conf.
     * If that's not present, it will do a final search for database.conf.
     *
     * The environment value can be any value. If no environment is specified,
     * the value will be taken from the ENVIRONMENT constant. If it is not set,
     * it will revert to "local." You may not specify "production" as the
     *  Production systems must have the ENVIRONMENT constant set.
     *
     * For best results, use the cougar-create-pdo-connection-file script
     * from a command prompt to generate your configuration files.
     *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
     *
     * @param string $name
     *   Database connection name
     * @param string $environment
     *   Environment to use (optional)
     * @return \Cougar\PDO\PDO Database connection
     * @throws \Cougar\Exceptions\Exception
     * @throws \Cougar\Exceptions\ConfigurationFileNotFoundException
     */
	static public function getConnection($name, $environment = null)
	{
		# Make sure we have a database name
		$name = strtolower($name);
		if (! $name)
		{
			throw new Exception("Database name is required");
		}
		
		# See if we have an environment
		if ($environment)
		{
			# Convert the environment to lowercase
			$environment = strtolower($environment);
			
			# Make sure we are not trying to set "production" as environment
			if ($environment == "production")
			{
				throw new Exception("You may not override the environment " .
					"\"production\"");
			}
		}
		else
		{
			# Grab the environment from the ENVIROMENT constant
			if (defined("ENVIRONMENT"))
			{
				$environment = strtolower(ENVIRONMENT);
			}
			else
			{
				$environment = "local";
			}
		}
		
		# Define the list of filenames to look for
		$filenames = array(
			$name . "." . $environment . ".conf",
			$name . ".conf",
			"database.conf"
		);
		
		# Try to load one of the configuration files
		$config = null;
		foreach($filenames as $filename)
		{
			try
			{
				$config = new Config($filename);
				break;
			}
			catch (ConfigurationFileNotFoundException $e)
			{
				# Ignore file not found exceptions
			}
		}
		
		if (! $config)
		{
			throw new ConfigurationFileNotFoundException(
				"Could not load configuration file for " . $name .
				" database for " . $environment);
		}
		
		# Get the values
		$dsn = $config->value("dsn");
		$orig_username = $config->value("username");
		$orig_password = $config->value("password");
		
		if (! $dsn)
		{
			throw new Exception(
				"DSN is not declared in the configuration file");
		}
		
		# Try to decode the username and password via obfuscation
		$username = StringObfuscator::decode($orig_username);
		$password = StringObfuscator::decode($orig_password);
		
		if ($username == $orig_username && $password == $orig_password)
		{
			# Try to decode with Arc4
			$param_file = $config->value("arc4");
			if (! $param_file)
			{
				$param_file = $config->value("encryption");
			}
			if ($param_file)
			{
				Arc4::loadParameters($param_file);
			}
			$username = Arc4::decode($username);
			$password = Arc4::decode($password);
		}
		
		# Return the new database connection
		return new PDO($dsn, $username, $password);
	}

	/**
	 * Creates a new connection file with the parameters given. The username
	 * and password will encoded either in ARC4 or via obfuscation. The key and
	 * other pertinent values must be set up beforehand.
	 *
     * @history
     * 2013.09.30:
     *   (AT)  Initial release
     *
     * @version 2013.09.30
     * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
	 *
	 * @param string $name
     *   The name of the database connection
	 * @param string $environment
     *   Connection environment
	 * @param string $dsn
     *   PDO DSN connection string
	 * @param string $username
     *   Username
	 * @param string $password
     *   Password
	 * @param string $encoding
     *   Encoding to use (Arc4 or Obfuscation)
     * @param string $arc4_config_file
     *   Configuration file with ARC4 parameters
	 * @return string Filename of created file
     * @throws \Cougar\Exceptions\Exception
	 */
	static public function createConnectionFile($name, $environment, $dsn,
		$username, $password, $encoding = "Arc4", $arc4_config_file = null)
	{
		# Make sure we have a name
		if (! $name)
		{
			throw new Exception("Connection name is required");
		}
			
		# Come up with the filename
		if ($environment)
		{
			$filename = strtolower($name . "." . $environment . ".conf");
			$env = $environment;
		}
		else
		{
			$filename = strtolower($name . ".conf");
			$env = "(default)";
		}
		
		# Make sure we have a DSN
		if (! $dsn)
		{
			throw new Exception("PDO DSN is required");
		}
		
		# Encode the username and password
		switch ($encoding)
		{
			case "Arc4":
				$enc_username = Arc4::encode($username);
				$enc_password = Arc4::encode($password);
				break;
			case "Obfuscation":
				$enc_username = StringObfuscator::encode($username);
				$enc_password = StringObfuscator::encode($password);
				break;
			default:
				throw new Exception("Invalid encoding: " . $encoding);
		}

        # See if we had an ARC4 encryption file
        $arc4_config_line = "";
        if ($arc4_config_file)
        {
            $arc4_config_line = "arc4 = " . $arc4_config_file;
        }
		
		# Create the contents of the file
		$file =
"# Begin Database Connection File

# Connection name: " . $name . "
# Environment: " . $env . "

dsn = " . $dsn . "
username = " . $enc_username . "
password = " . $enc_password . "
" . $arc4_config_line . "

# End Database connection File
";
		
		# Write out the contents
		file_put_contents($filename, $file);
	
		# Return the filename
		return $filename;
	}
}
?>
