<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@hakrichteam.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\HTTP;

use Hkm_code\Exceptions\HTTP\HTTPException;
use Hkm_code\HTTP\Files\FileCollection;
use Hkm_code\HTTP\Files\UploadedFile;
use Hkm_code\Vezirion\ServicesSystem;
use InvalidArgumentException;
use Locale;

/**
 * Class IncomingRequest
 *
 * Represents an incoming, getServer-side HTTP request.
 *
 * Per the HTTP specification, this interface includes properties for
 * each of the following:
 *
 * - Protocol version
 * - HTTP method
 * - URI
 * - Headers
 * - Message body
 *
 * Additionally, it encapsulates all data as it has arrived to the
 * lication from the CGI and/or PHP environment, including:
 *
 * - The values represented in $_SERVER.
 * - Any cookies provided (generally via $_COOKIE)
 * - Query string arguments (generally via $_GET, or as parsed via parse_str())
 * - Upload files, if any (as represented by $_FILES)
 * - Deserialized body binds (generally from $_POST)
 */
class IncomingRequest extends Request
{
	/**
	 * Enable CSRF flag
	 *
	 * Enables a CSRF cookie token to be set.
	 * Set automatically based on Config setting.
	 *
	 * @var boolean
	 */
	protected static $enableCSRF = false;

	/**
	 * The URI for this request.
	 *
	 * Note: This WILL NOT match the actual URL in the browser since for
	 * everything this cares about (and the router, etc) is the portion
	 * AFTER the script name. So, if hosted in a sub-folder this will
	 * ear different than actual URL. If you need that use getPath().
	 *
	 * @var URI
	 */
	public static $uri;

	/**
	 * The detected path (relative to SCRIPT_NAME).
	 *
	 * Note:hkm_current_url() uses this to build its URI,
	 * so this becomes the source for the "current URL"
	 * when working with the share request instance.
	 *
	 * @var string|null
	 */
	protected static $path;

	/**
	 * File collection
	 *
	 * @var FileCollection|null
	 */
	protected static $files;

	/**
	 * Negotiator
	 *
	 * @var Negotiate|null
	 */
	protected static $negotiator;

	/**
	 * The default Locale this request
	 * should operate under.
	 *
	 * @var string
	 */
	protected static $defaultLocale;

	/**
	 * The current locale of the lication.
	 * Default value is set in .php
	 *
	 * @var string
	 */
	protected static $locale;

	/**
	 * Stores the valid locale codes.
	 *
	 * @var array
	 */
	protected static $validLocales = [];

	/**
	 * Configuration settings.
	 *
	 * @var 
	 */
	public static $config;

	/**
	 * Holds the old data from a redirect.
	 *
	 * @var array
	 */
	protected static $oldInput = [];

	/**
	 * The user agent this request is from.
	 *
	 * @var UserAgent
	 */
	protected static $userAgent;

	//--------------------------------------------------------------------
	/**
	 * Constructor
	 *
	 * @param          $config
	 * @param URI         $uri
	 * @param string|null $body
	 * @param UserAgent   $userAgent
	 */
	public function __construct( $config, URI $uri = null, $body = 'php://input', UserAgent $userAgent = null)
	{
		
		if (empty($uri) || empty($userAgent))
		{
			throw new InvalidArgumentException('You must supply the parameters: uri, userAgent.');
		}

		// Get our body from php://input
		if ($body === 'php://input')
		{
			$body = file_get_contents('php://input');
		}



		self::$config       = $config;
		self::$uri          = $uri;
		self::$body         = ! empty($body) ? $body : null;
		self::$userAgent    = $userAgent;
		self::$validLocales = $config::$supportedLocales;

		parent::__construct($config);

		self::POPULATE_HEADERS();

		self::DETECT_URI($config::$uriProtocol, $config::$baseURL);

		self::DETECT_LOCALE($config);
	}

	public static function GET_SEGMENTS()
	{
		return self::$uri::GET_SEGMENTS();
	}


	//--------------------------------------------------------------------

	/**
	 * Handles setting up the locale, perhaps auto-detecting through
	 * content negotiation.
	 *
	 * @param  $config
	 */
	public static function DETECT_LOCALE($config)
	{
		self::$locale = self::$defaultLocale = $config::$defaultLocale;

		if (! $config::$negotiateLocale)
		{
			return;
		}

		self::SET_LOCALE(self::NEGOTIATE('language', $config::$supportedLocales));
	}

