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

/**
 * Interface ConnectionInterface
 */
interface ConnectionInterface
{
	/**
	 * Initializes the database connection/settings.
	 *
	 * @return mixed
	 */
	public static function INITIALIZE();

	//--------------------------------------------------------------------

	/**
	 * Connect to the database.
	 *
	 * @param  boolean $persistent
	 * @return mixed
	 */
	public static function CONNECT(bool $persistent = false);

	//--------------------------------------------------------------------

	/**
	 * Create a persistent database connection.
	 *
	 * @return mixed
	 */
	public static function PERSISTENT_CONNECT();

	//--------------------------------------------------------------------

	/**
	 * Keep or establish the connection if no queries have been sent for
	 * a length of time exceeding the server's idle timeout.
	 *
	 * @return mixed
	 */
	public static function RECONNECT();

	//--------------------------------------------------------------------

	/**
	 * Returns the actual connection object. If both a 'read' and 'write'
	 * connection has been specified, you can pass either term in to
	 * get that connection. If you pass either alias in and only a single
	 * connection is present, it must return the sole connection.
	 *
	 * @param string|null $alias
	 *
	 * @return mixed
	 */
	public static function GET_CONNECTION(string $alias = null);

	//--------------------------------------------------------------------

	/**
	 * Select a specific database table to use.
	 *
	 * @param string $databaseName
	 *
	 * @return mixed
	 */
	public static function SET_DATABASE(string $databaseName);

	//--------------------------------------------------------------------

	/**
	 * Returns the name of the current database being used.
	 *
	 * @return string
	 */
	public static function GET_DATABASE(): string;

	//--------------------------------------------------------------------

	/**
	 * Returns the last error encountered by this connection.
	 * Must return this format: ['code' => string|int, 'message' => string]
	 * intval(code) === 0 means "no error".
	 *
	 * @return array<string,string|int>
	 */
	public static function ERROR(): array;

	//--------------------------------------------------------------------

	/**
	 * The name of the platform in use (MySQLi, mssql, etc)
	 *
	 * @return string
	 */
	public static function GET_PLATFORM(): string;

	//--------------------------------------------------------------------

	/**
	 * Returns a string containing the version of the database being used.
	 *
	 * @return string
	 */
	public static function GET_VERSION(): string;

	//--------------------------------------------------------------------
	/**
	 * Orchestrates a query against the database. Queries must use
	 * Database\Statement objects to store the query and build it.
	 * This method works with the cache.
	 *
	 * Should automatically handle different connections for read/write
	 * queries if needed.
	 *
	 * @param string $sql
	 * @param mixed  ...$binds
	 *
	 * @return BaseResult|Query|boolean
	 */
	public static function QUERY(string $sql, $binds = null);

	//--------------------------------------------------------------------

	/**
	 * Performs a basic query against the database. No binding or caching
	 * is performed, nor are transactions handled. Simply takes a raw
	 * query string and returns the database-specific result id.
	 *
	 * @param string $sql
	 *
	 * @return mixed
	 */
	public static function SIMPLE_QUERY(string $sql);

	//--------------------------------------------------------------------
	/**
	 * Returns an instance of the query builder for this connection.
	 *
	 * @param string|array $tableName Table name.
	 *
	 * @return BaseBuilder Builder.
	 */
	public static function TABLE($tableName);

	//--------------------------------------------------------------------

	/**
	 * Returns the last query's statement object.
	 *
	 * @return mixed
	 */
	public static function GET_LAST_QUERY();

	//--------------------------------------------------------------------

	/**
	 * "Smart" Escaping
	 *
	 * Escapes data based on type.
	 * Sets boolean and null types.
	 *
	 * @param mixed $str
	 *
	 * @return mixed
	 */
	public static function ESCAPE($str);

	//--------------------------------------------------------------------

	/**
	 * Allows for custom calls to the database engine that are not
	 * supported through our database layer.
	 *
	 * @param string $functionName
	 * @param array  ...$params
	 *
	 * @return mixed
	 */
	public static function CALL_FUNCTION(string $functionName, ...$params);

	//--------------------------------------------------------------------

	/**
	 * Determines if the statement is a write-type query or not.
	 *
	 * @param  string $sql
	 * @return boolean
	 */
	public static function IS_WRITE_TYPE($sql): bool;
}
