<?php


namespace Hkm_code;

use BadMethodCallException;
use Closure;
use Hkm_code\Database\BaseBuilder;
use Hkm_code\Database\BaseConnection;
use Hkm_code\Database\BaseResult;
use Hkm_code\Database\ConnectionInterface;
use Hkm_code\Database\Exceptions\DatabaseException;
use Hkm_code\Database\Exceptions\DataException;
use Hkm_code\Database\Query;
use Hkm_code\Exceptions\ModelException;
use Hkm_code\I18n\Time;
use Hkm_code\Validation\ValidationInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Hkm_traits\AddOn\ModelSetGetTrait;

/**
 * Class Model
 *
 * The Model class extends BaseModel and provides additional
 * convenient features that makes working with a SQL database
 * table less painful.
 *
 * It will:
 *      - automatically connect to database
 *      - allow intermingling calls to the builder
 *      - removes the need to use Result object directly in most cases
 *
 * @mixin    BaseBuilder
 * @property BaseConnection $db
 */
class Model extends BaseModel
{
	/**
	 * Name of database table
	 *
	 * @var string
	 */
	public static $table;

	/**
	 * The table's primary key.
	 *
	 * @var string
	 */
	protected static $primaryKey = 'id';

	/**
	 * Whether primary key uses auto increment.
	 *
	 * @var boolean
	 */
	protected static $useAutoIncrement = true;


	protected static $cachedResponse        = [];

	/**
	 * Query Builder object
	 *
	 * @var BaseBuilder|null
	 */
	protected static $builder;

	/**
	 * Holds information passed in via 'set'
	 * so that we can capture it (not the builder)
	 * and ensure it gets validated first.
	 *
	 * @var array
	 */
	protected static $tempData = [];

	/**
	 * Escape array that maps usage of escape
	 * flag for every parameter.
	 *
	 * @var array
	 */
	protected static $escape = [];
	public static $thiss;

	

	/**
	 * Model constructor.
	 *
	 * @param ConnectionInterface|null $db         DB Connection
	 * @param ValidationInterface|null $validation Validation
	 */
	public function __construct(ConnectionInterface &$db = null, ValidationInterface $validation = null)
	{
		/**
		 * @var BaseConnection $db
		 */
		$db = $db ?? hkm_config('Database')::CONNECT($this::$DBGroup);

		self::$db = &$db;
		self::$thiss= $this;
		parent::__construct($validation);

	}

	public static function CHECK_ENGINE()
	{
		global $engine;
		if ($engine == "Authpost") {
			self::$thiss::$DBGroup = 'network';
		    self::$thiss::$db = hkm_config('Database')::CONNECT('network');
		}
		return self::$thiss;
	}

	use ModelSetGetTrait;

	/**
	 * Fetches the row of database from self::$thiss::$table with a primary key
	 * matching $id. This methods works only with dbCalls
	 * This methods works only with dbCalls
	 *
	 * @param boolean                   $singleton Single or multiple results
	 * @param array|integer|string|null $id        One primary key or an array of primary keys
	 *
	 * @return array|object|null    The resulting row of data, or null.
	 */
	protected static function DO_FIND(bool $singleton, $id = null)
	{
		$builder = self::$thiss::BUILDER();

		if (self::$tempUseSoftDeletes)
		{
			$builder->where(self::$thiss::$table . '.' . self::$thiss::$deletedField, '');
		}

		if (is_array($id))
		{
			$row = $builder->whereIn(self::$thiss::$table . '.' . self::$thiss::$primaryKey, $id)
				->get()
				->getResult(self::$thiss::$tempReturnType);
		}
		elseif ($singleton)
		{
			$row = $builder->where(self::$thiss::$table . '.' . self::$thiss::$primaryKey, $id)
				->get()
				->getFirstRow(self::$thiss::$tempReturnType);
		}
		else
		{
			$row = $builder->get()->getResult(self::$thiss::$tempReturnType);
		}


		return $row;
	}

