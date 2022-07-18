<?php

namespace Hkm_code;

use Closure;
use Exception;
use Kint\Kint;
use Hkm_code\HTTP\URI;
use Hkm_code\HTTP\Request;
use Hkm_code\HTTP\Response;
use Hkm_code\HTTP\CLIRequest;
use Kint\Renderer\CliRenderer;
use Hkm_code\Vezirion\ServicesSystem;
use Kint\Renderer\RichRenderer;
use Hkm_code\HTTP\DownloadResponse;
use Hkm_code\Vezirion\BaseVezirion;
use Hkm_code\Pobohet\PobohetCollection;
use Hkm_code\Vezirion\Kint as vezirionKint;
use Hkm_code\Exceptions\PageNotFoundException;

class Application
{
	protected static $path;
	protected static $thiss;
	protected static $request;
	protected static $response;
	protected static $useSafeOutput = false;
	protected static $benchmark;
	protected static $config;
	protected static $method;
	protected static $router;
	protected static $controller;
	/**
	 * Output handler to use.
	 *
	 * @var string
	 */
	protected static $output;
	protected static $session;
	/**
	 * Cache expiration time
	 *
	 * @var integer
	 */
	protected static $cacheTTL = 0;
	protected static $startTime;

	const HKM_VERSION = " 0.0.2";
	private const MIN_PHP_VERSION = '7.3';
	/**
	 * Total app execution time
	 *
	 * @var float
	 */
	protected static $totalTime;

