<?php
namespace Hkm_code\Pobohet;
use Closure;
use Hkm_code\Vezirion\ServicesSystem;
use InvalidArgumentException; 
use Hkm_traits\AddOn\PobohetCollectionTrait;
use Hkm_code\Exceptions\Pobohet\PobohetException;
use Hkm_code\Vezirion\vezirionData\vezirionDataHelper;


class PobohetCollection 
{
	protected static $defaultMethod;
	protected static $defaultPlaceholder;
	protected static $defaultNamespace = '\\';
	protected static $defaultController ;
	protected static $defaultHTTPMethods;

    protected static $fileLocator;
	protected static $translateURIDashes = false;
	protected static $autoRoute = false;
	protected static $override404;

	public static $routes;
	protected static $routesOptions = [];


	

    protected static $moduleConfig;
	

	protected static $group;
	protected static $currentOptions;
	protected static $currentSubdomain;
	protected static $prioritize = false;
	protected static $prioritizeDetected = false;

	protected static $HTTPVerb = '*';
    protected static $placeholders;
    protected static $thiss;
	public static $data;
	
     use PobohetCollectionTrait;

    public function __construct($moduleConfig = null)
	{
		// self::$fileLocator  = $locator;
        self::$moduleConfig = $moduleConfig;
		self::$thiss = $this;
		
	}
	
	public static function INITIALIZE(bool $default = true)
	{
		$data = ServicesSystem::DOM_XML_IN_ARRAY();
		$data::XML_READER('routes','Pobohet','Default');

		 if ($default) {
			 $Default_data = $data::$fileData['Pobohet'];
             foreach ($Default_data as $key=>$value) {
				$method = 'SET_'.strtoupper($key);
				if (is_callable(array(self::$thiss, $method))){
				  self::$method($value);
				}
			  }
		 } 

		$data::XML_READER('routes','Pobohet','All');
		$routes = $data::$fileData;
		unset($routes['Pobohet']);
		$data::XML_READER('routes','Pobohet',str_replace("HakrichApp",'',APP_NAME));
		$approutes = $data::$fileData;


        if (gettype($approutes) == 'array') {
			if (isset($approutes['Pobohet'])) unset($approutes['Pobohet']);
			$routes = array_merge($routes,$approutes);
		}
        
		$data::XML_READER('routes','Pobohet',str_replace("HakrichApp",'',APP_NAME).'HakrichApp');
		$approutes1 = $data::$fileData;

		if (gettype($approutes1) == 'array') {
			if (isset($approutes1['Pobohet'])) unset($approutes1['Pobohet']);
			$routes = array_merge($routes,$approutes1);
		}

		hkm_Runtime_regester(); 
		hkm_run_config();
		$routs = hkm_add_routes('hkm_apply_filters'); 
		$routes = array_merge($routes,$routs);

	  
		 self::$data = $routes;
		 self::INITIALIZE_POBOHET();
		
	}

	protected static function INITIALIZE_POBOHET()
	{
		
		foreach(self::$data as $pobohet => $value){
			if (is_array($value['pobohet'])){

			  foreach ($value['pobohet'] as $pobohet){
				$options = $value['options']??[];
				self::CREATE($pobohet,$value['url'],$value['controller']."::".$value['method'],empty($options)?[]:$options);
			  }
			}else{
				$options = $value['options']??[];
				self::CREATE($value['pobohet'],$value['url'],$value['controller']."::".$value['method'],empty($options)?[]:$options);
			} 
		}
		
	}