	/**
	 * Fetches the column of database from self::$thiss::$table
	 * This methods works only with dbCalls
	 *
	 * @param string $columnName Column Name
	 *
	 * @return array|null The resulting row of data, or null if no data found.
	 */
	protected static function DO_FIND_COLUMN(string $columnName)
	{
		return self::$thiss->select($columnName)::AS_ARRAY()->find(); // @phpstan-ignore-line
	}

	/**
	 * Works with the current Query Builder instance to return
	 * all results, while optionally limiting them.
	 * This methods works only with dbCalls
	 *
	 * @param int $direction  order By 1 = DESC, 0=ASC, 2 = RANDOM
	 * @param string $order  order
	 * @param integer $limit  Limit
	 * @param integer $offset Offset
	 *
	 * @return array
	 */
	protected static function DO_FIND_ALL( int $direction = 1, string $order='' ,int $limit = 0, int $offset = 0)
	{
		$builder = self::$thiss::BUILDER();

		if (self::$tempUseSoftDeletes)
		{
			$builder->where(self::$thiss::$table . '.' . self::$thiss::$deletedField, '');
		}

		$direction = $direction?($direction==2?'RANDOM':'DESC'):"ASC";
      
		return $builder->limit($limit, $offset)
		    ->orderBy($order,$direction)
			->get()
			->getResult(self::$thiss::$tempReturnType);
	}
	public static function WHERE_V3($key,$value=null,bool $escape=null,$build=false)
	{
		$builder = self::$thiss::BUILDER();

		if (self::$tempUseSoftDeletes)
		{
			$builder->where(self::$thiss::$table . '.' . self::$thiss::$deletedField, '');
		}
		if ($build) return $builder->where($key, $value,$escape); 
		else return $builder->where($key, $value,$escape)
		      ->get()
			  ->getResult(self::$thiss::$tempReturnType);
	}

	public static function WHERE($key,$value=null,bool $escape=null)
	{
		$builder = self::$thiss::BUILDER();
		
		return $builder->where($key, $value,$escape)
		      ->get()
			  ->getResult(self::$thiss::$tempReturnType);

	}

	/**
	 * Returns the first row of the result set. Will take any previous
	 * Query Builder calls into account when determining the result set.
	 * This methods works only with dbCalls
	 *
	 * @return array|object|null
	 */
	protected static function DO_FIRST()
	{
		$builder = self::$thiss::BUILDER();

		if (self::$tempUseSoftDeletes)
		{
			$builder->where(self::$thiss::$table . '.' . self::$thiss::$deletedField, '');
		}
		elseif (self::$useSoftDeletes && empty($builder->QBGroupBy) && self::$thiss::$primaryKey)
		{
			$builder->groupBy(self::$thiss::$table . '.' . self::$thiss::$primaryKey);
		}

		// Some databases, like PostgreSQL, need order
		// information to consistently return correct results.
		if ($builder->QBGroupBy && empty($builder->QBOrderBy) && self::$thiss::$primaryKey)
		{
			$builder->orderBy(self::$thiss::$table . '.' . self::$thiss::$primaryKey, 'asc');
		}

		return $builder->limit(1, 0)->get()->getFirstRow(self::$thiss::$tempReturnType);
	}

	/**
	 * Inserts data into the current table.
	 * This methods works only with dbCalls
	 *
	 * @param array $data Data
	 *
	 * @return Query|boolean
	 */
	protected static function DO_INSERT(array $data)
	{
		$escape       = self::$escape;
		self::$escape = [];

		// Require non empty primaryKey when
		// not using auto-increment feature
		if (! self::$thiss::$useAutoIncrement && empty($data[self::$thiss::$primaryKey]))
		{
			throw DataException::forEmptyPrimaryKey('insert');
		}

		$builder = self::$thiss::BUILDER();

		// Must use the set() method to ensure to set the correct escape flag
		foreach ($data as $key => $val)
		{
			$builder->set($key, $val, $escape[$key] ?? null);
		}

		$result = $builder->insert();

		// If insertion succeeded then save the insert ID
		if ($result)
		{
			self::$insertID = ! self::$thiss::$useAutoIncrement ? $data[self::$thiss::$primaryKey] : self::$thiss::$db::INSERT_ID();
		}

		return $result;
	}

