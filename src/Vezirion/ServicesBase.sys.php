<?php

namespace Hkm_code\Vezirion;

use Exception;
use Hkm_Config\Hkm_Bin\Services;

class ServicesBase
{
	protected static $services = [];
	/**
	 * Have we already discovered other Services?
	 *
	 * @var boolean
	 */
	protected static $discovered = false;

	private static $serviceNames = [];
	/**
	 * Cache for instance of any services that
	 * have been requested as a "shared" instance.
	 * Keys should be lowercase service names.
	 *
	 * @var array
	 */
	protected static $instances = [];

	/**
	 * Mock objects for testing which are returned if exist.
	 *
	 * @var array
	 */
	protected static $mocks = [];

	

	public static function LOCATOR(bool $getShared = true) :FileLocator
	{
		if ($getShared)
		{
			return static::GET_SHARED_INSTANCE('LOCATOR');
		}
		return new FileLocator();
	}
	protected static function GET_SHARED_INSTANCE(string $key, ...$params)
	{

		$key = strtoupper($key);

		// Returns mock if exists
		if (isset(static::$mocks[$key]))
		{
			return static::$mocks[$key];
		}

		if (! isset(static::$instances[$key]))
		{

			// Make sure $getShared is false
			$params[] = false;
			if (in_array($key,self::$serviceNames)) {
				$n = array_keys(self::$serviceNames,$key);
				if(count($n)>1){
					hkm_log_message('error','Delpicated service '.$key);
				}
				else{
					static::$instances[$key] = static::$services[$n[0]]::$key(...$params);
				}
			}else{
				static::$instances[$key] = static::$key(...$params);
			}
			
		    return static::$instances[$key];


		}
		
		if(isset(static::$instances[$key])){
			return static::$instances[$key];
		}
	


	}

	
	public static function __callStatic(string $name, array $arguments)
	{
		$service = static::SERVICE_EXISTS($name);

		if ($service === null)
		{
			return null;
		}
		return $service::$name(...$arguments);
	}

	/**
	 * Reset shared instances and mocks for testing.
	 *
	 * @param boolean $initAutoloader Initializes autoloader instance
	 */
	public static function RESET()
	{
		static::$mocks     = [];
		static::$instances = [];

	}

	/**
	 * Resets any mock and shared instances for a single service.
	 *
	 * @param string $name
	 */
	public static function RESET_SINGLE(string $name)
	{
		unset(static::$mocks[$name], static::$instances[$name]);
	}

	/**
	 * Inject mock object for testing.
	 *
	 * @param string $name
	 * @param mixed  $mock
	 */
	public static function INJECT_MOCK(string $name, $mock)
	{
		static::$mocks[strtoupper($name)] = $mock;
	}

	public static function SERVICE_EXISTS(string $name): ?string
	{
		$services = static::$services;
		$name     = strtoupper($name);
		foreach ($services as $service)
		{
			if (method_exists($service, $name))
			{
				return get_class($service);
			}
		}

		return null;
	}
	public static function DISCOVER_SYSTEM_SERVICES($serviceName,$classname)
	{
		if ($classname instanceof Services)
		{
			if (is_array($serviceName)) {
				foreach ($serviceName as $service) {
					static::$serviceNames[] = strtoupper($service);
			        static::$services[]   = $classname;
				}
			}else{
				static::$serviceNames[] = strtoupper($serviceName);
				static::$services[]   = $classname;
			}
			
		}
	}


	protected static function buildServicesCache(): void
	{
		if (! static::$discovered)
		{
			$config = hkm_config('Modules');

			$locator = static::LOCATOR();
			

			if ($config::SHOULD_DISCOVER('services'))
			{
				$files   = $locator::SEARCH(APP_NAMESPACE.'/Services');

				// Get instances of all service classes and cache them locally.
				foreach ($files as $file)
				{
					$classname = $locator::GET_CLASS_NAME($file);

					if ($classname !== 'Hkm_code\\Vezirion\\ServicesSystem')
					{
						self::$serviceNames[] = strtoupper($classname);
						static::$services[]   = new $classname();
					}
				}
			}

			static::$discovered = true;
		}
	}



	
}
