<?php


namespace Hkm_code;

use Hkm_traits\AddOn\ModelTraitAbstact;
use Hkm_code\Database\BaseConnection;
use Hkm_code\Database\BaseResult;
use Hkm_code\Database\Exceptions\DatabaseException;
use Hkm_code\Database\Exceptions\DataException;
use Hkm_code\Exceptions\ModelException;
use Hkm_code\I18n\Time;
use Hkm_code\Pager\Pager;
use Hkm_code\Validation\Validation;
use Hkm_code\Validation\ValidationInterface;
use Hkm_code\Vezirion\Services;
use Hkm_code\Vezirion\ServicesSystem;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use stdClass;

/**
 * Class Model
 *
 * The BaseModel class provides a number of convenient features that
 * makes working with a databases less painful. Extending this class
 * provide means of implementing various database systems
 *
 * It will:
 *      - simplifies pagination
 *      - allow specifying the return type (array, object, etc) with each call
 *      - automatically set and update timestamps
 *      - handle soft deletes
 *      - ensure validation is run against objects when saving items
 *      - process various callbacks
 *      - allow intermingling calls to the db connection
 */
abstract class BaseModel
{
	/**
	 * Pager instance.
	 * Populated after calling self::$PAGINATE()
	 *
	 * @var Pager
	 */
	public static $pager;

	/**
	 * Last insert ID
	 *
	 * @var integer|string
	 */
	protected static $insertID = 0;

	/**
	 * The Database connection group that
	 * should be instantiated.
	 *
	 * @var string
	 */
	protected static $DBGroup;
	public static $table;

	/**
	 * The format that the results should be returned as.
	 * Will be overridden if the as* methods are used.
	 *
	 * @var string
	 */
	protected static $returnType = 'array';

	/**
	 * If this model should use "softDeletes" and
	 * simply set a date when rows are deleted, or
	 * do hard deletes.
	 *
	 * @var boolean
	 */
	protected static $useSoftDeletes = false;

	/**
	 * An array of field names that are allowed
	 * to be set by the user in inserts/updates.
	 *
	 * @var array
	 */
	protected static $allowedFields = [];

	/**
	 * If true, will set created_at, and updated_at
	 * values during insert and update routines.
	 *
	 * @var boolean
	 */
	protected static $useTimestamps = false;

	/**
	 * The type of column that created_at and updated_at
	 * are expected to.
	 *
	 * Allowed: 'datetime', 'date', 'int'
	 *
	 * @var string
	 */
	protected static $dateFormat = 'datetime';

	/**
	 * The column used for insert timestamps
	 *
	 * @var string
	 */
	protected static $createdField = 'created_at';

	/**
	 * The column used for update timestamps
	 *
	 * @var string
	 */
	protected static $updatedField = 'updated_at';

	/**
	 * Used by withDeleted to override the
	 * model's softDelete setting.
	 *
	 * @var boolean
	 */
	protected static $tempUseSoftDeletes;

	/**
	 * The column used to save soft delete state
	 *
	 * @var string
	 */
	protected static $deletedField = 'deleted_at';

	/**
	 * Used by asArray and asObject to provide
	 * temporary overrides of model default.
	 *
	 * @var string
	 */
	protected static $tempReturnType;

	/**
	 * Whether we should limit fields in inserts
	 * and updates to those available in $allowedFields or not.
	 *
	 * @var boolean
	 */
	protected static $protectFields = true;

	/**
	 * Database Connection
	 *
	 * @var BaseConnection
	 */
	protected static $db;

	/**
	 * Rules used to validate data in insert, update, and save methods.
	 * The array must match the format of data passed to the Validation
	 * library.
	 *
	 * @var array|string
	 */
	protected static $validationRules = [];

	/**
	 * Contains any custom error messages to be
	 * used during data validation.
	 *
	 * @var array
	 */
	protected static $validationMessages = [];

	/**
	 * Skip the model's validation. Used in conjunction with SKIP_VALIDATION()
	 * to skip data validation for any future calls.
	 *
	 * @var boolean
	 */
	protected static $skipValidation = false;

	/**
	 * Whether rules should be removed that do not exist
	 * in the passed in data. Used between inserts/updates.
	 *
	 * @var boolean
	 */
	protected static $cleanValidationRules = true;

	/**
	 * Our validator instance.
	 *
	 * @var Validation
	 */
	protected static $validation;

	/*
	 * Callbacks.
	 *
	 * Each array should contain the method names (within the model)
	 * that should be called when those events are triggered.
	 *
	 * "Update" and "delete" methods are passed the same items that
	 * are given to their respective method.
	 *
	 * "Find" methods receive the ID searched for (if present), and
	 * 'afterFind' additionally receives the results that were found.
	 */

	/**
	 * Whether to trigger the defined callbacks
	 *
	 * @var boolean
	 */
	protected static $allowCallbacks = true;

	/**
	 * Used by ALLOW_CALLBACKS() to override the
	 * model's allowCallbacks setting.
	 *
	 * @var boolean
	 */
	protected static $tempAllowCallbacks;

