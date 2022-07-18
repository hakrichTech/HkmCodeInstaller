<?php
namespace Hkm_traits\AddOn;

use DateTime;
use DateTimeZone;
use Hkm_code\Cookie\Cookie;
use InvalidArgumentException;
use Hkm_code\Pager\PagerInterface;
use Hkm_code\HTTP\DownloadResponse;
use Hkm_code\Exceptions\HTTP\HTTPException;
use Hkm_code\Cookie\Exceptions\CookieException;
use Hkm_code\Vezirion\ServicesSystem;

/**
 * 
 */
trait ResponseTrait
{
	protected static $CSPEnabled = false;
	public static $CSP;
	protected static $cookieStore;
	protected static $cookiePrefix = '';
	protected static $cookieDomain = '';
	protected static $cookiePath = '/';
	protected static $cookieSecure = false;
	protected static $cookieHTTPOnly = false;
	// protected static $cookieSameSite = Cookie::SAMESITE_LAX;
	protected static $cookies = [];
	protected static $bodyFormat = 'html';
    

   
    
    public  static function SET_DATE(DateTime $date)
	{
		$date->setTimezone(new DateTimeZone('UTC'));

		self::SET_HEADER('Date', $date->format('D, d M Y H:i:s') . ' GMT');

		return self::$thiss;
    }
    
    public static  function SET_LINK(PagerInterface $pager)
	{
		$links = '';

		if ($previous = $pager::GET_PREVIOUS_PAGE_URL())
		{
			$links .= '<' . $pager::GET_PAGE_URI($pager::GET_FIRST_PAGE()) . '>; rel="first",';
			$links .= '<' . $previous . '>; rel="prev"';
		}

		if (($next = $pager::GET_NEXT_PAGE_URI()) && $previous)
		{
			$links .= ',';
		}

		if ($next)
		{
			$links .= '<' . $next . '>; rel="next",';
			$links .= '<' . $pager::GET_PAGE_URI($pager::GET_LAST_PAGE()) . '>; rel="last"';
		}

		self::SET_HEADER('Link', $links);

		return self::$thiss;
	}
	public static function SET_CONTENT_TYPE(string $mime, string $charset = 'UTF-8')
	{
		// add charset attribute if not already there and provided as parm
		if ((strpos($mime, 'charset=') < 1) && ! empty($charset))
		{
			$mime .= '; charset=' . $charset;
		}

		self::REMOVE_HEADER('Content-Type'); // replace existing content type
		self::SET_HEADER('Content-Type', $mime);

		return self::$thiss;
	}

	/**
	 * Converts the $body into JSON and sets the Content Type header.
	 *
	 * @param array|string $body
	 * @param boolean      $unencoded
	 *
	 * @return $this
	 */
	public static function SET_JSON($body, bool $unencoded = false)
	{
		self::$body = self::FORMAT_BODY($body, 'json' . ($unencoded ? '-unencoded' : ''));

		return self::$thiss;
	}

	/**
	 * Returns the current body, converted to JSON is it isn't already.
	 *
	 * @return mixed|string
	 *
	 * @throws InvalidArgumentException If the body property is not array.
	 */
	public static function GET_JSON_DATA()
	{
		$body = self::$body;

		if (self::$bodyFormat !== 'json')
		{
			$body = ServicesSystem::FORMAT()::GET_FORMATTER('application/json')::FORMAT($body);
		}

		return $body ?: null;
	}

	/**
	 * Converts $body into XML, and sets the correct Content-Type.
	 *
	 * @param array|string $body
	 *
	 * @return $this
	 */
	public static function SET_XML($body)
	{
		self::$body = self::FORMAT_BODY($body, 'xml');

		return self::$thiss;
	}

	/**
	 * Retrieves the current body into XML and returns it.
	 *
	 * @return mixed|string
	 * @throws InvalidArgumentException If the body property is not array.
	 */
	public static function GET_XML()
	{
		$body = self::$body;

		if (self::$bodyFormat !== 'xml')
		{
			$body = ServicesSystem::FORMAT()::GET_FORMATTER('application/xml')::FORMAT($body);
		}

		return $body;
	}