	/**
	 * Sets up our URI object based on the information we have. This is
	 * either provided by the user in the baseURL Config setting, or
	 * determined from the environment as needed.
	 *
	 * @param string $protocol
	 * @param string $baseURL
	 */
	protected static function DETECT_URI(string $protocol, string $baseURL)
	{
		// Passing the config is unnecessary but left for legacy purposes
		$config          = clone self::$config;
		$config::$baseURL = $baseURL;


		self::SET_PATH(self::DETECT_PATH($protocol), $config);
	}

	/**
	 * Detects the relative path based on
	 * the URIProtocol Config setting.
	 *
	 * @param string $protocol
	 *
	 * @return string
	 */
	public static function DETECT_PATH(string $protocol = ''): string
	{
		if (empty($protocol))
		{
			$protocol = 'REQUEST_URI';
		}

		switch ($protocol)
		{
			case 'REQUEST_URI':
				self::$path = self::PARSE_REQUEST_URI();
				break;
			case 'QUERY_STRING':
				self::$path = self::PARSE_QUERY_STRING();
				break;
			case 'PATH_INFO':
			default:
				self::$path = self::FETCH_GLOBAL('server', $protocol) ?? self::PARSE_REQUEST_URI();
				break;
		}

		return self::$path;
	}

	//--------------------------------------------------------------------