	/**
	 * Callbacks for beforeInsert
	 *
	 * @var array
	 */
	protected static $beforeInsert = [];

	/**
	 * Callbacks for afterInsert
	 *
	 * @var array
	 */
	protected static $afterInsert = [];

	/**
	 * Callbacks for beforeUpdate
	 *
	 * @var array
	 */
	protected static $beforeUpdate = [];

	/**
	 * Callbacks for afterUpdate
	 *
	 * @var array
	 */
	protected static $afterUpdate = [];

	/**
	 * Callbacks for beforeFind
	 *
	 * @var array
	 */
	protected static $beforeFind = [];

	/**
	 * Callbacks for afterFind
	 *
	 * @var array
	 */
	protected static $afterFind = [];

	/**
	 * Callbacks for beforeDelete
	 *
	 * @var array
	 */
	protected static $beforeDelete = [];

	/**
	 * Callbacks for afterDelete
	 *
	 * @var array
	 */
	protected static $afterDelete = [];

	public static $thiss;

	/**
	 * BaseModel constructor.
	 *
	 * @param ValidationInterface|null $validation Validation
	 */
	public  function __construct(ValidationInterface $validation = null)
	{
		self::$thiss = $this;

		self::$tempReturnType     = self::$thiss::$returnType;
		self::$tempUseSoftDeletes = self::$thiss::$useSoftDeletes;
		self::$tempAllowCallbacks = self::$thiss::$allowCallbacks;

		/**
		 * @var Validation $validation 
		 */
		$validation       = $validation ?? ServicesSystem::VALIDATION(null, false);
		self::$validation = $validation;

		self::$thiss::INITIALIZE();
	}

	/**
	 * Initializes the instance with any additional steps.
	 * Optionally implemented by child classes.
	 */
	protected static function INITIALIZE()
	{
	}

	use ModelTraitAbstact;

	/**
	 * Public static getter to return the id value using the ID_VALUE() method
	 * For example with SQL this will return $data->self::$primaryKey
	 *
	 * @param array|object $data
	 *
	 * @return array|integer|string|null
	 *
	 * @todo: Make abstract in version 5.0
	 */
	public static function GET_ID_VALUE($data)
	{
		return self::$thiss::ID_VALUE($data);
	}

	

	/**
	 * Fetches the row of database
	 *
	 * @param array|integer|string|null $id One primary key or an array of primary keys
	 *
	 * @return array|object|null The resulting row of data, or null.
	 */
	public static function FIND($id = null)
	{
		$singleton = is_numeric($id) || is_string($id);

		if (self::$tempAllowCallbacks)
		{
			// Call the before event and check for a return
			$eventData = self::TRIGGER('beforeFind', [
				'id'        => $id,
				'method'    => 'FIND',
				'singleton' => $singleton,
			]);

			if (! empty($eventData['returnData']))
			{
				return $eventData['data'];
			}
		}

		$eventData = [
			'id'        => $id,
			'data'      => self::$thiss::DO_FIND($singleton, $id),
			'method'    => 'FIND',
			'singleton' => $singleton,
		];

		if (self::$tempAllowCallbacks)
		{
			$eventData = self::TRIGGER('afterFind', $eventData);
		}

		self::$tempReturnType     = self::$thiss::$returnType;
		self::$tempUseSoftDeletes = self::$thiss::$useSoftDeletes;
		self::$tempAllowCallbacks = self::$thiss::$allowCallbacks;

		return $eventData['data'];
	}

	/**
	 * Fetches the column of database
	 *
	 * @param string $columnName Column Name
	 *
	 * @return array|null The resulting row of data, or null if no data found.
	 *
	 * @throws DataException
	 */
	public static function FIND_COLUMN(string $columnName)
	{
		if (strpos($columnName, ',') !== false) 
		{
			throw DataException::forFindColumnHaveMultipleColumns();
		}

		$resultSet = self::$thiss::DO_FIND_COLUMN($columnName);

		return $resultSet ? array_column($resultSet, $columnName) : null;
	}

	/**
	 * Fetches all results, while optionally limiting them.
	 *
	 * @param string $order  orderBy
	 * @param integer $direction  direction 1 = DESC, 0=ASC, 2 = RANDOM
	 * @param integer $limit  Limit
	 * @param integer $offset Offset
	 *
	 * @return array
	 */
	public static function FIND_ALL(int $direction = 1 ,string $order = 'created_at' , int $limit = 0, int $offset = 0)
	{
		if (self::$tempAllowCallbacks)
		{
			// Call the before event and check for a return
			$eventData = self::TRIGGER('beforeFind', [
				'method'    => 'findAll',
				'limit'     => $limit,
				'offset'    => $offset,
				'singleton' => false,
			]);

			if (! empty($eventData['returnData']))
			{
				return $eventData['data'];
			}
		}

		$eventData = [
			'data'      => self::$thiss::DO_FIND_ALL($direction, $order, $limit, $offset),
			'limit'     => $limit,
			'offset'    => $offset,
			'method'    => 'findAll',
			'singleton' => false,
		];

		if (self::$tempAllowCallbacks)
		{
			$eventData = self::TRIGGER('afterFind', $eventData);
		}

		self::$tempReturnType     = self::$thiss::$returnType;
		self::$tempUseSoftDeletes = self::$thiss::$useSoftDeletes;
		self::$tempAllowCallbacks = self::$thiss::$allowCallbacks;

		return $eventData['data'];
	}

