<?php

use Hkm_APIs\Hkm_Hook;
use LanguageApiPlugin\MO;
use Laminas\Escaper\Escaper;
use Hkm_code\Vezirion\Factories;
use Hkm_code\Exceptions\Hkm_Error;
use Hkm_code\Vezirion\FileLocator;
use Hkm_code\Encryption\Encryption;
use Hkm_code\HTTP\RedirectResponse;
use Hkm_code\Vezirion\ServicesBase;
use Hkm_code\Vezirion\ServicesSystem;
use Hkm_code\Exceptions\SystemException;
use Hkm_code\Exceptions\File\FileNotFoundException;
use Hkm_code\Validation\Exceptions\ValidationException;

function __RegesterAsServices()
{
	$locator = ServicesSystem::LOCATOR();
	$files   = $locator::SEARCH('RegesterAsService');

	// Get instances of all service classes and cache them locally.
	foreach ($files as $file)
	{
		$classname = $locator::GET_CLASS_NAME($file);

		if ($classname !== 'Hkm_code\\Vezirion\\ServicesSystem')
		{
			$class = new $classname(); 
			ServicesBase::DISCOVER_SYSTEM_SERVICES(
				$class->name,$class
			);
		}
	}
}

function __RegesterAsServicesHelpers()
{
	$locator = ServicesSystem::LOCATOR();
	$files   = $locator::SEARCH('RegesterAsService');

	// Get instances of all service classes and cache them locally.
	foreach ($files as $file)
	{
		$classname = $locator::GET_CLASS_NAME($file);

		if ($classname !== 'Hkm_code\\Vezirion\\ServicesSystem')
		{
			$class = new $classname(); 
            $helpers = $class->helpers;
			if (is_array($helpers) && !empty($helpers)) {
				foreach ($helpers as $helper) {
					require_once SYSTEMROOTPATH.$helper;

				}
			}else{
                if(!empty($helpers)) include SYSTEMROOTPATH.$helpers;
			}

		}
	}
}

__RegesterAsServices();





// use Hkm_code\Vezirion\vezirionData\vezirionDataHelper;
/** @var Hkm_Hook[] $hkm_filter */
global $hkm_filter;


/** @var int[] $hkm_actions */
global $hkm_actions;


/** @var string[] $hkm_plugin_paths */
global $hkm_plugin_paths;


/** @var string[] $hkm_current_filter */
global $hkm_current_filter;

if ( $hkm_filter ) {
	$hkm_filter = Hkm_Hook::BUILD_PREINITIALIZED_HOOKS( $hkm_filter );
} else {
	$hkm_filter = array();
}

if ( ! isset( $hkm_actions ) ) {
	$hkm_actions = array();
}

if ( ! isset( $hkm_plugin_paths ) ) {
	$hkm_plugin_paths = array();
}


if ( ! isset( $hkm_current_filter ) ) {
	$hkm_current_filter = array();
}
/**
 * @var MO[] $l10n
 */

global $l10n;

if (is_null($l10n)) {
    $l10n = array();
}

/**
 * @var String $hkm_local_package
 * @var String $locale
 */

global $locale, $hkm_local_package;

$locale = "fr";

/**
 * @var MO[] $l10n_unloaded
 */
global $l10n_unloaded;
if (is_null($l10n_unloaded)) {
    $l10n_unloaded = array();
}

global $current_site;

global $Auth_rest_auth_cookie;

global $Hkm_routesAddOn;
global $cridential;
global $appData;
global $config_functions;
$config_functions = [];

global $currentAppDatabase;
$currentAppDatabase = [];

$Hkm_routesAddOn = [];

$cridential = [];
$appData = [];

if ( $hkm_filter ) {
	$hkm_filter = Hkm_Hook::BUILD_PREINITIALIZED_HOOKS( $hkm_filter );
} else {
	$hkm_filter = array();
}

if ( ! isset( $hkm_actions ) ) {
	$hkm_actions = array();
}

if ( ! isset( $hkm_current_filter ) ) {
	$hkm_current_filter = array();
}


if (!function_exists('hkm_lang')) {

	function hkm_lang(string $line, array $args = [], string $locale = "en")
	{
		return ServicesSystem::LANGUAGE($locale)::GET_LINE($line, $args);
	}
}


if (!function_exists('hkm_helper')) {
	/**
	 * Loads a helper file into memory. Supports namespaced helpers,
	 * both in and out of the 'helpers' directory of a namespaced directory.
	 *
	 * Will load ALL helpers of the matching name, in the following order:
	 *   1. app/Helpers
	 *   2. {namespace}/Helpers
	 *   3. system/Helpers
	 *
	 * @param  string|array $filenames
	 * @throws FileNotFoundException
	 */
	function hkm_helper($filenames)
	{
		static $loaded = [];

		$loader = ServicesSystem::LOCATOR(true);

		if (!is_array($filenames)) {
			$filenames = [$filenames];
		}

		// Store a list of all files to include...
		$includes = [];

		foreach ($filenames as $filename) {
			// Store our system and application helper
			// versions so that we can control the load ordering.
			$systemHelper  = null;
			$appHelper     = null;
			$localIncludes = [];

			if (strpos($filename, '_helper') === false) {
				$filename .= '_helper';
			}

			// Check if this helper has already been loaded
			if (in_array($filename, $loaded, true)) {
				continue;
			}

			if (strpos($filename, '\\') !== false){
				$path = $loader::LOCATE_FILE($filename, 'Helpers');
				if (is_array($path)) {
					foreach ($path as $p) {
						if (!is_file($p)) {
							throw FileNotFoundException::FOR_FILE_NOT_FOUND($filename);
							// print_r($filename . " not Found!");
							// exit;
						}
			
						$includes[] = $p;
						$loaded[]   = $filename;
					}
				}
				
				// $path = SYSTEMPATH . "Helpers/" . $filename . '.php';
	
				
			}else{
				$paths = $loader::SEARCH('Helpers/' . $filename);
				if (! empty($paths))
				{
					foreach ($paths as $path)
					{
						if (strpos($path, APPPATH) === 0)
						{
							// @codeCoverageIgnoreStart
							$appHelper = $path;
							// @codeCoverageIgnoreEnd
						}
						elseif (strpos($path, SYSTEMPATH) === 0)
						{
							$systemHelper = $path;
						}
						else
						{
							$localIncludes[] = $path;
							$loaded[]        = $filename;
						}
					}
				}

				// App-level helpers should override all others
				if (! empty($appHelper))
				{
					// @codeCoverageIgnoreStart
					$includes[] = $appHelper;
					$loaded[]   = $filename;
					// @codeCoverageIgnoreEnd
				}

				// All namespaced files get added in next
				$includes = array_merge($includes, $localIncludes);

				// And the system default one should be added in last.
				if (! empty($systemHelper))
				{
					$includes[] = $systemHelper;
					$loaded[]   = $filename;
				}
			}

			
		}

		// Now actually include all of the files
		if (!empty($includes)) {
			foreach ($includes as $path) {
				include_once($path);
			}
		}
	}
}


if (!function_exists('hkm_view')) {
	/**
	 * Grabs the current RendererInterface-compatible class
	 * and tells it to render the specified view. Simply provides
	 * a convenience method that can be used in Controllers,
	 * libraries, and routed closures.
	 *
	 * NOTE: Does not provide any escaping of the data, so that must
	 * all be handled manually by the developer.
	 *
	 * @param string $name
	 * @param array  $data
	 * @param array  $options Unused - reserved for third-party extensions.
	 *
	 * @return string
	 */
	function hkm_view(string $name, array $data = [], array $options = []): string
	{
		/**
		 * @var Hkm_code\View\View $renderer
		 */
		$renderer = ServicesSystem::RENDERER();

		$saveData = hkm_config(View::class)::$saveData;

		if (array_key_exists('saveData', $options)) {
			$saveData = (bool) $options['saveData'];
			unset($options['saveData']);
		}

		return $renderer::SET_DATA($data, 'raw')
			::RENDER($name, $options, $saveData);
	}
}
if (!function_exists('hkm_redirect')) {
	/**
	 * Convenience method that works with the current global $request and
	 * $router instances to redirect using named/reverse-routed routes
	 * to determine the URL to go to. If nothing is found, will treat
	 * as a traditional redirect and pass the string in, letting
	 * $response->redirect() determine the correct method and code.
	 *
	 * If more control is needed, you must use $response->redirect explicitly.
	 *
	 * @param string $route
	 *
	 * @return RedirectResponse
	 */
	function hkm_redirect(string $route = null): RedirectResponse
	{
		$response = ServicesSystem::REDIRECT_RESPONSE(null, true);

		if (!empty($route)) {
			return $response::ROUTE($route);
		}

		return $response;
	}
}

if (!function_exists('hkm_view_string')) {
	/**
	 * Grabs the current RendererInterface-compatible class
	 * and tells it to render the specified view. Simply provides
	 * a convenience method that can be used in Controllers,
	 * libraries, and routed closures.
	 *
	 * NOTE: Does not provide any escaping of the data, so that must
	 * all be handled manually by the developer.
	 *
	 * @param string $view
	 * @param array  $data
	 * @param array  $options Unused - reserved for third-party extensions.
	 *
	 * @return string
	 */
	function hkm_view_string(string $view, array $data = [], array $options = []): string
	{
		/**
		 * @var Hkm_code\View\View $renderer
		 */
		$renderer = ServicesSystem::RENDERER();

		$saveData = hkm_config(View::class)::$saveData;

		if (array_key_exists('saveData', $options)) {
			$saveData = (bool) $options['saveData'];
			unset($options['saveData']);
		}

		return $renderer::SET_DATA($data, 'raw')
			::RENDER_STRING($view, $options, $saveData);
	}
}

if (! function_exists('hkm_stringify_attributes'))
{
	/**
	 * Stringify attributes for use in HTML tags.
	 *
	 * Helper function used to convert a string, array, or object
	 * of attributes to a string.
	 *
	 * @param mixed   $attributes string, array, object
	 * @param boolean $js
	 *
	 * @return string
	 */
	function hkm_stringify_attributes($attributes, bool $js = false): string
	{
		$atts = '';

		if (empty($attributes))
		{
			return $atts;
		}

		if (is_string($attributes))
		{
			return ' ' . $attributes;
		}

		$attributes = (array) $attributes;

		foreach ($attributes as $key => $val)
		{
			$atts .= ($js) ? $key . '=' . hkm_esc($val, 'js') . ',' : ' ' . $key . '="' . hkm_esc($val) . '"';
		}

		return rtrim($atts, ',');
	}
}

if (!function_exists('hkm_route_to')) {
	/**
	 * Given a controller/method string and any params,
	 * will attempt to build the relative URL to the
	 * matching route.
	 *
	 * NOTE: This requires the controller/method to
	 * have a route defined in the routes Config file.
	 *
	 * @param string $method
	 * @param mixed  ...$params
	 *
	 * @return false|string
	 */
	function hkm_route_to(string $method, ...$params)
	{
		return ServicesSystem::ROUTES()::REVERSE_ROUTE($method, ...$params);
	}
}

if (!function_exists('hkm_log_message')) {
	/**
	 * A convenience/compatibility method for logging events through
	 * the Log system.
	 *
	 * Allowed log levels are:
	 *  - emergency
	 *  - alert
	 *  - critical
	 *  - error
	 *  - warning
	 *  - notice
	 *  - info
	 *  - debug
	 *
	 * @param string $level
	 * @param string $message
	 * @param array  $context
	 *
	 * @return mixed
	 */
	function hkm_log_message(string $level, string $message, array $context = [])
	{
		// When running tests, we want to always ensure that the
		// TestLogger is running, which provides utilities for
		// for asserting that logs were called in the test code.
		if (ENVIRONMENT === 'testing') {
			// $logger = new TestLogger(new Logger());

			// return $logger->log($level, $message, $context);
		}

		// @codeCoverageIgnoreStart
		return ServicesSystem::LOGGER(true)->log($level, $message, $context);
		// @codeCoverageIgnoreEnd
	}
}



function hkm_add_config($fun){
	global $config_functions;
	if(function_exists($fun)){
		if(!in_array($fun,$config_functions))$config_functions[]=$fun;
	}
}



function hkm_text_format($string,$leng = null)
{
	if (!empty($string)) {
		$stringOb=array();
		$len = strlen($string);
		if ($len >$leng) {
			$b=$leng-3;
		for ($i=0; $i < $b; $i++) {
			$stringOb[]=$string[$i];
		}
		$stringOb[]="...";
		}else {
		 $stringOb[]=$string;
		}
		return join($stringOb);
	}

	return $string;
}


