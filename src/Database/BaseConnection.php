<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@hakrichteam.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Database;

use Closure;
use Hkm_code\Database\Exceptions\DatabaseException;
use Hkm_code\Vezirion\FileLocator;
use Throwable;

/**
 * Class BaseConnection
 */
abstract class BaseConnection implements ConnectionInterface
{
	/**
	 * Data Source Name / Connect string
	 *
	 * @var string
	 */
	protected static $DSN;

	/**
	 * Database port
	 *
	 * @var integer|string
	 */
	protected static $port = '';

	/**
	 * Hostname
	 *
	 * @var string
	 */
	protected static $hostname;

	/**
	 * Username
	 *
	 * @var string
	 */
	protected static $username;

	/**
	 * Password
	 *
	 * @var string
	 */
	protected static $password;

	/**
	 * Database name
	 *
	 * @var string
	 */
	protected static $database;

	/**
	 * Database driver
	 *
	 * @var string
	 */
	public static $DBDriver = 'MySQLi';

	/**
	 * Sub-driver
	 *
	 * @used-by HKM_DB_pdo_driver
	 * @var     string
	 */
	protected static $subdriver;

	/**
	 * Table prefix
	 *
	 * @var string
	 */
	public static $DBPrefix = '';

	/**
	 * Persistent connection flag
	 *
	 * @var boolean
	 */
	protected static $pConnect = false;

	/**
	 * Debug flag
	 *
	 * Whether to display error messages.
	 *
	 * @var boolean
	 */
	public static $DBDebug = false;

	/**
	 * Character set
	 *
	 * @var string
	 */
	public static $charset = 'utf8';

	/**
	 * Collation
	 *
	 * @var string
	 */
	public static $DBCollat = 'utf8_general_hkm';

	/**
	 * Swap Prefix
	 *
	 * @var string
	 */
	public static $swapPre = '';

	/**
	 * Encryption flag/data
	 *
	 * @var mixed
	 */
	protected static $encrypt = false;

	/**
	 * Compression flag
	 *
	 * @var boolean
	 */
	protected static $compress = false;

	/**
	 * Strict ON flag
	 *
	 * Whether we're running in strict SQL mode.
	 *
	 * @var boolean
	 */
	protected static $strictOn;

	/**
	 * Settings for a failover connection.
	 *
	 * @var array
	 */
	protected static $failover = [];

	//--------------------------------------------------------------------

	/**
	 * The last query object that was executed
	 * on this connection.
	 *
	 * @var mixed
	 */
	protected static $lastQuery;

	/**
	 * Connection ID
	 *
	 * @var object|resource|boolean
	 */
	public static $connID = false;

	/**
	 * Result ID
	 *
	 * @var object|resource|boolean
	 */
	public static $resultID = false;

	/**
	 * Protect identifiers flag
	 *
	 * @var boolean
	 */
	public static $protectIdentifiers = true;

	/**
	 * List of reserved identifiers
	 *
	 * Identifiers that must NOT be escaped.
	 *
	 * @var array
	 */
	protected static $reservedIdentifiers = ['*'];

	/**
	 * Identifier escape character
	 *
	 * @var string|array
	 */
	public static $escapeChar = '"';

	/**
	 * ESCAPE statement string
	 *
	 * @var string
	 */
	public static $likeEscapeStr = " ESCAPE '%s' ";

	/**
	 * ESCAPE character
	 *
	 * @var string
	 */
	public static $likeEscapeChar = '!';

	/**
	 * Holds previously looked up data
	 * for performance reasons.
	 *
	 * @var array
	 */
	public static $dataCache = [];

	/**
	 * Microtime when connection was made
	 *
	 * @var float
	 */
	protected static $connectTime;

	/**
	 * How long it took to establish connection.
	 *
	 * @var float
	 */
	protected static $connectDuration;

	/**
	 * If true, no queries will actually be
	 * ran against the database.
	 *
	 * @var boolean
	 */
	protected static $pretend = false;

	/**
	 * Transaction enabled flag
	 *
	 * @var boolean
	 */
	public static $transEnabled = true;

	/**
	 * Strict transaction mode flag
	 *
	 * @var boolean
	 */
	public static $transStrict = true;

	/**
	 * Transaction depth level
	 *
	 * @var integer
	 */
	protected static $transDepth = 0;