	/**
	 * Returns the first row of the result set.
	 *
	 * @return array|object|null
	 */
	public static function FIRST()
	{
		if (self::$tempAllowCallbacks)
		{
			// Call the before event and check for a return
			$eventData = self::TRIGGER('beforeFind', [
				'method'    => 'first',
				'singleton' => true,
			]);

			if (! empty($eventData['returnData']))
			{
				return $eventData['data'];
			}
		}

		$eventData = [
			'data'      => self::$thiss::DO_FIRST(),
			'method'    => 'first',
			'singleton' => true,
		];

		if (self::$tempAllowCallbacks)
		{
			$eventData = self::TRIGGER('afterFind', $eventData);
		}

		self::$tempReturnType     = self::$thiss::$returnType;
		self::$tempUseSoftDeletes = self::$thiss::$useSoftDeletes;
		self::$tempAllowCallbacks = self::$thiss::$allowCallbacks;

		return $eventData['data'];
	}

	/**
	 * A convenience method that will attempt to determine whether the
	 * data should be inserted or updated. Will work with either
	 * an array or object. When using with custom class objects,
	 * you must ensure that the class will provide access to the class
	 * variables, even if through a magic method.
	 *
	 * @param array|object $data Data
	 *
	 * @return boolean
	 *
	 * @throws ReflectionException
	 */
	public static function SAVE($data): bool
	{
		if (empty($data))
		{
			return true;
		}

		if (self::SHOULD_UPDATE($data))
		{
			$response = self::UPDATE(self::GET_ID_VALUE($data), $data);
		}
		else
		{
			$response = self::INSERT($data, false);

			if ($response !== false)
			{
				$response = true;
			}
		}
		return $response;
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
		return ! empty(self::GET_ID_VALUE($data));
	}

	/**
	 * Returns last insert ID or 0.
	 *
	 * @return integer|string
	 */
	public static function GET_INSERT_ID()
	{
		return is_numeric(self::$insertID) ? (int) self::$insertID : self::$insertID;
	}

	/**
	 * Inserts data into the database. If an object is provided,
	 * it will attempt to convert it to an array.
	 *
	 * @param array|object|null $data     Data
	 * @param boolean           $returnID Whether insert ID should be returned or not.
	 *
	 * @return integer|string|boolean
	 *
	 * @throws ReflectionException
	 */
	public static function INSERT($data = null, bool $returnID = true)
	{

		self::$insertID = 0;

		$data = self::TRANSFORM_DATA_TO_ARRAY($data, 'insert');

		// Validate data before saving.
		if (! self::$thiss::$skipValidation && ! self::CLEAN_RULES()::VALIDATE($data))
		{
			return false;
		}


		// Must be called first so we don't
		// strip out created_at values.
		$data = self::$thiss::DO_PROTECT_FIELDS($data);

		// DO_PROTECT_FIELDS() can further remove elements from
		// $data so we need to check for empty dataset again
		if (empty($data))
		{
			throw DataException::forEmptyDataset('insert');
		}

		// Set created_at and updated_at with same time
		$date = self::$thiss::SET_DATE();

		if (self::$thiss::$useTimestamps && self::$thiss::$createdField && ! array_key_exists(self::$thiss::$createdField, $data))
		{
			$data[self::$thiss::$createdField] = $date;
		}

		if (self::$thiss::$useTimestamps && self::$thiss::$updatedField && ! array_key_exists(self::$thiss::$updatedField, $data))
		{
			$data[self::$thiss::$updatedField] = $date;
		}

		$eventData = ['data' => $data];


		if (self::$tempAllowCallbacks)
		{
			$eventData = self::TRIGGER('beforeInsert', $eventData);
			
		}
		
		$result = self::$thiss::DO_INSERT($eventData['data']);

		$eventData = [
			'id'     => self::$insertID,
			'data'   => $eventData['data'],
			'result' => $result,
		];

		if (self::$tempAllowCallbacks)
		{
			// Trigger afterInsert events with the inserted data and new ID
			self::TRIGGER('afterInsert', $eventData);
		}

		self::$tempAllowCallbacks = self::$thiss::$allowCallbacks;

		// If insertion failed, get out of here
		if (! $result)
		{
			return $result;
		}

		// otherwise return the insertID, if requested.
		return $returnID ? self::$insertID : $result;
	}