	/**
	 * Compiles batch insert strings and runs the queries, validating each row prior.
	 * This methods works only with dbCalls
	 *
	 * @param array|null   $set       An associative array of insert values
	 * @param boolean|null $escape    Whether to escape values and identifiers
	 * @param integer      $batchSize The size of the batch to run
	 * @param boolean      $testing   True means only number of records is returned, false will execute the query
	 *
	 * @return integer|boolean Number of rows inserted or FALSE on failure
	 */
	protected static function DO_INSERT_BATCH(?array $set = null, ?bool $escape = null, int $batchSize = 100, bool $testing = false)
	{
		if (is_array($set))
		{
			foreach ($set as $row)
			{
				// Require non empty primaryKey when
				// not using auto-increment feature
				if (! self::$thiss::$useAutoIncrement && empty($row[self::$thiss::$primaryKey]))
				{
					throw DataException::forEmptyPrimaryKey('insertBatch');
				}
			}
		}

		return self::$thiss::BUILDER()->testMode($testing)->insertBatch($set, $escape, $batchSize);
	}

	/**
	 * Updates a single record in self::$thiss::$table.
	 * This methods works only with dbCalls
	 *
	 * @param integer|array|string|null $id   ID
	 * @param array|null                $data Data
	 *
	 * @return boolean
	 */
	protected static function DO_UPDATE($id = null, $data = null): bool
	{
		$escape       = self::$escape;
		self::$escape = [];

		$builder = self::$thiss::BUILDER();

		if ($id)
		{
			$builder = $builder->whereIn(self::$thiss::$table . '.' . self::$thiss::$primaryKey, $id);
		}

		// Must use the set() method to ensure to set the correct escape flag
		foreach ($data as $key => $val)
		{
			$builder->set($key, $val, $escape[$key] ?? null);
		}

		return $builder->update();
	}

	/**
	 * Compiles an update string and runs the query
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
	protected static function DO_UPDATE_BATCH(array $set = null, string $index = null, int $batchSize = 100, bool $returnSQL = false)
	{
		return self::$thiss::BUILDER()->testMode($returnSQL)->updateBatch($set, $index, $batchSize);
	}

	/**
	 * Deletes a single record from self::$thiss::$table where $id matches
	 * the table's primaryKey
	 * This methods works only with dbCalls
	 *
	 * @param integer|string|array|null $id    The rows primary key(s)
	 * @param boolean                   $purge Allows overriding the soft deletes setting.
	 *
	 * @return string|boolean
	 *
	 * @throws DatabaseException
	 */
	protected static function DO_DELETE($id = null, bool $purge = false)
	{
		$builder = self::$thiss::BUILDER();

		if ($id)
		{
			$builder = $builder->whereIn(self::$thiss::$primaryKey, $id);
		}

		if (self::$thiss::$useSoftDeletes && ! $purge)
		{
			

			if (empty($builder->getCompiledQBWhere()))
			{
				if (HKM_DEBUG)
				{
					throw new DatabaseException(
						'Deletes are not allowed unless they contain a "where" or "like" clause.'
					);
				}

				return false; // @codeCoverageIgnore
			}

			$set[self::$thiss::$deletedField] = self::SET_DATE();

			if (self::$thiss::$useTimestamps && self::$thiss::$updatedField)
			{
				$set[self::$updatedField] = self::SET_DATE();
			}

			return $builder->update($set);
		}

		return $builder->delete();
	}

