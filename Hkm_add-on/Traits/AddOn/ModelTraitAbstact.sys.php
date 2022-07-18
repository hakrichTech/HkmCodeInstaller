<?php
namespace Hkm_traits\AddOn;

use Closure;

/**
 * 
 */
trait ModelTraitAbstact
{
   /**
	 * Fetches the row of database
	 * This methods works only with dbCalls
	 *
	 * @param boolean                   $singleton Single or multiple results
	 * @param array|integer|string|null $id        One primary key or an array of primary keys
	 *
	 * @return array|object|null The resulting row of data, or null.
	 */
	abstract protected static function DO_FIND(bool $singleton, $id = null);

	/**
	 * Fetches the column of database
	 * This methods works only with dbCalls
	 *
	 * @param string $columnName Column Name
	 *
	 * @return array|null The resulting row of data, or null if no data found.
	 *
	 * @throws DataException
	 */
	abstract protected static function DO_FIND_COLUMN(string $columnName);

	/**
	 * Fetches all results, while optionally limiting them.
	 * This methods works only with dbCalls
	 *
	 * @param int $direction  order By 1 = DESC, 0=ASC, 2 = RANDOM
	 * @param string $order  order
	 * @param integer $limit  Limit
	 * @param integer $offset Offset
	 *
	 * @return array
	 */
	abstract protected static function DO_FIND_ALL( int $direction = 1, string $order='' ,int $limit = 0, int $offset = 0);

	/**
	 * Returns the first row of the result set.
	 * This methods works only with dbCalls
	 *
	 * @return array|object|null
	 */
	abstract protected static function DO_FIRST();

	/**
	 * Inserts data into the current database
	 * This methods works only with dbCalls
	 *
	 * @param array $data Data
	 *
	 * @return integer|string|boolean
	 */
	abstract protected static function DO_INSERT(array $data);

	/**
	 * Compiles batch insert and runs the queries, validating each row prior.
	 * This methods works only with dbCalls
	 *
	 * @param array|null   $set       An associative array of insert values
	 * @param boolean|null $escape    Whether to escape values and identifiers
	 * @param integer      $batchSize The size of the batch to run
	 * @param boolean      $testing   True means only number of records is returned, false will execute the query
	 *
	 * @return integer|boolean Number of rows inserted or FALSE on failure
	 */
	abstract protected static function DO_INSERT_BATCH(?array $set = null, ?bool $escape = null, int $batchSize = 100, bool $testing = false);

	/**
	 * Updates a single record in the database.
	 * This methods works only with dbCalls
	 *
	 * @param integer|array|string|null $id   ID
	 * @param array|null                $data Data
	 *
	 * @return boolean
	 */
	abstract protected static function DO_UPDATE($id = null, $data = null): bool;

	/**
	 * Compiles an update and runs the query
	 * This methods works only with dbCalls
	 *
	 * @param array|null  $set       An associative array of update values
	 * @param string|null $index     The where key
	 * @param integer     $batchSize The size of the batch to run
	 * @param boolean     $returnSQL True means SQL is returned, false will execute the query
	 *
	 * @return mixed    Number of rows affected or FALSE on failure
	 *
	 * @throws DatabaseException
	 */
	abstract protected static function DO_UPDATE_BATCH(array $set = null, string $index = null, int $batchSize = 100, bool $returnSQL = false);

	/**
	 * Deletes a single record from the database where $id matches
	 * This methods works only with dbCalls
	 *
	 * @param integer|string|array|null $id    The rows primary key(s)
	 * @param boolean                   $purge Allows overriding the soft deletes setting.
	 *
	 * @return string|boolean
	 *
	 * @throws DatabaseException
	 */
	abstract protected static function DO_DELETE($id = null, bool $purge = false);

	/**
	 * Permanently deletes all rows that have been marked as deleted
	 * through soft deletes (deleted = 1)
	 * This methods works only with dbCalls
	 *
	 * @return boolean|mixed
	 */
	abstract protected static function DO_PURGE_DELETED();

	/**
	 * Works with the find* methods to return only the rows that
	 * have been deleted.
	 * This methods works only with dbCalls
	 *
	 * @return void
	 */
	abstract protected static function DO_ONLY_DELETED();

	/**
	 * Compiles a replace and runs the query
	 * This methods works only with dbCalls
	 *
	 * @param array|null $data      Data
	 * @param boolean    $returnSQL Set to true to return Query String
	 *
	 * @return mixed
	 */
	abstract protected static function DO_REPLACE(array $data = null, bool $returnSQL = false);

	/**
	 * Grabs the last error(s) that occurred from the Database connection.
	 * This methods works only with dbCalls
	 *
	 * @return array|null
	 */
	abstract protected static function DO_ERRORS();

	/**
	 * Returns the id value for the data array or object
	 *
	 * @param array|object $data Data
	 *
	 * @return integer|array|string|null
	 *
	 * @deprecated Add an override on getID_VALUE() instead. Will be removed in version 5.0.
	 */
	abstract protected static function ID_VALUE($data); 




    /**
	 * Override countAllResults to account for soft deleted accounts.
	 * This methods works only with dbCalls
	 *
	 * @param boolean $reset Reset
	 * @param boolean $test  Test
	 *
	 * @return mixed
	 */
	abstract public static function COUNT_ALL_RESULTS(bool $reset = true, bool $test = false);

	/**
	 * Loops over records in batches, allowing you to operate on them.
	 * This methods works only with dbCalls
	 *
	 * @param integer $size     Size
	 * @param Closure $userFunc Callback Function
	 *
	 * @return void
	 *
	 * @throws DataException
	 */
	abstract public static function CHUNCK(int $size, Closure $userFunc);
}