	/**
	 * Compiles batch insert runs the queries, validating each row prior.
	 *
	 * @param array|null   $set       an associative array of insert values
	 * @param boolean|null $escape    Whether to escape values and identifiers
	 * @param integer      $batchSize The size of the batch to run
	 * @param boolean      $testing   True means only number of records is returned, false will execute the query
	 *
	 * @return integer|boolean Number of rows inserted or FALSE on failure
	 *
	 * @throws ReflectionException
	 */
	public static function INSERT_BATCH(?array $set = null, ?bool $escape = null, int $batchSize = 100, bool $testing = false)
	{
		if (is_array($set))
		{
			foreach ($set as &$row)
			{
				// If $data is using a custom class with public static or protected static
				// properties representing the collection elements, we need to grab
				// them as an array.
				if (is_object($row) && ! $row instanceof stdClass)
				{
					$row = self::OBJECT_TO_ARRAY($row, false, true);
				}

				// If it's still a stdClass, go ahead and convert to
				// an array so doProtectFields and other model methods
				// don't have to do special checks.
				if (is_object($row))
				{
					$row = (array) $row;
				}

				// Validate every row..
				if (! self::$thiss::$skipValidation && ! self::CLEAN_RULES()::VALIDATE($row))
				{
					return false;
				}

				// Must be called first so we don't
				// strip out created_at values.
				$row = self::$thiss::DO_PROTECT_FIELDS($row);

				// Set created_at and updated_at with same time
				$date = self::$thiss::SET_DATE();

				if (self::$thiss::$useTimestamps && self::$thiss::$createdField && ! array_key_exists(self::$thiss::$createdField, $row))
				{
					$row[self::$thiss::$createdField] = $date;
				}

				if (self::$thiss::$useTimestamps && self::$thiss::$updatedField && ! array_key_exists(self::$thiss::$updatedField, $row))
				{
					$row[self::$thiss::$updatedField] = $date;
				}
			}
		}

		return self::$thiss::DO_INSERT_BATCH($set, $escape, $batchSize, $testing);
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
		if (is_numeric($id) || is_string($id))
		{
			$id = [$id];
		}

		$data = self::TRANSFORM_DATA_TO_ARRAY($data, 'update');

		// Validate data before saving.
		if (! self::$thiss::$skipValidation && ! self::CLEAN_RULES(true)::VALIDATE($data))
		{
			return false;
		}

		// Must be called first so we don't
		// strip out updated_at values.
		$data = self::$thiss::DO_PROTECT_FIELDS($data);

		// DO_PROTECT_FIELDS() can further remove elements from
		// $data so we need to check for empty dataset again
		if (empty($data))
		{
			throw DataException::forEmptyDataset('update');
		}

		if (self::$thiss::$useTimestamps && self::$thiss::$updatedField && ! array_key_exists(self::$thiss::$updatedField, $data))
		{
			$data[self::$thiss::$updatedField] = self::$thiss::SET_DATE();
		}

		$eventData = [
			'id'   => $id,
			'data' => $data,
		];

		if (self::$tempAllowCallbacks)
		{
			$eventData = self::TRIGGER('beforeUpdate', $eventData);
		}

		$eventData = [
			'id'     => $id,
			'data'   => $eventData['data'],
			'result' => self::$thiss::DO_UPDATE($id, $eventData['data']),
		];

		if (self::$tempAllowCallbacks)
		{
			self::TRIGGER('afterUpdate', $eventData);
		}

		self::$tempAllowCallbacks = self::$thiss::$allowCallbacks;

		return $eventData['result'];
	}

	/**
	 * Compiles an update and runs the query
	 *
	 * @param array|null  $set       An associative array of update values
	 * @param string|null $index     The where key
	 * @param integer     $batchSize The size of the batch to run
	 * @param boolean     $returnSQL True means SQL is returned, false will execute the query
	 *
	 * @return mixed    Number of rows affected or FALSE on failure
	 *
	 * @throws DatabaseException
	 * @throws ReflectionException
	 */
	public static function UPDATE_BATCH(array $set = null, string $index = null, int $batchSize = 100, bool $returnSQL = false)
	{
		if (is_array($set))
		{
			foreach ($set as &$row)
			{
				// If $data is using a custom class with public static or protected static
				// properties representing the collection elements, we need to grab
				// them as an array.
				if (is_object($row) && ! $row instanceof stdClass)
				{
					$row = self::OBJECT_TO_ARRAY($row, true, true);
				}

				// If it's still a stdClass, go ahead and convert to
				// an array so doProtectFields and other model methods
				// don't have to do special checks.
				if (is_object($row))
				{
					$row = (array) $row;
				}

				// Validate data before saving.
				if (! self::$thiss::$skipValidation && ! self::CLEAN_RULES(true)::VALIDATE($row))
				{
					return false;
				}

				// Save updateIndex for later
				$updateIndex = $row[$index] ?? null;

				// Must be called first so we don't
				// strip out updated_at values.
				$row = self::$thiss::DO_PROTECT_FIELDS($row);

				// Restore updateIndex value in case it was wiped out
				if ($updateIndex !== null)
				{
					$row[$index] = $updateIndex;
				}

				if (self::$thiss::$useTimestamps && self::$thiss::$updatedField && ! array_key_exists(self::$thiss::$updatedField, $row))
				{
					$row[self::$thiss::$updatedField] = self::$thiss::SET_DATE();
				}
			}
		}

		return self::$thiss::DO_UPDATE_BATCH($set, $index, $batchSize, $returnSQL);
	}

