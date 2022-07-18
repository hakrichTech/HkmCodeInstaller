<?php
namespace Hkm_code\Pobohet;

use Hkm_code\HTTP\Request;
use Hkm_code\Exceptions\PageNotFoundException;
use Hkm_code\Exceptions\Pobohet\PobohetException;
use Hkm_code\Exceptions\Pobohet\RedirectException;

class Pobohet 
{
	
	protected static $directory;

	protected static $params = [];

	protected static $indexPage = 'index.php';

	protected static $translateURIDashes = false;

	protected static $matchedRoute;

	protected static $matchedRouteOptions;

	protected static $detectedLocale;

	
	protected static $filterInfo;

    protected static $collection;
    protected static $controller;
    protected static $method;
    protected static $thiss;
    public function __construct(PobohetCollection $routes, Request $request = null)
	{
		self::$collection = $routes;

		self::$controller = self::$collection::GET_DEFAULT_CONTROLLER();
		self::$method     = self::$collection::GET_DEFAULT_METHOD();
        self::$thiss = $this;
		// @phpstan-ignore-next-line
		self::$collection::SET_HTTP_VERB($request::GET_METHOD() ?? strtolower($_SERVER['REQUEST_METHOD']));
	}
	public static function HANDLE(string $uri = null)
	{


		self::$translateURIDashes = self::$collection::SHOUD_TRANSLATE_URI_DASHES();

		
		// If we cannot find a URI to match against, then
		// everything runs off of it's default settings.
		if ($uri === null || $uri === '')
		{
			
			return strpos(self::$controller, '\\') === false
				? self::$collection::GET_DEFAULT_NAMESPACE() . self::$controller
				: self::$controller;
		}

		// Decode URL-encoded string
		$uri = urldecode($uri);




		// Restart filterInfo
		self::$filterInfo = null;


		if (self::CHECK_ROUTE($uri))
		{


			if (self::$collection::IS_FILTERED(self::$matchedRoute[0]))
			{
				
				self::$filterInfo = self::$collection::GET_FILTER_FOR_ROUTE(self::$matchedRoute[0]);
			}

			return self::$controller;
		}



		// Still here? Then we can try to match the URI against
		// Controllers/directories, but the application may not
		// want this, like in the case of API's.
		if (! self::$collection::SHOULD_AUTO_ROUTE())
		{

			throw new PageNotFoundException("Can't find a route for '{$uri}'.");
		}else{
		  self::AUTO_ROUTE($uri);
		}


		return self::CONTROLLER_NAME();
	}
	public static function GET_FILTER()
	{
		return self::$filterInfo;
	}
	public static function CONTROLLER_NAME()
	{
		return self::$translateURIDashes
			? str_replace('-', '_', self::$controller)
			: self::$controller;
	}

	public static function METHOD_NAME(): string
	{
		return self::$translateURIDashes
			? str_replace('-', '_', self::$method)
			: self::$method;
	}

	public static function GET_404_OVERRIDE()
	{
		$route = self::$collection::GET_404_OVERRIDE();

		if (is_string($route))
		{
			$routeArray = explode('::', $route);

			return [
				$routeArray[0], // Controller
				$routeArray[1] ?? 'index',   // Method
			];
		}

		if (is_callable($route))
		{
			return $route;
		}

		return null;
	}

	public static function PARAMS(): array
	{
		return self::$params;
	}
	public static function DIRECTORY(): string
	{
		return ! empty(self::$directory) ? self::$directory : '';
	}

	public static function GET_MATCHED_ROUTE()
	{
		return self::$matchedRoute;
	}
	public static function GET_MATCHED_ROUTE_OPTIONS()
	{
		return self::$matchedRouteOptions;
	}

	public static function SET_INDEX_PAGE($page): self
	{
		self::$indexPage = $page;

		return self::$thiss;
	}
	Public static function SET_TRANSLATE_URI_DASHES(bool $val = false): self
	{
		self::$translateURIDashes = $val;

		return self::$thiss;
	}

	public static function HAS_LOCAL()
	{
		return (bool) self::$detectedLocale;
	}

	public static function GET_LOCAL()
	{
		return self::$detectedLocale;
	}