	/**
	 * Transaction status flag
	 *
	 * Used with transactions to determine if a rollback should occur.
	 *
	 * @var boolean
	 */
	protected static $transStatus = true;

	/**
	 * Transaction failure flag
	 *
	 * Used with transactions to determine if a transaction has failed.
	 *
	 * @var boolean
	 */
	protected static $transFailure = false;

	/**
	 * Array of table aliases.
	 *
	 * @var array
	 */
	protected static $aliasedTables = [];
	protected static $thiss;

	/**
	 * Query Class
	 *
	 * @var string
	 */
	protected static $queryClass = 'Hkm_code\\Database\\Query';

	//--------------------------------------------------------------------

	/**
	 * Saves our connection settings.
	 *
	 * @param array $params
	 */
	public function __construct(array $params)
	{
		
		self::$thiss = $this;
		foreach ($params as $key => $value)
		{
			self::$thiss::$$key = $value;
		}

		$queryClass = str_replace('Connection', 'Query', static::class);
		$f = FileLocator::SEARCH('Database/Query')[0];
		$class = FileLocator::GET_CLASS_NAME($f);


		if (class_exists($class))
		{
			self::$queryClass = $class;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Initializes the database connection/settings.
	 *
	 * @return mixed|void
	 * @throws DatabaseException
	 */
	public static function INITIALIZE()
	{
		/* If an established connection is available, then there's
		 * no need to connect and select the database.
		 *
		 * Depending on the database driver, conn_id can be either
		 * boolean TRUE, a resource or an object.
		 * 
		 * THIS STOP TO CREATE CONNECTION EACH AND EVERY TIME IT ONLY ONCE IN TH APP
		//  */
		// if (self::$connID)
		// {
			
		// 	return;
		// }

		//--------------------------------------------------------------------

		self::$connectTime = microtime(true);
		$connectionErrors  = [];

		try
		{
			// Connect to the database and set the connection ID
			self::$connID = self::$thiss::CONNECT(self::$pConnect);
		}

		catch (Throwable $e)
		
		{
			$connectionErrors[] = sprintf('Main connection [%s]: %s', self::$DBDriver, $e->getMessage());
			hkm_log_message('error', 'Error connecting to the database: ' . $e->getMessage());
		}

		// No connection resource? Check if there is a failover else throw an error
		if (! self::$connID)
		{
			// Check if there is a failover set
			if (! empty(self::$failover) && is_array(self::$failover))
			{
				// Go over all the failovers
				foreach (self::$failover as $index => $failover)
				{
					// Replace the current settings with those of the failover
					foreach ($failover as $key => $val)
					{
						if (property_exists(self::$thiss, $key))
						{
							self::$$key = $val;
						}
					}

					try
					{
						// Try to connect
						self::$connID = self::$thiss::CONNECT(self::$pConnect);
					}
					catch (Throwable $e)
					{
						$connectionErrors[] = sprintf('Failover #%d [%s]: %s', ++$index, self::$DBDriver, $e->getMessage());
						hkm_log_message('error', 'Error connecting to the database: ' . $e->getMessage());
					}

					// If a connection is made break the foreach loop
					if (self::$connID)
					{
						break;
					}
				}
			}

			// We still don't have a connection?
			if (! self::$connID)
			{
				throw new DatabaseException(sprintf(
					'Unable to connect to the database.%s%s',
					PHP_EOL,
					implode(PHP_EOL, $connectionErrors)
				));
			}
		}

		self::$connectDuration = microtime(true) - self::$connectTime;
	}

	//--------------------------------------------------------------------

	/**
	 * Connect to the database.
	 *
	 * @param  boolean $persistent
	 * @return mixed
	 */
	abstract public static function CONNECT(bool $persistent = false);

	//--------------------------------------------------------------------

	/**
	 * Close the database connection.
	 *
	 * @return void
	 */
	public static function CLOSE()
	{
		if (self::$connID)
		{
			self::$thiss::_CLOSE();
			self::$connID = false;
		}
	}


	abstract protected static function _CLOSE();

	
	public static function PERSISTENT_CONNECT()
	{
		return self::CONNECT(true);
	}

	
	abstract public static function RECONNECT();

	
	public static function GET_CONNECTION(string $alias = null)
	{
		//@todo work with read/write connections
		return self::$connID;
	}

	
	abstract public static function SET_DATABASE(string $databaseName);

	
	public static function GET_DATABASE(): string
	{
		return empty(self::$thiss::$database) ? '' : self::$thiss::$database;
	}

	
	public static function SET_PREFIX(string $prefix = ''): string
	{
		return self::$thiss::$DBPrefix = $prefix;
	}

	
	public static function GET_PREFIX(): string
	{
		return self::$thiss::$DBPrefix;
	}

	
	public static function GET_PLATFORM(): string
	{
		return self::$DBDriver;
	}

	
	abstract public static function GET_VERSION(): string;

	public static function SET_ALIASED_TABLES(array $aliases)
	{
		self::$aliasedTables = $aliases;

		return self::$thiss;
	}

	//--------------------------------------------------------------------

	
	public static function ADD_TABLE_ALIAS(string $table)
	{
		if (! in_array($table, self::$aliasedTables, true))
		{
			self::$aliasedTables[] = $table;
		}

		return self::$thiss;
	}

	
	abstract protected static function EXECUTE(string $sql);

	
	public static function QUERY(string $sql, $binds = null, bool $setEscapeFlags = true, string $queryClass = '')
	{
		$queryClass = $queryClass ?: self::$queryClass;
		

		if (empty(self::$thiss::$connID))
		{

			self::$thiss::INITIALIZE();
		}

		/**
		 * @var Query $query
		 */
		$query = new $queryClass(self::$thiss);

		$query::SET_QUERY($sql, $binds, $setEscapeFlags);

		
		
		if (! empty(self::$swapPre) && ! empty(self::$thiss::$DBPrefix))
		{
			$query::SWAP_PREFIX(self::$thiss::$DBPrefix, self::$swapPre);
		}


		$startTime = microtime(true);

		// Always save the last query so we can use
		// the GET_LAST_QUERY() method.
		self::$lastQuery = $query;

		// print_r($query::GET_QUERY());
		// Run the query for real

		if (! self::$pretend && false === (self::$resultID = self::SIMPLE_QUERY($query::GET_QUERY())))
		{


			$query::SET_DURATION($startTime, $startTime);

			if (self::$transDepth !== 0)
			{
				self::$transStatus = false;
			}

			if (self::$DBDebug)
			{
				
				while (self::$transDepth !== 0)
				{
					$transDepth = self::$transDepth;
					self::TRANS_COMPLETE();

					if ($transDepth === self::$transDepth)
					{
						hkm_log_message('error', 'Database: Failure during an automated transaction commit/rollback!');
						break;
					}
				}
				return false;
			}

			if (! self::$pretend)
			{
				// Let others do something with this query.
				// Events::TRIGGER('DBQuery', $query);
			}

			return false;
		}
		


		$query::SET_DURATION($startTime);

		if (! self::$pretend)
		{

			// Let others do something with this query
			// Events::TRIGGER('DBQuery', $query);
		}

		
		if (self::$pretend)
		{

			return $query;
		}

		// resultID is not false, so it must be successful
		if (self::IS_WRITE_TYPE($sql))
		{
			
			return true;
		}


		$resultClass = str_replace('Connection', 'Result', get_class(self::$thiss));
		
		return new $resultClass(self::$connID, self::$resultID);
	}

	
	public static function SIMPLE_QUERY(string $sql)
	{
		if (empty(self::$connID))
		{
			self::$thiss::INITIALIZE();
		}
		return self::$thiss::EXECUTE($sql);
	}

	
	public static function TRANS_OFF()
	{
		self::$transEnabled = false;
	}

	//--------------------------------------------------------------------

	
	public static function TRANS_STRICT(bool $mode = true)
	{
		self::$transStrict = $mode;

		return self::$thiss;
	}

	public static function TRANS_START(bool $testMode = false): bool
	{
		if (! self::$transEnabled)
		{
			return false;
		}

		return self::TRANS_BEGIN($testMode);
	}

	
	public static function TRANS_COMPLETE(): bool
	{
		if (! self::$transEnabled)
		{
			return false;
		}

		if (self::$transStatus === false || self::$transFailure === true)
		{
			self::TRANS_ROLLBACK();

			if (self::$transStrict === false)
			{
				self::$transStatus = true;
			}

			hkm_log_message('debug', 'DB Transaction Failure');
			return false;
		}

		return self::TRANS_COMMIT();
	}

	
	public static function TRANS_STATUS(): bool
	{
		return self::$transStatus;
	}

	public static function TRANS_BEGIN(bool $testMode = false): bool
	{
		if (! self::$transEnabled)
		{
			return false;
		}

		if (self::$transDepth > 0)
		{
			self::$transDepth ++;
			return true;
		}

		if (empty(self::$connID))
		{
			self::$thiss::INITIALIZE();
		}

		
		self::$transFailure = ($testMode === true);

		if (self::_TRANS_BEGIN())
		{
			self::$transDepth ++;
			return true;
		}

		return false;
	}

	
	public static function TRANS_COMMIT(): bool
	{
		if (! self::$transEnabled || self::$transDepth === 0)
		{
			return false;
		}

		if (self::$transDepth > 1 || self::_TRANS_COMMIT())
		{
			self::$transDepth --;
			return true;
		}

		return false;
	}


	public static function TRANS_ROLLBACK(): bool
	{
		if (! self::$transEnabled || self::$transDepth === 0)
		{
			return false;
		}

		// When transactions are nested we only begin/commit/rollback the outermost ones
		if (self::$transDepth > 1 || self::_TRANS_ROLLBACK())
		{
			self::$transDepth --;
			return true;
		}

		return false;
	}

	
	abstract protected static function _TRANS_BEGIN(): bool;

	abstract protected static function _TRANS_COMMIT(): bool;


	abstract protected static function _TRANS_ROLLBACK(): bool;

	public static function TABLE($tableName)
	{
		if (empty($tableName))
		{
			throw new DatabaseException('You must set the database table to be used with your query.');
		}

		$className = str_replace('Connection', 'Builder', get_class(self::$thiss));

		return new $className($tableName, self::$thiss);
	}

	
	public static function PREPARE(Closure $func, array $options = [])
	{
		if (empty(self::$connID))
		{
			self::$thiss::INITIALIZE();
		}

		self::PRETEND();

		$sql = $func(self::$thiss);

		self::PRETEND(false);

		if ($sql instanceof QueryInterface)
		{
			// @phpstan-ignore-next-line
			$sql = $sql::GET_ORIGINAL_QUERY();
		} 

		$class = str_ireplace('Connection', 'PreparedQuery', get_class(self::$thiss));
		/**
		 * @var BasePreparedQuery $class
		 */
		$class = new $class(self::$thiss);

		return $class->prepare($sql, $options);
	}

	
	public static function GET_LAST_QUERY()
	{
		return self::$lastQuery;
	}

	
	public static function SHOW_LAST_QUERY(): string
	{
		return (string) self::$lastQuery;
	}

	
	public static function GET_CONNECT_START(): ?float
	{
		return self::$connectTime;
	}

	public static function GET_CONNECT_DURATION(int $decimals = 6): string
	{
		return number_format(self::$connectDuration, $decimals);
	}

	
	public static function PROTECT_INDENTIFIERS($item, bool $prefixSingle = false, bool $protectIdentifiers = null, bool $fieldExists = true)
	{
		if (! is_bool($protectIdentifiers))
		{
			$protectIdentifiers = self::$protectIdentifiers;
		}

		if (is_array($item))
		{
			$escapedArray = [];
			foreach ($item as $k => $v)
			{
				$escapedArray[self::PROTECT_INDENTIFIERS($k)] = self::PROTECT_INDENTIFIERS($v, $prefixSingle, $protectIdentifiers, $fieldExists);
			}

			return $escapedArray;
		}

		// This is basically a bug fix for queries that use MAX, MIN, etc.
		// If a parenthesis is found we know that we do not need to
		// escape the data or add a prefix. There's probably a more graceful
		// way to deal with this, but I'm not thinking of it
		//
		// Added exception for single quotes as well, we don't want to alter
		// literal strings.
		if (strcspn($item, "()'") !== strlen($item))
		{
			return $item;
		}

		// Convert tabs or multiple spaces into single spaces
		$item = preg_replace('/\s+/', ' ', trim($item));

		// If the item has an alias declaration we remove it and set it aside.
		// Note: strripos() is used in order to support spaces in table names
		if ($offset = strripos($item, ' AS '))
		{
			$alias = ($protectIdentifiers) ? substr($item, $offset, 4) . self::ESCAPE_IDENTIFIERS(substr($item, $offset + 4)) : substr($item, $offset);
			$item  = substr($item, 0, $offset);
		}
		elseif ($offset = strrpos($item, ' '))
		{
			$alias = ($protectIdentifiers) ? ' ' . self::ESCAPE_IDENTIFIERS(substr($item, $offset + 1)) : substr($item, $offset);
			$item  = substr($item, 0, $offset);
		}
		else
		{
			$alias = '';
		}

		// Break the string apart if it contains periods, then insert the table prefix
		// in the correct location, assuming the period doesn't indicate that we're dealing
		// with an alias. While we're at it, we will escape the components
		if (strpos($item, '.') !== false)
		{
			$parts = explode('.', $item);

			// Does the first segment of the exploded item match
			// one of the aliases previously identified? If so,
			// we have nothing more to do other than escape the item
			//
			// NOTE: The ! empty() condition prevents this method
			//       from breaking when QB isn't enabled.
			if (! empty(self::$aliasedTables) && in_array($parts[0], self::$aliasedTables, true))
			{
				if ($protectIdentifiers === true)
				{
					foreach ($parts as $key => $val)
					{
						if (! in_array($val, self::$reservedIdentifiers, true))
						{
							$parts[$key] = self::ESCAPE_IDENTIFIERS($val);
						}
					}

					$item = implode('.', $parts);
				}

				return $item . $alias;
			}

			// Is there a table prefix defined in the config file? If not, no need to do anything
			if (self::$thiss::$DBPrefix !== '')
			{
				// We now add the table prefix based on some logic.
				// Do we have 4 segments (hostname.database.table.column)?
				// If so, we add the table prefix to the column name in the 3rd segment.
				if (isset($parts[3]))
				{
					$i = 2;
				}
				// Do we have 3 segments (database.table.column)?
				// If so, we add the table prefix to the column name in 2nd position
				elseif (isset($parts[2]))
				{
					$i = 1;
				}
				// Do we have 2 segments (table.column)?
				// If so, we add the table prefix to the column name in 1st segment
				else
				{
					$i = 0;
				}

				// This flag is set when the supplied $item does not contain a field name.
				// This can happen when this function is being called from a JOIN.
				if ($fieldExists === false)
				{
					$i++;
				}

				// Verify table prefix and replace if necessary
				if (self::$swapPre !== '' && strpos($parts[$i], self::$swapPre) === 0)
				{
					$parts[$i] = preg_replace('/^' . self::$swapPre . '(\S+?)/', self::$thiss::$DBPrefix . '\\1', $parts[$i]);
				}
				// We only add the table prefix if it does not already exist
				elseif (strpos($parts[$i], self::$thiss::$DBPrefix) !== 0)
				{
					$parts[$i] = self::$thiss::$DBPrefix . $parts[$i];
				}

				// Put the parts back together
				$item = implode('.', $parts);
			}

			if ($protectIdentifiers === true)
			{
				$item = self::ESCAPE_IDENTIFIERS($item);
			}

			return $item . $alias;
		}

		// In some cases, especially 'from', we end up running through
		// protect_identifiers twice. This algorithm won't work when
		// it contains the escapeChar so strip it out.
		$item = trim($item, self::$escapeChar);

		// Is there a table prefix? If not, no need to insert it
		if (self::$thiss::$DBPrefix !== '')
		{
			// Verify table prefix and replace if necessary
			if (self::$swapPre !== '' && strpos($item, self::$swapPre) === 0)
			{
				$item = preg_replace('/^' . self::$swapPre . '(\S+?)/', self::$thiss::$DBPrefix . '\\1', $item);
			}
			// Do we prefix an item with no segments?
			elseif ($prefixSingle === true && strpos($item, self::$thiss::$DBPrefix) !== 0)
			{
				$item = self::$thiss::$DBPrefix . $item;
			}
		}

		if ($protectIdentifiers === true && ! in_array($item, self::$reservedIdentifiers, true))
		{
			$item = self::ESCAPE_IDENTIFIERS($item);
		}

		return $item . $alias;
	}

	public static function ESCAPE_IDENTIFIERS($item)
	{
		if (self::$escapeChar === '' || empty($item) || in_array($item, self::$reservedIdentifiers, true))
		{
			return $item;
		}

		if (is_array($item))
		{
			foreach ($item as $key => $value)
			{
				$item[$key] = self::ESCAPE_IDENTIFIERS($value);
			}

			return $item;
		}

		// Avoid breaking functions and literal values inside queries
		if (ctype_digit($item)
			|| $item[0] === "'"
			|| (self::$escapeChar !== '"' && $item[0] === '"')
			|| strpos($item, '(') !== false)
		{
			return $item;
		}

		static $pregEc = [];

		if (empty($pregEc))
		{
			if (is_array(self::$escapeChar))
			{
				$pregEc = [
					preg_quote(self::$escapeChar[0], '/'),
					preg_quote(self::$escapeChar[1], '/'),
					self::$escapeChar[0],
					self::$escapeChar[1],
				];
			}
			else
			{
				$pregEc[0] = $pregEc[1] = preg_quote(self::$escapeChar, '/');
				$pregEc[2] = $pregEc[3] = self::$escapeChar;
			}
		}

		foreach (self::$reservedIdentifiers as $id)
		{
			if (strpos($item, '.' . $id) !== false)
			{
				return preg_replace('/' . $pregEc[0] . '?([^' . $pregEc[1] . '\.]+)' . $pregEc[1] . '?\./i', $pregEc[2] . '$1' . $pregEc[3] . '.', $item);
			}
		}

		return preg_replace('/' . $pregEc[0] . '?([^' . $pregEc[1] . '\.]+)' . $pregEc[1] . '?(\.)?/i', $pregEc[2] . '$1' . $pregEc[3] . '$2', $item);
	}

	//--------------------------------------------------------------------

	public static function PREFIX_TABLE(string $table = ''): string
	{
		if ($table === '')
		{
			throw new DatabaseException('A table name is required for that operation.');
		}

		return self::$thiss::$DBPrefix . $table;
	}

	
	abstract public static function AFFECTED_ROWS(): int;

	public static function ESCAPE($str)
	{
		if (is_array($str))
		{
			return array_map([&self::$thiss, 'escape'], $str);
		}

		if (is_string($str) || (is_object($str) && method_exists($str, '__toString')))
		{
			return "'" . self::ESCAPE_STRING($str) . "'";
		}

		if (is_bool($str))
		{
			return ($str === false) ? 0 : 1;
		}

		if (is_numeric($str) && $str < 0)
		{
			return "'{$str}'";
		}

		if ($str === null)
		{
			return 'NULL';
		}

		return $str;
	}

	
	public static function ESCAPE_STRING($str, bool $like = false)
	{
		if (is_array($str))
		{
			foreach ($str as $key => $val)
			{
				$str[$key] = self::ESCAPE_STRING($val, $like);
			}

			return $str;
		}

		$str = self::_ESCAPE_STRING($str);

		// escape LIKE condition wildcards
		if ($like === true)
		{
			return str_replace([
				self::$likeEscapeChar,
				'%',
				'_',
			], [
				self::$likeEscapeChar . self::$likeEscapeChar,
				self::$likeEscapeChar . '%',
				self::$likeEscapeChar . '_',
			], $str
			);
		}

		return $str;
	}

	
	public static function ESCAPE_LIKE_SRING($str)
	{
		return self::ESCAPE_STRING($str, true);
	}

	
	protected static function _ESCAPE_STRING(string $str): string
	{
		return str_replace("'", "''", hkm_remove_invisible_characters($str, false));
	}

	
	public static function CALL_FUNCTION(string $functionName, ...$params): bool
	{
		$driver = strtolower(self::$DBDriver);
		$driver = ($driver === 'postgre' ? 'pg' : $driver) . '_';

		if (false === strpos($driver, $functionName))
		{
			$functionName = $driver . $functionName;
		}

		if (! function_exists($functionName))
		{
			if (self::$DBDebug)
			{
				throw new DatabaseException('This feature is not available for the database you are using.');
			}

			return false;
		}

		return $functionName(...$params);
	}

	
	public static function LIST_TABLES(bool $constrainByPrefix = false)
	{
		// Is there a cached result?
		if (isset(self::$dataCache['table_names']) && self::$dataCache['table_names'])
		{
			$d = self::$thiss::$DBPrefix;
			return $constrainByPrefix ?
				preg_grep("/^{$d}/", self::$dataCache['table_names'])
				: self::$dataCache['table_names'];
		}

		if (false === ($sql = self::$thiss::_LIST_TABLES($constrainByPrefix)))
		{
			if (self::$DBDebug)
			{
				throw new DatabaseException('This feature is not available for the database you are using.');
			}
			return false;
		}

		self::$dataCache['table_names'] = [];
		$query = self::$thiss::QUERY($sql);
		foreach ($query->getResultArray() as $row)
		{
			// Do we know from which column to get the table name?
			if (! isset($key))
			{
				if (isset($row['table_name']))
				{
					$key = 'table_name';
				}
				elseif (isset($row['TABLE_NAME']))
				{
					$key = 'TABLE_NAME';
				}
				else
				{
					
					$key = array_keys($row);
					$key = array_shift($key);
				}
			}

			self::$dataCache['table_names'][] = $row[$key];
		}

		return self::$dataCache['table_names'];
	}

	
	public static function TABLE_EXISTS(string $tableName): bool
	{
		return in_array(self::PROTECT_INDENTIFIERS($tableName, true, false, false), self::LIST_TABLES(), true);
	}

	
	public static function GET_FIELD_NAMES(string $table)
	{
		// Is there a cached result?
		if (isset(self::$dataCache['field_names'][$table]))
		{
			return self::$dataCache['field_names'][$table];
		}

		if (empty(self::$connID))
		{
			self::$thiss::INITIALIZE();
		}

		if (false === ($sql = self::_LIST_COLUMNS($table)))
		{
			if (self::$DBDebug)
			{
				throw new DatabaseException('This feature is not available for the database you are using.');
			}
			return false;
		}

		$query                                  = self::QUERY($sql);
		self::$dataCache['field_names'][$table] = [];

		foreach ($query::GET_RESULT_ARRAY() as $row)
		{
			// Do we know from where to get the column's name?
			if (! isset($key))
			{
				if (isset($row['column_name']))
				{
					$key = 'column_name';
				}
				elseif (isset($row['COLUMN_NAME']))
				{
					$key = 'COLUMN_NAME';
				}
				else
				{
					// We have no other choice but to just get the first element's key.
					$key = key($row);
				}
			}

			self::$dataCache['field_names'][$table][] = $row[$key];
		}

		return self::$dataCache['field_names'][$table];
	}

	
	public static function FIELD_EXISTS(string $fieldName, string $tableName): bool
	{
		return in_array($fieldName, self::GET_FIELD_NAMES($tableName), true);
	}

	
	public static function GET_FIELD_DATA(string $table)
	{
		return self::_FIELD_DATA(self::PROTECT_INDENTIFIERS($table, true, false, false));
	}

	
	public static function GET_INDEX_DATA(string $table)
	{
		return self::_INDEX_DATA(self::PROTECT_INDENTIFIERS($table, true, false, false));
	}

	
	public static function GET_FOREIGN_KEY_DATA(string $table)
	{
		return self::_FOREIGN_KEY_DATA(self::PROTECT_INDENTIFIERS($table, true, false, false));
	}

	public static function DISABLED_FOREIGN_KEY_CHECKS()
	{
		$sql = self::$thiss::_DISABLED_FOREIGN_KEY_CHECKS();

		return self::QUERY($sql);
	}

	
	public static function ENABLE_FOREIGN_KEY_CHECKS()
	{
		$sql = self::$thiss::_ENABLE_FOREIGN_KEY_CHECKS();

		return self::QUERY($sql);
	}

	
	public static function PRETEND(bool $pretend = true)
	{
		self::$pretend = $pretend;

		return self::$thiss;
	}

	
	public static function RESET_DATA_CACHE()
	{
		self::$dataCache = [];

		return self::$thiss;
	}

	
	public static function IS_WRITE_TYPE($sql): bool
	{
		return (bool) preg_match('/^\s*"?(SET|INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|TRUNCATE|LOAD|COPY|ALTER|RENAME|GRANT|REVOKE|LOCK|UNLOCK|REINDEX|MERGE)\s/i', $sql);
	}

	
	abstract public static function ERROR(): array;

	
	abstract public static function INSERT_ID();

	
	abstract protected static function _LIST_TABLES(bool $constrainByPrefix = false);

	
	abstract protected static function _LIST_COLUMNS(string $table = '');

	abstract protected static function _FIELD_DATA(string $table): array;

	
	abstract protected static function _INDEX_DATA(string $table): array;

	abstract protected static function _FOREIGN_KEY_DATA(string $table): array;

	
	public  function __get(string $key)
	{
		if (property_exists($this, $key))
		{
			return $this::$$key;
		}

		return null;
	}

	
	public  function __isset(string $key): bool
	{
		return property_exists($this, $key);
	}

	//--------------------------------------------------------------------

}