	/**
	 * Deletes a single record from the database where $id matches
	 *
	 * @param integer|string|array|null $id    The rows primary key(s)
	 * @param boolean                   $purge Allows overriding the soft deletes setting.
	 *
	 * @return BaseResult|boolean
	 *
	 * @throws DatabaseException
	 */
	public static function DELETE($id = null, bool $purge = false)
	{
		if ($id && (is_numeric($id) || is_string($id)))
		{
			$id = [$id];
		}

		$eventData = [
			'id'    => $id,
			'purge' => $purge,
		];

		if (self::$tempAllowCallbacks)
		{
			self::TRIGGER('beforeDelete', $eventData);
		}
		

		$eventData = [
			'id'     => $id,
			'data'   => null,
			'purge'  => $purge,
			'result' => self::$thiss::DO_DELETE($id, $purge),
		];



		if (self::$tempAllowCallbacks)
		{
			self::TRIGGER('afterDelete', $eventData);
		}

		self::$tempAllowCallbacks = self::$thiss::$allowCallbacks;

		return $eventData['result'];
	}

	/**
	 * Permanently deletes all rows that have been marked as deleted
	 * through soft deletes (deleted = 1)
	 *
	 * @return boolean|mixed
	 */
	public static function PURGE_DELETED()
	{
		if (! self::$thiss::$useSoftDeletes)
		{
			return true;
		}

		return self::$thiss::DO_PURGE_DELETED();
	}

	/**
	 * Sets $useSoftDeletes value so that we can temporarily override
	 * the soft deletes settings. Can be used for all find* methods.
	 *
	 * @param boolean $val Value
	 *
	 * @return $this
	 */
	public static function WITH_DELETED(bool $val = true)
	{
		self::$tempUseSoftDeletes = ! $val;

		return self::$thiss;
	}

	/**
	 * Works with the find* methods to return only the rows that
	 * have been deleted.
	 *
	 * @return $this
	 */
	public static function ONLY_DELETED()
	{
		self::$tempUseSoftDeletes = false;
		self::$thiss::DO_ONLY_DELETED();

		return self::$thiss;
	}

	/**
	 * Compiles a replace and runs the query
	 *
	 * @param array|null $data      Data
	 * @param boolean    $returnSQL Set to true to return Query String
	 *
	 * @return mixed
	 */
	public static function REPLACE(array $data = null, bool $returnSQL = false)
	{
		// Validate data before saving.
		if ($data && ! self::$thiss::$skipValidation && ! self::CLEAN_RULES(true)::VALIDATE($data))
		{
			return false;
		}

		return self::$thiss::DO_REPLACE($data, $returnSQL);
	}

	/**
	 * Grabs the last error(s) that occurred. If data was validated,
	 * it will first check for errors there, otherwise will try to
	 * grab the last error from the Database connection.
	 * The return array should be in the following format:
	 *  ['source' => 'message']
	 *
	 * @param boolean $forceDB Always grab the db error, not validation
	 *
	 * @return array<string,string>
	 */
	public static function ERRORS(bool $forceDB = false)
	{
		// Do we have validation errors?
		if (! $forceDB && ! self::$thiss::$skipValidation && ($errors = self::$validation::GET_ERRORS()))
		{
			return $errors;
		}

		return self::$thiss::DO_ERRORS();
	}

	/**
	 * Works with Pager to get the size and offset parameters.
	 * Expects a GET variable (?page=2) that specifies the page of results
	 * to display.
	 *
	 * @param integer|null $perPage Items per page
	 * @param string       $group   Will be used by the pagination library to identify a unique pagination set.
	 * @param integer|null $page    Optional page number (useful when the page number is provided in different way)
	 * @param integer      $segment Optional URI segment number (if page number is provided by URI segment)
	 *
	 * @return array|null
	 */
	public static function PAGINATE(int $perPage = null, string $group = 'default', int $page = null, int $segment = 0)
	{
		$pager = ServicesSystem::PAGER(null, null, false);

		if ($segment)
		{
			$pager::SET_SEGMENT($segment);
		}

		$page = $page >= 1 ? $page : $pager::GET_CURRENT_PAGE($group);
		// Store it in the Pager library, so it can be paginated in the views.
		self::$pager = $pager::STORE($group, $page, $perPage, self::COUNT_ALL_RESULTS(false), $segment);
		$perPage     = self::$pager::GET_PER_PAGE($group);
		$offset      = ($page - 1) * $perPage;

		return self::FIND_ALL($perPage, $offset);
	}

	/**
	 * It could be used when you have to change default or override current allowed fields.
	 *
	 * @param array $allowedFields Array with names of fields
	 *
	 * @return $this
	 */
	public static function SET_ALLOWED_FIELDS(array $allowedFields)
	{
		self::$thiss::$allowedFields = $allowedFields;

		return self::$thiss;
	}

