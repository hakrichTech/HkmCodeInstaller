<?php


namespace Hkm_code\Filters;

use Hkm_code\Filters\Exceptions\FilterException;
use Hkm_code\HTTP\Request;
use Hkm_code\HTTP\Response;


/**
 * Filters
 */
class Filters
{
	/**
	 * The original config file
	 *
	 * @var Object
	 */
	protected static $config;

	/**
	 * The active IncomingRequest or CLIRequest
	 *
	 * @var Request
	 */
	protected static $request;

	/**
	 * The active Response instance
	 *
	 * @var Response
	 */
	protected static $response;

	
	protected static $thiss;

	/**
	 * Whether we've done initial processing
	 * on the filter lists.
	 *
	 * @var boolean
	 */
	protected static $initialized = false;

	/**
	 * The processed filters that will
	 * be used to check against.
	 *
	 * @var array
	 */
	protected static $filters = [
		'before' => [],
		'after'  => [],
	];

	/**
	 * The collection of filters' class names that will
	 * be used to execute in each position.
	 *
	 * @var array
	 */
	protected static $filtersClass = [
		'before' => [],
		'after'  => [],
	];

	/**
	 * Any arguments to be passed to filters.
	 *
	 * @var array
	 */
	protected static $arguments = [];

	/**
	 * Any arguments to be passed to filtersClass.
	 *
	 * @var array
	 */
	protected static $argumentsClass = [];

	
	public  function __construct($config, Request $request, Response $response)
	{
		self::$config  = $config;
		self::$request = &$request;
		self::SET_RESPONSE($response);
		self::$thiss = $this;

	}

	
	public static function SET_RESPONSE(Response $response)
	{
		self::$response = &$response;
	}

	/**
	 * Runs through all of the filters for the specified
	 * uri and position.
	 *
	 * @param string $uri
	 * @param string $position
	 *
	 * @return Request|Response|mixed
	 * @throws FilterException
	 */
	public static function RUN(string $uri, string $position = 'before')
	{

		self::INITIALIZE(strtolower($uri));

		foreach (self::$filtersClass[$position] as $className)
		{
			$class = new $className();

			if (! $class instanceof FilterInterface)
			{
				throw FilterException::FOR_INCORRECT_INTERFACE(get_class($class));
			}

			if ($position === 'before')
			{
				$result = $class::BEFORE(self::$request, self::$argumentsClass[$className] ?? null);

				if ($result instanceof Request)
				{
					self::$request = $result;
					continue;
				}

				// If the response object was sent back,
				// then send it and quit.
				if ($result instanceof Response)
				{
					// short circuit - bypass any other filters
					return $result;
				}
				// Ignore an empty result
				if (empty($result))
				{
					continue;
				}

				return $result;
			}

			if ($position === 'after')
			{
				$result = $class::AFTER(self::$request, self::$response, self::$argumentsClass[$className] ?? null);

				if ($result instanceof Response)
				{
					self::$response = $result;

					continue;
				}
			}
		}

		return $position === 'before' ? self::$request : self::$response;
	}

	
	public static function INITIALIZE(string $uri = null)
	{
		if (self::$initialized === true)
		{
			return self::$thiss;
		}


		self::PROCEDD_GLOBALS($uri);
		self::PROCESS_METHODS();
		self::PROCESS_FILTERS($uri);

		// Set the toolbar filter to the last position to be executed
		if (in_array('toolbar', self::$filters['after'], true) &&
			($count = count(self::$filters['after'])) > 1 &&
			self::$filters['after'][$count - 1] !== 'toolbar'
		)
		{
			array_splice(self::$filters['after'], array_search('toolbar', self::$filters['after'], true), 1);
			self::$filters['after'][] = 'toolbar';
		}

		self::PROCESS_ALIASES_TO_CLASS('before');
		self::PROCESS_ALIASES_TO_CLASS('after');

		self::$initialized = true;

		return self::$thiss;
	}

	public static function RESET(): self
	{
		self::$initialized = false;
		self::$arguments   = self::$argumentsClass = [];
		self::$filters     = self::$filtersClass = [
			'before' => [],
			'after'  => [],
		];

		return self::$thiss;
	}

	/**
	 * Returns the processed filters array.
	 *
	 * @return array
	 */
	public static function GET_FILTER(): array
	{
		return self::$filters;
	}

	/**
	 * Returns the filtersClass array.
	 *
	 * @return array
	 */
	public static function GET_FILTERS_CLASS(): array
	{
		return self::$filtersClass;
	}

	/**
	 * Adds a new alias to the config file.
	 * MUST be called prior to initialize();
	 * Intended for use within routes files.
	 *
	 * @param string      $class
	 * @param string|null $alias
	 * @param string      $when
	 * @param string      $section
	 *
	 * @return self::$thiss
	 */
	public static function ADD_FILTER(string $class, string $alias = null, string $when = 'before', string $section = 'globals')
	{
		$alias = $alias ?? md5($class);

		if (! isset(self::$config::${$section}))
		{
			self::$config::${$section} = [];
		}

		if (! isset(self::$config::${$section}[$when]))
		{
			self::$config::${$section}[$when] = [];
		}

		self::$config::$filters[$alias] = $class;

		self::$config::${$section}[$when][] = $alias;

		return self::$thiss;
	}