	public function __construct($config)
	{
		if (version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '<')) {
			exit(sprintf('Your PHP version must be %s or higher to run Hkm_code. Current version: %s', self::MIN_PHP_VERSION, PHP_VERSION));
		}
		self::$startTime = microtime(true);
		self::$config = $config;
		self::$thiss = $this;
		self::$session = ServicesSystem::SESSION();
	}

	protected static function INITILIZE_KINT()
	{
		// If we have KINT_DIR it means it's already loaded via composer
		if (!defined('KINT_DIR')) {
			require_once SYSTEMPATH . 'Kint_init.php';
		}


		/**
		 * Kint
		 */
		$config = new vezirionKint();

		Kint::$max_depth           = $config::$maxDepth;
		Kint::$display_called_from = $config::$displayCalledFrom;
		Kint::$expanded            = $config::$expanded;

		if (!empty($config::$plugins) && is_array($config::$plugins)) {
			Kint::$plugins = $config::$plugins;
		}

		RichRenderer::$theme  = $config::$richTheme;
		RichRenderer::$folder = $config::$richFolder;
		RichRenderer::$sort   = $config::$richSort;
		if (!empty($config::$richObjectPlugins) && is_array($config::$richObjectPlugins)) {
			RichRenderer::$object_plugins = $config::$richObjectPlugins;
		}
		if (!empty($config::$richTabPlugins) && is_array($config::$richTabPlugins)) {
			RichRenderer::$tab_plugins = $config::$richTabPlugins;
		}

		CliRenderer::$cli_colors         = $config::$cliColors;
		CliRenderer::$force_utf8         = $config::$cliForceUTF8;
		CliRenderer::$detect_width       = $config::$cliDetectWidth;
		CliRenderer::$min_terminal_width = $config::$cliMinWidth;
	}

	public static function INITIALIZE()
	{

		self::DETECT_ENVIRONMENT();
		self::BOOTSTAP_ENVIRONMENT();
		locale_set_default('en');
		date_default_timezone_set('UTC');

		self::INITILIZE_KINT();

		if (!HKM_DEBUG) {
			Kint::$enabled_mode = false;
		}
	}

	protected static function BOOTSTAP_ENVIRONMENT()
	{
		if (is_file(VEZIRIONPATH . 'Boot/' . ENVIRONMENT . '.php')) {

			require_once VEZIRIONPATH . 'Boot/' . ENVIRONMENT . '.php';

		} else {

			header('HTTP/1.1 503 Service Unavailable.', true, 503);
			echo 'The application environment is not set correctly.';
			exit;
		}
	}
	protected static function DETECT_ENVIRONMENT()
	{
		defined('ENVIRONMENT') || define('ENVIRONMENT', $_SERVER['HKM_ENVIRONMENT'] ?? 'production'); // @codeCoverageIgnore    
	}
	public static function SET_PATH(string $path)
	{
		self::$path = $path;
		return self::$thiss;
	}


	protected static function DETERMINE_PATH()
	{
		if (!empty(self::$path)) {
			return self::$path;
		}


		return method_exists(self::$request, 'GET_PATH') ? self::$request::GET_PATH() : self::$request::GET_URI()::GET_PATH();
	}

	public static function SET_REQUEST(Request $request)
	{
		self::$request = $request;

		return self::$thiss;
	}


	protected static function GET_REQUEST_OBJECT()
	{
		if (self::$request instanceof Request) {
			return;
		}

		if (hkm_is_cli() && ENVIRONMENT !== 'testing') {
			self::$request = ServicesSystem::CLI_REQUEST(self::$config);
		} else {

			self::$request = ServicesSystem::REQUEST(self::$config);
			// guess at protocol if needed
			self::$request::SET_PROTOCOL_VERSION($_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1');
		}
	}

	public static function TRY_ROUTE_IT(PobohetCollection $routes = null)
	{
		if ($routes === null) {
			$routes = ServicesSystem::ROUTES();
			$routes::SET_TRANSLATED_URI_DASH(false);
			$routes::SET_404_OVERRIDE(function ($message) {
				return hkm_view('errors/html/error_404', ['message' => $message]);
			});
			$routes::SET_AUTO_ROUTE(false);
			$config = hkm_config('App',false);
			if ($config::$filterHome)$routes::GET('/', 'Home::index', ['namespace' => APP_NAMESPACE . "\Controllers", 'filter' => 'login']);
			else $routes::GET('/', 'Home::index', ['namespace' => APP_NAMESPACE . "\Controllers"]);
			
		}

			

		self::$router = ServicesSystem::ROUTER($routes, self::$request);
		$path = self::DETERMINE_PATH();

		self::$benchmark::STOP('bootstrap');
		self::$benchmark::START('routing');


		ob_start();

		self::$controller = self::$router::HANDLE($path);

		self::$method     = self::$router::METHOD_NAME();
		if (self::$router::HAS_LOCAL()) {
			self::$request::SET_LOCALE(self::$router::GET_LOCAL()); // @phpstan-ignore-line
		}

		self::$benchmark::STOP('routing');

		return self::$router::GET_FILTER();
	}


	protected static function GET_RESPONSE_OBJECT()
	{
		self::$response = ServicesSystem::RESPONSE(self::$config);


		if (!hkm_is_cli() || ENVIRONMENT === 'testing') {
			self::$response::SET_PROTOCOL_VERSION(self::$request::GET_PROTOCOL_VERSION());
		}

		// Assume success until proven otherwise.
		self::$response::SET_STATUS_CODE(200);
	}

	protected static function START_CONTROLLER()
	{

		self::$benchmark::START('controller');
		self::$benchmark::START('controller_constructor');
		// Is it routed to a Closure?
		if (is_object(self::$controller) && (get_class(self::$controller) === 'Closure')) {

			$controller = self::$controller;
			return $controller(...self::$router::PARAMS());
		}

		// No controller specified - we don't know what to do now.
		if (empty(self::$controller)) {
			throw PageNotFoundException::FOR_EMPTY_CONTROLLER();
		}

		// Try to autoload the class
		if (!class_exists(self::$controller, true) || self::$method[0] === '_') {

			throw PageNotFoundException::FOR_CONTROLLER_NOT_FOUND(self::$controller, self::$method);
		}
	}

	public static function CACHE(int $time)
	{
		static::$cacheTTL = $time;
	}
	public static function CREATE_CONTROLLER()
	{

		$class = new self::$controller();
		
		$class::INIT_CONTROLLER(self::$request, self::$response, ServicesSystem::LOGGER());

		self::$benchmark::STOP('controller_constructor');

		return $class;
	}
	protected static function RUN_CONTROLLER($class)
	{
		// If this is a console request then use the input segments as parameters
		$params = defined('SYSTEM') ? self::$request::GET_SEGMENTS() : self::$router::PARAMS(); // @phpstan-ignore-line
		
		if (method_exists($class, '_REMAP')) {
			
			$output = $class::_REMAP(self::$method, ...$params);
		} else {
			
			
			$METH = strtoupper(self::$method);

			$output = $class::$METH(...$params);

			
		}


		self::$benchmark::STOP('controller');

		return $output;
	}

	protected static function  GATHER_OUTPUT($cacheConfig = null, $returned = null)
	{
		self::$output = ob_get_contents();

		// If buffering is not null.
		// Clean (erase) the output buffer and turn off output buffering
		if (ob_get_length()) {
			ob_end_clean();
		}


		if ($returned instanceof DownloadResponse) {
			self::$response = $returned;
			return;
		}
		// If the controller returned a response object,
		// we need to grab the body from it so it can
		// be added to anything else that might have been
		// echoed already.
		// We also need to save the instance locally
		// so that any status code changes, etc, take place.
		if ($returned instanceof Response) {
			self::$response = $returned;
			$returned       = $returned::GET_BODY();
		}

		if (is_string($returned)) {

			self::$output .= $returned;
		}


		// Cache it without the performance metrics replaced
		// so that we can have live speed updates along the way.
		if (static::$cacheTTL > 0) {

			self::CACHE_PAGE($cacheConfig);
		}

		self::$output = self::DISPLAY_PERFORMANCE_METRICS(self::$output);

		self::$response::SET_BODY(self::$output);
	}

	public static function DISPLAY_PERFORMANCE_METRICS(string $output): string
	{
		self::$totalTime = self::$benchmark::GET_ELAPSED_TIME('total_execution');

		return str_replace('{elapsed_time}', (string) self::$totalTime, $output);
	}

	public static function CACHE_PAGE($config)
	{
		$headers = [];
		foreach (self::$response::HEADERS() as $header) {
			$headers[$header::GET_NAME()] = $header::GET_VALUE_LINE();
		}
		return hkm_cache()::SAVE(self::GENERATE_CACHE_NAME($config), serialize(['headers' => $headers, 'output' => self::$output]), static::$cacheTTL);
	}
	protected static function GENERATE_CACHE_NAME($config): string
	{
		if (self::$request instanceof CLIRequest) {
			return md5(self::$request::GET_PATH());
		}

		$uri = self::$request::GET_URI();

		if ($config::$cacheQueryString) {
			$name = URI::CREATE_URI_STRING($uri::GET_SCHEME(), $uri::GET_AUTHORITY(), $uri::GET_PATH(), $uri::GET_QUERY());
		} else {
			$name = URI::CREATE_URI_STRING($uri::GET_SCHEME(), $uri::GET_AUTHORITY(), $uri::GET_PATH());
		}

		return md5($name);
	}
	public static function STORE_PREVIOUS_URL($uri)
	{
		// Ignore CLI requests
		if (hkm_is_cli()) {
			return;
		}
		// Ignore AJAX requests
		if (method_exists(self::$request, 'IS_AJAX') && self::$request::IS_AJAX()) {
			return;
		}

		// This is mainly needed during testing...
		if (is_string($uri)) {
			$uri = new URI($uri);
		}
		self::$session->set('_hkm_previous_url',URI::CREATE_URI_STRING($uri::GET_SCHEME(), $uri::GET_AUTHORITY(), $uri::GET_PATH(), $uri::GET_QUERY(), $uri::GET_FRAGMENT()));
		
	}
	public static function HANDLE_REQUEST($routes, BaseVezirion $cacheConfig, bool $returnResponse = false)
	{
		

		$routeFilter = self::TRY_ROUTE_IT($routes);
		$filters = ServicesSystem::FILTERS();


		if (!is_null($routeFilter)) {

			$filters::ENABLE_FILTER($routeFilter, 'before');
			$filters::ENABLE_FILTER($routeFilter, 'after');
		}

		$uri = self::DETERMINE_PATH();
		
		if (!defined('SYSTEM')) {
			if(!hkm_is_cli()){
				$possibleResponse = $filters::RUN($uri, 'before');


				// If a ResponseInterface instance is returned then send it back to the client and stop
				if ($possibleResponse instanceof Response) {
					hkm_helper("url");

					self::STORE_PREVIOUS_URL(hkm_current_url(true));

					
					unset($uri);
					self::$session->save();
					return $returnResponse ? $possibleResponse : $possibleResponse::PRETEND(self::$useSafeOutput)::SEND();
				}

			

			if ($possibleResponse instanceof Request) {
				self::$request = $possibleResponse;
			}

		  }
		}


		$returned = self::START_CONTROLLER();

		if (!is_callable(self::$controller)) {
			
			$controller = self::CREATE_CONTROLLER();



			if (!method_exists($controller, '_REMAP') && !is_callable([$controller, strtoupper(self::$method)], false)) {

				throw PageNotFoundException::FOR_METHOD_NOT_FOUND(self::$method);
			}


			$returned = self::RUN_CONTROLLER($controller);
		} else {


			self::$benchmark::STOP('controller_constructor');
			self::$benchmark::STOP('controller');
		}



		

		self::GATHER_OUTPUT($cacheConfig, $returned);

		if (!defined('SYSTEM')) {


			$filters::SET_RESPONSE(self::$response);
			// Run "after" filters
			$response = $filters::RUN($uri, 'after');
		} else {
			$response = self::$response;

			// Set response code for CLI command failures
			if (is_numeric($returned) || $returned === false) {
				$response::SET_STATUS_CODE(400);
			}
		}

		if ($response instanceof Response) {
			self::$response = $response;
		}

		

		// Save our current URI as the previous URI in the session
		// for safer, more accurate use with `previous_url()` helper function.
		hkm_helper("url");

		self::STORE_PREVIOUS_URL(hkm_current_url(true));

		
		unset($uri);
		if(!hkm_is_cli()){
			self::$session->save();
		}

		
		if (!$returnResponse) {
			self::SEND_RESPONSE();

		}

		

		return self::$response;

	}

	public static function DISPLAY_CACHE($config)
	{

		if ($cachedResponse = hkm_cache()::GET(self::GENERATE_CACHE_NAME($config))) {
			$cachedResponse = unserialize($cachedResponse);
			if (!is_array($cachedResponse) || !isset($cachedResponse['output']) || !isset($cachedResponse['headers'])) {
				throw new Exception('Error unserializing page cache');
			}

			$headers = $cachedResponse['headers'];
			$output  = $cachedResponse['output'];

			// Clear all default headers
			foreach (array_keys(self::$response::HEADERS()) as $key) {
				self::$response::REMOVE_HEADER($key);
			}

			// Set cached headers
			foreach ($headers as $name => $value) {
				self::$response::SET_HEADER($name, $value);
			}

			$output = self::DISPLAY_PERFORMANCE_METRICS($output);
			self::$response::SET_BODY($output);

			return self::$response;
		}

		return false;
	}


	protected static function SEND_RESPONSE()
	{
		self::$response::PRETEND(self::$useSafeOutput)::SEND();
	}

	protected static function FORCE_SECURE_ACCESS($duration = 31536000)
	{
		if (self::$config::$forceGlobalSecureRequests !== true) {
			return;
		}

		hkm_force_https($duration, self::$request, self::$response);
	}
	public static function SPOOF_REQUEST_METHOD()
	{
		// Only works with POSTED forms
		if (self::$request::GET_METHOD() !== 'post') {
			return;
		}

		$method = self::$request::GET_POST('_method'); // @phpstan-ignore-line

		if (empty($method)) {
			return;
		}

		self::$request = self::$request::SET_METHOD($method);
	}

	protected static function CALL_EXIT($code)
	{
		// @codeCoverageIgnoreStart
		exit($code);
		// @codeCoverageIgnoreEnd
	}
	protected static function DISPLAY_404_ERRORS(PageNotFoundException $e)
	{
		// Is there a 404 Override available?
		if ($override = self::$router::GET_404_OVERRIDE()) {

			if ($override instanceof Closure) {


				echo $override($e->getMessage());
			} elseif (is_array($override)) {
				self::$benchmark::START('controller');
				self::$benchmark::START('controller_constructor');

				self::$controller = $override[0];
				self::$method     = $override[1];
				$controller = self::CREATE_CONTROLLER();
				self::RUN_CONTROLLER($controller);
			}

			unset($override);

			$cacheConfig = hkm_config('Cache');
			self::GATHER_OUTPUT($cacheConfig);
			self::SEND_RESPONSE();

			return;
		}


		self::$response::SET_STATUS_CODE($e->getCode());

		if (ENVIRONMENT !== 'testing') {
			// @codeCoverageIgnoreStart
			if (ob_get_level() > 0) {
				ob_end_flush();
			}
			// @codeCoverageIgnoreEnd
		}
		// When testing, one is for phpunit, another is for test case.
		elseif (ob_get_level() > 2) {
			ob_end_flush(); // @codeCoverageIgnore
		}

		throw PageNotFoundException::FOR_PAGE_NOT_FOUND(ENVIRONMENT !== 'production' || hkm_is_cli() ? $e->getMessage() : '');
	}
}