	/**
	 * Sets whether or not we should whitelist data set during
	 * updates or inserts against self::$availableFields.
	 *
	 * @param boolean $protect Value
	 *
	 * @return $this
	 */
	public static function PROTECT(bool $protect = true)
	{
		self::$thiss::$protectFields = $protect;

		return self::$thiss;
	}

	/**
	 * Ensures that only the fields that are allowed to be updated
	 * are in the data array.
	 *
	 * Used by INSERT() and UPDATE() to protect against mass assignment
	 * vulnerabilities.
	 *
	 * @param array $data Data
	 *
	 * @return array
	 *
	 * @throws DataException
	 */
	protected static function DO_PROTECT_FIELDS(array $data): array
	{
		if (! self::$thiss::$protectFields)
		{
			return $data;
		}

		if (empty(self::$thiss::$allowedFields))
		{
			throw DataException::forInvalidAllowedFields(get_class(self::$thiss));
		}

		foreach (array_keys($data) as $key)
		{
			if (! in_array($key, self::$thiss::$allowedFields, true))
			{
				unset($data[$key]);
			}
		}

		return $data;
	}

	/**
	 * Sets the date or current date if null value is passed
	 *
	 * @param integer|null $userData An optional PHP timestamp to be converted.
	 *
	 * @return mixed
	 *
	 * @throws ModelException
	 */
	protected static function SET_DATE(?int $userData = null)
	{
		$currentDate = $userData ?? time();
		return self::INT_TO_DATE($currentDate);
	}

	/**
	 * A utility function to allow child models to use the type of
	 * date/time format that they prefer. This is primarily used for
	 * setting created_at, updated_at and deleted_at values, but can be
	 * used by inheriting classes.
	 *
	 * The available time formats are:
	 *  - 'int'      - Stores the date as an integer timestamp
	 *  - 'datetime' - Stores the data in the SQL datetime format
	 *  - 'date'     - Stores the date (only) in the SQL date format.
	 *
	 * @param integer $value value
	 *
	 * @return integer|string
	 *
	 * @throws ModelException
	 */
	protected static function INT_TO_DATE(int $value)
	{
		switch (self::$dateFormat)
		{
			case 'int':
				return $value;
			case 'datetime':
				return date('Y-m-d H:i:s', $value);
			case 'date':
				return date('Y-m-d', $value);
			default:
				throw ModelException::FOR_NO_DATE_FORMAT(static::class);
		}
	}

	/**
	 * Converts Time value to string using self::$dateFormat
	 *
	 * The available time formats are:
	 *  - 'int'      - Stores the date as an integer timestamp
	 *  - 'datetime' - Stores the data in the SQL datetime format
	 *  - 'date'     - Stores the date (only) in the SQL date format.
	 *
	 * @param Time $value value
	 *
	 * @return string|integer
	 */
	protected static function TIME_DO_DATE(Time $value)
	{
		switch (self::$dateFormat)
		{
			case 'datetime':
				return $value->format('Y-m-d H:i:s');
			case 'date':
				return $value->format('Y-m-d');
			case 'int':
				return $value->getTimestamp();
			default:
				return (string) $value;
		}
	}

	/**
	 * Set the value of the skipValidation flag.
	 *
	 * @param boolean $skip Value
	 *
	 * @return $this
	 */
	public static function SKIP_VALIDATION(bool $skip = true)
	{
		self::$thiss::$skipValidation = $skip;

		return self::$thiss;
	}

	/**
	 * Allows to set validation messages.
	 * It could be used when you have to change default or override current validate messages.
	 *
	 * @param array $validationMessages Value
	 *
	 * @return $this
	 */
	public static function SET_VALIDATION_MESSAGES(array $validationMessages)
	{
		self::$validationMessages = $validationMessages;

		return self::$thiss;
	}

	/**
	 * Allows to set field wise validation message.
	 * It could be used when you have to change default or override current validate messages.
	 *
	 * @param string $field         Field Name
	 * @param array  $fieldMessages Validation messages
	 *
	 * @return $this
	 */
	public static function SET_VALIDATION_MESSAGE(string $field, array $fieldMessages)
	{
		self::$validationMessages[$field] = $fieldMessages;

		return self::$thiss;
	}

	/**
	 * Allows to set validation rules.
	 * It could be used when you have to change default or override current validate rules.
	 *
	 * @param array $validationRules Value
	 *
	 * @return $this
	 */
	public static function SET_VALIDATION_RULES(array $validationRules)
	{
		self::$validationRules = $validationRules;

		return self::$thiss;
	}

	/**
	 * Allows to set field wise validation rules.
	 * It could be used when you have to change default or override current validate rules.
	 *
	 * @param string       $field      Field Name
	 * @param string|array $fieldRules Validation rules
	 *
	 * @return $this
	 */
	public static function SET_VALIDATION_RULE(string $field, $fieldRules)
	{
		self::$validationRules[$field] = $fieldRules;

		return self::$thiss;
	}

