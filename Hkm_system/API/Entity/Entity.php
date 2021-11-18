<?php

/**
 * This file is part of Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Hkm_code\Entity;

use Hkm_code\Entity\Cast\ArrayCast;
use Hkm_code\Entity\Cast\BooleanCast;
use Hkm_code\Entity\Cast\CastInterface;
use Hkm_code\Entity\Cast\CSVCast;
use Hkm_code\Entity\Cast\DatetimeCast;
use Hkm_code\Entity\Cast\FloatCast;
use Hkm_code\Entity\Cast\IntegerCast;
use Hkm_code\Entity\Cast\JsonCast;
use Hkm_code\Entity\Cast\ObjectCast;
use Hkm_code\Entity\Cast\StringCast;
use Hkm_code\Entity\Cast\TimestampCast;
use Hkm_code\Entity\Cast\URICast;
use Hkm_code\Entity\Exceptions\CastException;
use Hkm_code\I18n\Time;
use Exception;
use JsonSerializable;

/**
 * Entity encapsulation, for use with Hkm_code\Model
 */
class Entity implements JsonSerializable
{
    /**
     * Maps names used in sets and gets against unique
     * names within the class, allowing independence from
     * database column names.
     *
     * Example:
     *  $datamap = [
     *      'db_name' => 'class_name'
     *  ];
     */
    protected static $datamap = [];

    protected static $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Array of field names and the type of value to cast them as when
     * they are accessed.
     */
    protected static $casts = [];

    /**
     * Custom convert handlers
     *
     * @var array<string, string>
     */
    protected static $castHandlers = [];

    /**
     * Default convert handlers
     *
     * @var array<string, string>
     */
    private static $defaultCastHandlers = [
        'array'     => ArrayCast::class,
        'bool'      => BooleanCast::class,
        'boolean'   => BooleanCast::class,
        'csv'       => CSVCast::class,
        'datetime'  => DatetimeCast::class,
        'double'    => FloatCast::class,
        'float'     => FloatCast::class,
        'int'       => IntegerCast::class,
        'integer'   => IntegerCast::class,
        'json'      => JsonCast::class,
        'object'    => ObjectCast::class,
        'string'    => StringCast::class,
        'timestamp' => TimestampCast::class,
        'uri'       => URICast::class,
    ];

    /**
     * Holds the current values of all class vars.
     *
     * @var array
     */
    protected static $attributes = [];

    /**
     * Holds original copies of all class vars so we can determine
     * what's actually been changed and not accidentally write
     * nulls where we shouldn't.
     *
     * @var array
     */
    protected static $original = [];
    public static $thiss;

    /**
     * Holds info whenever properties have to be casted
     *
     * @var bool
     */
    private static $_cast = true;

    /**
     * Allows filling in Entity parameters during construction.
     */
    public  function __construct(?array $data = null)
    {
        self::$thiss = $this;
        self::SYNC_ORIGINAL();

        self::FILL($data);
    }

    /**
     * Takes an array of key/value pairs and sets them as class
     * properties, using any `setCamelCasedProperty()` methods
     * that may or may not exist.
     *
     * @param array $data
     *
     * @return $this
     */
    public static function FILL(?array $data = null)
    {
        if (! is_array($data)) {
            return self::$thiss;
        }

        foreach ($data as $key => $value) {
            self::$thiss->__set($key, $value);
        }

        return self::$thiss;
    }

    /**
     * General method that will return all public static and protected static values
     * of this entity as an array. All values are accessed through the
     * __get() magic method so will have any casts, etc applied to them.
     *
     * @param bool $onlyChanged If true, only return values that have changed since object creation
     * @param bool $cast        If true, properties will be casted.
     * @param bool $recursive   If true, inner entities will be casted as array as well.
     */
    public static function TO_ARRAY(bool $onlyChanged = false, bool $cast = true, bool $recursive = false): array
    {
        self::$_cast = $cast;

        $keys = array_filter(array_keys(self::$attributes), static function ($key) {
            return strpos($key, '_') !== 0;
        });

        if (is_array(self::$datamap)) {
            $keys = array_unique(
                array_merge(array_diff($keys, self::$datamap), array_keys(self::$datamap))
            );
        }

        $return = [];

        // Loop over the properties, to allow magic methods to do their thing.
        foreach ($keys as $key) {
            if ($onlyChanged && ! self::HAS_GHANGED($key)) {
                continue;
            }

            $return[$key] = self::$thiss->__get($key);

            if ($recursive) {
                if ($return[$key] instanceof self) {
                    $return[$key] = $return[$key]::TO_ARRAY($onlyChanged, $cast, $recursive);
                } elseif (is_callable([$return[$key], 'TO_ARRAY'])) {
                    $return[$key] = $return[$key]::TO_ARRAY();
                }
            }
        }

        self::$_cast = true;

        return $return;
    }