	public static function CLI_CREATE_ROUTE(array $new_route)
	{
		$D =  ServicesSystem::DOM_XML_IN_ARRAY()::XML_ADD_DATA("routes",$new_route,'Pobohet','url');
		self::INITIALIZE();
		return $D;
	}
   
	
	public static  function GET_ROUTES(string $verb = null)
	{
		if (empty($verb))
		{
			$verb = self::GET_HTTP_VERB();
		}


		$routes     = [];
		$collection = [];

		if (isset(self::$routes[$verb])){
			$extraRules = array_diff_key(self::$routes['*'], self::$routes[$verb]);
			$collection = array_merge(self::$routes[$verb],$extraRules);

			foreach ($collection as $r)
			{
				$key          = key($r['route']);
				$routes[$key] = $r['route'][$key];
			}
		}

		if (self::$prioritizeDetected && self::$prioritize && $routes !== [])
		{
			$order = [];

			foreach ($routes as $key => $value)
			{
				$key                    = $key === '/' ? $key : ltrim($key, '/ ');
				$priority               = self::GET_ROUTES_OPTIONS($key, $verb)['priority'] ?? 0;
				$order[$priority][$key] = $value;
			}

			ksort($order);
			$routes = array_merge(...$order);
		}

		return $routes;

	}
	public static function MAP(array $routes = [], array $options = null)
	{
		foreach ($routes as $from => $to)
		{
			self::ADD($from, $to, $options);
		}

		return self::$thiss;
	}

	public static function ADD(string $from, $to, array $options = null)
	{
		self::CREATE('*', $from, $to, $options);

		return self::$thiss; 
	}

	public static function ADD_REDIRECT(string $from, string $to, int $status = 302)
	{
		// Use the named route's pattern if this is a named route.
		if (array_key_exists($to, self::$routes['*']))
		{
			$to = self::$routes['*'][$to]['route'];
		}
		elseif (array_key_exists($to, self::$routes['get']))
		{
			$to = self::$routes['get'][$to]['route'];
		}

		self::CREATE('*', $from, $to, ['redirect' => $status]);

		return self::$thiss;
	}
	public static function IS_REDIRECT(string $from): bool
	{
		foreach (self::$routes['*'] as $name => $route)
		{
			// Named route?
			if ($name === $from || key($route['route']) === $from)
			{
				return isset($route['redirect']) && is_numeric($route['redirect']);
			}
		}

		return false;
	}

	public static function GET_DIRECT_CODE(string $from): int
	{
		foreach (self::$routes['*'] as $name => $route)
		{
			// Named route?
			if ($name === $from || key($route['route']) === $from)
			{
				return $route['redirect'] ?? 0;
			}
		}

		return 0;
	}
 
	public static function GROUP(string $name, ...$params)
	{
		$oldGroup   = self::$group;
		$oldOptions = self::$currentOptions;

		// To register a route, we'll set a flag so that our router
		// so it will see the group name.
		// If the group name is empty, we go on using the previously built group name.
		self::$group = $name ? ltrim($oldGroup . '/' . $name, '/') : $oldGroup;

		$callback = array_pop($params);

		if ($params && is_array($params[0]))
		{
			self::$currentOptions = array_shift($params);
		}

		if (is_callable($callback))
		{
			$callback(self::$thiss);
		}

		self::$group          = $oldGroup;
		self::$currentOptions = $oldOptions;
	}