	/**
	 * Should validation rules be removed before saving?
	 * Most handy when doing updates.
	 *
	 * @param boolean $choice Value
	 *
	 * @return $this
	 */
	public static function CLEAN_RULES(bool $choice = false)
	{
		self::$cleanValidationRules = $choice;

		return self::$thiss;
	}

	/**
	 * Validate the data against the validation rules (or the validation group)
	 * specified in the class property, $validationRules.
	 *
	 * @param array|object $data Data
	 *
	 * @return boolean
	 */
	public static function VALIDATE($data): bool
	{
		$rules = self::GET_VALIDATION_RULES();

		if (self::$thiss::$skipValidation || empty($rules) || empty($data))
		{
			return true;
		}

		//Validation requires array, so cast away.
		if (is_object($data))
		{
			$data = (array) $data;
		}

		$rules = self::$cleanValidationRules ? self::CLEAN_VALIDATION_RULES($rules, $data) : $rules;

		// If no data existed that needs validation
		// our job is done here.
		if (empty($rules))
		{
			return true;
		}

		return self::$validation::SET_RULES($rules, self::$validationMessages)::RUN($data, null, self::$DBGroup);
	}

	/**
	 * Returns the model's defined validation rules so that they
	 * can be used elsewhere, if needed.
	 *
	 * @param array $options Options
	 *
	 * @return array
	 */
	public static function GET_VALIDATION_RULES(array $options = []): array
	{
		$rules = self::$validationRules;

		// ValidationRules can be either a string, which is the group name,
		// or an array of rules.
		if (is_string($rules))
		{
			$rules = self::$validation::LOAD_RULE_GROUP($rules);
		}

		if (isset($options['except']))
		{
			$rules = array_diff_key($rules, array_flip($options['except']));
		}
		elseif (isset($options['only']))
		{
			$rules = array_intersect_key($rules, array_flip($options['only']));
		}

		return $rules;
	}

	/**
	 * Returns the model's define validation messages so they
	 * can be used elsewhere, if needed.
	 *
	 * @return array
	 */
	public static function GET_VALIDATION_MESSAGES(): array
	{
		return self::$validationMessages;
	}

	/**
	 * Removes any rules that apply to fields that have not been set
	 * currently so that rules don't block updating when only updating
	 * a partial row.
	 *
	 * @param array      $rules Array containing field name and rule
	 * @param array|null $data  Data
	 *
	 * @return array
	 */
	protected static function CLEAN_VALIDATION_RULES(array $rules, array $data = null): array
	{
		if (empty($data))
		{
			return [];
		}

		foreach (array_keys($rules) as $field)
		{
			if (! array_key_exists($field, $data))
			{
				unset($rules[$field]);
			}
		}

		return $rules;
	}

	/**
	 * Sets $tempAllowCallbacks value so that we can temporarily override
	 * the setting. Resets after the next method that uses triggers.
	 *
	 * @param boolean $val value
	 *
	 * @return $this
	 */
	public static function ALLOW_CALLBACKS(bool $val = true)
	{
		self::$tempAllowCallbacks = $val;

		return self::$thiss;
	}

	/**
	 * A simple event trigger for Model Events that allows additional
	 * data manipulation within the model. Specifically intended for
	 * usage by child models this can be used to format data,
	 * save/load related classes, etc.
	 *
	 * It is the responsibility of the callback methods to return
	 * the data itself.
	 *
	 * Each $eventData array MUST have a 'data' key with the relevant
	 * data for callback methods (like an array of key/value pairs to insert
	 * or update, an array of results, etc)
	 *
	 * If callbacks are not allowed then returns $eventData immediately.
	 *
	 * @param string $event     Event
	 * @param array  $eventData Event Data
	 *
	 * @return mixed
	 *
	 * @throws DataException
	 */
	protected static function TRIGGER(string $event, array $eventData)
	{
		// Ensure it's a valid event
		if (! isset(self::$thiss::$$event) || empty(self::$thiss::$$event))
		{

			return $eventData;
		}
		

		foreach (self::$thiss::$$event as $callback)
		{
			if (! method_exists(self::$thiss, $callback))
			{
				throw DataException::forInvalidMethodTriggered($callback);
			}

			$eventData = self::$thiss::$callback($eventData);
		}

		return $eventData;
	}

	/**
	 * Sets the return type of the results to be as an associative array.
	 *
	 * @return $this
	 */
	public static function AS_ARRAY()
	{
		self::$tempReturnType = 'array';

		return self::$thiss;
	}

	/**
	 * Sets the return type to be of the specified type of object.
	 * Defaults to a simple object, but can be any class that has
	 * class vars with the same name as the collection columns,
	 * or at least allows them to be created.
	 *
	 * @param string $class Class Name
	 *
	 * @return $this
	 */
	public static function AS_OBJECT(string $class = 'object')
	{
		self::$tempReturnType = $class;

		return self::$thiss;
	}