	/**
	 * Permanently deletes all rows that have been marked as deleted
	 * through soft deletes (deleted = 1)
	 * This methods works only with dbCalls
	 *
	 * @return boolean|mixed
	 */
	protected static function DO_PURGE_DELETED()
	{
		return self::$thiss::BUILDER()
			->where(self::$thiss::$table . '.' . self::$thiss::$deletedField . ' IS NOT NULL')
			->delete();
	}

	/**
	 * Works with the find* methods to return only the rows that
	 * have been deleted.
	 * This methods works only with dbCalls
	 *
	 * @return void
	 */
	protected static function DO_ONLY_DELETED()
	{
		self::$thiss::BUILDER()->where(self::$thiss::$table . '.' . self::$thiss::$deletedField . ' IS NOT NULL');
	}

	/**
	 * Compiles a replace into string and runs the query
	 * This methods works only with dbCalls
	 *
	 * @param array|null $data      Data
	 * @param boolean    $returnSQL Set to true to return Query String
	 *
	 * @return mixed
	 */
	protected static function DO_REPLACE(array $data = null, bool $returnSQL = false)
	{
		return self::$thiss::BUILDER()->testMode($returnSQL)->replace($data);
	}

	/**
	 * Grabs the last error(s) that occurred from the Database connection.
	 * The return array should be in the following format:
	 *  ['source' => 'message']
	 * This methods works only with dbCalls
	 *
	 * @return array<string,string>
	 */
	protected static function DO_ERRORS()
	{
		// $error is always ['code' => string|int, 'message' => string]
		$error = self::$thiss::$db::ERROR();

		if ((int) $error['code'] === 0)
		{
			return [];
		}

		return [get_class(self::$thiss::$db) => $error['message']];
	}

	/**
	 * Returns the id value for the data array or object
	 *
	 * @param array|object $data Data
	 *
	 * @return integer|array|string|null
	 *
	 * @deprecated Use getIdValue() instead. Will be removed in version 5.0.
	 */
	protected static function ID_VALUE($data)
	{
		return self::GET_ID_VALUE($data);
	}

	

	/**
	 * Loops over records in batches, allowing you to operate on them.
	 * Works with self::$thiss::$builder to get the Compiled select to
	 * determine the rows to operate on.
	 * This methods works only with dbCalls
	 *
	 * @param integer $size     Size
	 * @param Closure $userFunc Callback Function
	 *
	 * @return void
	 *
	 * @throws DataException
	 */
	public static function CHUNCK(int $size, Closure $userFunc)
	{
		$total  = self::$thiss::BUILDER()->countAllResults(false);
		$offset = 0;

		while ($offset <= $total)
		{
			$builder = clone self::$thiss::BUILDER();
			$rows    = $builder->get($size, $offset);

			if (! $rows)
			{
				throw DataException::forEmptyDataset('chunk');
			}

			$rows = $rows->getResult(self::$thiss::$tempReturnType);

			$offset += $size;

			if (empty($rows))
			{
				continue;
			}

			foreach ($rows as $row)
			{
				if ($userFunc($row) === false)
				{
					return;
				}
			}
		}
	}

	/**
	 * Override countAllResults to account for soft deleted accounts.
	 *
	 * @param boolean $reset Reset
	 * @param boolean $test  Test
	 *
	 * @return mixed
	 */
	public static function COUNT_ALL_RESULTS(bool $reset = true, bool $test = false)
	{
		if (self::$tempUseSoftDeletes)
		{
			self::$thiss::BUILDER()->where(self::$thiss::$table . '.' . self::$thiss::$deletedField, '');
		}

		// When $reset === false, the $tempUseSoftDeletes will be
		// dependant on $useSoftDeletes value because we don't
		// want to add the same "where" condition for the second time
		self::$tempUseSoftDeletes = $reset
			? self::$useSoftDeletes
			: (self::$useSoftDeletes ? false : self::$useSoftDeletes);

		return self::$thiss::BUILDER()->testMode($test)->countAllResults($reset);
	}

