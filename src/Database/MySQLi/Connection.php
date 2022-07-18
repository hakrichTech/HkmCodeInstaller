<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@hakrichteam.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Database\MySQLi;

use MySQLi;
use stdClass;
use Throwable;
use LogicException;
use mysqli_sql_exception;
use Hkm_code\Database\BaseConnection;
use Hkm_code\Database\Exceptions\DatabaseException;

/**
 * Connection for MySQLi
 */
class Connection extends BaseConnection
{
	/**
	 * Database driver
	 *
	 * @var string
	 */
	public static $DBDriver = 'MySQLi';

	/**
	 * DELETE hack flag
	 *
	 * Whether to use the MySQL "delete hack" which allows the number
	 * of affected rows to be shown. Uses a preg_replace when enabled,
	 * adding a bit more processing to all queries.
	 *
	 * @var boolean
	 */
	public static $deleteHack = true;

	// --------------------------------------------------------------------

	/**
	 * Identifier escape character
	 *
	 * @var string
	 */
	public static $escapeChar = '`';

	// --------------------------------------------------------------------

	/**
	 * MySQLi object
	 *
	 * Has to be preserved without being assigned to $conn_id.
	 *
	 * @var MySQLi
	 */
	public static $mysqli;


	public function __construct(array $params){

		parent::__construct($params);
	}
	//--------------------------------------------------------------------

	/**
	 * Connect to the database.
	 *
	 * @param boolean $persistent
	 *
	 * @return mixed
	 * @throws DatabaseException
	 */
	
	public  static function CONNECT(bool $persistent = false)
	{

		// Do we have a socket path?
		if (self::$hostname[0] === '/')
		{
			$hostname = null;
			$port     = null;
			$socket   = self::$hostname;
		}
		else
		{
			$hostname = ($persistent === true) ? 'p:' . self::$hostname : self::$hostname;
			$port     = empty(self::$port) ? null : self::$port;
			$socket   = '';
		}



		$clientFlags  = (self::$compress === true) ? MYSQLI_CLIENT_COMPRESS : 0;

		self::$mysqli = mysqli_init();


		mysqli_report(MYSQLI_REPORT_ALL & ~MYSQLI_REPORT_INDEX);

		self::$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);

