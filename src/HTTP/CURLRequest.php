<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@Hkm_code.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\HTTP;

use Hkm_code\Exceptions\HTTP\HTTPException;
use InvalidArgumentException;

/**
 * Class OutgoingRequest
 *
 * A lightweight HTTP client for sending synchronous HTTP requests
 * via cURL.
 */
class CURLRequest extends Request
{
	/**
	 * The response object associated with this request
	 *
	 * @var Response|null
	 */
	protected static $response;

	/**
	 * The URI associated with this request
	 *
	 * @var URI
	 */
	protected static $baseURI;

	/**
	 * The setting values
	 *
	 * @var array
	 */
	protected static $config = [
		'timeout'         => 0.0,
		'connect_timeout' => 150,
		'debug'           => false,
		'verify'          => true,
	];

	/**
	 * Default values for when 'allow_redirects'
	 * option is true.
	 *
	 * @var array
	 */
	protected static $redirectDefaults = [
		'max'       => 5,
		'strict'    => true,
		'protocols' => [
			'http',
			'https',
		],
	];

	/**
	 * The number of milliseconds to delay before
	 * sending the request.
	 *
	 * @var float
	 */
	protected static $delay = 0.0;

	//--------------------------------------------------------------------
	/**
	 * Takes an array of options to set the following possible class properties:
	 *
	 *  - baseURI
	 *  - timeout
	 *  - any other request options to use as defaults.
	 *
	 * @param App               $config
	 * @param URI               $uri
	 * @param Response $response
	 * @param array             $options
	 */
	public function __construct( $config, URI $uri, Response $response = null, array $options = [])
	{
		if (! function_exists('curl_version'))
		{
			// we won't see this during travis-CI
			// @codeCoverageIgnoreStart
			throw HTTPException::FOR_MISSING_CURL();
			// @codeCoverageIgnoreEnd
		}

		parent::__construct($config);

		self::$response = $response;
		self::$baseURI  = $uri::USE_RAW_QUERY_STRING();

		self::PARSE_OPTIONS($options);
	}

	//--------------------------------------------------------------------
	/**
	 * Sends an HTTP request to the specified $url. If this is a relative
	 * URL, it will be merged with self::$baseURI to form a complete URL.
	 *
	 * @param string $method
	 * @param string $url
	 * @param array  $options
	 *
	 * @return Response
	 */
	public static function REQUEST($method, string $url, array $options = []): Response
	{
		self::PARSE_OPTIONS($options);

		$url = self::PREPARE_URL($url);

		$method = filter_var($method, FILTER_SANITIZE_STRING);

		self::SEND($method, $url);

		return self::$response;
	}

	//--------------------------------------------------------------------
	/**
	 * Convenience method for sending a GET request.
	 *
	 * @param string $url
	 * @param array  $options
	 *
	 * @return Response
	 */
	public static function GET(string $url, array $options = []): Response
	{
		return self::REQUEST('get', $url, $options);
	}

	//--------------------------------------------------------------------
	/**
	 * Convenience method for sending a DELETE request.
	 *
	 * @param string $url
	 * @param array  $options
	 *
	 * @return Response
	 */
	public static function DELETE(string $url, array $options = []): Response
	{
		return self::REQUEST('delete', $url, $options);
	}

	//--------------------------------------------------------------------
	/**
	 * Convenience method for sending a HEAD request.
	 *
	 * @param string $url
	 * @param array  $options
	 *
	 * @return Response
	 */
	public static function HEAD(string $url, array $options = []): Response
	{
		return self::REQUEST('head', $url, $options);
	}

	//--------------------------------------------------------------------
	/**
	 * Convenience method for sending an OPTIONS request.
	 *
	 * @param string $url
	 * @param array  $options
	 *
	 * @return Response
	 */
	public static function OPTIONS(string $url, array $options = []): Response
	{
		return self::REQUEST('options', $url, $options);
	}