	/**
	 * Provides a shared instance of the Query Builder.
	 *
	 * @param string|null $table Table name
	 *
	 * @return BaseBuilder
	 * @throws ModelException
	 */
	public static function BUILDER(?string $table = null)
	{
		
		// Check for an existing Builder
		if (self::$thiss::$builder instanceof BaseBuilder)
		{
			// Make sure the requested table matches the builder
			if ($table && self::$thiss::$builder->getTable() !== $table)
			{
				return self::$thiss::$db::TABLE($table);
			}

		}

		// We're going to force a primary key to exist
		// so we don't have overly convoluted code,
		// and future features are likely to require them.
		if (empty(self::$thiss::$primaryKey))
		{
			throw ModelException::FOR_NO_PRIMARY_KEY(static::class);
		}

		$table = empty($table) ? self::$thiss::$table : $table;

		// Ensure we have a good db connection
		if (! self::$thiss::$db instanceof BaseConnection)
		{
			self::$thiss::$db = hkm_config('Database')::CONNECT(self::$thiss::$DBGroup);
		}

		$builder = self::$thiss::$db::TABLE($table);

		// Only consider it "shared" if the table is correct
		if ($table === self::$thiss::$table)
		{
			self::$thiss::$builder = $builder;
		}

		return $builder;
	}

	

	/**
	 * This method is called on save to determine if entry have to be updated
	 * If this method return false insert operation will be executed
	 *
	 * @param array|object $data Data
	 *
	 * @return boolean
	 */
	protected static function SHOULD_UPDATE($data) : bool
	{
		// When useAutoIncrement feature is disabled check
		// in the database if given record already exists
		return parent::SHOULD_UPDATE($data) &&
			(self::$thiss::$useAutoIncrement
				? true
				: self::$thiss::BULDER()->where(self::$thiss::$primaryKey, self::GET_ID_VALUE($data))->countAllResults() === 1
			);
	}

	/**
	 * Inserts data into the database. If an object is provided,
	 * it will attempt to convert it to an array.
	 *
	 * @param array|object|null $data     Data
	 * @param boolean           $returnID Whether insert ID should be returned or not.
	 *
	 * @return BaseResult|object|integer|string|false
	 *
	 * @throws ReflectionException
	 */
	public static function INSERT($data = null, bool $returnID = true)
	{

		if (! empty(self::$tempData['data']))
		{
			if (empty($data))
			{
				$data = self::$tempData['data'] ?? null;
			}
			else
			{
				$data = self::TRANSFORM_DATA_TO_ARRAY($data, 'insert');
				$data = array_merge(self::$tempData['data'], $data);
			}
		}

		self::$escape   = self::$tempData['escape'] ?? [];
		self::$tempData = [];

		return parent::INSERT($data, $returnID);
	}

	/**
	 * Updates a single record in the database. If an object is provided,
	 * it will attempt to convert it into an array.
	 *
	 * @param integer|array|string|null $id   ID
	 * @param array|object|null         $data Data
	 *
	 * @return boolean
	 *
	 * @throws ReflectionException
	 */
	public static function UPDATE($id = null, $data = null): bool
	{
		if (! empty(self::$tempData['data']))
		{
			if (empty($data))
			{
				$data = self::$tempData['data'] ?? null;
			}
			else
			{
				$data = self::TRANSFORM_DATA_TO_ARRAY($data, 'update');
				$data = array_merge(self::$tempData['data'], $data);
			}
		}

		self::$escape   = self::$tempData['escape'] ?? [];
		self::$tempData = [];


		return parent::UPDATE($id, $data);
	}