	/**
	 * Will parse the REQUEST_URI and automatically detect the URI from it,
	 * fixing the query string if necessary.
	 *
	 * @return string The URI it found.
	 */
	protected static function PARSE_REQUEST_URI(): string
	{
		if (! isset($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME']))
		{
			return '';
		}

		// parse_url() returns false if no host is present, but the path or query string
		// contains a colon followed by a number. So we attach a dummy host since
		// REQUEST_URI does not include the host. This allows us to parse out the query string and path.
		$parts = parse_url('http://dummy' . $_SERVER['REQUEST_URI']);
		$query = $parts['query'] ?? '';
		$uri   = $parts['path'] ?? '';

		// Strip the SCRIPT_NAME path from the URI
		if ($uri !== '' && isset($_SERVER['SCRIPT_NAME'][0]) && pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_EXTENSION) === 'php')
		{
			// Compare each segment, dropping them until there is no match
			$segments = $keep = explode('/', $uri);
			foreach (explode('/', $_SERVER['SCRIPT_NAME']) as $i => $segment)
			{
				// If these segments are not the same then we're done
				if (! isset($segments[$i]) || $segment !== $segments[$i])
				{
					break;
				}

				array_shift($keep);
			}

			$uri = implode('/', $keep);
		}

		// This section ensures that even on servers that require the URI to contain the query string (Nginx) a correct
		// URI is found, and also fixes the QUERY_STRING getServer var and $_GET array.
		if (trim($uri, '/') === '' && strncmp($query, '/', 1) === 0)
		{
			$query                   = explode('?', $query, 2);
			$uri                     = $query[0];
			$_SERVER['QUERY_STRING'] = $query[1] ?? '';
		}
		else
		{
			$_SERVER['QUERY_STRING'] = $query;
		}

		// Update our globals for values likely to been have changed
		parse_str($_SERVER['QUERY_STRING'], $_GET);
		self::POPULATE_GLOBALS('server');
		self::POPULATE_GLOBALS('get');

		$uri = URI::REMOVE_DOT_SEGMENTS($uri);

		return ($uri === '/' || $uri === '') ? '/' : ltrim($uri, '/');
	}

	/**
	 * Parse QUERY_STRING
	 *
	 * Will parse QUERY_STRING and automatically detect the URI from it.
	 *
	 * @return string
	 */
	protected static function PARSE_QUERY_STRING(): string
	{
		$uri = $_SERVER['QUERY_STRING'] ?? @getenv('QUERY_STRING');

		if (trim($uri, '/') === '')
		{
			return '';
		}

		if (strncmp($uri, '/', 1) === 0)
		{
			$uri                     = explode('?', $uri, 2);
			$_SERVER['QUERY_STRING'] = $uri[1] ?? '';
			$uri                     = $uri[0];
		}

		// Update our globals for values likely to been have changed
		parse_str($_SERVER['QUERY_STRING'], $_GET);
		self::POPULATE_GLOBALS('server');
		self::POPULATE_GLOBALS('get');

		$uri = URI::REMOVE_DOT_SEGMENTS($uri);

		return ($uri === '/' || $uri === '') ? '/' : ltrim($uri, '/');
	}

	//--------------------------------------------------------------------

	/**
	 * Provides a convenient way to work with the Negotiate class
	 * for content negotiation.
	 *
	 * @param string  $type
	 * @param array   $supported
	 * @param boolean $strictMatch
	 *
	 * @return string
	 */
	public static function NEGOTIATE(string $type, array $supported, bool $strictMatch = false): string
	{
		if (is_null(self::$negotiator))
		{
			self::$negotiator = ServicesSystem::NEGOTIATOR(self::$thiss, true);
		}

		switch (strtolower($type))
		{
			case 'media':
				return self::$negotiator::MEDIA($supported, $strictMatch);
			case 'charset':
				return self::$negotiator::CHARSET($supported);
			case 'encoding':
				return self::$negotiator::ENCODING($supported);
			case 'language':
				return self::$negotiator::LANGUAGE($supported);
		}

		throw HTTPException::FOR_INVALID_NEGOTIATION_TYPE($type);
	}

	


	//--------------------------------------------------------------------

	/**
	 * Sets the relative path and updates the URI object.
	 * Note: Sincehkm_current_url() accesses the shared request
	 * instance, this can be used to change the "current URL"
	 * for testing.
	 *
	 * @param string $path   URI path relative to SCRIPT_NAME
	 * @param     $config Optional alternate config to use
	 *
	 * @return $this
	 */
	public static function SET_PATH(string $path,  $config = null)
	{
		self::$path = $path;
		self::$uri::SET_PATH($path);

		$config = $config ?? self::$config;

		// It's possible the user forgot a trailing slash on their
		// baseURL, so let's help them out.
		$baseURL = $config::$baseURL === '' ? $config::$baseURL : rtrim($config::$baseURL, '/ ') . '/';

		// Based on our baseURL provided by the developer
		// set our current domain name, scheme
		if ($baseURL !== '')
		{
			self::$uri::SET_SCHEME(parse_url($baseURL, PHP_URL_SCHEME));

			self::$uri::SET_HOST(parse_url($baseURL, PHP_URL_HOST));
			self::$uri::SET_PORT(parse_url($baseURL, PHP_URL_PORT));

			// Ensure we have any query vars
			self::$uri::SET_QUERY($_SERVER['QUERY_STRING'] ?? '');

			// Check if the baseURL scheme needs to be coerced into its secure version
			if ($config::$forceGlobalSecureRequests && self::$uri::GET_SCHEME() === 'http')
			{
				self::$uri::SET_SCHEME('https');
			}
		}

		// @codeCoverageIgnoreStart
		elseif (! hkm_is_cli())
		{
			die('You have an empty or invalid base URL. The baseURL value must be set in .php, or through the .env file.');
		}
		// @codeCoverageIgnoreEnd

		return self::$thiss;
	}

	/**
	 * Returns the path relative to SCRIPT_NAME,
	 * running detection as necessary.
	 *
	 * @return string
	 */
	public static function GET_PATH(): string
	{
		if (is_null(self::$path))
		{
			self::DETECT_PATH(self::$config::$uriProtocol);
		}

		return self::$path;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the locale string for this request.
	 *
	 * @param string $locale
	 *
	 * @return IncomingRequest
	 */
	public static function SET_LOCALE(string $locale)
	{
		// If it's not a valid locale, set it
		// to the default locale for the site.
		if (! in_array($locale, self::$validLocales, true))
		{
			$locale = self::$defaultLocale;
		}

		self::$locale = $locale;
		Locale::setDefault($locale);

		return self::$thiss;
	}

	/**
	 * Gets the current locale, with a fallback to the default
	 * locale if none is set.
	 *
	 * @return string
	 */
	public static function GET_LOCALE(): string
	{
		return self::$locale ?? self::$defaultLocale;
	}

	/**
	 * Returns the default locale as set in .php
	 *
	 * @return string
	 */
	public static function GET_DEFAULT_LOCALE(): string
	{
		return self::$defaultLocale;
	}

	//--------------------------------------------------------------------

	/**
	 * Fetch an item from JSON input stream with fallback to $_REQUEST object. This is the simplest way
	 * to grab data from the request object and can be used in lieu of the
	 * other get* methods in most cases.
	 *
	 * @param string|array|null $index
	 * @param integer|null      $filter Filter constant
	 * @param mixed             $flags
	 *
	 * @return mixed
	 */
	public static function GET_VAR($index = null, $filter = null, $flags = null)
	{
		if (strpos(self::GET_HEADER_LINE('Content-Type'), 'lication/json') !== false && ! is_null(self::$body))
		{
			if (is_null($index))
			{
				return self::GET_JSON();
			}

			if (is_array($index))
			{
				$output = [];
				foreach ($index as $key)
				{
					$output[$key] = self::GET_JSON_VAR($key, false, $filter, $flags);
				}
				return $output;
			}

			return self::GET_JSON_VAR($index, false, $filter, $flags);
		}
		return self::FETCH_GLOBAL('request', $index, $filter, $flags);
	}

	//--------------------------------------------------------------------

	/**
	 * A convenience method that grabs the raw input stream and decodes
	 * the JSON into an array.
	 *
	 * If $assoc == true, then all objects in the response will be converted
	 * to associative arrays.
	 *
	 * @param boolean $assoc   Whether to return objects as associative arrays
	 * @param integer $depth   How many levels deep to decode
	 * @param integer $options Bitmask of options
	 *
	 * @see http://php.net/manual/en/function.json-decode.php
	 *
	 * @return mixed
	 */
	public static function GET_JSON(bool $assoc = false, int $depth = 512, int $options = 0)
	{
		return json_decode(self::$body, $assoc, $depth, $options);
	}

	/**
	 * Get a specific variable from a JSON input stream
	 *
	 * @param  string             $index  The variable that you want which can use dot syntax for getting specific values.
	 * @param  boolean            $assoc  If true, return the result as an associative array.
	 * @param  integer|null       $filter Filter Constant
	 * @param  array|integer|null $flags  Option
	 * @return mixed
	 */
	public static function GET_JSON_VAR(string $index, bool $assoc = false, ?int $filter = null, $flags = null)
	{
		hkm_helper('array');

		$data = hkm_dot_array_search($index, self::GET_JSON(true));

		if (! is_array($data))
		{
			$filter = $filter ?? FILTER_DEFAULT;
			$flags  = is_array($flags) ? $flags : (is_numeric($flags) ? (int) $flags : 0);

			return filter_var($data, $filter, $flags);
		}

		if (! $assoc)
		{
			return json_decode(json_encode($data));
		}

		return $data;
	}

	//--------------------------------------------------------------------

	/**
	 * A convenience method that grabs the raw input stream(send method in PUT, PATCH, DELETE) and decodes
	 * the String into an array.
	 *
	 * @return mixed
	 */
	public static function GET_RAW_INPUT()
	{
		parse_str(self::$body, $output);

		return $output;
	}

	//--------------------------------------------------------------------

	/**
	 * Fetch an item from GET data.
	 *
	 * @param string|array|null $index  Index for item to fetch from $_GET.
	 * @param integer|null      $filter A filter name to ly.
	 * @param mixed|null        $flags
	 *
	 * @return mixed
	 */
	public static function GET_GET($index = null, $filter = null, $flags = null)
	{
		return self::FETCH_GLOBAL('get', $index, $filter, $flags);
	}

	//--------------------------------------------------------------------

	/**
	 * Fetch an item from POST.
	 *
	 * @param string|array|null $index  Index for item to fetch from $_POST.
	 * @param integer|null      $filter A filter name to ly
	 * @param mixed             $flags
	 *
	 * @return mixed
	 */
	public static function GET_POST($index = null, $filter = null, $flags = null)
	{
		return self::FETCH_GLOBAL('post', $index, $filter, $flags);
	}

	//--------------------------------------------------------------------

	/**
	 * Fetch an item from POST data with fallback to GET.
	 *
	 * @param string|array|null $index  Index for item to fetch from $_POST or $_GET
	 * @param integer|null      $filter A filter name to ly
	 * @param mixed             $flags
	 *
	 * @return mixed
	 */
	public static function GET_POST_GET($index = null, $filter = null, $flags = null)
	{
		// Use $_POST directly here, since filter_has_var only
		// checks the initial POST data, not anything that might
		// have been added since.
		return isset($_POST[$index]) ? self::GET_POST($index, $filter, $flags) : (isset($_GET[$index]) ? self::GET_GET($index, $filter, $flags) : self::GET_POST($index, $filter, $flags));
	}

	//--------------------------------------------------------------------

	/**
	 * Fetch an item from GET data with fallback to POST.
	 *
	 * @param string|array|null $index  Index for item to be fetched from $_GET or $_POST
	 * @param integer|null      $filter A filter name to ly
	 * @param mixed             $flags
	 *
	 * @return mixed
	 */
	public static function GET_GET_POST($index = null, $filter = null, $flags = null)
	{
		// Use $_GET directly here, since filter_has_var only
		// checks the initial GET data, not anything that might
		// have been added since.
		return isset($_GET[$index]) ? self::GET_GET($index, $filter, $flags) : (isset($_POST[$index]) ? self::GET_POST($index, $filter, $flags) : self::GET_GET($index, $filter, $flags));
	}

	//--------------------------------------------------------------------

	/**
	 * Fetch an item from the COOKIE array.
	 *
	 * @param string|array|null $index  Index for item to be fetched from $_COOKIE
	 * @param integer|null      $filter A filter name to be lied
	 * @param mixed             $flags
	 *
	 * @return mixed
	 */
	public static function GET_COOKIE($index = null, $filter = null, $flags = null)
	{
		return self::FETCH_GLOBAL('cookie', $index, $filter, $flags);
	}

	//--------------------------------------------------------------------
	/**
	 * Fetch the user agent string
	 *
	 * @return UserAgent
	 */
	public static function GET_USER_AGENT()
	{
		return self::$userAgent;
	}

	//--------------------------------------------------------------------

	/**
	 * Attempts to get old Input data that has been flashed to the session
	 * with redirect_with_input(). It first checks for the data in the old
	 * POST data, then the old GET data and finally check for dot arrays
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public static function GET_OLD_INPUT(string $key)
	{
		$SESSION = ServicesSystem::SESSION();


		// If the session hasn't been started, or no
		// data was previously saved, we're done.
		if (empty($SESSION->get('_hkm_old_input')))
		{
			return null;
		}

		// Check for the value in the POST array first.
		if (isset($SESSION->get('_hkm_old_input')['post'][$key]))
		{
			return $SESSION->get('_hkm_old_input')['post'][$key];
		}

		// Next check in the GET array.
		if (isset($SESSION->get('_hkm_old_input')['get'][$key]))
		{
			return $SESSION->get('_hkm_old_input')['get'][$key];
		}

		hkm_helper('array');

		// Check for an array value in POST.
		if (isset($SESSION->get('_hkm_old_input')['post']))
		{
			$value = hkm_dot_array_search($key, $SESSION->get('_hkm_old_input')['post']);
			if (! is_null($value))
			{
				return $value;
			}
		}

		// Check for an array value in GET.
		if (isset($SESSION->get('_hkm_old_input')['get']))
		{
			$value = hkm_dot_array_search($key, $SESSION->get('_hkm_old_input')['get']);
			if (! is_null($value))
			{
				return $value;
			}
		}

		// requested session key not found
		return null;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an array of all files that have been uploaded with this
	 * request. Each file is represented by an UploadedFile instance.
	 *
	 * @return array
	 */
	public static function GET_FILES(): array
	{
		if (is_null(self::$files))
		{
			self::$files = new FileCollection();
		}

		return self::$files::ALL(); // return all files
	}

	/**
	 * Verify if a file exist, by the name of the input field used to upload it, in the collection
	 * of uploaded files and if is have been uploaded with multiple option.
	 *
	 * @param string $fileID
	 *
	 * @return array|null
	 */
	public static function GET_FILE_MULTIPLE(string $fileID)
	{
		if (is_null(self::$files))
		{
			self::$files = new FileCollection();
		}

		return self::$files::GET_FILE_MULTIPLE($fileID);
	}

	/**
	 * Retrieves a single file by the name of the input field used
	 * to upload it.
	 *
	 * @param string $fileID
	 *
	 * @return UploadedFile|null
	 */
	public static function GET_FILE(string $fileID)
	{
		if (is_null(self::$files))
		{
			self::$files = new FileCollection();
		}

		return self::$files::GET_FILE($fileID);
	}

	//--------------------------------------------------------------------

	/**
	 * Remove relative directory (../) and multi slashes (///)
	 *
	 * Do some final cleaning of the URI and return it, currently only used in static::_parse_request_uri()
	 *
	 * @param string $uri
	 *
	 * @return string
	 *
	 * @deprecated Use URI::REMOVE_DOT_SEGMENTS() directly
	 */
	protected static function REMOVE_RELATIVE_DIRECTORY(string $uri): string
	{
		$uri = URI::REMOVE_DOT_SEGMENTS($uri);

		return $uri === '/' ? $uri : ltrim($uri, '/');
	}
}