		if (isset(self::$strictOn))
		{
			if (self::$strictOn)
			{
				self::$mysqli->options(MYSQLI_INIT_COMMAND,
					'SET SESSION sql_mode = CONCAT(@@sql_mode, ",", "STRICT_ALL_TABLES")');
			}
			else
			{
				self::$mysqli->options(MYSQLI_INIT_COMMAND, 'SET SESSION sql_mode =
						REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
												@@sql_mode,
												"STRICT_ALL_TABLES,", ""),
											",STRICT_ALL_TABLES", ""),
										"STRICT_ALL_TABLES", ""),
									"STRICT_TRANS_TABLES,", ""),
								",STRICT_TRANS_TABLES", ""),
							"STRICT_TRANS_TABLES", "")'
				);
			}
		}

		if (is_array(self::$encrypt))
		{
			$ssl = [];

			if (! empty(self::$encrypt['ssl_key']))
			{
				$ssl['key'] = self::$encrypt['ssl_key'];
			}
			if (! empty(self::$encrypt['ssl_cert']))
			{
				$ssl['cert'] = self::$encrypt['ssl_cert'];
			}
			if (! empty(self::$encrypt['ssl_ca']))
			{
				$ssl['ca'] = self::$encrypt['ssl_ca'];
			}
			if (! empty(self::$encrypt['ssl_capath']))
			{
				$ssl['capath'] = self::$encrypt['ssl_capath'];
			}
			if (! empty(self::$encrypt['ssl_hkmpher']))
			{
				$ssl['cipher'] = self::$encrypt['ssl_hkmpher'];
			}

			if (! empty($ssl))
			{
				if (isset(self::$encrypt['ssl_verify']))
				{
					if (self::$encrypt['ssl_verify'])
					{
						defined('MYSQLI_OPT_SSL_VERIFY_SERVER_CERT') &&
						self::$mysqli->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, 1);
					}
					// Apparently (when it exists), setting MYSQLI_OPT_SSL_VERIFY_SERVER_CERT
					// to FALSE didn't do anything, so PHP 5.6.16 introduced yet another
					// constant ...
					//
					// https://secure.php.net/ChangeLog-5.php#5.6.16
					// https://bugs.php.net/bug.php?id=68344
					elseif (defined('MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT') && version_compare(self::$mysqli->client_info, 'mysqlnd 5.6', '>='))
					{
						$clientFlags += MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT;
					}
				}

				$clientFlags += MYSQLI_CLIENT_SSL;
				self::$mysqli->ssl_set(
					$ssl['key'] ?? null, $ssl['cert'] ?? null, $ssl['ca'] ?? null,
					$ssl['capath'] ?? null, $ssl['cipher'] ?? null
				);
			}
		}

		try
		{
          
             
			
			if (self::$mysqli->real_connect($hostname, self::$username, self::$password,
				self::$database, $port, $socket, $clientFlags)
			)
			{
				

				// Prior to version 5.7.3, MySQL silently downgrades to an unencrypted connection if SSL setup fails
				if (($clientFlags & MYSQLI_CLIENT_SSL) && version_compare(self::$mysqli->client_info, 'mysqlnd 5.7.3', '<=')
					&& empty(self::$mysqli->query("SHOW STATUS LIKE 'ssl_hkmpher'")
										  ->fetch_object()->Value)
				)
				{
					self::$mysqli->close();
					$message = 'MySQLi was configured for an SSL connection, but got an unencrypted connection instead!';
					hkm_log_message('error', $message);

					if (self::$DBDebug)
					{
						throw new DatabaseException($message);
					}

					return false;
				}

				if (! self::$mysqli->set_charset(self::$charset))
				{
					$sc = self::$charset;
					hkm_log_message('error',
						"Database: Unable to set the configured connection charset ('{$sc}').");
					self::$mysqli->close();

					if (self::$DBDebug)
					{
						throw new DatabaseException('Unable to set client connection character set: ' . self::$charset);
					}

					return false;
				}

				// echo self::$database;
				return self::$mysqli;
			}
		}
		catch (Throwable $e)
		{

			
			// Clean sensitive information from errors.
			$msg = $e->getMessage();

			$msg = str_replace(self::$username, '****', $msg);
			$msg = str_replace(self::$password, '****', $msg);

			throw new DatabaseException($msg, $e->getCode(), $e);
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Keep or establish the connection if no queries have been sent for
	 * a length of time exceeding the server's idle timeout.
	 *
	 * @return void
	 */
	public static function RECONNECT()
	{
		self::CLOSE();
		self::$thiss::INITIALIZE();
	}

	//--------------------------------------------------------------------

	/**
	 * Close the database connection.
	 *
	 * @return void
	 */
	protected static function _CLOSE()
	{
		self::$connID->close();
	}

	//--------------------------------------------------------------------

	/**
	 * Select a specific database table to use.
	 *
	 * @param string $databaseName
	 *
	 * @return boolean
	 */
	public static function SET_DATABASE(string $databaseName): bool
	{
		
		if ($databaseName === '')
		{
			$databaseName = self::$thiss::$database;
		}

		if (empty(self::$connID))
		{
			self::$thiss::INITIALIZE();
		}

		if (self::$connID->select_db($databaseName))
		{
			self::$thiss::$database = $databaseName;

			return true;
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a string containing the version of the database being used.
	 *
	 * @return string
	 */
	public static function GET_VERSION(): string
	{
		if (isset(self::$dataCache['version']))
		{
			return self::$dataCache['version'];
		}

		if (empty(self::$mysqli))
		{
			self::$thiss::INITIALIZE();
		}

		return self::$dataCache['version'] = self::$mysqli->server_info;
	}

	//--------------------------------------------------------------------

	/**
	 * Executes the query against the database.
	 *
	 * @param string $sql
	 *
	 * @return mixed
	 */
	public static function EXECUTE(string $sql)
	{

		while (self::$connID->more_results())
		{
			self::$connID->next_result();
			if ($res = self::$connID->store_result())
			{
				$res->free();
			}
		}
		
		try
		{


			$rf = str_replace("\\",'\\\\',str_replace("'",'"',self::PREP_QUERY($sql)));
			
			return self::$connID->query($rf);
		}
		catch (mysqli_sql_exception $e)
		{
			hkm_log_message('error', $e->getMessage());
			if (self::$DBDebug)
			{
				throw $e;
			}
		}
		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Prep the query
	 *
	 * If needed, each database adapter can prep the query string
	 *
	 * @param string $sql an SQL query
	 *
	 * @return string
	 */
	protected static function PREP_QUERY(string $sql): string
	{
		// mysqli_affected_rows() returns 0 for "DELETE FROM TABLE" queries. This hack
		// modifies the query so that it a proper number of affected rows is returned.
		if (self::$deleteHack === true && preg_match('/^\s*DELETE\s+FROM\s+(\S+)\s*$/i', $sql))
		{
			return trim($sql) . ' WHERE 1=1';
		}

		return $sql;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the total number of rows affected by this query.
	 *
	 * @return integer
	 */
	public static function AFFECTED_ROWS(): int
	{
		return self::$connID->affected_rows ?? 0;
	}

	//--------------------------------------------------------------------

	/**
	 * Platform-dependant string escape
	 *
	 * @param  string $str
	 * @return string
	 */
	protected static function _ESCAPE_STRING(string $str): string
	{
		if (! self::$connID)
		{
			self::$thiss::INITIALIZE();
		}

		return self::$connID->real_escape_string($str);
	}

	//--------------------------------------------------------------------

	/**
	 * Escape Like String Direct
	 * There are a few instances where MySQLi queries cannot take the
	 * additional "ESCAPE x" parameter for specifying the escape character
	 * in "LIKE" strings, and this handles those directly with a backslash.
	 *
	 * @param  string|string[] $str Input string
	 * @return string|string[]
	 */
	public static function ESCAPE_LIKE_STRING_DIRECT($str)
	{
		if (is_array($str))
		{
			foreach ($str as $key => $val)
			{
				$str[$key] = self::ESCAPE_LIKE_STRING_DIRECT($val);
			}

			return $str;
		}

		$str = self::_ESCAPE_STRING($str);

		// Escape LIKE condition wildcards
		return str_replace([
			self::$likeEscapeChar,
			'%',
			'_',
		], [
			'\\' . self::$likeEscapeChar,
			'\\' . '%',
			'\\' . '_',
		], $str
		);
	}

	//--------------------------------------------------------------------

	/**
	 * Generates the SQL for listing tables in a platform-dependent manner.
	 * Uses ESCAPE_LIKE_STRING_DIRECT().
	 *
	 * @param boolean $prefixLimit
	 *
	 * @return string
	 */
	protected static function _LIST_TABLES(bool $prefixLimit = false): string
	{
		$sql = str_replace('"','','SHOW TABLES FROM ' . self::ESCAPE_IDENTIFIERS(self::$database));


		if ($prefixLimit !== false && self::$DBPrefix !== '')
		{
			return $sql . " LIKE '" . self::ESCAPE_LIKE_STRING_DIRECT(self::$DBPrefix) . "%'";
		}

		return $sql;
	}

	//--------------------------------------------------------------------

	/**
	 * Generates a platform-specific query string so that the column names can be fetched.
	 *
	 * @param string $table
	 *
	 * @return string
	 */
	protected static function _LIST_COLUMNS(string $table = ''): string
	{
		return 'SHOW COLUMNS FROM ' . self::PROTECT_INDENTIFIERS($table, true, null, false);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an array of objects with field data
	 *
	 * @param  string $table
	 * @return stdClass[]
	 * @throws DatabaseException
	 */
	public static function _FIELD_DATA(string $table): array
	{
		$table = self::PROTECT_INDENTIFIERS($table, true, null, false);

		if (($query = self::QUERY('SHOW COLUMNS FROM ' . $table)) === false)
		{
			throw new DatabaseException(hkm_lang('Database.failGetFieldData'));
		}
		$query = $query->getResultObject();

		$retVal = [];
		for ($i = 0, $c = count($query); $i < $c; $i++)
		{
			$retVal[$i]       = new stdClass();
			$retVal[$i]->name = $query[$i]->Field;

			sscanf($query[$i]->Type, '%[a-z](%d)', $retVal[$i]->type, $retVal[$i]->max_length);

			$retVal[$i]->nullable    = $query[$i]->Null === 'YES';
			$retVal[$i]->default     = $query[$i]->Default;
			$retVal[$i]->primary_key = (int) ($query[$i]->Key === 'PRI');
		}

		return $retVal;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an array of objects with index data
	 *
	 * @param  string $table
	 * @return stdClass[]
	 * @throws DatabaseException
	 * @throws LogicException
	 */
	public static function _INDEX_DATA(string $table): array
	{
		$table = self::PROTECT_INDENTIFIERS($table, true, null, false);

		if (($query = self::QUERY('SHOW INDEX FROM ' . $table)) === false)
		{
			throw new DatabaseException(hkm_lang('Database.failGetIndexData'));
		}

		if (! $indexes = $query->getResultArray())
		{
			return [];
		}

		$keys = [];

		foreach ($indexes as $index)
		{
			if (empty($keys[$index['Key_name']]))
			{
				$keys[$index['Key_name']]       = new stdClass();
				$keys[$index['Key_name']]->name = $index['Key_name'];

				if ($index['Key_name'] === 'PRIMARY')
				{
					$type = 'PRIMARY';
				}
				elseif ($index['Index_type'] === 'FULLTEXT')
				{
					$type = 'FULLTEXT';
				}
				elseif ($index['Non_unique'])
				{
					$type = $index['Index_type'] === 'SPATIAL' ? 'SPATIAL' : 'INDEX';
				}
				else
				{
					$type = 'UNIQUE';
				}

				$keys[$index['Key_name']]->type = $type;
			}

			$keys[$index['Key_name']]->fields[] = $index['Column_name'];
		}

		return $keys;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an array of objects with Foreign key data
	 *
	 * @param  string $table
	 * @return stdClass[]
	 * @throws DatabaseException
	 */
	public static function _FOREIGN_KEY_DATA(string $table): array
	{
		$sql = '
                    SELECT
                        tc.CONSTRAINT_NAME,
                        tc.TABLE_NAME,
                        kcu.COLUMN_NAME,
                        rc.REFERENCED_TABLE_NAME,
                        kcu.REFERENCED_COLUMN_NAME
                    FROM information_schema.TABLE_CONSTRAINTS AS tc
                    INNER JOIN information_schema.REFERENTIAL_CONSTRAINTS AS rc
                        ON tc.CONSTRAINT_NAME = rc.CONSTRAINT_NAME
                    INNER JOIN information_schema.KEY_COLUMN_USAGE AS kcu
                        ON tc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
                    WHERE
                        tc.CONSTRAINT_TYPE = ' . self::ESCAPE('FOREIGN KEY') . ' AND
                        tc.TABLE_SCHEMA = ' . self::ESCAPE(self::$database) . ' AND
                        tc.TABLE_NAME = ' . self::ESCAPE($table);

		if (($query = self::QUERY($sql)) === false)
		{
			throw new DatabaseException(hkm_lang('Database.failGetForeignKeyData'));
		}
		$query = $query->getResultObject();

		$retVal = [];
		foreach ($query as $row)
		{
			$obj                      = new stdClass();
			$obj->constraint_name     = $row->CONSTRAINT_NAME;
			$obj->table_name          = $row->TABLE_NAME;
			$obj->column_name         = $row->COLUMN_NAME;
			$obj->foreign_table_name  = $row->REFERENCED_TABLE_NAME;
			$obj->foreign_column_name = $row->REFERENCED_COLUMN_NAME;

			$retVal[] = $obj;
		}

		return $retVal;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns platform-specific SQL to disable foreign key checks.
	 *
	 * @return string
	 */
	protected static function _DISABLED_FOREIGN_KEY_CHECKS()
	{
		return 'SET FOREIGN_KEY_CHECKS=0';
	}

	//--------------------------------------------------------------------

	/**
	 * Returns platform-specific SQL to enable foreign key checks.
	 *
	 * @return string
	 */
	protected static function _ENABLE_FOREIGN_KEY_CHECKS()
	{
		return 'SET FOREIGN_KEY_CHECKS=1';
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the last error code and message.
	 * Must return this format: ['code' => string|int, 'message' => string]
	 * intval(code) === 0 means "no error".
	 *
	 * @return array<string,string|int>
	 */
	public static function ERROR(): array
	{
		if (! empty(self::$mysqli->connect_errno))
		{
			return [
				'code'    => self::$mysqli->connect_errno,
				'message' => self::$mysqli->connect_error,
			];
		}

		return [
			'code'    => self::$connID->errno,
			'message' => self::$connID->error,
		];
	}

	//--------------------------------------------------------------------

	/**
	 * Insert ID
	 *
	 * @return integer
	 */
	public static function INSERT_ID(): int
	{
		return self::$connID->insert_id;
	}

	//--------------------------------------------------------------------

	/**
	 * Begin Transaction
	 *
	 * @return boolean
	 */
	protected static function _TRANS_BEGIN(): bool
	{
		self::$connID->AUTOCOMMIT(false);

		return self::$connID->BEGIN_TRANSACTION();
	}

	//--------------------------------------------------------------------

	/**
	 * Commit Transaction
	 *
	 * @return boolean
	 */
	protected static function _TRANS_COMMIT(): bool
	{
		if (self::$connID->commit())
		{
			self::$connID->autoCommit(true);

			return true;
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Rollback Transaction
	 *
	 * @return boolean
	 */
	protected static function _TRANS_ROLLBACK(): bool
	{
		if (self::$connID->ROLLBACK())
		{
			self::$connID->AUTOCOMMIT(true);

			return true;
		}

		return false;
	}

	//--------------------------------------------------------------------
}