function hkm_add_routes(callable $callback)
{
	global $Hkm_routesAddOn;

	$routes = [];

	$Hkm_routesAddOn = $callback('on_extend_roots_system',$Hkm_routesAddOn);

	
	if (count($Hkm_routesAddOn)) {
		foreach ($Hkm_routesAddOn as $route) {
			$options = [];
			if(isset($route['options'])){
				if (is_array($route['options'])) {
					array_walk(
						$route['options'], 
						function ($item, $key) use (&$options) {
							$options[$key]=$item;  
						} 
					);
				}
			}
			
		    $options = empty($options)?null:$options;
			$route['type'] = explode(',',$route['type']);
			$route['options'] = $options;

			$route['url'] = $route['from'];
			unset($route['from']);
			$route['pobohet'] = $route['type'];
			unset($route['type']);
			$route['controller'] = $route['to'];
			unset($route['to']);

		  if(!isset($routes[$route['url']])) $routes[$route['url']]=$route;

		}
	}

	return $routes;
}





function hkm_is_class_valid($value)
{
	$files = FileLocator::SEARCH($value);
	if (count($files)>0) {
		return true;
	}else{
		return false;
	}
}



function hkm_run_config(){
	global $config_functions;
	foreach($config_functions as $fun){
		@call_user_func($fun);
	}
}


if (! function_exists('hkm_slash_item'))
{
	//Unlike CI3, this function is placed here because
	//it's not a config, or part of a config.
	/**
	 * Fetch a config file item with slash appended (if not empty)
	 *
	 * @param string $item Config item name
	 *
	 * @return string|null The configuration item or NULL if
	 * the item doesn't exist
	 */
	function hkm_slash_item(string $item): ?string
	{
		$config     = hkm_config(App::class);
		$configItem = $config->{$item};

		if (! isset($configItem) || empty(trim($configItem)))
		{
			return $configItem;
		}
 
		return rtrim($configItem, '/') . '/';
	}
}

if (!function_exists('hkm_db_connect')) {
	/**
	 * Grabs a database connection and returns it to the user.
	 *
	 * This is a convenience wrapper for \Database::connect()
	 * and supports the same parameters. Namely:
	 *
	 * When passing in $db, you may pass any of the following to connect:
	 * - group name
	 * - existing connection instance
	 * - array of database configuration values
	 *
	 * If $getShared === false then a new connection instance will be provided,
	 * otherwise it will all calls will return the same instance.
	 *
	 * @param ConnectionInterface|array|string|null $db
	 * @param boolean                               $getShared
	 *
	 * @return BaseConnection
	 */
	function hkm_db_connect($db = null, bool $getShared = true)
	{
		$database = hkm_config('Database');;

		return $database::CONNECT($db, $getShared);
	}
}

if (!function_exists('hkm_Runtime_regester')) {
	/**
	 * Loads a helper file into memory. Supports namespaced helpers,
	 * both in and out of the 'helpers' directory of a namespaced directory.
	 *
	 * Will load ALL helpers of the matching name, in the following order:
	 *   1. app/Helpers
	 *   2. {namespace}/Helpers
	 *   3. system/Helpers
	 *
	 * @param  string|array $filenames
	 * @throws FileNotFoundException
	 */
	function hkm_Runtime_regester($filenames = "__run_")
	{
		static $loaded = [];

		$loader = ServicesSystem::LOCATOR(true);

		if (!is_array($filenames)) {
			$filenames = [$filenames];
		}

		// Store a list of all files to include...
		$includes = [];

		foreach ($filenames as $filename) {
			// Store our system and application helper
			// versions so that we can control the load ordering.
			$systemRuntime  = null;
			$appRuntime     = null;
			$localIncludes = [];

			if (strpos($filename, '_filter') === false) {
				$filename .= '_filter';
			}

			// Check if this helper has already been loaded
			if (in_array($filename, $loaded, true)) {
				continue;
			}

			if (strpos($filename, '\\') !== false){
				$path = $loader::LOCATE_FILE($filename, 'Runtime_filter');
				if (is_array($path)) {
					foreach ($path as $p) {
						if (!is_file($p)) {
							throw FileNotFoundException::FOR_FILE_NOT_FOUND($filename);
							// print_r($filename . " not Found!");
							// exit;
						}
			
						$includes[] = $p;
						$loaded[]   = $filename;
					}
				}
				
				// $path = SYSTEMPATH . "Helpers/" . $filename . '.php';
	
				
			}else{
				$paths = $loader::SEARCH('Runtime_filter/' . $filename);
				if (! empty($paths))
				{
					foreach ($paths as $path)
					{
						if (strpos($path, APPPATH) === 0)
						{
							// @codeCoverageIgnoreStart
							$appRuntime = $path;
							// @codeCoverageIgnoreEnd
						}
						elseif (strpos($path, SYSTEMPATH) === 0)
						{
							$systemRuntime = $path;
						}
						else
						{
							$localIncludes[] = $path;
							$loaded[]        = $filename;
						}
					}
				}

				// App-level helpers should override all others
				if (! empty($appRuntime))
				{
					// @codeCoverageIgnoreStart
					$includes[] = $appRuntime;
					$loaded[]   = $filename;
					// @codeCoverageIgnoreEnd
				}

				// All namespaced files get added in next
				$includes = array_merge($includes, $localIncludes);

				// And the system default one should be added in last.
				if (! empty($systemRuntime))
				{
					$includes[] = $systemRuntime;
					$loaded[]   = $filename;
				}
			}

			
		}

		// Now actually include all of the files
		if (!empty($includes)) {
			foreach ($includes as $path) {
				include_once($path);
			}
		}
	}
}


if (!function_exists('hkm_remove_invisible_characters')) {
	/**
	 * Remove Invisible Characters
	 *
	 * This prevents sandwiching null characters
	 * between ascii characters, like Java\0script.
	 *
	 * @param string  $str
	 * @param boolean $urlEncoded
	 *
	 * @return string
	 */
	function hkm_remove_invisible_characters(string $str, bool $urlEncoded = true): string
	{
		$nonDisplayables = [];

		// every control character except newline (dec 10),
		// carriage return (dec 13) and horizontal tab (dec 09)
		if ($urlEncoded) {
			$nonDisplayables[] = '/%0[0-8bcef]/';  // url encoded 00-08, 11, 12, 14, 15
			$nonDisplayables[] = '/%1[0-9a-f]/';   // url encoded 16-31
		}

		$nonDisplayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';   // 00-08, 11, 12, 14-31, 127

		do {
			$str = preg_replace($nonDisplayables, '', $str, -1, $count);
		} while ($count);

		return $str;
	}
}



if (!function_exists('hkm_config')) {
	/**
	 * More simple way of getting config instances from Factories
	 *
	 * @param string  $name
	 * @param boolean $getShared
	 *
	 * @return mixed
	 */
	function hkm_config(string $name, bool $getShared = true)
	{
		return Factories::config($name, ['getShared' => $getShared]);
	}
}



function hkm_sanitize_meta( $meta_key, $content, $object_type, $object_subtype = '' ) {
	if ( ! empty( $object_subtype ) && hkm_has_filter( "sanitize_{$object_type}_meta_{$meta_key}_for_{$object_subtype}" ) ) {

		return  hkm_apply_filters( "sanitize_{$object_type}_meta_{$meta_key}_for_{$object_subtype}", $content, $meta_key, $object_type, $object_subtype );
	}

	
	return  hkm_apply_filters( "sanitize_{$object_type}_meta_{$meta_key}", $content, $meta_key, $object_type );
}
function hkm_sanitize_key( $key ) {
	$key     = strtolower( $key );
	$key     = preg_replace( '/[^a-z0-9_\-]/', '', $key );
	return $key;
}
function hkm_is_serialized( $data, $strict = true ) {
	// If it isn't a string, it isn't serialized.
	if ( ! is_string( $data ) ) {
		return false;
	}
	$data = trim( $data );
	if ( 'N;' === $data ) {
		return true;
	}
	if ( strlen( $data ) < 4 ) {
		return false;
	}
	if ( ':' !== $data[1] ) {
		return false;
	}
	if ( $strict ) {
		$lastc = substr( $data, -1 );
		if ( ';' !== $lastc && '}' !== $lastc ) {
			return false;
		}
	} else {
		$semicolon = strpos( $data, ';' );
		$brace     = strpos( $data, '}' );
		// Either ; or } must exist.
		if ( false === $semicolon && false === $brace ) {
			return false;
		}
		// But neither must be in the first X characters.
		if ( false !== $semicolon && $semicolon < 3 ) {
			return false;
		}
		if ( false !== $brace && $brace < 4 ) {
			return false;
		}
	}
	$token = $data[0];
	switch ( $token ) {
		case 's':
			if ( $strict ) {
				if ( '"' !== substr( $data, -2, 1 ) ) {
					return false;
				}
			} elseif ( false === strpos( $data, '"' ) ) {
				return false;
			}
			// Or else fall through.
		case 'a':
		case 'O':
			return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
		case 'b':
		case 'i':
		case 'd':
			$end = $strict ? '$' : '';
			return (bool) preg_match( "/^{$token}:[0-9.E+-]+;$end/", $data );
	}
	return false;
}


function hkm_maybe_serialize( $data ) {
	if ( is_array( $data ) || is_object( $data ) ) {
		return serialize( $data );
	}

	if ( hkm_is_serialized( $data, false ) ) {
		return serialize( $data );
	}

	return $data;
}


function hkm_maybe_unserialize( $data ) {
	if ( hkm_is_serialized( $data ) ) { // Don't attempt to unserialize data that wasn't serialized going in.
		return @unserialize( trim( $data ) );
	}

	return $data;
}


function hkm_is_numeric_array( $data ) {
	if ( ! is_array( $data ) ) {
		return false;
	}

	$keys        = array_keys( $data );
	$string_keys = array_filter( $keys, 'is_string' );

	return count( $string_keys ) === 0;
}


if (!function_exists('hkm_app_timezone')) {
	/**
	 * Returns the timezone the application has been set to display
	 * dates in. This might be different than the timezone set
	 * at the server level, as you often want to stores dates in UTC
	 * and convert them on the fly for the user.
	 *
	 * @return string
	 */
	function hkm_app_timezone(): string
	{
		$config = hkm_config("App");

		return $config::$appTimezone;
	}
}