	public static function RESOURCE(string $name, array $options = null)
	{
		// In order to allow customization of the route the
		// resources are sent to, we need to have a new name
		// to store the values in.
		$newName = implode('\\', array_map('ucfirst', explode('/', $name)));
		// If a new controller is specified, then we replace the
		// $name value with the name of the new controller.
		if (isset($options['controller']))
		{
			$newName = ucfirst(filter_var($options['controller'], FILTER_SANITIZE_STRING));
		}

		// In order to allow customization of allowed id values
		// we need someplace to store them.
		$id = self::$placeholders[self::$defaultPlaceholder] ?? '(:segment)';

		if (isset($options['placeholder']))
		{
			$id = $options['placeholder'];
		}

		// Make sure we capture back-references
		$id = '(' . trim($id, '()') . ')';

		$methods = isset($options['only']) ? (is_string($options['only']) ? explode(',', $options['only']) : $options['only']) : ['index', 'show', 'create', 'update', 'delete', 'new', 'edit'];

		if (isset($options['except']))
		{
			$options['except'] = is_array($options['except']) ? $options['except'] : explode(',', $options['except']);
			foreach ($methods as $i => $method)
			{
				if (in_array($method, $options['except'], true))
				{
					unset($methods[$i]);
				}
			}
		}

		if (in_array('index', $methods, true))
		{
			self::GET($name, $newName . '::index', $options);
		}
		if (in_array('new', $methods, true))
		{
			self::GET($name . '/new', $newName . '::new', $options);
		}
		if (in_array('edit', $methods, true))
		{
			self::GET($name . '/' . $id . '/edit', $newName . '::edit/$1', $options);
		}
		if (in_array('show', $methods, true))
		{
			self::GET($name . '/' . $id, $newName . '::show/$1', $options);
		}
		if (in_array('create', $methods, true))
		{
			self::POST($name, $newName . '::create', $options);
		}
		if (in_array('update', $methods, true))
		{
			self::PUT($name . '/' . $id, $newName . '::update/$1', $options);
			self::PATCH($name . '/' . $id, $newName . '::update/$1', $options);
		}
		if (in_array('delete', $methods, true))
		{
			self::DELETE($name . '/' . $id, $newName . '::delete/$1', $options);
		}

		// Web Safe? delete needs checking before update because of method name
		if (isset($options['websafe']))
		{
			if (in_array('delete', $methods, true))
			{
				self::POST($name . '/' . $id . '/delete', $newName . '::delete/$1', $options);
			}
			if (in_array('update', $methods, true))
			{
				self::POST($name . '/' . $id, $newName . '::update/$1', $options);
			}
		}

		return self::$thiss;
	}
	public static function PRESENTER(string $name, array $options = null)
	{
		// In order to allow customization of the route the
		// resources are sent to, we need to have a new name
		// to store the values in.
		$newName = implode('\\', array_map('ucfirst', explode('/', $name)));
		// If a new controller is specified, then we replace the
		// $name value with the name of the new controller.
		if (isset($options['controller']))
		{
			$newName = ucfirst(filter_var($options['controller'], FILTER_SANITIZE_STRING));
		}

		// In order to allow customization of allowed id values
		// we need someplace to store them.
		$id = self::$placeholders[self::$defaultPlaceholder] ?? '(:segment)';

		if (isset($options['placeholder']))
		{
			$id = $options['placeholder'];
		}

		// Make sure we capture back-references
		$id = '(' . trim($id, '()') . ')';

		$methods = isset($options['only']) ? (is_string($options['only']) ? explode(',', $options['only']) : $options['only']) : ['index', 'show', 'new', 'create', 'edit', 'update', 'remove', 'delete'];

		if (isset($options['except']))
		{
			$options['except'] = is_array($options['except']) ? $options['except'] : explode(',', $options['except']);
			foreach ($methods as $i => $method)
			{
				if (in_array($method, $options['except'], true))
				{
					unset($methods[$i]);
				}
			}
		}

		if (in_array('index', $methods, true))
		{
			self::GET($name, $newName . '::index', $options);
		}
		if (in_array('show', $methods, true))
		{
			self::GET($name . '/show/' . $id, $newName . '::show/$1', $options);
		}
		if (in_array('new', $methods, true))
		{
			self::GET($name . '/new', $newName . '::new', $options);
		}
		if (in_array('create', $methods, true))
		{
			self::POST($name . '/create', $newName . '::create', $options);
		}
		if (in_array('edit', $methods, true))
		{
			self::GET($name . '/edit/' . $id, $newName . '::edit/$1', $options);
		}
		if (in_array('update', $methods, true))
		{
			self::POST($name . '/update/' . $id, $newName . '::update/$1', $options);
		}
		if (in_array('remove', $methods, true))
		{
			self::GET($name . '/remove/' . $id, $newName . '::remove/$1', $options);
		}
		if (in_array('delete', $methods, true))
		{
			self::POST($name . '/delete/' . $id, $newName . '::delete/$1', $options);
		}
		if (in_array('show', $methods, true))
		{
			self::GET($name . '/' . $id, $newName . '::show/$1', $options);
		}
		if (in_array('create', $methods, true))
		{
			self::POST($name, $newName . '::create', $options);
		}

		return self::$thiss;
	}

