<?php
namespace Hkm_traits\AddOn;

use Hkm_code\Database\BaseBuilder;
use BadMethodCallException;

/**
 * 
 */
trait ModelSetGetTrait
{
    /**
	 * Specify the table associated with a model
	 *
	 * @param string $table Table
	 *
	 * @return $this
	 */
	public static function SET_TABLE(string $table)
	{
		self::$table = $table;

		return self::$thiss;
	}
    /**
	 * Captures the builder's set() method so that we can validate the
	 * data here. This allows it to be used with any of the other
	 * builder methods and still get validated data, like replace.
	 *
	 * @param mixed        $key    Field name, or an array of field/value pairs
	 * @param string|null  $value  Field value, if $key is a single field
	 * @param boolean|null $escape Whether to escape values and identifiers
	 *
	 * @return $this
	 */
	public static function SET($key, ?string $value = '', ?bool $escape = null)
	{
		$data = is_array($key) ? $key : [$key => $value];

		foreach (array_keys($data) as $k)
		{
			self::$tempData['escape'][$k] = $escape;
		}

		self::$tempData['data'] = array_merge(self::$tempData['data'] ?? [], $data);

		return self::$thiss;
	}
	/**
	 * Returns the id value for the data array or object
	 *
	 * @param array|object $data Data
	 *
	 * @return integer|array|string|null
	 */
	public static function GET_ID_VALUE($data)
	{
		if (is_object($data) && isset($data->{self::$primaryKey}))
		{
			return $data->{self::$primaryKey};
		}

		if (is_array($data) && ! empty($data[self::$primaryKey]))
		{
			return $data[self::$primaryKey];
		}

		return null;
	}
	/**
	 * Provides/instantiates the builder/db connection and model's table/primary key names and return type.
	 *
	 * @param string $name Name
	 *
	 * @return mixed
	 */
	public function __get(string $name)
	{
		if (parent::__isset($name))
		{
			return parent::__get($name);
		}

		if (isset(self::BUILDER()->$name))
		{
			return self::BUILDER()->$name;
		}

		return null;
	}

	/**
	 * Checks for the existence of properties across this model, builder, and db connection.
	 *
	 * @param string $name Name
	 *
	 * @return boolean
	 */
	public  function __isset(string $name): bool
	{
		if (parent::__isset($name))
		{
			return true;
		}
		return isset(self::BUILDER()->$name);
	}

	public static function __callStatic(string $name, array $params)
	{
		$result = parent::__callStatic($name, $params);
		if ($result === null && method_exists($builder = self::BUILDER(), $name))
		{
			$result = $builder->{strtolower($name)}(...$params);
		}
		if (empty($result))
		{
			if (! method_exists(self::BUILDER(), $name))
			{
				$className = static::class;

				throw new BadMethodCallException('Call to undefined method ' . $className . '::' . $name);
			}

			return $result;
		}

		if ($result instanceof BaseBuilder)
		{
			return self::$thiss;
		}

		return $result;
	}


	/**
	 * Provides direct access to method in the builder (if available)
	 * and the database connection.
	 *
	 * @param string $name   Name
	 * @param array  $params Params
	 *
	 * @return $this|null
	 */
	public  function __call(string $name, array $params)
	{
		$result = parent::__call($name, $params);

		if ($result === null && method_exists($builder = self::BUILDER(), $name))
		{
			$result = $builder->{$name}(...$params);
		}

		if (empty($result))
		{
			if (! method_exists(self::BUILDER(), $name))
			{
				$className = static::class;

				throw new BadMethodCallException('Call to undefined method ' . $className . '::' . $name);
			}

			return $result;
		}

		if ($result instanceof BaseBuilder)
		{
			return self::$thiss;
		}

		return $result;
	}

	
}
