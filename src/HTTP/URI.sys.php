<?php


namespace Hkm_code\HTTP;

use InvalidArgumentException;
use Hkm_code\Exceptions\HTTP\HTTPException;

/**
 * Abstraction for a uniform resource identifier (URI).
 */
class URI
{
	/**
	 * Sub-delimiters used in query strings and fragments.
	 */
	const CHAR_SUB_DELIMS = '!\$&\'\(\)\*\+,;=';

	/**
	 * Unreserved characters used in paths, query strings, and fragments.
	 */
	const CHAR_UNRESERVED = 'a-zA-Z0-9_\-\.~';

	/**
	 * Current URI string
	 *
	 * @var string
	 */
	protected static $uriString;

	/**
	 * List of URI segments.
	 *
	 * Starts at 1 instead of 0
	 *
	 * @var array
	 */
	protected static $segments = [];

	/**
	 * The URI Scheme.
	 *
	 * @var string
	 */
	protected static $scheme = 'http';

	/**
	 * URI User Info
	 *
	 * @var string
	 */
	protected static $user;

	/**
	 * URI User Password
	 *
	 * @var string
	 */
	protected static $password;

	/**
	 * URI Host
	 *
	 * @var string
	 */
	protected static $host;

	/**
	 * URI Port
	 *
	 * @var integer
	 */
	protected static $port;

	/**
	 * URI path.
	 *
	 * @var string
	 */
	protected static $path;

	/**
	 * The name of any fragment.
	 *
	 * @var string
	 */
	protected static $fragment = '';

	/**
	 * The query string.
	 *
	 * @var array
	 */
	protected static $query = [];

	/**
	 * Default schemes/ports.
	 *
	 * @var array
	 */
	protected static $defaultPorts = [
		'http'  => 80,
		'https' => 443,
		'ftp'   => 21,
		'sftp'  => 22,
	];

	/**
	 * Whether passwords should be shown in userInfo/authority calls.
	 * Default to false because URIs often show up in logs
	 *
	 * @var boolean
	 */
	protected static $showPassword = false;
	protected static $thiss ;

	/**
	 * If true, will continue instead of throwing exceptions.
	 *
	 * @var boolean
	 */
	protected static $silent = false;
	protected static $config ;

	/**
	 * If true, will use raw query string.
	 *
	 * @var boolean
	 */
	protected static $rawQueryString = false;

	//--------------------------------------------------------------------

	/**
	 * Builds a representation of the string from the component parts.
	 *
	 * @param string $scheme
	 * @param string $authority
	 * @param string $path
	 * @param string $query
	 * @param string $fragment
	 *
	 * @return string
	 */
	public static function CREATE_URI_STRING(string $scheme = null, string $authority = null, string $path = null, string $query = null, string $fragment = null): string
	{
		$uri = '';
		if (! empty($scheme))
		{
			$uri .= $scheme . '://';
		}


		if (! empty($authority))
		{
			$uri .= $authority;
		}

		if (isset($path) && $path !== '')
		{
			$uri .= substr($uri, -1, 1) !== '/' ? '/' . ltrim($path, '/') : ltrim($path, '/');
		}

		if ($query)
		{
			$uri .= '?' . $query;
		}

		if ($fragment)
		{
			$uri .= '#' . $fragment;
		}

		return $uri;
	}