	/**
	 * Ensures that a specific filter is on and enabled for the current request.
	 *
	 * Filters can have "arguments". This is done by placing a colon immediately
	 * after the filter name, followed by a comma-separated list of arguments that
	 * are passed to the filter when executed.
	 *
	 * @param string $name
	 * @param string $when
	 *
	 * @return Filters
	 */
	public static function ENABLE_FILTER(string $name, string $when = 'before')
	{
		$name = strtolower($name);
		// Get parameters and clean name
		if (strpos($name, ':') !== false)
		{

			[$name, $params] = explode(':', $name);

			$params = explode(',', $params);
			array_walk($params, function (&$item) {
				$item = trim($item);
			});

			self::$arguments[$name] = $params;
		}
     

		if (! array_key_exists($name, self::$config::$aliases))
		{
			throw FilterException::FOR_NO_ALIAS($name);
		}


		$classNames = (array) self::$config::$aliases[$name];


		foreach ($classNames as $className)
		{
			self::$argumentsClass[$className] = self::$arguments[$name] ?? null;
		}

		if (! isset(self::$filters[$when][$name]))
		{

			self::$filters[$when][]    = $name;
			self::$filtersClass[$when] = array_merge(self::$filtersClass[$when], $classNames);
			
		}

		return self::$thiss;
	}

	/**
	 * Returns the arguments for a specified key, or all.
	 *
	 * @param string|null $key
	 *
	 * @return mixed
	 */
	public static function GET_ARGUMENTS(string $key = null)
	{
		return is_null($key) ? self::$arguments : self::$arguments[$key];
	}

	protected static function PROCEDD_GLOBALS(string $uri = null)
	{
		if (! isset(self::$config::$globals) || ! is_array(self::$config::$globals))
		{
			return;
		}

		$uri = strtolower(trim($uri, '/ '));

		// Add any global filters, unless they are excluded for this URI
		$sets = [
			'before',
			'after',
		];

		foreach ($sets as $set)
		{
			if (isset(self::$config::$globals[$set]))
			{
				// look at each alias in the group
				foreach (self::$config::$globals[$set] as $alias => $rules)
				{
					$keep = true;
					if (is_array($rules))
					{
						// see if it should be excluded
						if (isset($rules['except']))
						{
							// grab the exclusion rules
							$check = $rules['except'];
							if (self::PATH_APPLIES($uri, $check))
							{
								$keep = false;
							}
						}
					}
					else
					{
						$alias = $rules; // simple name of filter to apply
					}

					if ($keep)
					{
						self::$filters[$set][] = $alias;
					}
				}
			}
		}
	}

	/**
	 * Add any method-specific filters to the mix.
	 *
	 * @return void
	 */
	protected static function PROCESS_METHODS()
	{
		if (! isset(self::$config::$methods) || ! is_array(self::$config::$methods))
		{
			return;
		}

		// Request method won't be set for CLI-based requests
		$method = strtolower($_SERVER['REQUEST_METHOD'] ?? 'cli');

		if (array_key_exists($method, self::$config::$methods))
		{
			self::$filters['before'] = array_merge(self::$filters['before'], self::$config::$methods[$method]);
			return;
		}
	}

	/**
	 * Add any applicable configured filters to the mix.
	 *
	 * @param string $uri
	 *
	 * @return void
	 */
	protected static function PROCESS_FILTERS(string $uri = null)
	{
		if (! isset(self::$config::$filters) || ! self::$config::$filters)
		{
			return;
		}

		$uri = strtolower(trim($uri, '/ '));

		// Add any filters that apply to this URI
		foreach (self::$config::$filters as $alias => $settings)
		{
			// Look for inclusion rules
			if (isset($settings['before']))
			{
				$path = $settings['before'];
				if (self::PATH_APPLIES($uri, $path))
				{
					self::$filters['before'][] = $alias;
				}
			}

			if (isset($settings['after']))
			{
				$path = $settings['after'];
				if (self::PATH_APPLIES($uri, $path))
				{
					self::$filters['after'][] = $alias;
				}
			}
		}
	}

	/**
	 * Maps filter aliases to the equivalent filter classes
	 *
	 * @param  string $position
	 * @throws FilterException
	 *
	 * @return void
	 */
	protected static function PROCESS_ALIASES_TO_CLASS(string $position)
	{
		foreach (self::$filters[$position] as $alias => $rules)
		{
			if (is_numeric($alias) && is_string($rules))
			{
				$alias = $rules;
			}

			if (! array_key_exists($alias, self::$config::$aliases))
			{
				throw FilterException::FOR_NO_ALIAS($alias);
			}

			if (is_array(self::$config::$aliases[$alias]))
			{
				self::$filtersClass[$position] = array_merge(self::$filtersClass[$position], self::$config::$aliases[$alias]);
			}
			else
			{
				self::$filtersClass[$position][] = self::$config::$aliases[$alias];
			}
		}

		// when using ENABLE_FILTER() we already write the class name in ::$ as well as the
		// alias in ::$. This leads to duplicates when using route filters.
		// Since some filters like rate limiters rely on being executed once a request we filter em here.
		self::$filtersClass[$position] = array_unique(self::$filtersClass[$position]);
	}

	/**
	 * Check paths for match for URI
	 *
	 * @param string $uri   URI to test against
	 * @param mixed  $paths The path patterns to test
	 *
	 * @return boolean True if any of the paths apply to the URI
	 */
	private static function PATH_APPLIES(string $uri, $paths)
	{
		// empty path matches all
		if (empty($paths))
		{
			return true;
		}

		// make sure the paths are iterable
		if (is_string($paths))
		{
			$paths = [$paths];
		}

		// treat each paths as pseudo-regex
		foreach ($paths as $path)
		{
			// need to escape path separators
			$path = str_replace('/', '\/', trim($path, '/ '));
			// need to make pseudo wildcard real
			$path = strtolower(str_replace('*', '.*', $path));
			// Does this rule apply here?
			if (preg_match('#^' . $path . '$#', $uri, $match) === 1)
			{
				return true;
			}
		}

		return false;
	}
}