    /**
     * Returns the raw values of the current attributes.
     *
     * @param bool $onlyChanged If true, only return values that have changed since object creation
     * @param bool $recursive   If true, inner entities will be casted as array as well.
     */
    public static function TO_RAW_ARRAY(bool $onlyChanged = false, bool $recursive = false): array
    {
        $return = [];

        if (! $onlyChanged) {
            if ($recursive) {
                return array_map(static function ($value) use ($onlyChanged, $recursive) {
                    if ($value instanceof self) {
                        $value = $value::TO_RAW_ARRAY($onlyChanged, $recursive);
                    } elseif (is_callable([$value, 'TO_RAW_ARRAY'])) {
                        $value = $value::TO_RAW_ARRAY();
                    }

                    return $value;
                }, self::$attributes);
            }

            return self::$attributes;
        }

        foreach (self::$attributes as $key => $value) {
            if (! self::HAS_GHANGED($key)) {
                continue;
            }

            if ($recursive) {
                if ($value instanceof self) {
                    $value = $value::TO_RAW_ARRAY($onlyChanged, $recursive);
                } elseif (is_callable([$value, 'TO_RAW_ARRAY'])) {
                    $value = $value::TO_RAW_ARRAY();
                }
            }

            $return[$key] = $value;
        }

        return $return;
    }

    /**
     * Ensures our "original" values match the current values.
     *
     * @return $this
     */
    public static function SYNC_ORIGINAL()
    {
        self::$original = self::$attributes;

        return self::$thiss;
    }

    /**
     * Checks a property to see if it has changed since the entity
     * was created. Or, without a parameter, checks if any
     * properties have changed.
     *
     * @param string $key
     */
    public static function HAS_GHANGED(?string $key = null): bool
    {
        // If no parameter was given then check all attributes
        if ($key === null) {
            return self::$original !== self::$attributes;
        }

        // Key doesn't exist in either
        if (! array_key_exists($key, self::$original) && ! array_key_exists($key, self::$attributes)) {
            return false;
        }

        // It's a new element
        if (! array_key_exists($key, self::$original) && array_key_exists($key, self::$attributes)) {
            return true;
        }

        return self::$original[$key] !== self::$attributes[$key];
    }

    /**
     * Set raw data array without any mutations
     *
     * @return $this
     */
    public static function SET_ATTRIBUTES(array $data)
    {
        self::$attributes = $data;

        self::SYNC_ORIGINAL();

        return self::$thiss;
    }

    /**
     * Checks the datamap to see if this column name is being mapped,
     * and returns the mapped name, if any, or the original name.
     *
     * @return mixed|string
     */
    protected static function MAP_PROPERTY(string $key)
    {
        if (empty(self::$datamap)) {
            return $key;
        }

        if (! empty(self::$datamap[$key])) {
            return self::$datamap[$key];
        }

        return $key;
    }

    /**
     * Converts the given string|timestamp|DateTime|Time instance
     * into the "Hkm_code\I18n\Time" object.
     *
     * @param mixed $value
     *
     * @throws Exception
     *
     * @return mixed|Time
     */
    protected static function MUTEATE_DATE($value)
    {
        return DatetimeCast::get($value);
    }

    /**
     * Provides the ability to cast an item as a specific data type.
     * Add ? at the beginning of $type  (i.e. ?string) to get NULL
     * instead of casting $value if $value === null
     *
     * @param mixed  $value     Attribute value
     * @param string $attribute Attribute name
     * @param string $method    Allowed to "get" and "set"
     *
     * @throws CastException
     *
     * @return mixed
     */
    protected static function CAST_AS($value, string $attribute, string $method = 'get')
    {
        if (empty(self::$casts[$attribute])) {
            return $value;
        }

        $type = self::$casts[$attribute];

        $isNullable = false;

        if (strpos($type, '?') === 0) {
            $isNullable = true;

            if ($value === null) {
                return null;
            }

            $type = substr($type, 1);
        }

        // In order not to create a separate handler for the
        // json-array type, we transform the required one.
        $type = $type === 'json-array' ? 'json[array]' : $type;

        if (! in_array($method, ['get', 'set'], true)) {
            throw CastException::forInvalidMethod($method);
        }

        $params = [];

        // Attempt to retrieve additional parameters if specified
        // type[param, param2,param3]
        if (preg_match('/^(.+)\[(.+)\]$/', $type, $matches)) {
            $type   = $matches[1];
            $params = array_map('trim', explode(',', $matches[2]));
        }

        if ($isNullable) {
            $params[] = 'nullable';
        }

        $type = trim($type, '[]');

        $handlers = array_merge(self::$defaultCastHandlers, self::$castHandlers);

        if (empty($handlers[$type])) {
            return $value;
        }

        if (! is_subclass_of($handlers[$type], CastInterface::class)) {
            throw CastException::forInvalidInterface($handlers[$type]);
        }

        return $handlers[$type]::$method($value, $params);
    }