	/**
	 * Takes a class an returns an array of it's public static and protected static
	 * properties as an array with raw values.
	 *
	 * @param string|object $data        Data
	 * @param boolean       $onlyChanged Only Changed Property
	 * @param boolean       $recursive   If true, inner entities will be casted as array as well
	 *
	 * @return array|null Array
	 *
	 * @throws ReflectionException
	 */
	protected static function OBJECT_TO_RAW_ARRAY($data, bool $onlyChanged = true, bool $recursive = false): ?array
	{
		$properties = parent::objectToRawArray($data, $onlyChanged);

		// Always grab the primary key otherwise updates will fail.
		if (method_exists($data, 'toRawArray') && (! empty($properties) && ! empty(self::$thiss::$primaryKey) && ! in_array(self::$thiss::$primaryKey, $properties, true)
				&& ! empty($data->{self::$thiss::$primaryKey})))
		{
			$properties[self::$thiss::$primaryKey] = $data->{self::$thiss::$primaryKey};
		}

		return $properties;
	}

	

	/**
	 * Takes a class an returns an array of it's public static and protected static
	 * properties as an array suitable for use in creates and updates.
	 *
	 * @param string|object $data        Data
	 * @param string|null   $primaryKey  Primary Key
	 * @param string        $dateFormat  Date Format
	 * @param boolean       $onlyChanged Only Changed
	 *
	 * @return array
	 *
	 * @throws ReflectionException
	 *
	 * @codeCoverageIgnore
	 *
	 * @deprecated since 4.1
	 */
	public static  function CLASS_TO_ARRAY($data, $primaryKey = null, string $dateFormat = 'datetime', bool $onlyChanged = true): array
	{
		if (method_exists($data, 'toRawArray'))
		{
			$properties = $data->toRawArray($onlyChanged);

			// Always grab the primary key otherwise updates will fail.
			if (! empty($properties) && ! empty($primaryKey) && ! in_array($primaryKey, $properties, true) && ! empty($data->{$primaryKey}))
			{
				$properties[$primaryKey] = $data->{$primaryKey};
			}
		}
		else
		{
			$mirror = new ReflectionClass($data);
			$props  = $mirror->getProperties(ReflectionProperty::IS_PUBLIC  | ReflectionProperty::IS_PROTECTED );

			$properties = [];

			// Loop over each property,
			// saving the name/value in a new array we can return.
			foreach ($props as $prop)
			{
				// Must make protected static values accessible.
				$prop->setAccessible(true);
				$properties[$prop->getName()] = $prop->getValue($data);
			}
		}

		// Convert any Time instances to appropriate $dateFormat
		if ($properties)
		{
			foreach ($properties as $key => $value)
			{
				if ($value instanceof Time)
				{
					switch ($dateFormat)
					{
						case 'datetime':
							$converted = $value->format('Y-m-d H:i:s');
							break;
						case 'date':
							$converted = $value->format('Y-m-d');
							break;
						case 'int':
							$converted = $value->getTimestamp();
							break;
						default:
							$converted = (string) $value;
					}

					$properties[$key] = $converted;
				}
			}
		}

		return $properties;
	}


	public static function checkCache(array $data)
	{
		if (isset($data['id']) ) {
			
			if (isset(self::$thiss::$cachedResponse[self::$thiss::$table]) && isset(self::$thiss::$cachedResponse[self::$thiss::$table][$data['id']]) &&$item = self::GET_CACHED_ITEM($data['id'])) {
				$data['data']       = $item;
				$data['returnData'] = true;
			}
			
			return $data;
		}

		return $data;
	}

	public static function setCache(array $data)
	{
		if (isset($data['id'])) {
			self::$thiss::$cachedResponse[self::$thiss::$table][$data['id']] = $data['data'];
		}
        return $data;
	}


	public static function setTimes(array $data)
	{
		if (!isset($data['data']['created_at'])) {
			$data['data']['created_at'] = Time::NOW();
		}
		$data['data']['updated_at'] = Time::NOW();

		return $data;
	}

	public static function GET_CACHED_ITEM($id)
	{
		return self::$thiss::$cachedResponse[self::$thiss::$table][$id];
	}
}