	/**
	 * Handles conversion of the of the data into the appropriate format,
	 * and sets the correct Content-Type header for our response.
	 *
	 * @param string|array $body
	 * @param string       $format Valid: json, xml
	 *
	 * @return mixed
	 * @throws InvalidArgumentException If the body property is not string or array.
	 */
	protected static function FORMAT_BODY($body, string $format)
	{
		self::$bodyFormat = ($format === 'json-unencoded' ? 'json' : $format);
		$f = self::$bodyFormat; 
		$mime             = "application/{$f}";
		self::SET_CONTENT_TYPE($mime);

		// Nothing much to do for a string...
		if (! is_string($body) || $format === 'json-unencoded')
		{
			$body = ServicesSystem::FORMAT()::GET_FORMATTER($mime)::FORMAT($body);
		}

		return $body;
	}

	//--------------------------------------------------------------------
	// Cache Control Methods
	//
	// http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.9
	//--------------------------------------------------------------------

	/**
	 * Sets the appropriate headers to ensure this response
	 * is not cached by the browsers.
	 *
	 * @return Response
	 *
	 * @todo Recommend researching these directives, might need: 'private', 'no-transform', 'no-store', 'must-revalidate'
	 *
	 * @see DownloadResponse::noCache()
	 */
	public static function NO_CACHE()
	{
		self::REMOVE_HEADER('Cache-control');
		self::SET_HEADER('Cache-control', ['no-store', 'max-age=0', 'no-cache']);

		return self::$thiss;
	}

	/**
	 * A shortcut method that allows the developer to set all of the
	 * cache-control headers in one method call.
	 *
	 * The options array is used to provide the cache-control directives
	 * for the header. It might look something like:
	 *
	 *      $options = [
	 *          'max-age'  => 300,
	 *          's-maxage' => 900
	 *          'etag'     => 'abcde',
	 *      ];
	 *
	 * Typical options are:
	 *  - etag
	 *  - last-modified
	 *  - max-age
	 *  - s-maxage
	 *  - private
	 *  - public static
	 *  - must-revalidate
	 *  - proxy-revalidate
	 *  - no-transform
	 *
	 * @param array $options
	 *
	 * @return Response
	 */
	public static function SET_CACHE(array $options = [])
	{
		if (empty($options))
		{
			return self::$thiss;
		}

		self::REMOVE_HEADER('Cache-Control');
		self::REMOVE_HEADER('ETag');

		// ETag
		if (isset($options['etag']))
		{
			self::SET_HEADER('ETag', $options['etag']);
			unset($options['etag']);
		}

		// Last Modified
		if (isset($options['last-modified']))
		{
			self::SET_LAST_MODIFIED($options['last-modified']);

			unset($options['last-modified']);
		}

		self::SET_HEADER('Cache-control', $options);

		return self::$thiss;
	}

	/**
	 * Sets the Last-Modified date header.
	 *
	 * $date can be either a string representation of the date or,
	 * preferably, an instance of DateTime.
	 *
	 * @param DateTime|string $date
	 *
	 * @return Response
	 */
	public static function SET_LAST_MODIFIED($date)
	{
		if ($date instanceof DateTime)
		{
			$date->setTimezone(new DateTimeZone('UTC'));
			self::SET_HEADER('Last-Modified', $date->format('D, d M Y H:i:s') . ' GMT');
		}
		elseif (is_string($date))
		{
			self::SET_HEADER('Last-Modified', $date);
		}

		return self::$thiss;
	}

	//--------------------------------------------------------------------
	// Output Methods
	//--------------------------------------------------------------------

	/**
	 * Sends the output to the browser.
	 *
	 * @return Response
	 */
	public static function SEND()
	{
		// If we're enforcing a Content Security Policy,
		// we need to give it a chance to build out it's headers.
		if (self::$CSPEnabled === true)
		{
			self::$CSP->finalize(self::$thiss);
		}
		else
		{
			self::$body = str_replace(['{csp-style-nonce}', '{csp-script-nonce}'], '', self::$body??"");
		}


		self::SEND_HEADERS();
		self::SEND_COOKIES();
		self::SEND_BODY();

		return self::$thiss;
	}