	protected static function CHECK_ROUTE(string $uri): bool
	{
		$routes = self::$collection::GET_ROUTES(self::$collection::GET_HTTP_VERB());

		
		

		// Don't waste any time
		if (empty($routes))return false;
		

		$uri = $uri === '/'
			? $uri
			: ltrim($uri, '/ ');



		// Loop through the route array looking for wildcards
		foreach ($routes as $key => $val)
		{
			
			// Reset localeSegment
			$localeSegment = null;

			$key = $key === '/'
				? $key
				: ltrim($key, '/ ');
				

			$matchedKey = $key;

			// Are we dealing with a locale?
			if (strpos($key, '{locale}') !== false)
			{

				$localeSegment = array_search('{locale}', preg_split('/[\/]*((^[a-zA-Z0-9])|\(([^()]*)\))*[\/]+/m', $key), true);

				// Replace it with a regex so it
				// will actually match.
				$key = str_replace('/', '\/', $key);
				$key = str_replace('{locale}', '[^\/]+', $key);
			}

			

			// Does the RegEx match?
			if (preg_match('#^' . $key . '$#u', $uri, $matches))
			{
		       
				
				// Is this route supposed to redirect to another?
				if (self::$collection::IS_REDIRECT($key))
				{
					throw new RedirectException(is_array($val) ? key($val) : $val, self::$collection::GET_DIRECT_CODE($key));
				}
				
				// Store our locale so Hkm_code object can
				// assign it to the Request.
				if (isset($localeSegment))
				{
					
					// The following may be inefficient, but doesn't upset NetBeans :-/
					$temp                 = (explode('/', $uri));
					self::$detectedLocale = $temp[$localeSegment];
				}

				
				
				// Are we using Closures? If so, then we need
				// to collect the params into an array
				// so it can be passed to the controller method later.
				if (! is_string($val) && is_callable($val))
				{

					self::$controller = $val;

					// Remove the original string from the matches array
					array_shift($matches);

					self::$params = $matches;

					self::$matchedRoute = [
						$matchedKey,
						$val,
					];

					self::$matchedRouteOptions = self::$collection::GET_ROUTES_OPTIONS($matchedKey);

					return true;
				}
				// Are we using the default method for back-references?

				// Support resource route when function with subdirectory
				// ex: $routes->resource('Admin/Admins');
				if (strpos($val, '$') !== false && strpos($key, '(') !== false && strpos($key, '/') !== false)
				{
					
					$replacekey = str_replace('/(.*)', '', $key);
					$val        = preg_replace('#^' . $key . '$#u', $val, $uri);
					$val        = str_replace($replacekey, str_replace('/', '\\', $replacekey), $val);
					
				}
				elseif (strpos($val, '$') !== false && strpos($key, '(') !== false)
				{
					
					
					$val = preg_replace('#^' . $key . '$#u', $val, $uri);
				}
				elseif (strpos($val, '/') !== false)
				{
					[
						$controller,
						$method,
					] = explode('::', $val);

					// Only replace slashes in the controller, not in the method.
					$controller = str_replace('/', '\\', $controller);

					$val = $controller . '::' . $method;
				}

				
                 
				self::SET_REQUEST(explode('/', $val));

				self::$matchedRoute = [
					$matchedKey,
					$val,
				];

				self::$matchedRouteOptions = self::$collection::GET_ROUTES_OPTIONS($matchedKey);

				return true;
			}

		}

		return false;
	}

	public static function AUTO_ROUTE(string $uri)
	{
		$segments = explode('/', $uri);


		$segments = self::SCAN_CONTROLLERS($segments);

		// If we don't have any segments left - try the default controller;
		// WARNING: Directories get shifted out of the segments array.
		if (empty($segments))
		{
			self::SET_DEFAULT_CONTROLLER();
		}
		// If not empty, then the first segment should be the controller
		else
		{
			self::$controller = ucfirst(array_shift($segments));

		}

		$controllerName = self::CONTROLLER_NAME();

		if (! self::IS_VALID_SEGMENT($controllerName))
		{
			throw new PageNotFoundException(self::$controller . ' is not a valid controller name');
		}

		// Use the method name if it exists.
		// If it doesn't, no biggie - the default method name
		// has already been set.
		if (! empty($segments))
		{
			self::$method = array_shift($segments) ?: self::$method;
		}

		if (! empty($segments))
		{
			self::$params = $segments;
		}

		$defaultNamespace = self::$collection::GET_DEFAULT_NAMESPACE();
		if (self::$collection::GET_HTTP_VERB() !== 'cli')
		{
			$controller = '\\' . $defaultNamespace;

			$controller .= self::$directory ? str_replace('/', '\\', self::$directory) : '';
			$controller .= $controllerName;

			$controller = strtolower($controller);
			$methodName = strtolower(self::METHOD_NAME());

			foreach (self::$collection::GET_ROUTES('cli') as $route)
			{
				if (is_string($route))
				{
					$route = strtolower($route);
					if (strpos($route, $controller . '::' . $methodName) === 0)
					{
						throw new PageNotFoundException();
					}

					if ($route === $controller)
					{
						throw new PageNotFoundException();
					}
				}
			}
		}

		// Load the file so that it's available for Hkm_code.
		$file = SYSTEMPATH . '../../Controllers/' . self::$directory . $controllerName . '.php';


		if (is_file($file))
		{
			include_once $file;
		}

		// Ensure the controller stores the fully-qualified class name
		// We have to check for a length over 1, since by default it will be '\'
		if (strpos(self::$controller, '\\') === false && strlen($defaultNamespace) > 1)
		{
			self::$controller = '\\' . ltrim(str_replace('/', '\\', $defaultNamespace . self::$directory . $controllerName), '\\');
		}

	}

