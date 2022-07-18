<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@hakrichteam.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Vezirion;


use ReflectionClass;
use RuntimeException;
use ReflectionException;
use Hkm_Config\Hkm_Bin\Encryption;

/**
 * Class BaseConfig
 *
 * Not intended to be used on its own, this class will attempt to
 * automatically populate the child class' properties with values
 * from the environment.
 *
 * These can be set within the .env file.
 */
class BaseVezirion
{
	/**
	 * An optional array of classes that will act as Registrars
	 * for rapidly setting config class properties.
	 *
	 * @var array
	 */
	public static $registrars = [];

	/**
	 * Has module discovery happened yet?
	 *
	 * @var boolean
	 */
	protected static $didDiscovery = false;

	/**
	 * The modules configuration.
	 *
	 * @var Modules
	 */
	protected static $moduleConfig;
	protected static $thiss;

	/**
	 * Will attempt to get environment variables with names
	 * that match the properties of the child class.
	 *
	 * The "shortPrefix" is the lowercase-only config class name.
	 */
	public function __construct()
	{

		static::$moduleConfig = hkm_config('Modules');
        self::$thiss = $this;
		self::REGISTER_PROPERTIES();

		$properties  = array_keys(get_class_vars(static::class));
		$prefix      = static::class;
		$slashAt     = strrpos($prefix, '\\');
		$shortPrefix = strtolower(substr($prefix, $slashAt === false ? 0 : $slashAt + 1));
		foreach ($properties as $property)
		{
			

			self::INIT_ENV_VALUE($this::$$property, $property, $prefix, $shortPrefix);
			
		
			if ($this instanceof Encryption && $property === 'key')
			{
				// Handle hex2bin prefix
				if (strpos($this::$$property, 'hex2bin:') === 0)
				{
					$this::$$property = hex2bin(substr($this::$$property, 8));
				}
				// Handle base64 prefix
				elseif (strpos($this::$$property, 'base64:') === 0)
				{
					$this::$$property = base64_decode(substr($this::$$property, 7), true);
				}
			}
		}
	}

	/**
	 * Initialization an environment-specific configuration setting
	 *
	 * @param mixed  $property
	 * @param string $name
	 * @param string $prefix
	 * @param string $shortPrefix
	 *
	 * @return mixed
	 */
	protected static function INIT_ENV_VALUE(&$property, string $name, string $prefix, string $shortPrefix)
	{
		if (is_array($property))
		{
			foreach (array_keys($property) as $key)
			{
				self::INIT_ENV_VALUE($property[$key], "{$name}.{$key}", $prefix, $shortPrefix);
			}
		}
		elseif (($value = self::GET_ENV_VALUE($name, $prefix, $shortPrefix)) !== false && ! is_null($value))
		{
			if ($value === 'false')
			{
				$value = false;
			}
			elseif ($value === 'true')
			{
				$value = true;
			}
			$property = is_bool($value) ? $value : trim($value, '\'"');
		}
		return $property;
	}

	/**
	 * Retrieve an environment-specific configuration setting
	 *
	 * @param string $property
	 * @param string $prefix
	 * @param string $shortPrefix
	 *
	 * @return mixed
	 */
	protected static function GET_ENV_VALUE(string $property, string $prefix, string $shortPrefix)
	{
		$shortPrefix = ltrim($shortPrefix, '\\');

		switch (true)
		{
			case array_key_exists("{$shortPrefix}.{$property}", $_ENV):
				return $_ENV["{$shortPrefix}.{$property}"];
			case array_key_exists("{$shortPrefix}.{$property}", $_SERVER):
				return $_SERVER["{$shortPrefix}.{$property}"];
			case array_key_exists("{$prefix}.{$property}", $_ENV):
				return $_ENV["{$prefix}.{$property}"];
			case array_key_exists("{$prefix}.{$property}", $_SERVER):
				return $_SERVER["{$prefix}.{$property}"];
			default:
				$value = getenv("{$shortPrefix}.{$property}");
				$value = $value === false ? getenv("{$prefix}.{$property}") : $value;

				return $value === false ? null : $value;
		}
	}

	/**
	 * Provides external libraries a simple way to register one or more
	 * options into a config file.
	 *
	 * @throws ReflectionException
	 */
	protected static function REGISTER_PROPERTIES()
	{
		// if (! static::$moduleConfig->shouldDiscover('registrars'))
		// {
		// 	return;
		// }

		// if (! static::$didDiscovery)
		// {
		// 	$locator         = ServicesSystem::LOCATOR();
		// 	$registrarsFiles = $locator->search('Config/Registrar.php');

		// 	foreach ($registrarsFiles as $file)
		// 	{
		// 		$className            = $locator->getClassname($file);
		// 		static::$registrars[] = new $className();
		// 	}

		// 	static::$didDiscovery = true;
		// }

		$shortName = (new ReflectionClass(self::$thiss))->getShortName();

		// Check the registrar class for a method named after this class' shortName
		foreach (self::$registrars as $callable)
		{
			// ignore non-applicable registrars
			if (! method_exists($callable, $shortName))
			{
				// @codeCoverageIgnoreStart
				continue;
				// @codeCoverageIgnoreEnd
			}

			$properties = $callable::$shortName();

			if (! is_array($properties))
			{
				throw new RuntimeException('Registrars must return an array of properties and their values.');
			}

			foreach ($properties as $property => $value)
			{
				if (isset(self::$thiss::$$property) && is_array(self::$thiss::$$property) && is_array($value))
				{
					self::$thiss::$$property = array_merge(self::$thiss::$$property, $value);
				}
				else
				{
					self::$thiss::$$property = $value;
				}
			}
		}
	}
}