    /**
     * Support for json_encode()
     *
     * @return array
     */
    /* 
     *#[ReturnTypeWillChange]
    */
    public  function jsonSerialize()
    {
        return $this::TO_ARRAY();
    }

    /**
     * Change the value of the private static $_cast property
     *
     * @return bool|Entity
     */
    public static function CAST(?bool $cast = null)
    {
        if ($cast === null) {
            return self::$_cast;
        }

        self::$_cast = $cast;

        return self::$thiss;
    }

    /**
     * Magic method to all protected static/private static class properties to be
     * easily set, either through a direct access or a
     * `setCamelCasedProperty()` method.
     *
     * Examples:
     *  self::$my_property = $p;
     *  self::$setMyProperty() = $p;
     *
     * @param mixed|null $value
     *
     * @throws Exception
     *
     * @return $this
     */
    public  function __set(string $key, $value = null)
    {
        $key = self::$thiss::MAP_PROPERTY($key);

        // Check if the field should be mutated into a date
        if (in_array($key, self::$dates, true)) {
            $value = self::$thiss::MUTEATE_DATE($value);
        }

        $value = self::$thiss::CAST_AS($value, $key, 'set');

        // if a set* method exists for this key, use that method to
        // insert this value. should be outside $isNullable check,
        // so maybe wants to do sth with null value automatically
        $method = strtoupper('set_' . str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $key))));

        if (method_exists($this, $method)) {
            self::$thiss::{$method}($value);

            return self::$thiss;
        }

        // Otherwise, just the value. This allows for creation of new
        // class properties that are undefined, though they cannot be
        // saved. Useful for grabbing values through joins, assigning
        // relationships, etc.
        self::$attributes[$key] = $value;

        return self::$thiss;
    }

    /**
     * Magic method to allow retrieval of protected static and private static class properties
     * either by their name, or through a `getCamelCasedProperty()` method.
     *
     * Examples:
     *  $p = self::$my_property
     *  $p = self::$getMyProperty()
     *
     * @throws Exception
     *
     * @return mixed
     */
    public  function __get(string $key)
    {
        
        $key = self::$thiss::MAP_PROPERTY($key);

        $result = null;

        // Convert to CamelCase for the method
        $method = strtoupper('get_' . str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $key))));

        // if a set* method exists for this key,
        // use that method to insert this value.
        if (method_exists($this, $method)) {
            $result = self::$thiss::{$method}();
        }

        // Otherwise return the protected static property
        // if it exists.
        elseif (array_key_exists($key, self::$attributes)) {
            $result = self::$attributes[$key];
        }

        // Do we need to mutate this into a date?
        if (in_array($key, self::$dates, true)) {
            $result = self::$thiss::MUTEATE_DATE($result);
        }
        // Or cast it as something?
        elseif (self::$_cast) {
            $result = self::$thiss::CAST_AS($result, $key);
        }

        return $result;
    }

    /**
     * Returns true if a property exists names $key, or a getter method
     * exists named like for __get().
     */
    public  function __isset(string $key): bool
    {
        $key = self::$thiss::MAP_PROPERTY($key);

        $method = strtoupper('get_' . str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $key))));

        if (method_exists($this, $method)) {
            return true;
        }

        return isset(self::$attributes[$key]);
    }

    public static function SET_CREATED_AT(string $PAS)
	{
		self::$attributes['created_at'] = $PAS;
		return self::$thiss;
		
	}
	public static function SET_UPDATED_AT(string $PAS)
	{
		self::$attributes['updated_at'] = $PAS;
		return self::$thiss;
		
	}
	public static function SET_DELETED_AT(string $PAS)
	{
		self::$attributes['deleted_at'] = $PAS;
		return self::$thiss;
		
	}

    /**
     * Unsets an attribute property.
     */
    public  function __unset(string $key): void
    {
        unset(self::$attributes[$key]);
    }
}