	/**
	 * Takes a class an returns an array of it's public static and protected static
	 * properties as an array suitable for use in creates and updates.
	 * This method use objectToRawArray internally and does conversion
	 * to string on all Time instances
	 *
	 * @param string|object $data        Data
	 * @param boolean       $onlyChanged Only Changed Property
	 * @param boolean       $recursive   If true, inner entities will be casted as array as well
	 *
	 * @return array Array
	 *
	 * @throws ReflectionException
	 */
	protected static function OBJECT_TO_ARRAY($data, bool $onlyChanged = true, bool $recursive = false): array
	{
		$properties = self::OBJECT_TO_RAW_ARRAY($data, $onlyChanged, $recursive);

		// Convert any Time instances to appropriate $dateFormat
		if ($properties)
		{
			$properties = array_map(function ($value) {
				if ($value instanceof Time)
				{
					return self::TIME_DO_DATE($value);
				}
				return $value;
			}, $properties);
		}

		return $properties;
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
		if (method_exists($data, 'toRawArray'))
		{
			$properties = $data->toRawArray($onlyChanged, $recursive);
		}
		else
		{
			$mirror = new ReflectionClass($data);
			$props  = $mirror->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED );

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

		return $properties;
	}

	/**
	 * Transform data to array
	 *
	 * @param array|object|null $data Data
	 * @param string            $type Type of data (insert|update)
	 *
	 * @return array
	 *
	 * @throws DataException
	 * @throws InvalidArgumentException
	 * @throws ReflectionException
	 */
	protected static function TRANSFORM_DATA_TO_ARRAY($data, string $type): array
	{
		if (! in_array($type, ['insert', 'update'], true))
		{
			throw new InvalidArgumentException(sprintf('Invalid type "%s" used upon transforming data to array.', $type));
		}

		if (empty($data))
		{
			throw DataException::forEmptyDataset($type);
		}

		// If $data is using a custom class with public static or protected static
		// properties representing the collection elements, we need to grab
		// them as an array.
		if (is_object($data) && ! $data instanceof stdClass)
		{
			$data = self::OBJECT_TO_ARRAY($data, true, true);
		}

		// If it's still a stdClass, go ahead and convert to
		// an array so doProtectFields and other model methods
		// don't have to do special checks.
		if (is_object($data))
		{
			$data = (array) $data;
		}

		// If it's still empty here, means $data is no change or is empty object
		if (empty($data))
		{
			throw DataException::forEmptyDataset($type);
		}

		return $data;
	}

	/**
	 * Provides the db connection and model's properties.
	 *
	 * @param string $name Name
	 *
	 * @return mixed
	 */
	public function __get(string $name)
	{
		if (property_exists(self::$thiss, $name))
		{
			return self::$$name;
		}

		if (isset(self::$db::$$name))
		{
			return self::$db::$$name;
		}

		return null;
	}

	/**
	 * Checks for the existence of properties across this model, and db connection.
	 *
	 * @param string $name Name
	 *
	 * @return boolean
	 */
	public  function __isset(string $name): bool
	{
		if (property_exists(self::$thiss, $name))
		{
			return true;
		}
		return isset(self::$db::$$name);
	}

	/**
	 * Provides direct access to method in the database connection.
	 *
	 * @param string $name   Name
	 * @param array  $params Params
	 *
	 * @return $this|null
	 */
	public  function __call(string $name, array $params)
	{
		if (method_exists(self::$db, $name))
		{
			return self::$db::$$name(...$params);
		}

		return null;
	}

	public static function __callStatic(string $name, array $params){
		if (method_exists(self::$db, $name))
		{
			return self::$db::$$name(...$params);
		}

		return null;
	}


	/**
	 * Replace any placeholders within the rules with the values that
	 * match the 'key' of any properties being set. For example, if
	 * we had the following $data array:
	 *
	 * [ 'id' => 13 ]
	 *
	 * and the following rule:
	 *
	 *  'required|is_unique[users,email,id,{id}]'
	 *
	 * The value of {id} would be replaced with the actual id in the form data:
	 *
	 *  'required|is_unique[users,email,id,13]'
	 *
	 * @param array $rules Validation rules
	 * @param array $data  Data
	 *
	 * @codeCoverageIgnore
	 *
	 * @deprecated use FILL_PLACEHOLDERS($rules, $data) from Validation instead
	 *
	 * @return array
	 */
	protected static function FILL_PLACEHOLDERS(array $rules, array $data): array
	{
		$replacements = [];

		foreach ($data as $key => $value)
		{
			$replacements['{' . $key . '}'] = $value;
		}

		if (! empty($replacements))
		{
			foreach ($rules as &$rule)
			{
				if (is_array($rule))
				{
					foreach ($rule as &$row)
					{
						// Should only be an `errors` array
						// which doesn't take placeholders.
						if (is_array($row))
						{
							continue;
						}

						$row = strtr($row, $replacements);
					}

					continue;
				}

				$rule = strtr($rule, $replacements);
			}
		}

		return $rules;
	}
}