if (!function_exists('hkm_is_valid_host')) {
	/**
	 * Validate whether a string contains a valid value to use as a hostname or IP address.
	 * IPv6 addresses must include [], e.g. `[::1]`, not just `::1`.
	 *
	 * @param string $host The host name or IP address to check
	 *
	 * @return bool
	 */
	function hkm_is_valid_host($host)
	{
		//Simple syntax limits
		if (empty($host) || !is_string($host) || strlen($host) > 256 || !preg_match('/^([a-zA-Z\d.-]*|\[[a-fA-F\d:]+\])$/', $host)) {
			return false;
		}
		//Looks like a bracketed IPv6 address
		if (strlen($host) > 2 && substr($host, 0, 1) === '[' && substr($host, -1, 1) === ']') {
			return filter_var(substr($host, 1, -1), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
		}
		//If removing all the dots results in a numeric string, it must be an IPv4 address.
		//Need to check this first because otherwise things like `999.0.0.0` are considered valid host names
		if (is_numeric(str_replace('.', '', $host))) {
			//Is it a valid IPv4 address?
			return filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
		}
		if (filter_var('http://' . $host, FILTER_VALIDATE_URL) !== false) {
			//Is it a syntactically valid hostname?
			return true;
		}

		return false;
	}
}
if (!function_exists('hkm_mb_pathinfo')) {
	/**
	 * Multi-byte-safe pathinfo replacement.
	 * Drop-in replacement for pathinfo(), but multibyte- and cross-platform-safe.
	 *
	 * @see http://www.php.net/manual/en/function.pathinfo.php#107461
	 *
	 * @param string     $path    A filename or path, does not need to exist as a file
	 * @param int|string $options Either a PATHINFO_* constant,
	 *                            or a string name to return only the specified piece
	 *
	 * @return string|array
	 */
	function hkm_mb_pathinfo($path, $options = null)
	{
		$ret = ['dirname' => '', 'basename' => '', 'extension' => '', 'filename' => ''];
		$pathinfo = [];
		if (preg_match('#^(.*?)[\\\\/]*(([^/\\\\]*?)(\.([^.\\\\/]+?)|))[\\\\/.]*$#m', $path, $pathinfo)) {
			if (array_key_exists(1, $pathinfo)) {
				$ret['dirname'] = $pathinfo[1];
			}
			if (array_key_exists(2, $pathinfo)) {
				$ret['basename'] = $pathinfo[2];
			}
			if (array_key_exists(5, $pathinfo)) {
				$ret['extension'] = $pathinfo[5];
			}
			if (array_key_exists(3, $pathinfo)) {
				$ret['filename'] = $pathinfo[3];
			}
		}
		switch ($options) {
			case PATHINFO_DIRNAME:
			case 'dirname':
				return $ret['dirname'];
			case PATHINFO_BASENAME:
			case 'basename':
				return $ret['basename'];
			case PATHINFO_EXTENSION:
			case 'extension':
				return $ret['extension'];
			case PATHINFO_FILENAME:
			case 'filename':
				return $ret['filename'];
			default:
				return $ret;
		}
	}
}
if (!function_exists('hkm_mime_type')) {
	/**
	 * Get the MIME type for a file extension.
	 *
	 * @param string $ext File extension
	 *
	 * @return string MIME type of file
	 */
	function hkm_mime_types($ext = '')
	{
		$mimes = [
			'xl' => 'application/excel',
			'js' => 'application/javascript',
			'hqx' => 'application/mac-binhex40',
			'cpt' => 'application/mac-compactpro',
			'bin' => 'application/macbinary',
			'doc' => 'application/msword',
			'word' => 'application/msword',
			'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
			'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
			'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
			'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
			'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
			'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
			'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
			'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
			'class' => 'application/octet-stream',
			'dll' => 'application/octet-stream',
			'dms' => 'application/octet-stream',
			'exe' => 'application/octet-stream',
			'lha' => 'application/octet-stream',
			'lzh' => 'application/octet-stream',
			'psd' => 'application/octet-stream',
			'sea' => 'application/octet-stream',
			'so' => 'application/octet-stream',
			'oda' => 'application/oda',
			'pdf' => 'application/pdf',
			'ai' => 'application/postscript',
			'eps' => 'application/postscript',
			'ps' => 'application/postscript',
			'smi' => 'application/smil',
			'smil' => 'application/smil',
			'mif' => 'application/vnd.mif',
			'xls' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',
			'wbxml' => 'application/vnd.wap.wbxml',
			'wmlc' => 'application/vnd.wap.wmlc',
			'dcr' => 'application/x-director',
			'dir' => 'application/x-director',
			'dxr' => 'application/x-director',
			'dvi' => 'application/x-dvi',
			'gtar' => 'application/x-gtar',
			'php3' => 'application/x-httpd-php',
			'php4' => 'application/x-httpd-php',
			'php' => 'application/x-httpd-php',
			'phtml' => 'application/x-httpd-php',
			'phps' => 'application/x-httpd-php-source',
			'swf' => 'application/x-shockwave-flash',
			'sit' => 'application/x-stuffit',
			'tar' => 'application/x-tar',
			'tgz' => 'application/x-tar',
			'xht' => 'application/xhtml+xml',
			'xhtml' => 'application/xhtml+xml',
			'zip' => 'application/zip',
			'mid' => 'audio/midi',
			'midi' => 'audio/midi',
			'mp2' => 'audio/mpeg',
			'mp3' => 'audio/mpeg',
			'm4a' => 'audio/mp4',
			'mpga' => 'audio/mpeg',
			'aif' => 'audio/x-aiff',
			'aifc' => 'audio/x-aiff',
			'aiff' => 'audio/x-aiff',
			'ram' => 'audio/x-pn-realaudio',
			'rm' => 'audio/x-pn-realaudio',
			'rpm' => 'audio/x-pn-realaudio-plugin',
			'ra' => 'audio/x-realaudio',
			'wav' => 'audio/x-wav',
			'mka' => 'audio/x-matroska',
			'bmp' => 'image/bmp',
			'gif' => 'image/gif',
			'jpeg' => 'image/jpeg',
			'jpe' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'png' => 'image/png',
			'tiff' => 'image/tiff',
			'tif' => 'image/tiff',
			'webp' => 'image/webp',
			'avif' => 'image/avif',
			'heif' => 'image/heif',
			'heifs' => 'image/heif-sequence',
			'heic' => 'image/heic',
			'heics' => 'image/heic-sequence',
			'eml' => 'message/rfc822',
			'css' => 'text/css',
			'html' => 'text/html',
			'htm' => 'text/html',
			'shtml' => 'text/html',
			'log' => 'text/plain',
			'text' => 'text/plain',
			'txt' => 'text/plain',
			'rtx' => 'text/richtext',
			'rtf' => 'text/rtf',
			'vcf' => 'text/vcard',
			'vcard' => 'text/vcard',
			'ics' => 'text/calendar',
			'xml' => 'text/xml',
			'xsl' => 'text/xml',
			'wmv' => 'video/x-ms-wmv',
			'mpeg' => 'video/mpeg',
			'mpe' => 'video/mpeg',
			'mpg' => 'video/mpeg',
			'mp4' => 'video/mp4',
			'm4v' => 'video/mp4',
			'mov' => 'video/quicktime',
			'qt' => 'video/quicktime',
			'rv' => 'video/vnd.rn-realvideo',
			'avi' => 'video/x-msvideo',
			'movie' => 'video/x-sgi-movie',
			'webm' => 'video/webm',
			'mkv' => 'video/x-matroska',
		];
		$ext = strtolower($ext);
		if (array_key_exists($ext, $mimes)) {
			return $mimes[$ext];
		}

		return 'application/octet-stream';
	}
}
if (!function_exists('hkm_esc')) {
	/**
	 * Performs simple auto-escaping of data for security reasons.
	 * Might consider making this more complex at a later date.
	 *
	 * If $data is a string, then it simply escapes and returns it.
	 * If $data is an array, then it loops over it, escaping each
	 * 'value' of the key/value pairs.
	 *
	 * Valid context values: html, js, css, url, attr, raw, null
	 *
	 * @param string|array $data
	 * @param string       $context
	 * @param string       $encoding
	 *
	 * @return string|array
	 * @throws InvalidArgumentException
	 */
	function hkm_esc($data, string $context = 'html', string $encoding = null)
	{
		if (is_array($data)) {
			foreach ($data as &$value) {

				$value = hkm_esc($value, $context);
			}
		}

		if (is_string($data)) {

			$context = strtolower($context);

			// Provide a way to NOT escape data since
			// this could be called automatically by
			// the View library.
			if (empty($context) || $context === 'raw') {

				return $data;
			}

			if (!in_array($context, ['html', 'js', 'css', 'url', 'attr'], true)) {
				throw new InvalidArgumentException('Invalid escape context provided.');
			}

			$method = $context === 'attr' ? 'escapeHtmlAttr' : 'escape' . ucfirst($context);

			static $escaper;
			if (!$escaper) {
				$escaper = new Escaper($encoding);
			}

			if ($encoding && $escaper->getEncoding() !== $encoding) {
				$escaper = new Escaper($encoding);
			}

			$data = $escaper->$method($data);
		}

		return $data;
	}
}
if (!function_exists('hkm_html2text')) {

	/**
	 * Convert an HTML string into plain text.
	 * This is used by msgHTML().
	 * Note - hkm_older versions of this function used a bundled advanced converter
	 * which was removed for license reasons in #232.
	 * Example usage:
	 *
	 * ```php
	 * //Use default conversion
	 * $plain = $mail->html2text($html);
	 * //Use your own custom converter
	 * $plain = $mail->html2text($html, function($html) {
	 *     $converter = new MyHtml2text($html);
	 *     return $converter->get_text();
	 * });
	 * ```
	 *
	 * @param string        $html     The HTML text to convert
	 * @param string        $CharSet     The Character set of the text
	 * @param bool|callable $advanced Any boolean value to use the internal converter,
	 *                                or provide your own callable for custom conversion.
	 *                                *Never* pass user-supplied data into this parameter
	 *
	 * @return string
	 */
	function hkm_html2text($html, $CharSet, $advanced = false)
	{
		if (is_callable($advanced)) {
			return call_user_func($advanced, $html);
		}

		return html_entity_decode(
			trim(strip_tags(preg_replace('/<(head|title|style|script)[^>]*>.*?<\/\\1>/si', '', $html))),
			ENT_QUOTES,
			$CharSet
		);
	}
}


if (!function_exists('hkm_is_cli')) {

	function hkm_is_cli(): bool
	{
		if (PHP_SAPI === 'cli') {
			return true;
		}

		if (defined('STDIN')) {
			return true;
		}

		if (stristr(PHP_SAPI, 'cgi') && getenv('TERM')) {
			return true;
		}

		if (!isset($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']) && isset($_SERVER['argv']) && count($_SERVER['argv']) > 0) {
			return true;
		}

		// if source of request is from CLI, the `$_SERVER` array will not populate this key
		return !isset($_SERVER['REQUEST_METHOD']);
	}
}



if (!function_exists('hkm_array_string')) {
	function hkm_array_string($value)
	{
		if (is_array($value)) {
			$cv = "";
			foreach ($value as $key => $valu) $cv .= $valu . ",";
			return $cv;
		} else return $value;
	}
}
if (!function_exists('hkm_count_values')) {
	function hkm_count_values($value)
	{
		if (is_array($value)) {
			return strlen(join(",", array_values($value)) . join(",", array_keys($value)));
		} else return strlen($value);
	}
}
if (!function_exists('hkm_setPad')) {
	/**
	 * Pads our string out so that all titles are the same length to nicely line up descriptions.
	 *
	 * @param string  $item
	 * @param integer $max
	 * @param integer $extra  How many extra spaces to add at the end
	 * @param integer $indent
	 *
	 * @return string
	 */
	function hkm_setPad(string $item, int $max, int $extra = 2, int $indent = 0): string
	{
		$max += $extra + $indent;

		return str_pad(str_repeat(' ', $indent) . $item, $max);
	}
}
function hkm_isAssoc(array $arr)
{
	if (array() === $arr) return false;
	return array_keys($arr) !== range(0, count($arr) - 1);
}
if (!function_exists('hkm_XMLSanitizeValue')) {
	function hkm_XMLSanitizeValue($value)
	{
		if (is_array($value)) {
			if (hkm_isAssoc($value)) {
				$v = '';
				foreach ($value as $key => $va) {
					$v .= $key . "=" . $va . "'-,-'";
				}
				return rtrim($v, "'-,-'");
			} else return join("'-,-'", $value);
		} else return $value;
	}
}
if (!function_exists('hkm_XMLSanitizeArray')) {
	function hkm_XMLSanitizeArray($value)
	{
		if (!is_array($value)) {
			if (stripos($value, "'-,-'") !== false && stripos($value, "'-,-'") > 0) {
				$v = explode("'-,-'", $value);
				$ar = [];
				foreach ($v as $key) {
					if (stripos($key, "=") !== false && stripos($key, "=") > 0) {
						$d = explode("=", $key);
						$ar[$d[0]] = $d[1];
					}
				}
				if (count($ar) > 0) return $ar;
				else return $v;
			} else return $value;
		} else return array_map("hkm_XMLSanitizeArray", $value);
	}
}
/**
 * Find the last character boundary prior to $maxLength in a utf-8
 * quoted-printable encoded string.
 * Original written by Colin Brown.
 *
 * @param string $encodedText utf-8 QP text
 * @param int    $maxLength   Find the last character boundary prior to this length
 *
 * @return int
 */
function hkm_utf8CharBoundary($encodedText, $maxLength)
{
	$foundSplitPos = false;
	$lookBack = 3;
	while (!$foundSplitPos) {
		$lastChunk = substr($encodedText, $maxLength - $lookBack, $lookBack);
		$encodedCharPos = strpos($lastChunk, '=');
		if (false !== $encodedCharPos) {
			//Found start of encoded character byte within $lookBack block.
			//Check the encoded byte value (the 2 chars after the '=')
			$hex = substr($encodedText, $maxLength - $lookBack + $encodedCharPos + 1, 2);
			$dec = hexdec($hex);
			if ($dec < 128) {
				//Single byte character.
				//If the encoded char was found at pos 0, it will fit
				//otherwise reduce maxLength to start of the encoded char
				if ($encodedCharPos > 0) {
					$maxLength -= $lookBack - $encodedCharPos;
				}
				$foundSplitPos = true;
			} elseif ($dec >= 192) {
				//First byte of a multi byte character
				//Reduce maxLength to split at start of character
				$maxLength -= $lookBack - $encodedCharPos;
				$foundSplitPos = true;
			} elseif ($dec < 192) {
				//Middle byte of a multi byte character, look further back
				$lookBack += 3;
			}
		} else {
			//No encoded character found
			$foundSplitPos = true;
		}
	}

	return $maxLength;
}
/**
 * Format a header line.
 *
 * @param string     $name
 * @param string|int $value
 *
 * @return string
 */
function hkm_headerLine($name, $value, $LE)
{
	return $name . ': ' . $value . $LE;
}
/**
 * Return an RFC 822 formatted date.
 *
 * @return string
 */
function hkm_rfcDate()
{
	//Set the time zone to whatever the default is to avoid 500 errors
	//Will default to UTC if it's not set properly in php.ini
	date_default_timezone_set(@date_default_timezone_get());

	return date('D, j M Y H:i:s O');
}
/**
 * Strip newlines to prevent header injection.
 *
 * @param string $str
 *
 * @return string
 */
function hkm_secureHeader($str)
{
	return trim(str_replace(["\r", "\n"], '', $str));
}
/**
 * Remove trailing breaks from a string.
 *
 * @param string $text
 *
 * @return string The text to remove breaks from
 */
function hkm_stripTrailingWSP($text)
{
	return rtrim($text, " \r\n\t");
}
/**
 * If a string contains any "special" characters, double-quote the name,
 * and escape any double quotes with a backslash.
 *
 * @param string $str
 *
 * @return string
 *
 * @see RFC822 3.4.1
 */
function hkm_quotedString($str)
{
	if (preg_match('/[ ()<>@,;:"\/\[\]?=]/', $str)) {
		//If the string contains any of these chars, it must be double-quoted
		//and any double quotes must be escaped with a backslash
		return '"' . str_replace('"', '\\"', $str) . '"';
	}

	//Return the string untouched, it doesn't need quoting
	return $str;
}
/**
 * Tells whether IDNs (Internationalized Domain Names) are supported or not. This requires the
 * `intl` and `mbstring` PHP extensions.
 *
 * @return bool `true` if required functions for IDN support are present
 */
function hkm_idnSupported()
{
	return function_exists('idn_to_ascii') && function_exists('mb_convert_encoding');
}
/**
 * Create a unique ID to use for boundaries.
 *
 * @return string
 */
function HKM_GENERATE_ID()
{
	$len = 32; //32 bytes = 256 bits
	$bytes = '';
	if (function_exists('random_bytes')) {
		try {
			$bytes = random_bytes($len);
		} catch (\Exception $e) {
			//Do nothing
		}
	} elseif (function_exists('openssl_random_pseudo_bytes')) {
		/** @noinspection CryptographicallySecureRandomnessInspection */
		$bytes = openssl_random_pseudo_bytes($len);
	}
	if ($bytes === '') {
		//We failed to produce a proper random string, so make do.
		//Use a hash to force the length to the same as the other methods
		$bytes = hash('sha256', uniqid((string) mt_rand(), true), true);
	}

	//We don't care about messing up base64 format here, just want a random string
	return str_replace(['=', '+', '/','-'], '', base64_encode(hash('sha256', $bytes, true)));
}
/**
 * Fix CVE-2016-10033 and CVE-2016-10045 by disallowing potentially unsafe shell characters.
 * Note that escapeshellarg and escapeshellcmd are inadequate for our purposes, especially on Windows.
 *
 * @see https://github.com/PHPMailer/PHPMailer/issues/924 CVE-2016-10045 bug report
 *
 * @param string $string The string to be validated
 *
 * @return bool
 */
function hkm_isShellSafe($string)
{
	//Future-proof
	if (
		escapeshellcmd($string) !== $string
		|| !in_array(escapeshellarg($string), ["'$string'", "\"$string\""])
	) {
		return false;
	}

	$length = strlen($string);

	for ($i = 0; $i < $length; ++$i) {
		$c = $string[$i];

		//All other characters have a special meaning in at least one common shell, including = and +.
		//Full stop (.) has a special meaning in cmd.exe, but its impact should be negligible here.
		//Note that this does permit non-Latin alphanumeric characters based on the current locale.
		if (!ctype_alnum($c) && strpos('@_-.', $c) === false) {
			return false;
		}
	}

	return true;
}
/**
 * Calculate an MD5 HMAC hash.
 * Works like hash_HMAC('md5', $data, $key)
 * in case that function is not available.
 *
 * @param string $data The data to hash
 * @param string $key  The key to hash with
 *
 * @return string
 */
function hkm_hmac($data, $key)
{
	if (function_exists('hash_hmac')) {
		return hash_hmac('md5', $data, $key);
	}

	//The following borrowed from
	//http://php.net/manual/en/function.mhash.php#27225

	//RFC 2104 HMAC implementation for php.
	//Creates an md5 HMAC.
	//Eliminates the need to install mhash to compute a HMAC
	//by Lance Rushing

	$bytelen = 64; //byte length for md5
	if (strlen($key) > $bytelen) {
		$key = pack('H*', md5($key));
	}
	$key = str_pad($key, $bytelen, chr(0x00));
	$ipad = str_pad('', $bytelen, chr(0x36));
	$opad = str_pad('', $bytelen, chr(0x5c));
	$k_ipad = $key ^ $ipad;
	$k_opad = $key ^ $opad;

	return md5($k_opad . pack('H*', md5($k_ipad . $data)));
}
if (!function_exists('hkm_fetch_Dir')) {
	function hkm_fetch_Dir($x, $WithfFiles = false, $third_party = false)
	{
		$array = [];
		if (is_dir($x)) {
			$files = scandir($x);
			foreach ($files as $file) {
				if ($file != '.' && $file != "..") {
					if ($third_party) {
						if (is_dir($x . "/" . $file)) {
							$array[$file . '\\'] = $x . "/" . $file;
						}
					} else {
						if ($WithfFiles) {
							if (is_dir($x . "/" . $file)) {
								$array = array_merge(hkm_fetch_Dir($x . "/" . $file, $WithfFiles), $array);
							} else {
								$array[] = $x . "/" . $file;
							}
						} else {
							if (is_dir($x . "/" . $file)) {
								$array[] = $x . "/" . $file;
								$array = array_merge(hkm_fetch_Dir($x . "/" . $file), $array);
							}
						}
					}
				}
			}
		}
		return $array;
	}
}






if (!function_exists('hkm_HttpPatchData')) {
    function hkm_HttpPatchData()
    {
        parse_str(file_get_contents('php://input'), $_PATCH);
        $body=[];
                if (is_array($_PATCH)) {
                   foreach ($_PATCH as $key => $value) {
                    $body[$key] = $value;
                }  
                   
                }
            return $body; 
    }
}


if (!function_exists('hkm_HttpPostData')) {
    function hkm_HttpPostData()
    {
        
        $body=[];
                if (is_array($_POST)) {
                   foreach ($_POST as $key => $value) {
                    $body[$key] = $value;
                }  
                   
                }
            return $body; 
    }
}



function hkm_urlSafeEncode($m) {
    return rtrim(strtr(base64_encode($m), '+/', '-_'), '=');
}
function hkm_urlSafeDecode($m) {
    return base64_decode(strtr($m, '-_', '+/'));
}

if (!function_exists("hkm_random_username")) {
    function hkm_random_username($string) {
        $pattern = " ";
        $firstPart = strstr(strtolower($string), $pattern, true);
        $secondPart = substr(strstr(strtolower($string), $pattern, false), 0,3);
    
        $username = trim($firstPart).ucfirst(trim($secondPart));
        return $username;
    }
}



if (!function_exists("hkm_generateNumericOTP")) {

	function hkm_generateNumericOTP($n) {
       
		$generator = "1357902468";
		$result = "";

		for ($i = 1; $i <= $n; $i++) {
			$result .= substr($generator, (rand()%(strlen($generator))), 1);
		}
		return $result;
	}

}

if (! function_exists('hkm_get_filenames'))
{
	/**
	 * Get Filenames
	 *
	 * Reads the specified directory and builds an array containing the filenames.
	 * Any sub-folders contained within the specified path are read as well.
	 *
	 * @param string       $sourceDir   Path to source
	 * @param boolean|null $includePath Whether to include the path as part of the filename; false for no path, null for a relative path, true for full path
	 * @param boolean      $hidden      Whether to include hidden files (files beginning with a period)
	 *
	 * @return array
	 */
	function hkm_get_filenames(string $sourceDir, ?bool $includePath = false, bool $hidden = false): array
	{
		$files = [];

		$sourceDir = realpath($sourceDir) ?: $sourceDir;
		$sourceDir = rtrim($sourceDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

		try
		{
			foreach (new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
					RecursiveIteratorIterator::SELF_FIRST
				) as $name => $object)
			{
				$basename = pathinfo($name, PATHINFO_BASENAME);
				if (! $hidden && $basename[0] === '.')
				{
					continue;
				}

				if ($includePath === false)
				{
					$files[] = $basename;
				}
				elseif (is_null($includePath))
				{
					$files[] = str_replace($sourceDir, '', $name);
				}
				else
				{
					$files[] = $name;
				}
			}
		}
		catch (Throwable $e)
		{
			return [];
		}

		sort($files);

		return $files;
	}
}



function hkm_parse_str( $string, &$array ) {
	parse_str( $string, $array );
}

function hkm_parse_args( $args, $defaults = array() ) {
	if ( is_object( $args ) ) {
		$parsed_args = get_object_vars( $args );
	} elseif ( is_array( $args ) ) {
		$parsed_args =& $args;
	} else {
		hkm_parse_str( $args, $parsed_args );
	}

	if ( is_array( $defaults ) && $defaults ) {
		return array_merge( $defaults, $parsed_args );
	}
	return $parsed_args;
}

function hkm_fill_data( array &$default, array &$data, $excepts = array() ) {
	
	foreach ($default as $key => $value) {
		 $keys = explode('_',$key);
		 $keyUp = $keys[0];
		 if(count($keys)>1){
			unset($keys[0]);
			foreach ($keys as $key_) $keyUp.=ucfirst($key_);
		 }

		 $default[$key] = isset($data[$key])? $data[$key] : $data[$keyUp]??$value;
		 if(!in_array($key,$excepts)){
			unset($data[$key]);
		 }
		 if(!in_array($keyUp,$excepts)){
			unset($data[$keyUp]);
		 }
	}
}



function hkm_strip_all_tags( $string, $remove_breaks = false ) {
	$string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $string );
	$string = strip_tags( $string );

	if ( $remove_breaks ) {
		$string = preg_replace( '/[\r\n\t ]+/', ' ', $string );
	}

	return trim( $string );
}

function hkm_remove_accents( $string ) {
	if ( ! preg_match( '/[\x80-\xff]/', $string ) ) {
		return $string;
	}

	if ( hkm_seems_utf8( $string ) ) {
		$chars = array(
			// Decompositions for Latin-1 Supplement.
			'ª' => 'a',
			'º' => 'o',
			'À' => 'A',
			'Á' => 'A',
			'Â' => 'A',
			'Ã' => 'A',
			'Ä' => 'A',
			'Å' => 'A',
			'Æ' => 'AE',
			'Ç' => 'C',
			'È' => 'E',
			'É' => 'E',
			'Ê' => 'E',
			'Ë' => 'E',
			'Ì' => 'I',
			'Í' => 'I',
			'Î' => 'I',
			'Ï' => 'I',
			'Ð' => 'D',
			'Ñ' => 'N',
			'Ò' => 'O',
			'Ó' => 'O',
			'Ô' => 'O',
			'Õ' => 'O',
			'Ö' => 'O',
			'Ù' => 'U',
			'Ú' => 'U',
			'Û' => 'U',
			'Ü' => 'U',
			'Ý' => 'Y',
			'Þ' => 'TH',
			'ß' => 's',
			'à' => 'a',
			'á' => 'a',
			'â' => 'a',
			'ã' => 'a',
			'ä' => 'a',
			'å' => 'a',
			'æ' => 'ae',
			'ç' => 'c',
			'è' => 'e',
			'é' => 'e',
			'ê' => 'e',
			'ë' => 'e',
			'ì' => 'i',
			'í' => 'i',
			'î' => 'i',
			'ï' => 'i',
			'ð' => 'd',
			'ñ' => 'n',
			'ò' => 'o',
			'ó' => 'o',
			'ô' => 'o',
			'õ' => 'o',
			'ö' => 'o',
			'ø' => 'o',
			'ù' => 'u',
			'ú' => 'u',
			'û' => 'u',
			'ü' => 'u',
			'ý' => 'y',
			'þ' => 'th',
			'ÿ' => 'y',
			'Ø' => 'O',
			// Decompositions for Latin Extended-A.
			'Ā' => 'A',
			'ā' => 'a',
			'Ă' => 'A',
			'ă' => 'a',
			'Ą' => 'A',
			'ą' => 'a',
			'Ć' => 'C',
			'ć' => 'c',
			'Ĉ' => 'C',
			'ĉ' => 'c',
			'Ċ' => 'C',
			'ċ' => 'c',
			'Č' => 'C',
			'č' => 'c',
			'Ď' => 'D',
			'ď' => 'd',
			'Đ' => 'D',
			'đ' => 'd',
			'Ē' => 'E',
			'ē' => 'e',
			'Ĕ' => 'E',
			'ĕ' => 'e',
			'Ė' => 'E',
			'ė' => 'e',
			'Ę' => 'E',
			'ę' => 'e',
			'Ě' => 'E',
			'ě' => 'e',
			'Ĝ' => 'G',
			'ĝ' => 'g',
			'Ğ' => 'G',
			'ğ' => 'g',
			'Ġ' => 'G',
			'ġ' => 'g',
			'Ģ' => 'G',
			'ģ' => 'g',
			'Ĥ' => 'H',
			'ĥ' => 'h',
			'Ħ' => 'H',
			'ħ' => 'h',
			'Ĩ' => 'I',
			'ĩ' => 'i',
			'Ī' => 'I',
			'ī' => 'i',
			'Ĭ' => 'I',
			'ĭ' => 'i',
			'Į' => 'I',
			'į' => 'i',
			'İ' => 'I',
			'ı' => 'i',
			'Ĳ' => 'IJ',
			'ĳ' => 'ij',
			'Ĵ' => 'J',
			'ĵ' => 'j',
			'Ķ' => 'K',
			'ķ' => 'k',
			'ĸ' => 'k',
			'Ĺ' => 'L',
			'ĺ' => 'l',
			'Ļ' => 'L',
			'ļ' => 'l',
			'Ľ' => 'L',
			'ľ' => 'l',
			'Ŀ' => 'L',
			'ŀ' => 'l',
			'Ł' => 'L',
			'ł' => 'l',
			'Ń' => 'N',
			'ń' => 'n',
			'Ņ' => 'N',
			'ņ' => 'n',
			'Ň' => 'N',
			'ň' => 'n',
			'ŉ' => 'n',
			'Ŋ' => 'N',
			'ŋ' => 'n',
			'Ō' => 'O',
			'ō' => 'o',
			'Ŏ' => 'O',
			'ŏ' => 'o',
			'Ő' => 'O',
			'ő' => 'o',
			'Œ' => 'OE',
			'œ' => 'oe',
			'Ŕ' => 'R',
			'ŕ' => 'r',
			'Ŗ' => 'R',
			'ŗ' => 'r',
			'Ř' => 'R',
			'ř' => 'r',
			'Ś' => 'S',
			'ś' => 's',
			'Ŝ' => 'S',
			'ŝ' => 's',
			'Ş' => 'S',
			'ş' => 's',
			'Š' => 'S',
			'š' => 's',
			'Ţ' => 'T',
			'ţ' => 't',
			'Ť' => 'T',
			'ť' => 't',
			'Ŧ' => 'T',
			'ŧ' => 't',
			'Ũ' => 'U',
			'ũ' => 'u',
			'Ū' => 'U',
			'ū' => 'u',
			'Ŭ' => 'U',
			'ŭ' => 'u',
			'Ů' => 'U',
			'ů' => 'u',
			'Ű' => 'U',
			'ű' => 'u',
			'Ų' => 'U',
			'ų' => 'u',
			'Ŵ' => 'W',
			'ŵ' => 'w',
			'Ŷ' => 'Y',
			'ŷ' => 'y',
			'Ÿ' => 'Y',
			'Ź' => 'Z',
			'ź' => 'z',
			'Ż' => 'Z',
			'ż' => 'z',
			'Ž' => 'Z',
			'ž' => 'z',
			'ſ' => 's',
			// Decompositions for Latin Extended-B.
			'Ș' => 'S',
			'ș' => 's',
			'Ț' => 'T',
			'ț' => 't',
			// Euro sign.
			'€' => 'E',
			// GBP (Pound) sign.
			'£' => '',
			// Vowels with diacritic (Vietnamese).
			// Unmarked.
			'Ơ' => 'O',
			'ơ' => 'o',
			'Ư' => 'U',
			'ư' => 'u',
			// Grave accent.
			'Ầ' => 'A',
			'ầ' => 'a',
			'Ằ' => 'A',
			'ằ' => 'a',
			'Ề' => 'E',
			'ề' => 'e',
			'Ồ' => 'O',
			'ồ' => 'o',
			'Ờ' => 'O',
			'ờ' => 'o',
			'Ừ' => 'U',
			'ừ' => 'u',
			'Ỳ' => 'Y',
			'ỳ' => 'y',
			// Hook.
			'Ả' => 'A',
			'ả' => 'a',
			'Ẩ' => 'A',
			'ẩ' => 'a',
			'Ẳ' => 'A',
			'ẳ' => 'a',
			'Ẻ' => 'E',
			'ẻ' => 'e',
			'Ể' => 'E',
			'ể' => 'e',
			'Ỉ' => 'I',
			'ỉ' => 'i',
			'Ỏ' => 'O',
			'ỏ' => 'o',
			'Ổ' => 'O',
			'ổ' => 'o',
			'Ở' => 'O',
			'ở' => 'o',
			'Ủ' => 'U',
			'ủ' => 'u',
			'Ử' => 'U',
			'ử' => 'u',
			'Ỷ' => 'Y',
			'ỷ' => 'y',
			// Tilde.
			'Ẫ' => 'A',
			'ẫ' => 'a',
			'Ẵ' => 'A',
			'ẵ' => 'a',
			'Ẽ' => 'E',
			'ẽ' => 'e',
			'Ễ' => 'E',
			'ễ' => 'e',
			'Ỗ' => 'O',
			'ỗ' => 'o',
			'Ỡ' => 'O',
			'ỡ' => 'o',
			'Ữ' => 'U',
			'ữ' => 'u',
			'Ỹ' => 'Y',
			'ỹ' => 'y',
			// Acute accent.
			'Ấ' => 'A',
			'ấ' => 'a',
			'Ắ' => 'A',
			'ắ' => 'a',
			'Ế' => 'E',
			'ế' => 'e',
			'Ố' => 'O',
			'ố' => 'o',
			'Ớ' => 'O',
			'ớ' => 'o',
			'Ứ' => 'U',
			'ứ' => 'u',
			// Dot below.
			'Ạ' => 'A',
			'ạ' => 'a',
			'Ậ' => 'A',
			'ậ' => 'a',
			'Ặ' => 'A',
			'ặ' => 'a',
			'Ẹ' => 'E',
			'ẹ' => 'e',
			'Ệ' => 'E',
			'ệ' => 'e',
			'Ị' => 'I',
			'ị' => 'i',
			'Ọ' => 'O',
			'ọ' => 'o',
			'Ộ' => 'O',
			'ộ' => 'o',
			'Ợ' => 'O',
			'ợ' => 'o',
			'Ụ' => 'U',
			'ụ' => 'u',
			'Ự' => 'U',
			'ự' => 'u',
			'Ỵ' => 'Y',
			'ỵ' => 'y',
			// Vowels with diacritic (Chinese, Hanyu Pinyin).
			'ɑ' => 'a',
			// Macron.
			'Ǖ' => 'U',
			'ǖ' => 'u',
			// Acute accent.
			'Ǘ' => 'U',
			'ǘ' => 'u',
			// Caron.
			'Ǎ' => 'A',
			'ǎ' => 'a',
			'Ǐ' => 'I',
			'ǐ' => 'i',
			'Ǒ' => 'O',
			'ǒ' => 'o',
			'Ǔ' => 'U',
			'ǔ' => 'u',
			'Ǚ' => 'U',
			'ǚ' => 'u',
			// Grave accent.
			'Ǜ' => 'U',
			'ǜ' => 'u',
		);

		// Used for locale-specific rules.
		$locale = get_locale();

		if ( in_array( $locale, array( 'de_DE', 'de_DE_formal', 'de_CH', 'de_CH_informal', 'de_AT' ), true ) ) {
			$chars['Ä'] = 'Ae';
			$chars['ä'] = 'ae';
			$chars['Ö'] = 'Oe';
			$chars['ö'] = 'oe';
			$chars['Ü'] = 'Ue';
			$chars['ü'] = 'ue';
			$chars['ß'] = 'ss';
		} elseif ( 'da_DK' === $locale ) {
			$chars['Æ'] = 'Ae';
			$chars['æ'] = 'ae';
			$chars['Ø'] = 'Oe';
			$chars['ø'] = 'oe';
			$chars['Å'] = 'Aa';
			$chars['å'] = 'aa';
		} elseif ( 'ca' === $locale ) {
			$chars['l·l'] = 'll';
		} elseif ( 'sr_RS' === $locale || 'bs_BA' === $locale ) {
			$chars['Đ'] = 'DJ';
			$chars['đ'] = 'dj';
		}

		$string = strtr( $string, $chars );
	} else {
		$chars = array();
		// Assume ISO-8859-1 if not UTF-8.
		$chars['in'] = "\x80\x83\x8a\x8e\x9a\x9e"
			. "\x9f\xa2\xa5\xb5\xc0\xc1\xc2"
			. "\xc3\xc4\xc5\xc7\xc8\xc9\xca"
			. "\xcb\xcc\xcd\xce\xcf\xd1\xd2"
			. "\xd3\xd4\xd5\xd6\xd8\xd9\xda"
			. "\xdb\xdc\xdd\xe0\xe1\xe2\xe3"
			. "\xe4\xe5\xe7\xe8\xe9\xea\xeb"
			. "\xec\xed\xee\xef\xf1\xf2\xf3"
			. "\xf4\xf5\xf6\xf8\xf9\xfa\xfb"
			. "\xfc\xfd\xff";

		$chars['out'] = 'EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy';

		$string              = strtr( $string, $chars['in'], $chars['out'] );
		$double_chars        = array();
		$double_chars['in']  = array( "\x8c", "\x9c", "\xc6", "\xd0", "\xde", "\xdf", "\xe6", "\xf0", "\xfe" );
		$double_chars['out'] = array( 'OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th' );
		$string              = str_replace( $double_chars['in'], $double_chars['out'], $string );
	}

	return $string;
}

function hkm_seems_utf8( $str ) {
	mbstring_binary_safe_encoding();
	$length = strlen( $str );
	reset_mbstring_encoding();
	for ( $i = 0; $i < $length; $i++ ) {
		$c = ord( $str[ $i ] );
		if ( $c < 0x80 ) {
			$n = 0; // 0bbbbbbb
		} elseif ( ( $c & 0xE0 ) == 0xC0 ) {
			$n = 1; // 110bbbbb
		} elseif ( ( $c & 0xF0 ) == 0xE0 ) {
			$n = 2; // 1110bbbb
		} elseif ( ( $c & 0xF8 ) == 0xF0 ) {
			$n = 3; // 11110bbb
		} elseif ( ( $c & 0xFC ) == 0xF8 ) {
			$n = 4; // 111110bb
		} elseif ( ( $c & 0xFE ) == 0xFC ) {
			$n = 5; // 1111110b
		} else {
			return false; // Does not match any model.
		}
		for ( $j = 0; $j < $n; $j++ ) { // n bytes matching 10bbbbbb follow ?
			if ( ( ++$i == $length ) || ( ( ord( $str[ $i ] ) & 0xC0 ) != 0x80 ) ) {
				return false;
			}
		}
	}
	return true;
}

function mbstring_binary_safe_encoding( $reset = false ) {
	static $encodings  = array();
	static $overloaded = null;

	if ( is_null( $overloaded ) ) {
		if ( function_exists( 'mb_internal_encoding' )
			&& ( (int) ini_get( 'mbstring.func_overload' ) & 2 ) // phpcs:ignore PHPCompatibility.IniDirectives.RemovedIniDirectives.mbstring_func_overloadDeprecated
		) {
			$overloaded = true;
		} else {
			$overloaded = false;
		}
	}

	if ( false === $overloaded ) {
		return;
	}

	if ( ! $reset ) {
		$encoding = mb_internal_encoding();
		array_push( $encodings, $encoding );
		mb_internal_encoding( 'ISO-8859-1' );
	}

	if ( $reset && $encodings ) {
		$encoding = array_pop( $encodings );
		mb_internal_encoding( $encoding );
	}
}

function reset_mbstring_encoding() {
	mbstring_binary_safe_encoding( true );
}

function hkm_is_ssl() {
	if ( isset( $_SERVER['HTTPS'] ) ) {
		if ( 'on' === strtolower( $_SERVER['HTTPS'] ) ) {
			return true;
		}

		if ( '1' == $_SERVER['HTTPS'] ) {
			return true;
		}
	} elseif ( isset( $_SERVER['SERVER_PORT'] ) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
		return true;
	}
	return false;
}



if ( ! function_exists( 'hkm_hash' ) ) :
	/**
	 * Get hash of given string.
	 *
	 *
	 * @param string $data   Plain text to hash
	 * @param string $scheme Authentication scheme (auth, secure_auth, logged_in, nonce)
	 * @return string Hash of $data
	 */
	function hkm_hash( $data, $scheme = 'auth' ) {
		$salt = hkm_salt( $scheme );

		return hash_hmac( 'md5', $data, $salt );
	}
endif;

function generateRandomKey(int $length, string $prefix = 'hex2bin'): string
{
		$key = Encryption::createKey($length);

		if ($prefix === 'hex2bin')
		{
			return  bin2hex($key);
		}

		return base64_encode($key);
}

if ( ! function_exists( 'hkm_rand' ) ) :
	/**
	 * Generates a random number.
	 *
	 * @global string $rnd_value
	 *
	 * @param int $min Lower limit for the generated number
	 * @param int $max Upper limit for the generated number
	 * @return int A random number between min and max
	 */
	function hkm_rand( $min = 0, $max = 0 ) {
		global $rnd_value;

		// Some misconfigured 32-bit environments (Entropy PHP, for example)
		// truncate integers larger than PHP_INT_MAX to PHP_INT_MAX rather than overflowing them to floats.
		$max_random_number = 3000000000 === 2147483647 ? (float) '4294967295' : 4294967295; // 4294967295 = 0xffffffff

		// We only handle ints, floats are truncated to their integer value.
		$min = (int) $min;
		$max = (int) $max;

		// Use PHP's CSPRNG, or a compatible method.
		static $use_random_int_functionality = true;
		if ( $use_random_int_functionality ) {
			try {
				$_max = ( 0 != $max ) ? $max : $max_random_number;
				// wp_rand() can accept arguments in either order, PHP cannot.
				$_max = max( $min, $_max );
				$_min = min( $min, $_max );
				$val  = random_int( $_min, $_max );
				if ( false !== $val ) {
					return absint( $val );
				} else {
					$use_random_int_functionality = false;
				}
			} catch ( Error $e ) {
				$use_random_int_functionality = false;
			} catch ( Exception $e ) {
				$use_random_int_functionality = false;
			}
		}

		// Reset $rnd_value after 14 uses.
		// 32 (md5) + 40 (sha1) + 40 (sha1) / 8 = 14 random numbers from $rnd_value.
		if ( strlen( $rnd_value ) < 8 ) {
			if ( defined( 'HKM_SETUP_CONFIG' ) ) {
				static $seed = '';
			} 
			$rnd_value  = md5( uniqid( microtime() . mt_rand(), true ) . $seed );
			$rnd_value .= sha1( $rnd_value );
			$rnd_value .= sha1( $rnd_value . $seed );
			$seed       = md5( $seed . $rnd_value );
			
		}

		// Take the first 8 digits for our value.
		$value = substr( $rnd_value, 0, 8 );

		// Strip the first eight, leaving the remainder for the next call to wp_rand().
		$rnd_value = substr( $rnd_value, 8 );

		$value = abs( hexdec( $value ) );

		// Reduce the value to be within the min - max range.
		if ( 0 != $max ) {
			$value = $min + ( $max - $min + 1 ) * $value / ( $max_random_number + 1 );
		}

		return abs( (int) $value );
	}
endif;

if ( ! function_exists( 'hkm_generate_password' ) ) :
	/**
	 * Generates a random password drawn from the defined set of characters.
	 *
	 * Uses hkm_rand() is used to create passwords with far less predictability
	 * than similar native PHP functions like `rand()` or `mt_rand()`.
	 *
	 * @param int  $length              Optional. The length of password to generate. Default 12.
	 * @param bool $special_chars       Optional. Whether to include standard special characters.
	 *                                  Default true.
	 * @param bool $extra_special_chars Optional. Whether to include other special characters.
	 *                                  Used when generating secret keys and salts. Default false.
	 * @return string The random password.
	 */
	function hkm_generate_password( $length = 12, $special_chars = true, $extra_special_chars = false ) {
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		if ( $special_chars ) {
			$chars .= '!@#$%^&*()';
		}
		if ( $extra_special_chars ) {
			$chars .= '-_ []{}<>~`+=,.;:/?|';
		}

		$password = '';
		for ( $i = 0; $i < $length; $i++ ) {
			$password .= substr( $chars, hkm_rand( 0, strlen( $chars ) - 1 ), 1 );
		}
		return $password;
	}
endif;

if ( ! function_exists( 'hkm_salt' ) ) :
	
	function hkm_salt( $scheme = 'auth' ) {

		static $duplicated_keys;
		if ( null === $duplicated_keys ) {
			$duplicated_keys = array( 'put your unique phrase here' => true );
			foreach ( array( 'AUTH', 'SECURE_AUTH', 'LOGGED_IN', 'NONCE', 'SECRET' ) as $first ) {
				foreach ( array( 'KEY', 'SALT' ) as $second ) {
					if ( ! defined( "{$first}_{$second}" ) ) {
						continue;
					}
					$value                     = constant( "{$first}_{$second}" );
					$duplicated_keys[ $value ] = isset( $duplicated_keys[ $value ] );
				}
			}
		}

		$values = array(
			'key'  => '',
			'salt' => '',
		);
		if ( defined( 'SECRET_KEY' ) && SECRET_KEY && empty( $duplicated_keys[ SECRET_KEY ] ) ) {
			$values['key'] = SECRET_KEY;
		}
		if ( 'auth' === $scheme && defined( 'SECRET_SALT' ) && SECRET_SALT && empty( $duplicated_keys[ SECRET_SALT ] ) ) {
			$values['salt'] = SECRET_SALT;
		}

		if ( in_array( $scheme, array( 'auth', 'secure_auth', 'logged_in', 'nonce' ), true ) ) {
			foreach ( array( 'key', 'salt' ) as $type ) {
				$const = strtoupper( "{$scheme}_{$type}" );
				if ( defined( $const ) && constant( $const ) && empty( $duplicated_keys[ constant( $const ) ] ) ) {
					$values[ $type ] = constant( $const );
				} elseif ( ! $values[ $type ] ) {
					$values[ $type ] = hkm_generate_password( 64, true, true );
				}
			}
		} else {
			if ( ! $values['key'] ) {
				$values['key'] = hkm_generate_password( 64, true, true );
			}
			$values['salt'] = hash_hmac( 'md5', $scheme, $values['key'] );
		}

		$cached_salts[ $scheme ] = $values['key'] . $values['salt'];

		/** This filter is documented in wp-includes/pluggable.php */
		return $cached_salts[ $scheme ];
	}
endif;


function hkm_is_json_request() {

	if ( isset( $_SERVER['HTTP_ACCEPT'] ) && hkm_is_json_media_type( $_SERVER['HTTP_ACCEPT'] ) ) {
		return true;
	}

	if ( isset( $_SERVER['CONTENT_TYPE'] ) && hkm_is_json_media_type( $_SERVER['CONTENT_TYPE'] ) ) {
		return true;
	}

	return false;

}

function hkm_check_jsonp_callback( $callback ) {
	if ( ! is_string( $callback ) ) {
		return false;
	}

	preg_replace( '/[^\w\.]/', '', $callback, -1, $illegal_char_count );

	return 0 === $illegal_char_count;
}

function hkm_is_jsonp_request() {
	if ( ! isset( $_GET['_jsonp'] ) ) {
		return false;
	}


	$jsonp_callback = $_GET['_jsonp'];
	if ( ! hkm_check_jsonp_callback( $jsonp_callback ) ) {
		return false;
	}

	return true;

}


function hkm_is_json_media_type( $media_type ) {
	static $cache = array();

	if ( ! isset( $cache[ $media_type ] ) ) {
		$cache[ $media_type ] = (bool) preg_match( '/(^|\s|,)application\/([\w!#\$&-\^\.\+]+\+)?json(\+oembed)?($|\s|;|,)/i', $media_type );
	}

	return $cache[ $media_type ];
}


function hkm_is_xml_request() {
	$accepted = array(
		'text/xml',
		'application/rss+xml',
		'application/atom+xml',
		'application/rdf+xml',
		'text/xml+oembed',
		'application/xml+oembed',
	);

	if ( isset( $_SERVER['HTTP_ACCEPT'] ) ) {
		foreach ( $accepted as $type ) {
			if ( false !== strpos( $_SERVER['HTTP_ACCEPT'], $type ) ) {
				return true;
			}
		}
	}

	if ( isset( $_SERVER['CONTENT_TYPE'] ) && in_array( $_SERVER['CONTENT_TYPE'], $accepted, true ) ) {
		return true;
	}

	return false;
}



function map_deep( $value, $callback ) {
	if ( is_array( $value ) ) {
		foreach ( $value as $index => $item ) {
			$value[ $index ] = map_deep( $item, $callback );
		}
	} elseif ( is_object( $value ) ) {
		$object_vars = get_object_vars( $value );
		foreach ( $object_vars as $property_name => $property_value ) {
			$value->$property_name = map_deep( $property_value, $callback );
		}
	} else {
		$value = call_user_func( $callback, $value );
	}

	return $value;
}
function stripslashes_from_strings_only( $value ) {
	return is_string( $value ) ? stripslashes( $value ) : $value;
}

function stripslashes_deep( $value ) {
	return map_deep( $value, 'stripslashes_from_strings_only' );
}

function hkm_get_server_protocol() {
	$protocol = isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : '';
	if ( ! in_array( $protocol, array( 'HTTP/1.1', 'HTTP/2', 'HTTP/2.0' ), true ) ) {
		$protocol = 'HTTP/1.0';
	}
	return $protocol;
}
function hkm_validate($data,$config)
{
	    $validation = ServicesSystem::VALIDATION();
		if (count($config::$validationRules['rules'])>0) {
			if(!$validation::ADD_RULES_FILES($config::$validationRulesFiles['paths'])
			::SET_RULES($config::$validationRules['rules'])
			::RUN($data))return $validation;
			else return false; 
		}else return false;
        
}


function hkm_validate_v3($rules, array $messages ,&$validator){
	
	$validator = ServicesSystem::VALIDATION();

		// If you replace the $rules array with the name of the group
		if (is_string($rules))
		{
			$validation = hkm_config('Validation');

			// If the rule wasn't found in the \Validation, we
			// should throw an exception so the developer can find it.
			if (! isset($validation::$rules))
			{
				throw ValidationException::FOR_RULE_NOT_FOUND($rules);
			}

			// If no error message is defined, use the error message in the Validation file
			if (! $messages)
			{
				$errorName = $rules . '_errors';
				$messages  = $validation::$errorName ?? [];
			}

			$rules = $validation::$rules;
		}

		return $validator::WITH_REQUEST(ServicesSystem::REQUEST())::SET_RULES($rules, $messages)::RUN();
}

function hkm_doing_ajax() {
	$request = ServicesSystem::REQUEST();
	return $request::IS_AJAX();
}
function absint( $maybeint ) {
	return abs( (int) $maybeint );
}

function get_status_header_desc( $code ) {
	global $hkm_header_to_desc;

	$code = absint( $code );

	if ( ! isset( $wp_header_to_desc ) ) {
		$wp_header_to_desc = array(
			100 => 'Continue',
			101 => 'Switching Protocols',
			102 => 'Processing',
			103 => 'Early Hints',

			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			207 => 'Multi-Status',
			226 => 'IM Used',

			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			306 => 'Reserved',
			307 => 'Temporary Redirect',
			308 => 'Permanent Redirect',

			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			418 => 'I\'m a teapot',
			421 => 'Misdirected Request',
			422 => 'Unprocessable Entity',
			423 => 'Locked',
			424 => 'Failed Dependency',
			426 => 'Upgrade Required',
			428 => 'Precondition Required',
			429 => 'Too Many Requests',
			431 => 'Request Header Fields Too Large',
			451 => 'Unavailable For Legal Reasons',

			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported',
			506 => 'Variant Also Negotiates',
			507 => 'Insufficient Storage',
			510 => 'Not Extended',
			511 => 'Network Authentication Required',
		);
	}

	if ( isset( $hkm_header_to_desc[ $code ] ) ) {
		return $hkm_header_to_desc[ $code ];
	} else {
		return '';
	}
}


/**
 * Checks for invalid UTF8 in a string.
 *
 * @param string $string The text which is to be checked.
 * @param bool   $strip  Optional. Whether to attempt to strip out invalid UTF8. Default false.
 * @return string The checked text.
 */
function hkm_check_invalid_utf8( $string, $strip = false ) {
	$string = (string) $string;

	if ( 0 === strlen( $string ) ) {
		return '';
	}

	// Store the site charset as a static to avoid multiple calls to get_option().
	static $is_utf8 = null;
	if ( ! isset( $is_utf8 ) ) {
		$is_utf8 = in_array( 'utf-8', array( 'utf8', 'utf-8', 'UTF8', 'UTF-8' ), true );
	}
	if ( ! $is_utf8 ) {
		return $string;
	}

	// Check for support for utf8 in the installed PCRE library once and store the result in a static.
	static $utf8_pcre = null;
	if ( ! isset( $utf8_pcre ) ) {
		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		$utf8_pcre = @preg_match( '/^./u', 'a' );
	}
	// We can't demand utf8 in the PCRE installation, so just return the string in those cases.
	if ( ! $utf8_pcre ) {
		return $string;
	}

	// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- preg_match fails when it encounters invalid UTF8 in $string.
	if ( 1 === @preg_match( '/^./us', $string ) ) {
		return $string;
	}

	// Attempt to strip the bad chars if requested (not recommended).
	if ( $strip && function_exists( 'iconv' ) ) {
		return iconv( 'utf-8', 'utf-8', $string );
	}

	return '';
}


/**
 * Convert a string to UTF-8, so that it can be safely encoded to JSON.
 *
 * @ignore
 * @since 4.1.0
 * @access private
 *
 * @see _hkm_json_sanity_check()
 *
 * @param string $string The string which is to be converted.
 * @return string The checked string.
 */
function _hkm_json_convert_string( $string ) {
	static $use_mb = null;
	if ( is_null( $use_mb ) ) {
		$use_mb = function_exists( 'mb_convert_encoding' );
	}

	if ( $use_mb ) {
		$encoding = mb_detect_encoding( $string, mb_detect_order(), true );
		if ( $encoding ) {
			return mb_convert_encoding( $string, 'UTF-8', $encoding );
		} else {
			return mb_convert_encoding( $string, 'UTF-8', 'UTF-8' );
		}
	} else {
		return hkm_check_invalid_utf8( $string, true );
	}
}



/**
 * Perform sanity checks on data that shall be encoded to JSON.
 *
 * @ignore
 * @access private
 *
 * @see hkm_json_encode() 
 *
 * @throws Exception If depth limit is reached.
 *
 * @param mixed $data  Variable (usually an array or object) to encode as JSON.
 * @param int   $depth Maximum depth to walk through $data. Must be greater than 0.
 * @return mixed The sanitized data that shall be encoded to JSON.
 */
function _hkm_json_sanity_check( $data, $depth ) {
	if ( $depth < 0 ) {
		throw new Exception( 'Reached depth limit' );
	}

	if ( is_array( $data ) ) {
		$output = array();
		foreach ( $data as $id => $el ) {
			// Don't forget to sanitize the ID!
			if ( is_string( $id ) ) {
				$clean_id = _hkm_json_convert_string( $id );
			} else {
				$clean_id = $id;
			}

			// Check the element type, so that we're only recursing if we really have to.
			if ( is_array( $el ) || is_object( $el ) ) {
				$output[ $clean_id ] = _hkm_json_sanity_check( $el, $depth - 1 );
			} elseif ( is_string( $el ) ) {
				$output[ $clean_id ] = _hkm_json_convert_string( $el );
			} else {
				$output[ $clean_id ] = $el;
			}
		}
	} elseif ( is_object( $data ) ) {
		$output = new stdClass;
		foreach ( $data as $id => $el ) {
			if ( is_string( $id ) ) {
				$clean_id = _hkm_json_convert_string( $id );
			} else {
				$clean_id = $id;
			}

			if ( is_array( $el ) || is_object( $el ) ) {
				$output->$clean_id = _hkm_json_sanity_check( $el, $depth - 1 );
			} elseif ( is_string( $el ) ) {
				$output->$clean_id = _hkm_json_convert_string( $el );
			} else {
				$output->$clean_id = $el;
			}
		}
	} elseif ( is_string( $data ) ) {
		return _hkm_json_convert_string( $data );
	} else {
		return $data;
	}

	return $output;
}


function hkm_json_encode( $data, $options = 0, $depth = 512 ) {
	$json = json_encode( $data, $options, $depth );

	// If json_encode() was successful, no need to do more sanity checking.
	if ( false !== $json ) {
		return $json;
	}

	try {
		$data = _hkm_json_sanity_check( $data, $depth );
	} catch ( Exception $e ) {
		return false;
	}

	return json_encode( $data, $options, $depth );
}

/**
 * Determines if a Unicode codepoint is valid.
 *
 * @since 2.7.0
 *
 * @param int $i Unicode codepoint.
 * @return bool Whether or not the codepoint is a valid Unicode codepoint.
 */
function valid_unicode( $i ) {
	return ( 0x9 == $i || 0xa == $i || 0xd == $i ||
			( 0x20 <= $i && $i <= 0xd7ff ) ||
			( 0xe000 <= $i && $i <= 0xfffd ) ||
			( 0x10000 <= $i && $i <= 0x10ffff ) );
}
/**
 * Perform a deep string replace operation to ensure the values in $search are no longer present
 *
 * Repeats the replacement operation until it no longer replaces anything so as to remove "nested" values
 * e.g. $subject = '%0%0%0DDD', $search ='%0D', $result ='' rather than the '%0%0DD' that
 * str_replace would return
 *
 * @since 2.8.1
 * @access private
 *
 * @param string|array $search  The value being searched for, otherwise known as the needle.
 *                              An array may be used to designate multiple needles.
 * @param string       $subject The string being searched and replaced on, otherwise known as the haystack.
 * @return string The string with the replaced values.
 */
function _deep_replace( $search, $subject ) {
	$subject = (string) $subject;

	$count = 1;
	while ( $count ) {
		$subject = str_replace( $search, '', $subject, $count );
	}

	return $subject;
}

/**
 * Callback for `hkm_kses_normalize_entities()` regular expression.
 *
 * This function only accepts valid named entity references, which are finite,
 * case-sensitive, and highly scrutinized by HTML and XML validators.
 *
 * @since 3.0.0
 *
 * @global array $allowedentitynames
 *
 * @param array $matches preg_replace_callback() matches array.
 * @return string Correctly encoded entity.
 */
function hkm_kses_named_entities( $matches ) {
	global $allowedentitynames;

	if ( empty( $matches[1] ) ) {
		return '';
	}

	$i = $matches[1];
	return ( ! in_array( $i, $allowedentitynames, true ) ) ? "&amp;$i;" : "&$i;";
}
/**
 * Callback for `wp_kses_normalize_entities()` regular expression.
 *
 * This function only accepts valid named entity references, which are finite,
 * case-sensitive, and highly scrutinized by XML validators.  HTML named entity
 * references are converted to their code points.
 *
 * @since 5.5.0
 *
 * @global array $allowedentitynames
 * @global array $allowedxmlnamedentities
 *
 * @param array $matches preg_replace_callback() matches array.
 * @return string Correctly encoded entity.
 */
function hkm_kses_xml_named_entities( $matches ) {
	global $allowedentitynames, $allowedxmlnamedentities;

	if ( empty( $matches[1] ) ) {
		return '';
	}

	$i = $matches[1];

	if ( in_array( $i, $allowedxmlnamedentities, true ) ) {
		return "&$i;";
	} elseif ( in_array( $i, $allowedentitynames, true ) ) {
		return html_entity_decode( "&$i;", ENT_HTML5 );
	}

	return "&amp;$i;";
}


/**
 * Callback for `wp_kses_normalize_entities()` for regular expression.
 *
 * This function helps `wp_kses_normalize_entities()` to only accept valid Unicode
 * numeric entities in hex form.
 *
 * @since 2.7.0
 * @access private
 * @ignore
 *
 * @param array $matches `preg_replace_callback()` matches array.
 * @return string Correctly encoded entity.
 */
function hkm_kses_normalize_entities3( $matches ) {
	if ( empty( $matches[1] ) ) {
		return '';
	}

	$hexchars = $matches[1];
	return ( ! valid_unicode( hexdec( $hexchars ) ) ) ? "&amp;#x$hexchars;" : '&#x' . ltrim( $hexchars, '0' ) . ';';
}
/**
 * Callback for `wp_kses_normalize_entities()` regular expression.
 *
 * This function helps `wp_kses_normalize_entities()` to only accept 16-bit
 * values and nothing more for `&#number;` entities.
 *
 * @access private
 * @ignore
 * @since 1.0.0
 *
 * @param array $matches `preg_replace_callback()` matches array.
 * @return string Correctly encoded entity.
 */
function hkm_kses_normalize_entities2( $matches ) {
	if ( empty( $matches[1] ) ) {
		return '';
	}

	$i = $matches[1];
	if ( valid_unicode( $i ) ) {
		$i = str_pad( ltrim( $i, '0' ), 3, '0', STR_PAD_LEFT );
		$i = "&#$i;";
	} else {
		$i = "&amp;#$i;";
	}

	return $i;
}

/**
 * Callback for `wp_kses_normalize_entities()` regular expression.
 *
 * This function only accepts valid named entity references, which are finite,
 * case-sensitive, and highly scrutinized by HTML and XML validators.
 *
 * @since 3.0.0
 *
 * @global array $allowedentitynames
 *
 * @param array $matches preg_replace_callback() matches array.
 * @return string Correctly encoded entity.
 */
function hkkm_kses_named_entities( $matches ) {
	global $allowedentitynames;

	if ( empty( $matches[1] ) ) {
		return '';
	}

	$i = $matches[1];
	return ( ! in_array( $i, $allowedentitynames, true ) ) ? "&amp;$i;" : "&$i;";
}

/**
 * Converts and fixes HTML entities.
 *
 * This function normalizes HTML entities. It will convert `AT&T` to the correct
 * `AT&amp;T`, `&#00058;` to `&#058;`, `&#XYZZY;` to `&amp;#XYZZY;` and so on.
 *
 * When `$context` is set to 'xml', HTML entities are converted to their code points.  For
 * example, `AT&T&hellip;&#XYZZY;` is converted to `AT&amp;T…&amp;#XYZZY;`.
 *
 * @since 1.0.0
 * @since 5.5.0 Added `$context` parameter.
 *
 * @param string $string  Content to normalize entities.
 * @param string $context Context for normalization. Can be either 'html' or 'xml'.
 *                        Default 'html'.
 * @return string Content with normalized entities.
 */
function hkm_kses_normalize_entities( $string, $context = 'html' ) {
	// Disarm all entities by converting & to &amp;
	$string = str_replace( '&', '&amp;', $string );

	// Change back the allowed entities in our list of allowed entities.
	if ( 'xml' === $context ) {
		$string = preg_replace_callback( '/&amp;([A-Za-z]{2,8}[0-9]{0,2});/', 'hkm_kses_xml_named_entities', $string );
	} else {
		$string = preg_replace_callback( '/&amp;([A-Za-z]{2,8}[0-9]{0,2});/', 'hkm_kses_named_entities', $string );
	}
	$string = preg_replace_callback( '/&amp;#(0*[0-9]{1,7});/', 'hkm_kses_normalize_entities2', $string );
	$string = preg_replace_callback( '/&amp;#[Xx](0*[0-9A-Fa-f]{1,6});/', 'hkm_kses_normalize_entities3', $string );

	return $string;
}


/**
	* Removes any invalid control characters in a text string.
	*
	* Also removes any instance of the `\0` string.
	*
	* @since 1.0.0
	*
	* @param string $string  Content to filter null characters from.
	* @param array  $options Set 'slash_zero' => 'keep' when '\0' is allowed. Default is 'remove'.
	* @return string Filtered content.
	*/
function hkm_kses_no_null( $string, $options = null ) {
   if ( ! isset( $options['slash_zero'] ) ) {
	   $options = array( 'slash_zero' => 'remove' );
   }

   $string = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $string );
   if ( 'remove' === $options['slash_zero'] ) {
	   $string = preg_replace( '/\\\\+0+/', '', $string );
   }

   return $string;
}