	/**
	 * Used when resolving and merging paths to correctly interpret and
	 * remove single and double dot segments from the path per
	 * RFC 3986 Section 5.2.4
	 *
	 * @see http://tools.ietf.org/html/rfc3986#section-5.2.4
	 *
	 * @param string $path
	 *
	 * @return   string
	 * @internal
	 */
	public static function REMOVE_DOT_SEGMENTS(string $path): string
	{
		if ($path === '' || $path === '/')
		{
			return $path;
		}

		$output = [];

		$input = explode('/', $path);

		if ($input[0] === '')
		{
			unset($input[0]);
			$input = array_values($input);
		}

		// This is not a perfect representation of the
		// RFC, but matches most cases and is pretty
		// much what Guzzle uses. Should be good enough
		// for almost every real use case.
		foreach ($input as $segment)
		{
			if ($segment === '..')
			{
				array_pop($output);
			}
			elseif ($segment !== '.' && $segment !== '')
			{
				$output[] = $segment;
			}
		}

		$output = implode('/', $output);
		$output = trim($output, '/ ');

		// Add leading slash if necessary
		if (strpos($path, '/') === 0)
		{
			$output = '/' . $output;
		}

		// Add trailing slash if necessary
		if ($output !== '/' && substr($path, -1, 1) === '/')
		{
			$output .= '/';
		}

		return $output;
	}

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 *
	 * @param string $uri
	 *
	 * @throws InvalidArgumentException
	 */
	public  function __construct(string $uri = null)
	{
		self::$config = hkm_config('App');
		self::$thiss = $this;
		if (! is_null($uri))
		{
			self::SET_URI($uri);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * If $silent == true, then will not throw exceptions and will
	 * attempt to continue gracefully.
	 *
	 * @param boolean $silent
	 *
	 * @return URI
	 */
	public static function SET_SILENT(bool $silent = true)
	{
		self::$silent = $silent;

		return self::$thiss;
	}

	//--------------------------------------------------------------------

	/**
	 * If $raw == true, then will use PARSE_STR() method
	 * instead of native parse_str() function.
	 *
	 * @param boolean $raw
	 *
	 * @return URI
	 */
	public static function USE_RAW_QUERY_STRING(bool $raw = true)
	{
		self::$rawQueryString = $raw;

		return self::$thiss;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets and overwrites any current URI information.
	 *
	 * @param string|null $uri
	 *
	 * @return URI
	 */
	public static function SET_URI(string $uri = null)
	{
		if (! is_null($uri))
		{
			$parts = parse_url($uri);

			if ($parts === false)
			{
				if (self::$silent)
				{
					return self::$thiss;
				}

                throw HTTPException::FOR_UNABLE_TO_PARSE_URI($uri);
			}

			self::APPLY_PARTS($parts);
		}

		return self::$thiss;
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieve the scheme component of the URI.
	 *
	 * If no scheme is present, this method MUST return an empty string.
	 *
	 * The value returned MUST be normalized to lowercase, per RFC 3986
	 * Section 3.1.
	 *
	 * The trailing ":" character is not part of the scheme and MUST NOT be
	 * added.
	 *
	 * @see    https://tools.ietf.org/html/rfc3986#section-3.1
	 * @return string The URI scheme.
	 */
	public static function GET_SCHEME(): string
	{
		return self::$scheme;
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieve the authority component of the URI.
	 *
	 * If no authority information is present, this method MUST return an empty
	 * string.
	 *
	 * The authority syntax of the URI is:
	 *
	 * <pre>
	 * [user-info@]host[:port]
	 * </pre>
	 *
	 * If the port component is not set or is the standard port for the current
	 * scheme, it SHOULD NOT be included.
	 *
	 * @see https://tools.ietf.org/html/rfc3986#section-3.2
	 *
	 * @param boolean $ignorePort
	 *
	 * @return string The URI authority, in "[user-info@]host[:port]" format.
	 */
	public static function GET_AUTHORITY(bool $ignorePort = false): string
	{
		if (empty(self::$host))
		{
			return '';
		}

		$authority = self::$host;

		if (! empty(self::GET_USER_INFO()))
		{
			$authority = self::GET_USER_INFO() . '@' . $authority;
		}

		// Don't add port if it's a standard port for
		// this scheme
		if (! empty(self::$port) && ! $ignorePort && self::$port !== self::$defaultPorts[self::$scheme])
		{
			$authority .= ':' . self::$port;
		}

		self::$showPassword = false;

		return $authority;
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieve the user information component of the URI.
	 *
	 * If no user information is present, this method MUST return an empty
	 * string.
	 *
	 * If a user is present in the URI, this will return that value;
	 * additionally, if the password is also present, it will be appended to the
	 * user value, with a colon (":") separating the values.
	 *
	 * NOTE that be default, the password, if available, will NOT be shown
	 * as a security measure as discussed in RFC 3986, Section 7.5. If you know
	 * the password is not a security issue, you can force it to be shown
	 * with self::$SHOW_PASSWORD();
	 *
	 * The trailing "@" character is not part of the user information and MUST
	 * NOT be added.
	 *
	 * @return string|null The URI user information, in "username[:password]" format.
	 */
	public static function GET_USER_INFO()
	{
		$userInfo = self::$user;

		if (self::$showPassword === true && ! empty(self::$password))
		{
			$userInfo .= ':' . self::$password;
		}

		return $userInfo;
	}

	//--------------------------------------------------------------------

	/**
	 * Temporarily sets the URI to show a password in userInfo. Will
	 * reset itself after the first call to authority().
	 *
	 * @param boolean $val
	 *
	 * @return URI
	 */
	public static function SHOW_PASSWORD(bool $val = true)
	{
		self::$showPassword = $val;

		return self::$thiss;
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieve the host component of the URI.
	 *
	 * If no host is present, this method MUST return an empty string.
	 *
	 * The value returned MUST be normalized to lowercase, per RFC 3986
	 * Section 3.2.2.
	 *
	 * @see    http://tools.ietf.org/html/rfc3986#section-3.2.2
	 * @return string The URI host.
	 */
	public static function GET_HOST(): string
	{
		return self::$host ?? '';
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieve the port component of the URI.
	 *
	 * If a port is present, and it is non-standard for the current scheme,
	 * this method MUST return it as an integer. If the port is the standard port
	 * used with the current scheme, this method SHOULD return null.
	 *
	 * If no port is present, and no scheme is present, this method MUST return
	 * a null value.
	 *
	 * If no port is present, but a scheme is present, this method MAY return
	 * the standard port for that scheme, but SHOULD return null.
	 *
	 * @return null|integer The URI port.
	 */
	public static function GET_PORT()
	{
		return self::$port;
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieve the path component of the URI.
	 *
	 * The path can either be empty or absolute (starting with a slash) or
	 * rootless (not starting with a slash). Implementations MUST support all
	 * three syntaxes.
	 *
	 * Normally, the empty path "" and absolute path "/" are considered equal as
	 * defined in RFC 7230 Section 2.7.3. But this method MUST NOT automatically
	 * do this normalization because in contexts with a trimmed base path, e.g.
	 * the front controller, this difference becomes significant. It's the task
	 * of the user to handle both "" and "/".
	 *
	 * The value returned MUST be percent-encoded, but MUST NOT double-encode
	 * any characters. To determine what characters to encode, please refer to
	 * RFC 3986, Sections 2 and 3.3.
	 *
	 * As an example, if the value should include a slash ("/") not intended as
	 * delimiter between path segments, that value MUST be passed in encoded
	 * form (e.g., "%2F") to the instance.
	 *
	 * @see    https://tools.ietf.org/html/rfc3986#section-2
	 * @see    https://tools.ietf.org/html/rfc3986#section-3.3
	 * @return string The URI path.
	 */
	public static function GET_PATH(): string
	{
		return self::$path ?? '';
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieve the query string
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public static function GET_QUERY(array $options = []): string
	{
		$vars = self::$query;

		if (array_key_exists('except', $options))
		{
			if (! is_array($options['except']))
			{
				$options['except'] = [$options['except']];
			}

			foreach ($options['except'] as $var)
			{
				unset($vars[$var]);
			}
		}
		elseif (array_key_exists('only', $options))
		{
			$temp = [];

			if (! is_array($options['only']))
			{
				$options['only'] = [$options['only']];
			}

			foreach ($options['only'] as $var)
			{
				if (array_key_exists($var, $vars))
				{
					$temp[$var] = $vars[$var];
				}
			}

			$vars = $temp;
		}

		return empty($vars) ? '' : http_build_query($vars);
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieve a URI fragment
	 *
	 * @return string
	 */
	public static function GET_FRAGMENT(): string
	{
		return self::$fragment ?? '';
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the segments of the path as an array.
	 *
	 * @return array
	 */
	public static function GET_SEGMENTS(): array
	{
		return self::$segments;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the value of a specific segment of the URI path.
	 *
	 * @param integer $number  Segment number
	 * @param string  $default Default value
	 *
	 * @return string     The value of the segment. If no segment is found,
	 *                    throws InvalidArgumentError
	 */
	public static function GET_SEGMENT(int $number, string $default = ''): string
	{
		// The segment should treat the array as 1-based for the user
		// but we still have to deal with a zero-based array.
		$number -= 1;

		if ($number > count(self::$segments) && ! self::$silent)
		{
            // throw HTTPException::forURISegmentOutOfRange($number);
            d($number);
            
		}

		return self::$segments[$number] ?? $default;
	}

	/**
	 * Set the value of a specific segment of the URI path.
	 * Allows to set only existing segments or add new one.
	 *
	 * @param integer $number
	 * @param mixed   $value  (string or int)
	 *
	 * @return $this
	 */
	public static function SET_SEGMENT(int $number, $value)
	{
		// The segment should treat the array as 1-based for the user
		// but we still have to deal with a zero-based array.
		$number -= 1;

		if ($number > count(self::$segments) + 1)
		{
			if (self::$silent)
			{
				return self::$thiss;
			}

            // throw HTTPException::forURISegmentOutOfRange($number);
            d($number);
            
		}

		self::$segments[$number] = $value;
		self::REFRESH_PATH();

		return self::$thiss;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the total number of segments.
	 *
	 * @return integer
	 */
	public static function GET_TOTAL_SEGMENTS(): int
	{
		return count(self::$segments);
	}

	//--------------------------------------------------------------------

	/**
	 * Formats the URI as a string.
	 *
	 * Warning: For backwards-compatability this method
	 * assumes URIs with the same host as baseURL should
	 * be relative to the project's configuration.
	 * This aspect of __toString() is deprecated and should be avoided.
	 *
	 * @return string
	 */
	public  function __toString(): string
	{
		$path   = self::GET_PATH();
		
		$scheme = self::GET_SCHEME();
		// Check if this is an internal URI
		$baseUri = new self(self::$config::$baseURL);
		// If the hosts matches then assume this should be relative to baseURL
		if (self::GET_HOST() === $baseUri::GET_HOST())
		{
			// Check for additional segments
			$basePath = trim($baseUri::GET_PATH(), '/') . '/';
			$trimPath = ltrim($path, '/');

			if ($basePath !== '/' && strpos($trimPath, $basePath) !== 0)
			{
				$path = $basePath . $trimPath;
			}

			// Check for forced HTTPS
			if (self::$config::$forceGlobalSecureRequests)
			{
				$scheme = 'https';
			}
		}

		
	
		return static::CREATE_URI_STRING(
			$scheme, self::GET_AUTHORITY(self::$config::$ignorePort), $path, // Absolute URIs should use a "/" for an empty path
			self::GET_QUERY(), self::GET_FRAGMENT()
		);
	}

	//--------------------------------------------------------------------

	/**
	 * Parses the given string and saves the appropriate authority pieces.
	 *
	 * @param string $str
	 *
	 * @return $this
	 */
	public static function SET_AUTHORITY(string $str)
	{
		$parts = parse_url($str);

		if (! isset($parts['path']))
		{
			$parts['path'] = self::GET_PATH();
		}

		if (empty($parts['host']) && $parts['path'] !== '')
		{
			$parts['host'] = $parts['path'];
			unset($parts['path']); // @phpstan-ignore-line
		}

		self::APPLY_PARTS($parts);

		return self::$thiss;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the scheme for this URI.
	 *
	 * Because of the large number of valid schemes we cannot limit this
	 * to only http or https.
	 *
	 * @see https://www.iana.org/assignments/uri-schemes/uri-schemes.xhtml
	 *
	 * @param string $str
	 *
	 * @return $this
	 */
	public static function SET_SCHEME(string $str)
	{
		$str = strtolower($str);
		$str = preg_replace('#:(//)?$#', '', $str);

		self::$scheme = $str;

		return self::$thiss;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the userInfo/Authority portion of the URI.
	 *
	 * @param string $user The user's username
	 * @param string $pass The user's password
	 *
	 * @return $this
	 */
	public static function SET_USER_INFO(string $user, string $pass)
	{
		self::$user     = trim($user);
		self::$password = trim($pass);

		return self::$thiss;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the host name to use.
	 *
	 * @param string $str
	 *
	 * @return $this
	 */
	public static function SET_HOST(string $str)
	{

        self::$host = trim($str);

		return self::$thiss;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the port portion of the URI.
	 *
	 * @param integer $port
	 *
	 * @return $this
	 */
	public static function SET_PORT(int $port = null)
	{
		if (is_null($port))
		{
			return self::$thiss;
		}

		if ($port <= 0 || $port > 65535)
		{
			if (self::$silent)
			{
				return self::$thiss;
			}

            // throw HTTPException::forInvalidPort($port);
            d($port);
            
		}

		self::$port = $port;

		return self::$thiss;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the path portion of the URI.
	 *
	 * @param string $path
	 *
	 * @return $this
	 */
	public static function SET_PATH(string $path)
	{
		self::$path = self::FILTER_PATH($path);

		$tempPath = trim(self::$path, '/');

		self::$segments = ($tempPath === '') ? [] : explode('/', $tempPath);

		return self::$thiss;
	}

	/**
	 * Sets the path portion of the URI based on segments.
	 *
	 * @return $this
	 */
	public static function REFRESH_PATH()
	{
		self::$path = self::FILTER_PATH(implode('/', self::$segments));

		$tempPath = trim(self::$path, '/');

		self::$segments = ($tempPath === '') ? [] : explode('/', $tempPath);

		return self::$thiss;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the query portion of the URI, while attempting
	 * to clean the various parts of the query keys and values.
	 *
	 * @param string $query
	 *
	 * @return $this
	 */
	public static function SET_QUERY(string $query)
	{
		if (strpos($query, '#') !== false)
		{
			if (self::$silent)
			{
				return self::$thiss;
			}

            throw HTTPException::FOR_MAL_FORMED_QUERY_STRING();
            
		}

		// Can't have leading ?
		if (! empty($query) && strpos($query, '?') === 0)
		{
			$query = substr($query, 1);
		}

		if (self::$rawQueryString)
		{
			self::$query = self::PARSE_STR($query);
		}
		else
		{
			parse_str($query, self::$query);
		}

		return self::$thiss;
	}

	//--------------------------------------------------------------------

	/**
	 * A convenience method to pass an array of items in as the Query
	 * portion of the URI.
	 *
	 * @param array $query
	 *
	 * @return URI
	 */
	public static function SET_QUERY_ARRAY(array $query)
	{
		$query = http_build_query($query);

		return self::SET_QUERY($query);
	}

	//--------------------------------------------------------------------

	/**
	 * Adds a single new element to the query vars.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return $this
	 */
	public static function ADD_QUERY(string $key, $value = null)
	{
		self::$query[$key] = $value;

		return self::$thiss;
	}

	//--------------------------------------------------------------------

	/**
	 * Removes one or more query vars from the URI.
	 *
	 * @param string ...$params
	 *
	 * @return $this
	 */
	public static function STRIP_QUERY(...$params)
	{
		foreach ($params as $param)
		{
			unset(self::$query[$param]);
		}

		return self::$thiss;
	}

	//--------------------------------------------------------------------

	/**
	 * Filters the query variables so that only the keys passed in
	 * are kept. The rest are removed from the object.
	 *
	 * @param string ...$params
	 *
	 * @return $this
	 */
	public static function KEEP_QUERY(...$params)
	{
		$temp = [];

		foreach (self::$query as $key => $value)
		{
			if (! in_array($key, $params, true))
			{
				continue;
			}

			$temp[$key] = $value;
		}

		self::$query = $temp;

		return self::$thiss;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the fragment portion of the URI.
	 *
	 * @see https://tools.ietf.org/html/rfc3986#section-3.5
	 *
	 * @param string $string
	 *
	 * @return $this
	 */
	public static function SET_FRAGMENT(string $string)
	{
		self::$fragment = trim($string, '# ');

		return self::$thiss;
	}

	//--------------------------------------------------------------------

	/**
	 * Encodes any dangerous characters, and removes dot segments.
	 * While dot segments have valid uses according to the spec,
	 * this URI class does not allow them.
	 *
	 * @param string|null $path
	 *
	 * @return string
	 */
	protected static function FILTER_PATH(string $path = null): string
	{
		$orig = $path;

		// Decode/normalize percent-encoded chars so
		// we can always have matching for Routes, etc.
		$path = urldecode($path);

		// Remove dot segments
		$path = self::REMOVE_DOT_SEGMENTS($path);

		// Fix up some leading slash edge cases...
		if (strpos($orig, './') === 0)
		{
			$path = '/' . $path;
		}
		if (strpos($orig, '../') === 0)
		{
			$path = '/' . $path;
		}

		// Encode characters
		$path = preg_replace_callback(
				'/(?:[^' . static::CHAR_UNRESERVED . ':@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/', function (array $matches) {
					return rawurlencode($matches[0]);
				}, $path
		);

		return $path;
	}

	//--------------------------------------------------------------------

	/**
	 * Saves our parts from a parse_url call.
	 *
	 * @param array $parts
	 */
	protected static function APPLY_PARTS(array $parts)
	{
		if (! empty($parts['host']))
		{
			self::$host = $parts['host'];
		}
		if (! empty($parts['user']))
		{
			self::$user = $parts['user'];
		}
		if (isset($parts['path']) && $parts['path'] !== '')
		{
			self::$path = self::FILTER_PATH($parts['path']);
		}
		if (! empty($parts['query']))
		{
			self::SET_QUERY($parts['query']);
		}
		if (! empty($parts['fragment']))
		{
			self::$fragment = $parts['fragment'];
		}

		// Scheme
		if (isset($parts['scheme']))
		{
			self::SET_SCHEME(rtrim($parts['scheme'], ':/'));
		}
		else
		{
			self::SET_SCHEME('http');
		}

		// Port
		if (isset($parts['port']) && ! is_null($parts['port']))
		{
			// Valid port numbers are enforced by earlier parse_url or SET_PORT()
			$port       = $parts['port'];
			self::$port = $port;
		}

		if (isset($parts['pass']))
		{
			self::$password = $parts['pass'];
		}

		// Populate our segments array
		if (isset($parts['path']) && $parts['path'] !== '')
		{
			$tempPath = trim($parts['path'], '/');

			self::$segments = ($tempPath === '') ? [] : explode('/', $tempPath);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Combines one URI string with this one based on the rules set out in
	 * RFC 3986 Section 2
	 *
	 * @see http://tools.ietf.org/html/rfc3986#section-5.2
	 *
	 * @param string $uri
	 *
	 * @return URI
	 */
	public static function RESOLVE_RELATIVE_URI(string $uri)
	{
		/*
		 * NOTE: We don't use REMOVE_DOT_SEGMENTS in this
		 * algorithm since it's already done by this line!
		 */
		$relative = new self();
		$relative::SET_URI($uri);

		if ($relative::GET_SCHEME() === self::GET_SCHEME())
		{
			$relative::SET_SCHEME('');
		}

		$transformed = clone $relative;

		// 5.2.2 Transform References in a non-strict method (no scheme)
		if (! empty($relative::GET_AUTHORITY(self::$config::$ignorePort)))
		{
			$transformed::SET_AUTHORITY($relative::GET_AUTHORITY(self::$config::$ignorePort))
					::SET_PATH($relative::GET_PATH())
					::SET_QUERY($relative::GET_QUERY());
		}
		else
		{
			if ($relative::GET_PATH() === '')
			{
				$transformed::SET_PATH(self::GET_PATH());

				if ($relative::GET_QUERY())
				{
					$transformed::SET_QUERY($relative::GET_QUERY());
				}
				else
				{
					$transformed::SET_QUERY(self::GET_QUERY());
				}
			}
			else
			{
				if (strpos($relative::GET_PATH(), '/') === 0)
				{
					$transformed::SET_PATH($relative::GET_PATH());
				}
				else
				{
					$transformed::SET_PATH(self::MERGE_PATHS(self::$thiss, $relative));
				}

				$transformed::SET_QUERY($relative::GET_QUERY());
			}

			$transformed::SET_AUTHORITY(self::GET_AUTHORITY(self::$config::$ignorePort));
		}

		$transformed::SET_SCHEME(self::GET_SCHEME());

		$transformed::SET_FRAGMENT($relative::GET_FRAGMENT());

		return $transformed;
	}

	//--------------------------------------------------------------------

	/**
	 * Given 2 paths, will merge them according to rules set out in RFC 2986,
	 * Section 5.2
	 *
	 * @see http://tools.ietf.org/html/rfc3986#section-5.2.3
	 *
	 * @param URI $base
	 * @param URI $reference
	 *
	 * @return string
	 */
	protected static function MERGE_PATHS(URI $base, URI $reference): string
	{
		if (! empty($base::GET_AUTHORITY(self::$config::$ignorePort)) && $base::GET_PATH() === '')
		{
			return '/' . ltrim($reference::GET_PATH(), '/ ');
		}

		$path = explode('/', $base::GET_PATH());

		if ($path[0] === '')
		{
			unset($path[0]);
		}

		array_pop($path);
		$path[] = $reference::GET_PATH();

		return implode('/', $path);
	}

	//--------------------------------------------------------------------

	/**
	 * This is equivalent to the native PHP parse_str() function.
	 * This version allows the dot to be used as a key of the query string.
	 *
	 * @param string $query
	 *
	 * @return array
	 */
	protected static function PARSE_STR(string $query): array
	{
		$return = [];
		$query  = explode('&', $query);

		$params = array_map(function (string $chunk) {
			return preg_replace_callback('/^(?<key>[^&=]+?)(?:\[[^&=]*\])?=(?<value>[^&=]+)/', function (array $match) {
				return str_replace($match['key'], bin2hex($match['key']), $match[0]);
			}, urldecode($chunk));
		}, $query);

		$param = implode('&', $params);
		parse_str($param, $params);
	

		foreach ($params as $key => $value)
		{
			$return[hex2bin($key)] = $value;
		}

		$query = $params = null;

		return $return;
	}

	//--------------------------------------------------------------------
}