	public static function MATCH_(array $verbs = [], string $from = '', $to = '', array $options = null)
	{
		if (empty($from) || empty($to))
		{
			throw new InvalidArgumentException('You must supply the parameters: from, to.');
		}

		foreach ($verbs as $verb)
		{
			$verb = strtoupper($verb);

			self::{$verb}($from, $to, $options);
		}

		return self::$thiss;
	}

	public static function REVERSE_ROUTE(string $search, ...$params)
	{
		// Named routes get higher priority.
		foreach (self::$routes as $collection)
		{
			if (array_key_exists($search, $collection))
			{
				$route = self::FILL_ROUTE_PARAMS(key($collection[$search]['route']), $params);
				return self::LOCALIZE_ROUTE($route);
			}
		}

		// If it's not a named route, then loop over
		// all routes to find a match.
		foreach (self::$routes as $collection)
		{
			foreach ($collection as $route)
			{
				$from = key($route['route']);
				$to   = $route['route'][$from];

				// ignore closures
				if (! is_string($to))
				{
					continue;
				}

				// Lose any namespace slash at beginning of strings
				// to ensure more consistent match.
				$to     = ltrim($to, '\\');
				$search = ltrim($search, '\\');

				// If there's any chance of a match, then it will
				// be with $search at the beginning of the $to string.
				if (strpos($to, $search) !== 0)
				{
					continue;
				}

				// Ensure that the number of $params given here
				// matches the number of back-references in the route
				if (substr_count($to, '$') !== count($params))
				{
					continue;
				}

				$route = self::FILL_ROUTE_PARAMS($from, $params);
				return self::LOCALIZE_ROUTE($route);
			}
		}

		// If we're still here, then we did not find a match.
		return false;
	}

	protected static function LOCALIZE_ROUTE(string $route) :string
	{
		return strtr($route, ['{locale}' => ServicesSystem::REQUEST()::GET_LOCALE()]);
	}

	public static function GET_FILTER_FOR_ROUTE(string $search, string $verb = null): string
	{
		$options = self::LOAD_ROUTES_OPTIONS($verb);

		return $options[$search]['filter'] ?? '';
	}

	protected static function FILL_ROUTE_PARAMS(string $from, array $params = null): string
	{
		// Find all of our back-references in the original route
		preg_match_all('/\(([^)]+)\)/', $from, $matches);

		if (empty($matches[0]))
		{
			return '/' . ltrim($from, '/');
		}

		// Build our resulting string, inserting the $params in
		// the appropriate places.
		foreach ($matches[0] as $index => $pattern)
		{
			if (! preg_match('#^' . $pattern . '$#u', $params[$index]))
			{
				throw PobohetException::FOR_INVALID_PARAMETER_TYPE();
			}

			// Ensure that the param we're inserting matches
			// the expected param type.
			$pos  = strpos($from, $pattern);
			$from = substr_replace($from, $params[$index], $pos, strlen($pattern));
		}

		return '/' . ltrim($from, '/');
	}