/**
 * Retrieve a list of protocols to allow in HTML attributes.
 *
 * @since 3.3.0
 * @since 4.3.0 Added 'webcal' to the protocols array.
 * @since 4.7.0 Added 'urn' to the protocols array.
 * @since 5.3.0 Added 'sms' to the protocols array.
 * @since 5.6.0 Added 'irc6' and 'ircs' to the protocols array.
 *
 * @see wp_kses()
 * @see esc_url()
 *
 * @return string[] Array of allowed protocols. Defaults to an array containing 'http', 'https',
 *                  'ftp', 'ftps', 'mailto', 'news', 'irc', 'irc6', 'ircs', 'gopher', 'nntp', 'feed',
 *                  'telnet', 'mms', 'rtsp', 'sms', 'svn', 'tel', 'fax', 'xmpp', 'webcal', and 'urn'.
 *                  This covers all common link protocols, except for 'javascript' which should not
 *                  be allowed for untrusted users.
 */
function hkm_allowed_protocols() {
	static $protocols = array();

	if ( empty( $protocols ) ) {
		$protocols = array( 'http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'irc6', 'ircs', 'gopher', 'nntp', 'feed', 'telnet', 'mms', 'rtsp', 'sms', 'svn', 'tel', 'fax', 'xmpp', 'webcal', 'urn' );
	}

	if ( ! hkm_did_action( 'hkm_loaded' ) ) {
		/**
		 * Filters the list of protocols allowed in HTML attributes.
		 *
		 * @since 3.0.0
		 *
		 * @param string[] $protocols Array of allowed protocols e.g. 'http', 'ftp', 'tel', and more.
		 */
		$protocols = array_unique( (array)  hkm_apply_filters( 'kses_allowed_protocols', $protocols ) );
	}

	return $protocols;
}

/**
 * Regex callback for `wp_kses_decode_entities()`.
 *
 * @since 2.9.0
 * @access private
 * @ignore
 *
 * @param array $match preg match
 * @return string
 */
function _hkm_kses_decode_entities_chr_hexdec( $match ) {
	return chr( hexdec( $match[1] ) );
}

/**
 * Regex callback for `wp_kses_decode_entities()`.
 *
 * @since 2.9.0
 * @access private
 * @ignore
 *
 * @param array $match preg match
 * @return string
 */
function _hkm_kses_decode_entities_chr( $match ) {
	return chr( $match[1] );
}

/**
 * Converts all numeric HTML entities to their named counterparts.
 *
 * This function decodes numeric HTML entities (`&#65;` and `&#x41;`).
 * It doesn't do anything with named entities like `&auml;`, but we don't
 * need them in the allowed URL protocols system anyway.
 *
 * @since 1.0.0
 *
 * @param string $string Content to change entities.
 * @return string Content after decoded entities.
 */
function hkm_kses_decode_entities( $string ) {
	$string = preg_replace_callback( '/&#([0-9]+);/', '_hkm_kses_decode_entities_chr', $string );
	$string = preg_replace_callback( '/&#[Xx]([0-9A-Fa-f]+);/', '_hkm_kses_decode_entities_chr_hexdec', $string );

	return $string;
}

/**
 * Callback for `wp_kses_bad_protocol_once()` regular expression.
 *
 * This function processes URL protocols, checks to see if they're in the
 * list of allowed protocols or not, and returns different data depending
 * on the answer.
 *
 * @access private
 * @ignore
 * @since 1.0.0
 *
 * @param string   $string            URI scheme to check against the list of allowed protocols.
 * @param string[] $allowed_protocols Array of allowed URL protocols.
 * @return string Sanitized content.
 */
function hkm_kses_bad_protocol_once2( $string, $allowed_protocols ) {
	$string2 = hkm_kses_decode_entities( $string );
	$string2 = preg_replace( '/\s/', '', $string2 );
	$string2 = hkm_kses_no_null( $string2 );
	$string2 = strtolower( $string2 );

	$allowed = false;
	foreach ( (array) $allowed_protocols as $one_protocol ) {
		if ( strtolower( $one_protocol ) == $string2 ) {
			$allowed = true;
			break;
		}
	}

	if ( $allowed ) {
		return "$string2:";
	} else {
		return '';
	}
}


/**
 * Sanitizes content from bad protocols and other characters.
 *
 * This function searches for URL protocols at the beginning of the string, while
 * handling whitespace and HTML entities.
 *
 * @since 1.0.0
 *
 * @param string   $string            Content to check for bad protocols.
 * @param string[] $allowed_protocols Array of allowed URL protocols.
 * @param int      $count             Depth of call recursion to this function.
 * @return string Sanitized content.
 */
function hkm_kses_bad_protocol_once( $string, $allowed_protocols, $count = 1 ) {
	$string  = preg_replace( '/(&#0*58(?![;0-9])|&#x0*3a(?![;a-f0-9]))/i', '$1;', $string );
	$string2 = preg_split( '/:|&#0*58;|&#x0*3a;|&colon;/i', $string, 2 );
	if ( isset( $string2[1] ) && ! preg_match( '%/\?%', $string2[0] ) ) {
		$string   = trim( $string2[1] );
		$protocol = hkm_kses_bad_protocol_once2( $string2[0], $allowed_protocols );
		if ( 'feed:' === $protocol ) {
			if ( $count > 2 ) {
				return '';
			}
			$string = hkm_kses_bad_protocol_once( $string, $allowed_protocols, ++$count );
			if ( empty( $string ) ) {
				return $string;
			}
		}
		$string = $protocol . $string;
	}

	return $string;
}



/**
 * Sanitizes a string and removed disallowed URL protocols.
 *
 * This function removes all non-allowed protocols from the beginning of the
 * string. It ignores whitespace and the case of the letters, and it does
 * understand HTML entities. It does its work recursively, so it won't be
 * fooled by a string like `javascript:javascript:alert(57)`.
 *
 * @since 1.0.0
 *
 * @param string   $string            Content to filter bad protocols from.
 * @param string[] $allowed_protocols Array of allowed URL protocols.
 * @return string Filtered content.
 */
function hkm_kses_bad_protocol( $string, $allowed_protocols ) {
	$string     = hkm_kses_no_null( $string );
	$iterations = 0;

	do {
		$original_string = $string;
		$string          = hkm_kses_bad_protocol_once( $string, $allowed_protocols );
	} while ( $original_string != $string && ++$iterations < 6 );

	if ( $original_string != $string ) {
		return '';
	}

	return $string;
}


/**
 * Checks and cleans a URL.
 *
 * A number of characters are removed from the URL. If the URL is for displaying
 * (the default behaviour) ampersands are also replaced. The {@see 'clean_url'} filter
 * is applied to the returned cleaned URL.
 *
 * @since 2.8.0
 *
 * @param string   $url       The URL to be cleaned.
 * @param string[] $protocols Optional. An array of acceptable protocols.
 *                            Defaults to return value of hkm_allowed_protocols().
 * @param string   $_context  Private. Use esc_url_raw() for database usage.
 * @return string The cleaned URL after the {@see 'clean_url'} filter is applied.
 *                An empty string is returned if `$url` specifies a protocol other than
 *                those in `$protocols`, or if `$url` contains an empty string.
 */
function esc_url( $url, $protocols = null, $_context = 'display' ) {
	$original_url = $url;

	if ( '' === $url ) {
		return $url;
	}

	$url = str_replace( ' ', '%20', ltrim( $url ) );
	$url = preg_replace( '|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\[\]\\x80-\\xff]|i', '', $url );

	if ( '' === $url ) {
		return $url;
	}

	if ( 0 !== stripos( $url, 'mailto:' ) ) {
		$strip = array( '%0d', '%0a', '%0D', '%0A' );
		$url   = _deep_replace( $strip, $url );
	}

	$url = str_replace( ';//', '://', $url );
	/*
	 * If the URL doesn't appear to contain a scheme, we presume
	 * it needs http:// prepended (unless it's a relative link
	 * starting with /, # or ?, or a PHP file).
	 */
	if ( strpos( $url, ':' ) === false && ! in_array( $url[0], array( '/', '#', '?' ), true ) &&
		! preg_match( '/^[a-z0-9-]+?\.php/i', $url ) ) {
		$url = 'http://' . $url;
	}
	// Replace ampersands and single quotes only when displaying.
	if ( 'display' === $_context ) {
		$url = hkm_kses_normalize_entities( $url );
		$url = str_replace( '&amp;', '&#038;', $url );
		$url = str_replace( "'", '&#039;', $url );
	}

	if ( ( false !== strpos( $url, '[' ) ) || ( false !== strpos( $url, ']' ) ) ) {

		$parsed = hkm_parse_url( $url );
		$front  = '';

		if ( isset( $parsed['scheme'] ) ) {
			$front .= $parsed['scheme'] . '://';
		} elseif ( '/' === $url[0] ) {
			$front .= '//';
		}

		if ( isset( $parsed['user'] ) ) {
			$front .= $parsed['user'];
		}

		if ( isset( $parsed['pass'] ) ) {
			$front .= ':' . $parsed['pass'];
		}

		if ( isset( $parsed['user'] ) || isset( $parsed['pass'] ) ) {
			$front .= '@';
		}

		if ( isset( $parsed['host'] ) ) {
			$front .= $parsed['host'];
		}

		if ( isset( $parsed['port'] ) ) {
			$front .= ':' . $parsed['port'];
		}

		$end_dirty = str_replace( $front, '', $url );
		$end_clean = str_replace( array( '[', ']' ), array( '%5B', '%5D' ), $end_dirty );
		$url       = str_replace( $end_dirty, $end_clean, $url );

	}

	if ( '/' === $url[0] ) {
		$good_protocol_url = $url;
	} else {
		if ( ! is_array( $protocols ) ) {
			$protocols = hkm_allowed_protocols();
		}
		$good_protocol_url = hkm_kses_bad_protocol( $url, $protocols );
		if ( strtolower( $good_protocol_url ) != strtolower( $url ) ) {
			return '';
		}
	}

	/**
	 * Filters a string cleaned and escaped for output as a URL.
	 *
	 * @since 2.3.0
	 *
	 * @param string $good_protocol_url The cleaned URL to be returned.
	 * @param string $original_url      The URL prior to cleaning.
	 * @param string $_context          If 'display', replace ampersands and single quotes only.
	 */
	return  hkm_apply_filters( 'clean_url', $good_protocol_url, $original_url, $_context );
}


/**
 * Set HTTP status header.
 *
 * @since 2.0.0
 * @since 4.4.0 Added the `$description` parameter.
 *
 * @see get_status_header_desc()
 *
 * @param int    $code        HTTP status code.
 * @param string $description Optional. A custom description for the HTTP status.
 */
function status_header( $code, $description = '' ) {
	if ( ! $description ) {
		$description = get_status_header_desc( $code );
	}

	if ( empty( $description ) ) {
		return;
	}

	$protocol      = hkm_get_server_protocol();
	$status_header = "$protocol $code $description";
	if ( function_exists( ' hkm_apply_filters' ) ) {

		/**
		 * Filters an HTTP status header.
		 *
		 * @since 2.2.0
		 *
		 * @param string $status_header HTTP status header.
		 * @param int    $code          HTTP status code.
		 * @param string $description   Description for the status code.
		 * @param string $protocol      Server protocol.
		 */
		$status_header =  hkm_apply_filters( 'status_header', $status_header, $code, $description, $protocol );
	}

	if ( ! headers_sent() ) {
		header( $status_header, true, $code );
	}
}
function hkm_unslash( $value ) {
	return stripslashes_deep( $value );
}

function is_hkm_error( $thing ) {
	$is_hkm_error = ( $thing instanceof SystemException );
	if($is_hkm_error)return $is_hkm_error;

	$is_hkm_error = ( $thing instanceof Hkm_Error );

	return $is_hkm_error;
}

/**
 * Get the header information to prevent caching.
 *
 * The several different headers cover the different ways cache prevention
 * is handled by different browsers
 *
 *
 * @return array The associative array of header names and field values.
 */
function hkm_get_nocache_headers() {
	$headers = array(
		'Expires'       => 'Wed, 11 Jan 1984 05:00:00 GMT',
		'Cache-Control' => 'no-cache, must-revalidate, max-age=0',
	);
	$headers['Cache-Control'] .= ', no-store';;
	$headers['Last-Modified'] = false;
	return $headers;
}

function nocache_headers() {
	if ( headers_sent() ) {
		return;
	}

	$headers = hkm_get_nocache_headers();

	unset( $headers['Last-Modified'] );

	header_remove( 'Last-Modified' );

	foreach ( $headers as $name => $field_value ) {
		header( "{$name}: {$field_value}" );
	}
}