	/**
	 * Sends the headers of this HTTP request to the browser.
	 *
	 * @return Response
	 */
	public static function SEND_HEADERS()
	{

		// Have the headers already been sent?
		if (self::$pretend || headers_sent())
		{
			return self::$thiss;
		}

		// Per spec, MUST be sent with each request, if possible.
		// http://www.w3.org/Protocols/rfc2616/rfc2616-sec13.html
		if (! isset(self::$headers['Date']) && php_sapi_name() !== 'cli-server')
		{
		    self::SET_DATE(DateTime::createFromFormat('U', (string) time()));
		}


		// HTTP Status
		header(sprintf('HTTP/%s %s %s', self::GET_PROTOCOL_VERSION(), self::GET_STATUS_CODE(), self::GET_REASON()), true, self::GET_STATUS_CODE());

		if(hkm_doing_ajax()){
			return self::$thiss;
		}
		// Send all of our headers

		foreach (array_keys(self::GET_HEADERS()) as $name)
		{
			header($name . ': ' . self::GET_HEADER_LINE($name), false, self::GET_STATUS_CODE());
		}

		return self::$thiss;
	}

	/**
	 * Sends the Body of the message to the browser.
	 *
	 * @return Response
	 */
	public static function SEND_BODY()
	{
		
		echo self::$body;

		return self::$thiss;
	}

	/**
	 * Perform a redirect to a new URL, in two flavors: header or location.
	 *
	 * @param string  $uri    The URI to redirect to
	 * @param string  $method
	 * @param integer $code   The type of redirection, defaults to 302
	 *
	 * @return $this
	 * @throws HTTPException For invalid status code.
	 */
	public static function REDIRECT(string $uri, string $method = 'auto', int $code = null)
	{
		// Assume 302 status code response; override if needed
		if (empty($code))
		{
			$code = 302;
		}

		// IIS environment likely? Use 'refresh' for better compatibility
		if ($method === 'auto' && isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false)
		{
			$method = 'refresh';
		}

		// override status code for HTTP/1.1 & higher
		// reference: http://en.wikipedia.org/wiki/Post/Redirect/Get
		if (isset($_SERVER['SERVER_PROTOCOL'], $_SERVER['REQUEST_METHOD']) && self::GET_PROTOCOL_VERSION() >= 1.1 && $method !== 'refresh')
		{
			$code = ($_SERVER['REQUEST_METHOD'] !== 'GET') ? 303 : ($code === 302 ? 307 : $code);
		}

		switch ($method)
		{
			case 'refresh':
				self::SET_HEADER('Refresh', '0;url=' . $uri);
				break;
			default:
				self::SET_HEADER('Location', $uri);
				break;
		}

		self::SET_STATUS_CODE($code);

		return self::$thiss;
	}

	/**
	 * Set a cookie
	 *
	 * Accepts an arbitrary number of binds (up to 7) or an associative
	 * array in the first parameter containing all the values.
	 *
	 * @param string|array $name     Cookie name or array containing binds
	 * @param string       $value    Cookie value
	 * @param array  $options
	 *
	 * @return $this
	 */
	public static function SET_COOKIE(
		$name,
		$value = '',
		$options = []
	)
	{
		if (is_array($name))
		{
			// always leave 'name' in last place, as the loop will break otherwise, due to $$item
			foreach (['samesite', 'value', 'expire', 'domain', 'path', 'prefix', 'secure', 'httponly', 'name'] as $item)
			{
				if (isset($name[$item]))
				{
					$$item = $name[$item];
				}
			}
		}

		if (is_numeric($options['expire']))
		{
			$expire = $options['expire'] > 0 ? time() + $options['expire'] : 0;
		}

		$cookie = new Cookie($name, $value, [
			'expires'  => $expire ?: 0,
			'domain'   => $options['domain'],
			'path'     => $options['path'],
			'prefix'   => $options['prefix']??'hkm_code',
			'secure'   => $options['secure'],
			'httponly' => $options['httponly'],
			'samesite' => $options['samesite'] ?? '',
		]);

		self::$cookieStore = self::$cookieStore->put($cookie);

		return self::$thiss;
	}