	protected static function CREATE(string $verb, string $from, $to, array $options = null)
	{
		$overwrite = false;
		$prefix    = is_null(self::$group) ? '' : self::$group . '/';
		$from = htmlspecialchars($prefix . $from);
		if ($from !== '/')$from = trim($from, '/');
		
		$options = array_merge(self::$currentOptions ?? [], $options ?? []);

		// Route priority detect
		if (isset($options['priority']))
		{
			$options['priority'] = abs((int) $options['priority']);

			if ($options['priority'] > 0)
			{
				self::$prioritizeDetected = true;
			}
		}

		// Hostname limiting?
		if (! empty($options['hostname']))
		{
			// @todo determine if there's a way to whitelist hosts?
			if (isset($_SERVER['HTTP_HOST']) && strtolower($_SERVER['HTTP_HOST']) !== strtolower($options['hostname']))
			{
				return;
			}

			$overwrite = true;
		}

		// Limiting to subdomains?
		elseif (! empty($options['subdomain']))
		{
			// If we don't match the current subdomain, then
			// we don't need to add the route.
			if (! self::CHECK_SUBDOMAINS($options['subdomain']))
			{
				return;
			}

			$overwrite = true;
		}
		// Are we offsetting the binds?
		// If so, take care of them here in one
		// fell swoop.
		if (isset($options['offset']) && is_string($to))
		{
			// Get a constant string to work with.
			$to = preg_replace('/(\$\d+)/', '$X', $to);

			for ($i = (int) $options['offset'] + 1; $i < (int) $options['offset'] + 7; $i ++)
			{
				$to = preg_replace_callback(
						'/\$X/', function ($m) use ($i) {
							return '$' . $i;
						}, $to, 1
				);
			}
		}

		foreach (self::$placeholders as $tag => $pattern)
		{ 
			$from = str_ireplace(':' . $tag, $pattern, $from);
			$verb = str_ireplace(':' . $tag, $pattern, $verb);
		}



		if (! isset($options['redirect']) && is_string($to))
		{
			if (strpos($to, '\\') === false || strpos($to, '\\') > 0)
				{
					$namespace = $options['namespace'] ?? self::$defaultNamespace;
					$to        = trim($namespace, '\\') . '\\' . $to;
			}
			$to = '\\' . ltrim($to, '\\');
		}

		$name = $options['as'] ?? $from;


		if (isset(self::$routes[$verb][$name]) && ! $overwrite)
		{
			return;
		}

		self::$routes[$verb][$name] = [
			'route' => [$from => $to],
		];
		self::$routesOptions[$verb][$from] = $options;


		if (isset($options['redirect']) && is_numeric($options['redirect']))
		{
			self::$routes['*'][$name]['redirect'] = $options['redirect'];
		}

		
	}

	private static function CHECK_SUBDOMAINS($subdomains): bool
	{
		// CLI calls can't be on subdomain.
		if (! isset($_SERVER['HTTP_HOST']))
		{
			return false;
		}

		if (is_null(self::$currentSubdomain))
		{
			self::$currentSubdomain = self::DETERMINE_CURRENT_SUBDOMAIN();
		}

		if (! is_array($subdomains))
		{
			$subdomains = [$subdomains];
		}

		// Routes can be limited to any sub-domain. In that case, though,
		// it does require a sub-domain to be present.
		if (! empty(self::$currentSubdomain) && in_array('*', $subdomains, true))
		{
			return true;
		}

		return in_array(self::$currentSubdomain, $subdomains, true);
	}

	private static function DETERMINE_CURRENT_SUBDOMAIN()
	{
		// We have to ensure that a scheme exists
		// on the URL else parse_url will mis-interpret
		// 'host' as the 'path'.
		$url = $_SERVER['HTTP_HOST'];
		if (strpos($url, 'http') !== 0)
		{
			$url = 'http://' . $url;
		}

		$parsedUrl = parse_url($url);

		$host = explode('.', $parsedUrl['host']);

		if ($host[0] === 'www')
		{
			unset($host[0]);
		}

		// Get rid of any domains, which will be the last
		unset($host[count($host)]);

		// Account for .co.uk, .co.nz, etc. domains
		if (end($host) === 'co')
		{
			$host = array_slice($host, 0, -1);
		}

		// If we only have 1 part left, then we don't have a sub-domain.
		if (count($host) === 1)
		{
			// Set it to false so we don't make it back here again.
			return false;
		}

		return array_shift($host);
	}

	public static function RESET_ROUTES()
	{
		self::$routes = ['*' => []];
		foreach (self::$defaultHTTPMethods as $verb)
		{
			self::$routes[$verb] = [];
		}

		self::$prioritizeDetected = false;
	}

	protected static function LOAD_ROUTES_OPTIONS(string $verb = null): array
	{
		$verb = $verb ?: self::GET_HTTP_VERB();


		$options = self::$routesOptions[$verb] ?? [];

		if (isset(self::$routesOptions['*']))
		{
			foreach (self::$routesOptions['*'] as $key => $val)
			{
				if (isset($options[$key]))
				{
					$extraOptions  = array_diff_key($val, $options[$key]);
					$options[$key] = array_merge($options[$key], $extraOptions);
				}
				else
				{
					$options[$key] = $val;
				}
			}
		}

		return $options;
	}

	


}