	//--------------------------------------------------------------------
	/**
	 * Convenience method for sending a PATCH request.
	 *
	 * @param string $url
	 * @param array  $options
	 *
	 * @return Response
	 */
	public static function PATCH(string $url, array $options = []): Response
	{
		return self::REQUEST('patch', $url, $options);
	}

	//--------------------------------------------------------------------
	/**
	 * Convenience method for sending a POST request.
	 *
	 * @param string $url
	 * @param array  $options
	 *
	 * @return Response
	 */
	public static function POST(string $url, array $options = []): Response
	{
		return self::REQUEST('post', $url, $options);
	}

	//--------------------------------------------------------------------
	/**
	 * Convenience method for sending a PUT request.
	 *
	 * @param string $url
	 * @param array  $options
	 *
	 * @return Response
	 */
	public static function PUT(string $url, array $options = []): Response
	{
		return self::REQUEST('put', $url, $options);
	}

	//--------------------------------------------------------------------

	/**
	 * Set the HTTP Authentication.
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $type     basic or digest
	 *
	 * @return $this
	 */
	public static function SET_AUTH(string $username, string $password, string $type = 'basic')
	{
		self::$config['auth'] = [
			$username,
			$password,
			$type,
		];

		return self::$thiss;
	}

	//--------------------------------------------------------------------

	/**
	 * Set form data to be sent.
	 *
	 * @param array   $params
	 * @param boolean $multipart Set TRUE if you are sending CURLFiles
	 *
	 * @return $this
	 */
	public static function SET_FROM(array $params, bool $multipart = false)
	{
		if ($multipart)
		{
			self::$config['multipart'] = $params;
		}
		else
		{
			self::$config['form_params'] = $params;
		}

		return self::$thiss;
	}

	//--------------------------------------------------------------------