	/**
	 * Returns the `CookieStore` instance.
	 *
	 * @return CookieStore
	 */
	public static function GET_COOKIESTORE()
	{
		return self::$cookieStore;
	}

	/**
	 * Checks to see if the Response has a specified cookie or not.
	 *
	 * @param string      $name
	 * @param string|null $value
	 * @param string      $prefix
	 *
	 * @return boolean
	 */
	public static function HAS_COOKIE(string $name, string $value = null, string $prefix = ''): bool
	{
		$prefix = $prefix ?: Cookie::setDefaults()['prefix']; // to retain BC

		return self::$cookieStore->has($name, $prefix, $value);
	}

	/**
	 * Returns the cookie
	 *
	 * @param string|null $name
	 * @param string      $prefix
	 *
	 * @return Cookie[]|Cookie|null
	 */
	public static function GET_COOKIE(string $name = null, string $prefix = '')
	{
		if ((string) $name === '')
		{
			return self::$cookieStore->display();
		}

		try
		{
			$prefix = $prefix ?: Cookie::setDefaults()['prefix']; // to retain BC

			return self::$cookieStore->get($name, $prefix);
		}
		catch (CookieException $e)
		{
			hkm_log_message('error', $e->getMessage());
			return null;
		}
	}

	/**
	 * Sets a cookie to be deleted when the response is sent.
	 *
	 * @param string $name
	 * @param string $domain
	 * @param string $path
	 * @param string $prefix
	 *
	 * @return $this
	 */
	public static function DELETE_COOKIE(string $name = '', string $domain = '', string $path = '/', string $prefix = '')
	{
		if ($name === '')
		{
			return self::$thiss;
		}

		$prefix = $prefix ?: Cookie::setDefaults()['prefix']; // to retain BC

		$prefixed = $prefix . $name;
		$store    = self::$cookieStore;
		$found    = false;

		foreach ($store as $cookie)
		{
			if ($cookie->getPrefixedName() === $prefixed)
			{
				if ($domain !== $cookie->getDomain())
				{
					continue;
				}

				if ($path !== $cookie->getPath())
				{
					continue;
				}

				$cookie = $cookie->withValue('')->withExpired();
				$found  = true;

				self::$cookieStore = $store->put($cookie);
				break;
			}
		}

		if (! $found)
		{
			self::SET_COOKIE($name, '', '', $domain, $path, $prefix);
		}

		return self::$thiss;
	}

	/**
	 * Returns all cookies currently set.
	 *
	 * @return Cookie[]
	 */
	public static function GET_COOKIES()
	{
		return self::$cookieStore->display();
	}

	/**
	 * Actually sets the cookies.
	 */
	protected static function SEND_COOKIES()
	{
	
		if (self::$pretend)
		{
			return;
		}

		self::$cookieStore->dispatch();
	}

	/**
	 * Force a download.
	 *
	 * Generates the headers that force a download to happen. And
	 * sends the file to the browser.
	 *
	 * @param string      $filename The path to the file to send
	 * @param string|null $data     The data to be downloaded
	 * @param boolean     $setMime  Whether to try and send the actual MIME type
	 *
	 * @return DownloadResponse|null
	 */
	public static function DOWNLOAD(string $filename = '', $data = null, bool $setMime = false,$fileName=null)
	{
		if ($filename === '' || $data === '')
		{
			return null;
		}

		$filepath = '';
		if ($data === null)
		{
			$filepath = $filename;
			$filename = explode('/', str_replace(DIRECTORY_SEPARATOR, '/', $filename));
			$filename = $fileName===null?end($filename):($fileName!=''?$fileName:end($filename));
		}

		$response = new DownloadResponse($filename, $setMime);

		if ($filepath !== '')
		{
			$response::SET_FILE_PATH($filepath);
		}
		elseif ($data !== null)
		{
			$response::SET_BINARY($data);
		}

		return $response;
	}
}