	protected static function VALIDATE_REQUEST(array $segments): array
	{
		return self::SCAN_CONTROLLERS($segments);
	}
	protected static function SCAN_CONTROLLERS(array $segments): array
	{
		$segments = array_filter($segments, function ($segment) {
			return $segment !== '';
		});
		// numerically reindex the array, removing gaps
		$segments = array_values($segments);

		// if a prior directory value has been set, just return segments and get out of here
		if (isset(self::$directory))
		{
			
			return $segments;
		}

		// Loop through our segments and return as soon as a controller
		// is found or when such a directory doesn't exist
		$c = count($segments);
		while ($c-- > 0)
		{
			$segmentConvert = ucfirst(self::$translateURIDashes === true ? str_replace('-', '_', $segments[0]) : $segments[0]);
			// as soon as we encounter any segment that is not PSR-4 compliant, stop searching
			
			if (! self::IS_VALID_SEGMENT($segmentConvert))
			{
				return $segments;
			}

			$test = SYSTEMPATH . '../../Controllers/' . self::$directory . $segmentConvert;


			// as long as each segment is *not* a controller file but does match a directory, add it to self::$directory
			if (! is_file($test . '.php') && is_dir($test))
			{
				self::SET_DIRECTORY($segmentConvert, true, false);
				array_shift($segments);
				continue;
			}

			return $segments;
		}

		// This means that all segments were actually directories
		return $segments;
	}

	public static function SET_DIRECTORY(string $dir = null, bool $append = false, bool $validate = true)
	{
		if (empty($dir))
		{
			self::$directory = null;
			return;
		}

		if ($validate)
		{
			$segments = explode('/', trim($dir, '/'));
			foreach ($segments as $segment)
			{
				if (! self::IS_VALID_SEGMENT($segment))
				{
					return;
				}
			}
		}

		if ($append !== true || empty(self::$directory))
		{
			self::$directory = trim($dir, '/') . '/';
		}
		else
		{
			self::$directory .= trim($dir, '/') . '/';
		}
	}

	private static function IS_VALID_SEGMENT(string $segment): bool
	{
		return (bool) preg_match('/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/', $segment);
	}

	protected static function SET_DEFAULT_CONTROLLER()
	{
		if (empty(self::$controller))
		{
			throw PobohetException::FOR_MISSING_DEFAULT_ROUTE();
		}

		// Is the method being specified?
		if (sscanf(self::$controller, '%[^/]/%s', $class, self::$method) !== 2)
		{
			self::$method = 'index';
		}

		if (! is_file(SYSTEMPATH . '../../Controllers/' . self::$directory . ucfirst($class) . '.php'))
		{
			return;
		}

		self::$controller = ucfirst($class);

		hkm_log_message('info', 'Used the default controller.');
	}

	protected static function SET_REQUEST(array $segments = [])
	{
		
		// If we don't have any segments - try the default controller;
		if (empty($segments))
		{
			self::SET_DEFAULT_CONTROLLER();

			return;
		}
		

		[$controller, $method] = array_pad(explode('::', $segments[0]), 2, null);


		self::$controller = $controller;


      
		// self::$method already contains the default method name,
		// so don't overwrite it with emptiness.
		if (! empty($method))
		{
			self::$method = $method;
		}

		array_shift($segments);
	
		
		self::$params = $segments;
	}


}