	/**
	 * Set JSON data to be sent.
	 *
	 * @param mixed $data
	 *
	 * @return $this
	 */
	public static function SET_JSON($data)
	{
		self::$config['json'] = $data;

		return self::$thiss;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the correct settings based on the options array
	 * passed in.
	 *
	 * @param array $options
	 */
	protected static function PARSE_OPTIONS(array $options)
	{
		if (array_key_exists('baseURI', $options))
		{
			self::$baseURI = self::$baseURI::SET_URI($options['baseURI']);
			unset($options['baseURI']);
		}

		if (array_key_exists('headers', $options) && is_array($options['headers']))
		{
			foreach ($options['headers'] as $name => $value)
			{
				self::SET_HEADER($name, $value);
			}

			unset($options['headers']);
		}

		if (array_key_exists('delay', $options))
		{
			// Convert from the milliseconds passed in
			// to the seconds that sleep requires.
			self::$delay = (float) $options['delay'] / 1000;
			unset($options['delay']);
		}

		foreach ($options as $key => $value)
		{
			self::$config[$key] = $value;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * If the $url is a relative URL, will attempt to create
	 * a full URL by prepending self::$baseURI to it.
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	protected static function PREPARE_URL(string $url): string
	{
		// If it's a full URI, then we have nothing to do here...
		if (strpos($url, '://') !== false)
		{
			return $url;
		}

		$uri = self::$baseURI::RESOLVE_RELATIVE_URI($url);

		// Create the string instead of casting to prevent baseURL muddling
		return URI::CREATE_URI_STRING($uri::GET_SCHEME(), $uri::GET_AUTHORITY(), $uri::GET_PATH(), $uri::GET_QUERY(), $uri::GET_FRAGMENT());
	}

	//--------------------------------------------------------------------

	/**
	 * Get the request method. Overrides the Request class' method
	 * since users expect a different answer here.
	 *
	 * @param boolean|false $upper Whether to return in upper or lower case.
	 *
	 * @return string
	 */
	public static function GET_METHOD(bool $upper = false): string
	{
		return ($upper) ? strtoupper(self::$method) : strtolower(self::$method);
	}

	//--------------------------------------------------------------------
	/**
	 * Fires the actual cURL request.
	 *
	 * @param string $method
	 * @param string $url
	 *
	 * @return Response
	 */
	public static function SEND(string $method, string $url)
	{
		// Reset our curl options so we're on a fresh slate.
		$curlOptions = [];

		if (! empty(self::$config['query']) && is_array(self::$config['query']))
		{
			// This is likely too naive a solution.
			// Should look into handling when $url already
			// has query vars on it.
			$url .= '?' . http_build_query(self::$config['query']);
			unset(self::$config['query']);
		}

		$curlOptions[CURLOPT_URL]            = $url;
		$curlOptions[CURLOPT_RETURNTRANSFER] = true;
		$curlOptions[CURLOPT_HEADER]         = true;
		$curlOptions[CURLOPT_FRESH_CONNECT]  = true;
		// Disable @file uploads in post data.
		$curlOptions[CURLOPT_SAFE_UPLOAD] = true;

		$curlOptions = self::SET_CURL_OPTIONS($curlOptions, self::$config);
		$curlOptions = self::APPLY_METHOD($method, $curlOptions);
		$curlOptions = self::APPLY_REQUEST_HEADERS($curlOptions);

		// Do we need to delay this request?
		if (self::$delay > 0)
		{
			sleep(self::$delay); // @phpstan-ignore-line
		}

		$output = self::SEND_REQUEST($curlOptions);

		// Set the string we want to break our response from
		$breakString = "\r\n\r\n";

		if (strpos($output, 'HTTP/1.1 100 Continue') === 0)
		{
			$output = substr($output, strpos($output, $breakString) + 4);
		}

		 // If request and response have Digest
		if (isset(self::$config['auth'][2]) && self::$config['auth'][2] === 'digest' && strpos($output, 'WWW-Authenticate: Digest') !== false)
		{
				$output = substr($output, strpos($output, $breakString) + 4);
		}

		// Split out our headers and body
		$break = strpos($output, $breakString);

		if ($break !== false)
		{
			// Our headers
			$headers = explode("\n", substr($output, 0, $break));

			self::SET_RESPONSE_HEADERS($headers);

			// Our body
			$body = substr($output, $break + 4);
			self::$response::SET_BODY($body);
		}
		else
		{
			self::$response::SET_BODY($output);
		}

		return self::$response;
	}

	//--------------------------------------------------------------------

	/**
	 * Takes all headers current part of this request and adds them
	 * to the cURL request.
	 *
	 * @param array $curlOptions
	 *
	 * @return array
	 */
	protected static function APPLY_REQUEST_HEADERS(array $curlOptions = []): array
	{
		if (empty(self::$headers))
		{
			self::POPULATE_HEADERS();
			// Otherwise, it will corrupt the request
			self::REMOVE_HEADER('Host');
			self::REMOVE_HEADER('Accept-Encoding');
		}

		$headers = self::HEADERS();

		if (empty($headers))
		{
			return $curlOptions;
		}

		$set = [];

		foreach (array_keys($headers) as $name)
		{
			$set[] = $name . ': ' . self::GET_HEADER_LINE($name);
		}

		$curlOptions[CURLOPT_HTTPHEADER] = $set;

		return $curlOptions;
	}

	//--------------------------------------------------------------------

	/**
	 * Apply method
	 *
	 * @param string $method
	 * @param array  $curlOptions
	 *
	 * @return array
	 */
	protected static function APPLY_METHOD(string $method, array $curlOptions): array
	{
		$method = strtoupper($method);

		self::$method                       = $method;
		$curlOptions[CURLOPT_CUSTOMREQUEST] = $method;

		$size = strlen(self::$body);

		// Have content?
		if ($size > 0)
		{
			return self::APPLY_BODY($curlOptions);
		}

		if ($method === 'PUT' || $method === 'POST')
		{
			// See http://tools.ietf.org/html/rfc7230#section-3.3.2
			if (is_null(self::HEADER('content-length')) && ! isset(self::$config['multipart']))
			{
				self::SET_HEADER('Content-Length', '0');
			}
		}
		elseif ($method === 'HEAD')
		{
			$curlOptions[CURLOPT_NOBODY] = 1;
		}

		return $curlOptions;
	}

	//--------------------------------------------------------------------

	/**
	 * Apply body
	 *
	 * @param array $curlOptions
	 *
	 * @return array
	 */
	protected static function APPLY_BODY(array $curlOptions = []): array
	{
		if (! empty(self::$body))
		{
			$curlOptions[CURLOPT_POSTFIELDS] = (string) self::GET_BODY();
		}

		return $curlOptions;
	}

	//--------------------------------------------------------------------

	/**
	 * Parses the header retrieved from the cURL response into
	 * our Response object.
	 *
	 * @param array $headers
	 */
	protected static function SET_RESPONSE_HEADERS(array $headers = [])
	{
		foreach ($headers as $header)
		{
			if (($pos = strpos($header, ':')) !== false)
			{
				$title = substr($header, 0, $pos);
				$value = substr($header, $pos + 1);

				self::$response::SET_HEADER($title, $value);
			}
			elseif (strpos($header, 'HTTP') === 0)
			{
				preg_match('#^HTTP\/([12](?:\.[01])?) ([0-9]+) (.+)#', $header, $matches);

				if (isset($matches[1]))
				{
					self::$response::SET_PROTOCOL_VERSION($matches[1]);
				}

				if (isset($matches[2]))
				{
					self::$response::SET_STATUS($matches[2], $matches[3] ?? null);
				}
			}
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Set CURL options
	 *
	 * @param  array $curlOptions
	 * @param  array $config
	 * @return array
	 * @throws InvalidArgumentException
	 */
	protected static function SET_CURL_OPTIONS(array $curlOptions = [], array $config = [])
	{
		// Auth Headers
		if (! empty($config['auth']))
		{
			$curlOptions[CURLOPT_USERPWD] = $config['auth'][0] . ':' . $config['auth'][1];

			if (! empty($config['auth'][2]) && strtolower($config['auth'][2]) === 'digest')
			{
				$curlOptions[CURLOPT_HTTPAUTH] = CURLAUTH_DIGEST;
			}
			else
			{
				$curlOptions[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
			}
		}

		// Certificate
		if (! empty($config['cert']))
		{
			$cert = $config['cert'];

			if (is_array($cert))
			{
				$curlOptions[CURLOPT_SSLCERTPASSWD] = $cert[1];
				$cert                               = $cert[0];
			}

			if (! is_file($cert))
			{
				throw HTTPException::FOR_SSL_CERT_NOT_FOUND($cert);
			}

			$curlOptions[CURLOPT_SSLCERT] = $cert;
		}

		// SSL Verification
		if (isset($config['verify']))
		{
			if (is_string($config['verify']))
			{
				$file = realpath($config['ssl_key']) ?: $config['ssl_key'];

				if (! is_file($file))
				{
					throw HTTPException::FOR_INVALID_SSL_KEY($config['ssl_key']);
				}

				$curlOptions[CURLOPT_CAINFO]         = $file;
				$curlOptions[CURLOPT_SSL_VERIFYPEER] = 1;
			}
			elseif (is_bool($config['verify']))
			{
				$curlOptions[CURLOPT_SSL_VERIFYPEER] = $config['verify'];
			}
		}

		// Debug
		if ($config['debug'])
		{
			$curlOptions[CURLOPT_VERBOSE] = 1;
			$curlOptions[CURLOPT_STDERR]  = is_string($config['debug']) ? fopen($config['debug'], 'a+') : fopen('php://stderr', 'w');
		}

		// Decode Content
		if (! empty($config['decode_content']))
		{
			$accept = self::GET_HEADER_LINE('Accept-Encoding');

			if ($accept)
			{
				$curlOptions[CURLOPT_ENCODING] = $accept;
			}
			else
			{
				$curlOptions[CURLOPT_ENCODING]   = '';
				$curlOptions[CURLOPT_HTTPHEADER] = 'Accept-Encoding';
			}
		}

		// Allow Redirects
		if (array_key_exists('allow_redirects', $config))
		{
			$settings = self::$redirectDefaults;

			if (is_array($config['allow_redirects']))
			{
				$settings = array_merge($settings, $config['allow_redirects']);
			}

			if ($config['allow_redirects'] === false)
			{
				$curlOptions[CURLOPT_FOLLOWLOCATION] = 0;
			}
			else
			{
				$curlOptions[CURLOPT_FOLLOWLOCATION] = 1;
				$curlOptions[CURLOPT_MAXREDIRS]      = $settings['max'];

				if ($settings['strict'] === true)
				{
					$curlOptions[CURLOPT_POSTREDIR] = 1 | 2 | 4;
				}

				$protocols = 0;
				foreach ($settings['protocols'] as $proto)
				{
					$protocols += constant('CURLPROTO_' . strtoupper($proto));
				}

				$curlOptions[CURLOPT_REDIR_PROTOCOLS] = $protocols;
			}
		}

		// Timeout
		$curlOptions[CURLOPT_TIMEOUT_MS] = (float) $config['timeout'] * 1000;

		// Connection Timeout
		$curlOptions[CURLOPT_CONNECTTIMEOUT_MS] = (float) $config['connect_timeout'] * 1000;

		// Post Data - application/x-www-form-urlencoded
		if (! empty($config['form_params']) && is_array($config['form_params']))
		{
			$postFields                      = http_build_query($config['form_params']);
			$curlOptions[CURLOPT_POSTFIELDS] = $postFields;

			// Ensure content-length is set, since CURL doesn't seem to
			// calculate it when HTTPHEADER is set.
			self::SET_HEADER('Content-Length', (string) strlen($postFields));
			self::SET_HEADER('Content-Type', 'application/x-www-form-urlencoded');
		}

		// Post Data - multipart/form-data
		if (! empty($config['multipart']) && is_array($config['multipart']))
		{
			// setting the POSTFIELDS option automatically sets multipart
			$curlOptions[CURLOPT_POSTFIELDS] = $config['multipart'];
		}

		// HTTP Errors
		$curlOptions[CURLOPT_FAILONERROR] = array_key_exists('http_errors', $config) ? (bool) $config['http_errors'] : true;

		// JSON
		if (isset($config['json']))
		{
			// Will be set as the body in `APPLY_BODY()`
			$json = json_encode($config['json']);
			self::SET_BODY($json);
			self::SET_HEADER('Content-Type', 'application/json');
			self::SET_HEADER('Content-Length', (string) strlen($json));
		}

		// version
		if (! empty($config['version']))
		{
			if ($config['version'] === 1.0)
			{
				$curlOptions[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_0;
			}
			elseif ($config['version'] === 1.1)
			{
				$curlOptions[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_1;
			}
		}

		// Cookie
		if (isset($config['cookie']))
		{
			$curlOptions[CURLOPT_COOKIEJAR]  = $config['cookie'];
			$curlOptions[CURLOPT_COOKIEFILE] = $config['cookie'];
		}

		// User Agent
		if (isset($config['user_agent']))
		{
			$curlOptions[CURLOPT_USERAGENT] = $config['user_agent'];
		}

		return $curlOptions;
	}

	//--------------------------------------------------------------------

	/**
	 * Does the actual work of initializing cURL, setting the options,
	 * and grabbing the output.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param array $curlOptions
	 *
	 * @return string
	 */
	protected static function SEND_REQUEST(array $curlOptions = []): string
	{
		$ch = curl_init();

		curl_setopt_array($ch, $curlOptions);

		// Send the request and wait for a response.
		$output = curl_exec($ch);

		// print_r($curlOptions);exit;

		if ($output === false)
		{
			throw HTTPException::FOR_CURL_ERROR((string) curl_errno($ch), curl_error($ch));
		}

		curl_close($ch);

		return $output;
	}

	//--------------------------------------------------------------------
}
