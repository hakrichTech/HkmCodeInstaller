<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@hakrichteam.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Hkm_code\HTTP\IncomingRequest;
use Hkm_code\HTTP\URI;
use Hkm_code\Exceptions\Pobohet\PobohetException;
use Hkm_code\HTTP\Request;
use Hkm_code\Vezirion\ServicesSystem;

function hkm_http_validate_url( $url ) {
	$original_url = $url;
	$url          = hkm_kses_bad_protocol( $url, array( 'http', 'https' ) );
	if ( ! $url || strtolower( $url ) !== strtolower( $original_url ) ) {
		return false;
	}

	$parsed_url = parse_url( $url );
	if ( ! $parsed_url || empty( $parsed_url['host'] ) ) {
		return false;
	}

	if ( isset( $parsed_url['user'] ) || isset( $parsed_url['pass'] ) ) {
		return false;
	}

	if ( false !== strpbrk( $parsed_url['host'], ':#?[]' ) ) {
		return false;
	}

	$parsed_home = parse_url( hkm_config('App',false)::$baseUrl );

	if ( isset( $parsed_home['host'] ) ) {
		$same_host = strtolower( $parsed_home['host'] ) === strtolower( $parsed_url['host'] );
	} else {
		$same_host = false;
	}

	if ( ! $same_host ) {
		$host = trim( $parsed_url['host'], '.' );
		if ( preg_match( '#^(([1-9]?\d|1\d\d|25[0-5]|2[0-4]\d)\.){3}([1-9]?\d|1\d\d|25[0-5]|2[0-4]\d)$#', $host ) ) {
			$ip = $host;
		} else {
			$ip = gethostbyname( $host );
			if ( $ip === $host ) { // Error condition for gethostbyname().
				return false;
			}
		}
		if ( $ip ) {
			$parts = array_map( 'intval', explode( '.', $ip ) );
			if ( 127 === $parts[0] || 10 === $parts[0] || 0 === $parts[0]
				|| ( 172 === $parts[0] && 16 <= $parts[1] && 31 >= $parts[1] )
				|| ( 192 === $parts[0] && 168 === $parts[1] )
			) {
				
				// if ( ! allowed_http_request_hosts(false, $host, $url ) ) {
				// 	return false;
				// }
			}
		}
	}

	

	if ( empty( $parsed_url['port'] ) ) {
		return $url;
	}

	$port = $parsed_url['port'];
	if ( 80 === $port || 443 === $port || 8080 === $port ) {
		return $url;
	}

	if ( $parsed_home && $same_host && isset( $parsed_home['port'] ) && $parsed_home['port'] === $port ) {
		return $url;
	}

	return false;
}
/**
 * Hkm_code URL Helpers
 */

if (! function_exists('hkm_get_uri'))
{
	/**
	 * Used by the other URL functions to build a
	 * framework-specific URI based on the App config.
	 *
	 * @internal Outside of the framework this should not be used directly.
	 *
	 * @param string   $relativePath May include queries or fragments
	 * @param |null $config
	 *
	 * @return URI
	 *
	 * @throws InvalidArgumentException For invalid paths or config
	 */
	function hkm_get_uri(string $relativePath = '',  $config = null): URI
	{
		$config = $config ??hkm_config('App',false);

		if ($config::$baseURL === '')
		{
			throw new InvalidArgumentException('hkm_get_uri() requires a valid baseURL.');
		}

		// If a full URI was passed then convert it
		if (is_int(strpos($relativePath, '://')))
		{
			$full         = new URI($relativePath);
			$relativePath = URI::CREATE_URI_STRING(null, null, $full::GET_PATH(), $full::GET_QUERY(), $full::GET_FRAGMENT());
		}

		$relativePath = URI::REMOVE_DOT_SEGMENTS($relativePath);

		// Build the full URL based on $config and $relativePath
		$url = rtrim($config::$baseURL, '/ ') . '/';

		// // Check for an index page
		// if ($config::$indexPage !== '')
		// {
		// 	$url .= $config::$indexPage;

		// 	// Check if we need a separator
		// 	if ($relativePath !== '' && $relativePath[0] !== '/' && $relativePath[0] !== '?')
		// 	{
		// 		$url .= '/';
		// 	}
		// }

		$url .= ltrim($relativePath,'/');

		$uri = new URI($url);

		// Check if the baseURL scheme needs to be coerced into its secure version
		if ($config::$forceGlobalSecureRequests && $uri::GET_SCHEME() === 'http')
		{
			$uri::SET_SCHEME('https');
		}


		return $uri;
	}
}

//--------------------------------------------------------------------

if (! function_exists('hkm_site_url'))
{
	/**
	 * Returns a site URL as defined by the App config.
	 *
	 * @param mixed       $relativePath URI string or array of URI segments
	 * @param string|null $scheme
	 * @param |null    $config       Alternate configuration to use
	 *
	 * @return string
	 */
	function hkm_site_url($relativePath = '', string $scheme = null,  $config = null): string
	{
		// Convert array of segments to a string
		if (is_array($relativePath))
		{
			$relativePath = implode('/', $relativePath);
		}

		$uri = hkm_get_uri($relativePath, $config);

		return URI::CREATE_URI_STRING($scheme ?? $uri::GET_SCHEME(), $uri::GET_AUTHORITY(), $uri::GET_PATH(), $uri::GET_QUERY(), $uri::GET_FRAGMENT());
	}
}

//--------------------------------------------------------------------

if (! function_exists('hkm_base_url'))
{
	/**
	 * Returns the base URL as defined by the App config.
	 * Base URLs are trimmed site URLs without the index page.
	 *
	 * @param  mixed  $relativePath URI string or array of URI segments
	 * @param  string $scheme
	 * @return string
	 */
	function hkm_base_url($relativePath = '', string $scheme = null): string
	{
		$config            = clone hkm_config('App');
		$config::$indexPage = '';

		return rtrim(hkm_site_url($relativePath, $scheme, $config), '/');
	}
}

//--------------------------------------------------------------------

if (! function_exists('hkm_current_url'))
{
	/**
	 * Returns the current full URL based on the IncomingRequest.
	 * String returns ignore query and fragment parts.
	 *
	 * @param boolean              $returnObject True to return an object instead of a string
	 * @param IncomingRequest|Request|null $request      A request to use when retrieving the path
	 *
	 * @return string|URI
	 */
	function hkm_current_url(bool $returnObject = false, $request = null)
	{
		$request = $request ?? ServicesSystem::REQUEST();
		/**
		 * @var IncomingRequest $request
		 */
		$path    = $request::GET_PATH();

		// Append queries and fragments
		if ($query = $request::GET_URI()::GET_QUERY())
		{
			$path .= '?' . $query;
		}
		if ($fragment = $request::GET_URI()::GET_FRAGMENT())
		{
			$path .= '#' . $fragment;
		}

		$uri = hkm_get_uri($path);

		return $returnObject ? $uri : URI::CREATE_URI_STRING($uri::GET_SCHEME(), $uri::GET_AUTHORITY(), $uri::GET_PATH());
	}
}

//--------------------------------------------------------------------

if (! function_exists('hkm_previous_url'))
{
	/**
	 * Returns the previous URL the current visitor was on. For security reasons
	 * we first check in a saved session variable, if it exists, and use that.
	 * If that's not available, however, we'll use a sanitized url from $_SERVER['HTTP_REFERER']
	 * which can be set by the user so is untrusted and not set by certain browsers/servers.
	 *
	 * @param boolean $returnObject
	 *
	 * @return URI|mixed|string
	 */
	function hkm_previous_url(bool $returnObject = false)
	{
		// Grab from the session first, if we have it,
		// since it's more reliable and safer.
		// Otherwise, grab a sanitized version from $_SERVER.
		$SESSION = ServicesSystem::SESSION();
				
		$referer = $SESSION->has('_hkm_previous_url')?$SESSION->get('_hkm_previous_url'): ServicesSystem::REQUEST()::GET_SERVER('HTTP_REFERER', FILTER_SANITIZE_URL);

		$referer = $referer ?? hkm_site_url('/');

		return $returnObject ? new URI($referer) : $referer;
	}
}

//--------------------------------------------------------------------

if (! function_exists('hkm_uri_string'))
{
	/**
	 * URL String
	 *
	 * Returns the path part of the current URL
	 *
	 * @param boolean $relative Whether the resulting path should be relative to baseURL
	 *
	 * @return string
	 */
	function hkm_uri_string(bool $relative = false): string
	{
		return $relative
			? ltrim(ServicesSystem::REQUEST()::GET_PATH(), '/')
			: ServicesSystem::REQUEST()::GET_URI()::GET_PATH();
	}
}

//--------------------------------------------------------------------

if (! function_exists('hkm_index_page'))
{
	/**
	 * Index page
	 *
	 * Returns the "hkm_index_page" from your config file
	 *
	 * @param  |null $altConfig Alternate configuration to use
	 * @return string
	 */
	function hkm_index_page( $altConfig = null): string
	{
		// use alternate config if provided, else default one
		$config = $altConfig ??hkm_config('App');

		return $config::$indexPage;
	}
}

// ------------------------------------------------------------------------

if (! function_exists('hkm_anchor'))
{
	/**
	 * Anchor Link
	 *
	 * Creates an hkm_anchor based on the local URL.
	 *
	 * @param mixed    $uri        URI string or array of URI segments
	 * @param string   $title      The link title
	 * @param mixed    $attributes Any attributes
	 * @param |null $altConfig  Alternate configuration to use
	 *
	 * @return string
	 */
	function hkm_anchor($uri = '', string $title = '', $attributes = '',  $altConfig = null): string
	{
		// use alternate config if provided, else default one
		$config = $altConfig ??hkm_config('App');

		$siteUrl = is_array($uri) ? hkm_site_url($uri, null, $config) : (preg_match('#^(\w+:)?//#i', $uri) ? $uri : hkm_site_url($uri, null, $config));
		// eliminate trailing slash
		$siteUrl = rtrim($siteUrl, '/');

		if ($title === '')
		{
			$title = $siteUrl;
		}

		if ($attributes !== '')
		{
			$attributes = hkm_stringify_attributes($attributes);
		}

		return '<a href="' . $siteUrl . '"' . $attributes . '>' . $title . '</a>';
	}
}

// ------------------------------------------------------------------------

if (! function_exists('hkm_anchor_popup'))
{
	/**
	 * Anchor Link - Pop-up version
	 *
	 * Creates an hkm_anchor based on the local URL. The link
	 * opens a new window based on the attributes specified.
	 *
	 * @param string   $uri        the URL
	 * @param string   $title      the link title
	 * @param mixed    $attributes any attributes
	 * @param |null $altConfig  Alternate configuration to use
	 *
	 * @return string
	 */
	function hkm_anchor_popup($uri = '', string $title = '', $attributes = false,  $altConfig = null): string
	{
		// use alternate config if provided, else default one
		$config = $altConfig ??hkm_config('App');

		$siteUrl = preg_match('#^(\w+:)?//#i', $uri) ? $uri : hkm_site_url($uri, null, $config);
		$siteUrl = rtrim($siteUrl, '/');

		if ($title === '')
		{
			$title = $siteUrl;
		}

		if ($attributes === false)
		{
			return '<a href="' . $siteUrl . '" onclick="window.open(\'' . $siteUrl . "', '_blank'); return false;\">" . $title . '</a>';
		}

		if (! is_array($attributes))
		{
			$attributes = [$attributes];

			// Ref: http://www.w3schools.com/jsref/met_win_open.asp
			$windowName = '_blank';
		}
		elseif (! empty($attributes['window_name']))
		{
			$windowName = $attributes['window_name'];
			unset($attributes['window_name']);
		}
		else
		{
			$windowName = '_blank';
		}

		foreach (['width' => '800', 'height' => '600', 'scrollbars' => 'yes', 'menubar' => 'no', 'status' => 'yes', 'resizable' => 'yes', 'screenx' => '0', 'screeny' => '0'] as $key => $val)
		{
			$atts[$key] = $attributes[$key] ?? $val;
			unset($attributes[$key]);
		}

		$attributes = hkm_stringify_attributes($attributes);

		return '<a href="' . $siteUrl
				. '" onclick="window.open(\'' . $siteUrl . "', '" . $windowName . "', '" . hkm_stringify_attributes($atts, true) . "'); return false;\""
				. $attributes . '>' . $title . '</a>';
	}
}

// ------------------------------------------------------------------------

if (! function_exists('hkm_mailto'))
{
	/**
	 * Mailto Link
	 *
	 * @param string $email      the email address
	 * @param string $title      the link title
	 * @param mixed  $attributes any attributes
	 *
	 * @return string
	 */
	function hkm_mailto(string $email, string $title = '', $attributes = ''): string
	{
		if (trim($title) === '')
		{
			$title = $email;
		}

		return '<a href="hkm_mailto:' . $email . '"' . hkm_stringify_attributes($attributes) . '>' . $title . '</a>';
	}
}

// ------------------------------------------------------------------------

if (! function_exists('hkm_safe_mailto'))
{
	/**
	 * Encoded Mailto Link
	 *
	 * Create a spam-protected hkm_mailto link written in Javascript
	 *
	 * @param string $email      the email address
	 * @param string $title      the link title
	 * @param mixed  $attributes any attributes
	 *
	 * @return string
	 */
	function hkm_safe_mailto(string $email, string $title = '', $attributes = ''): string
	{
		if (trim($title) === '')
		{
			$title = $email;
		}

		$x = str_split('<a href="mailto:', 1);

		for ($i = 0, $l = strlen($email); $i < $l; $i ++)
		{
			$x[] = '|' . ord($email[$i]);
		}

		$x[] = '"';

		if ($attributes !== '')
		{
			if (is_array($attributes))
			{
				foreach ($attributes as $key => $val)
				{
					$x[] = ' ' . $key . '="';
					for ($i = 0, $l = strlen($val); $i < $l; $i ++)
					{
						$x[] = '|' . ord($val[$i]);
					}
					$x[] = '"';
				}
			}
			else
			{
				for ($i = 0, $l = mb_strlen($attributes); $i < $l; $i ++)
				{
					$x[] = mb_substr($attributes, $i, 1);
				}
			}
		}

		$x[] = '>';

		$temp = [];
		for ($i = 0, $l = strlen($title); $i < $l; $i ++)
		{
			$ordinal = ord($title[$i]);

			if ($ordinal < 128)
			{
				$x[] = '|' . $ordinal;
			}
			else
			{
				if (empty($temp))
				{
					$count = ($ordinal < 224) ? 2 : 3;
				}

				$temp[] = $ordinal;
				if (count($temp) === $count) // @phpstan-ignore-line
				{
					$number = ($count === 3) ? (($temp[0] % 16) * 4096) + (($temp[1] % 64) * 64) + ($temp[2] % 64) : (($temp[0] % 32) * 64) + ($temp[1] % 64);
					$x[]    = '|' . $number;
					$count  = 1;
					$temp   = [];
				}
			}
		}

		$x[] = '<';
		$x[] = '/';
		$x[] = 'a';
		$x[] = '>';

		$x = array_reverse($x);

		// improve obfuscation by eliminating newlines & whitespace
		$output = '<script type="text/javascript">'
				. 'var l=new Array();';

		foreach ($x as $i => $value)
		{
			$output .= 'l[' . $i . "] = '" . $value . "';";
		}

		return $output . ('for (var i = l.length-1; i >= 0; i=i-1) {'
				. "if (l[i].substring(0, 1) === '|') document.write(\"&#\"+unescape(l[i].substring(1))+\";\");"
				. 'else document.write(unescape(l[i]));'
				. '}'
				. '</script>');
	}
}

// ------------------------------------------------------------------------

if (! function_exists('hkm_auto_link'))
{
	/**
	 * Auto-linker
	 *
	 * Automatically links URL and Email addresses.
	 * Note: There's a bit of extra code here to deal with
	 * URLs or emails that end in a period. We'll strip these
	 * off and add them after the link.
	 *
	 * @param string  $str   the string
	 * @param string  $type  the type: email, url, or both
	 * @param boolean $popup whether to create pop-up links
	 *
	 * @return string
	 */
	function hkm_auto_link(string $str, string $type = 'both', bool $popup = false): string
	{
		// Find and replace any URLs.
		if ($type !== 'email' && preg_match_all('#(\w*://|www\.)[^\s()<>;]+\w#i', $str, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER))
		{
			// Set our target HTML if using popup links.
			$target = ($popup) ? ' target="_blank"' : '';

			// We process the links in reverse order (last -> first) so that
			// the returned string offsets from preg_match_all() are not
			// moved as we add more HTML.
			foreach (array_reverse($matches) as $match)
			{
				// $match[0] is the matched string/link
				// $match[1] is either a protocol prefix or 'www.'
				//
				// With PREG_OFFSET_CAPTURE, both of the above is an array,
				// where the actual value is held in [0] and its offset at the [1] index.
				$a   = '<a href="' . (strpos($match[1][0], '/') ? '' : 'http://') . $match[0][0] . '"' . $target . '>' . $match[0][0] . '</a>';
				$str = substr_replace($str, $a, $match[0][1], strlen($match[0][0]));
			}
		}

		// Find and replace any emails.
		if ($type !== 'url' && preg_match_all('#([\w\.\-\+]+@[a-z0-9\-]+\.[a-z0-9\-\.]+[^[:punct:]\s])#i', $str, $matches, PREG_OFFSET_CAPTURE))
		{
			foreach (array_reverse($matches[0]) as $match)
			{
				if (filter_var($match[0], FILTER_VALIDATE_EMAIL) !== false)
				{
					$str = substr_replace($str, hkm_safe_mailto($match[0]), $match[1], strlen($match[0]));
				}
			}
		}

		return $str;
	}
}

// ------------------------------------------------------------------------

if (! function_exists('hkm_prep_url'))
{
	/**
	 * Prep URL - Simply adds the http:// or https:// part if no scheme is included.
	 *
	 * Formerly used URI, but that does not play nicely with URIs missing
	 * the scheme.
	 *
	 * @param  string  $str    the URL
	 * @param  boolean $secure set true if you want to force https://
	 * @return string
	 */
	function hkm_prep_url(string $str = '', bool $secure = false): string
	{
		if (in_array($str, ['http://', 'https://', '//', ''], true))
		{
			return '';
		}

		if (parse_url($str, PHP_URL_SCHEME) === null)
		{
			$str = 'http://' . ltrim($str, '/');
		}

		// force replace http:// with https://
		if ($secure)
		{
			$str = preg_replace('/^(?:http):/i', 'https:', $str);
		}

		return $str;
	}
}

// ------------------------------------------------------------------------

if (! function_exists('hkm_url_title'))
{
	/**
	 * Create URL Title
	 *
	 * Takes a "title" string as input and creates a
	 * human-friendly URL string with a "separator" string
	 * as the word separator.
	 *
	 * @param  string  $str       Input string
	 * @param  string  $separator Word separator (usually '-' or '_')
	 * @param  boolean $lowercase Whether to transform the output string to lowercase
	 * @return string
	 */
	function hkm_url_title(string $str, string $separator = '-', bool $lowercase = false): string
	{
		$qSeparator = preg_quote($separator, '#');

		$trans = [
			'&.+?;'                  => '',
			'[^\w\d\pL\pM _-]'       => '',
			'\s+'                    => $separator,
			'(' . $qSeparator . ')+' => $separator,
		];

		$str = strip_tags($str);
		foreach ($trans as $key => $val)
		{
			$str = preg_replace('#' . $key . '#iu', $val, $str);
		}

		if ($lowercase === true)
		{
			$str = mb_strtolower($str);
		}

		return trim(trim($str, $separator));
	}
}

// ------------------------------------------------------------------------

if (! function_exists('hkm_mb_url_title'))
{
	/**
	 * Create URL Title that takes into account accented characters
	 *
	 * Takes a "title" string as input and creates a
	 * human-friendly URL string with a "separator" string
	 * as the word separator.
	 *
	 * @param  string  $str       Input string
	 * @param  string  $separator Word separator (usually '-' or '_')
	 * @param  boolean $lowercase Whether to transform the output string to lowercase
	 * @return string
	 */
	function hkm_mb_url_title(string $str, string $separator = '-', bool $lowercase = false): string
	{
		hkm_helper('text');

		return hkm_url_title(convert_accented_characters($str), $separator, $lowercase);
	}
}


if (! function_exists('hkm_is_url_valid')) {
    
	function hkm_is_url_valid($url)
	{
		$headers = @get_headers($url);
  
		// Use condition to check the existence of URL
		if($headers && strpos( $headers[0], '200')) return true;
		else{
			$curl = curl_init($url);
  
			// Use curl_setopt() to set an option for cURL transfer
			curl_setopt($curl, CURLOPT_NOBODY, true);
			
			// Use curl_exec() to perform cURL session
			$result = curl_exec($curl);
			
			if ($result !== false) {
				
				// Use curl_getinfo() to get information
				// regarding a specific transfer
				$statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE); 
				
				if ($statusCode == 404) return false;
				else return true;
				
			}
			else return true;
			
		}
		
	}
}

//--------------------------------------------------------------------

if (! function_exists('hkm_url_to'))
{
	/**
	 * Get the full, absolute URL to a controller method
	 * (with additional arguments)
	 *
	 * @param string $controller
	 * @param mixed  ...$args
	 *
	 * @throws PobohetException
	 *
	 * @return string
	 */
	function hkm_url_to(string $controller, ...$args): string
	{
		if (! $route = hkm_route_to($controller, ...$args))
		{
			$explode = explode('::', $controller);

			if (isset($explode[1]))
			{
				throw PobohetException::FOR_CONTROLLER_NOT_FOUND($explode[0], $explode[1]);
			}

			throw PobohetException::FOR_INVALID_ROUTE($controller);
		}

		return hkm_site_url($route);
	}
}

if (! function_exists('hkm_url_is'))
{
	/**
	 * Determines if current url path contains
	 * the given path. It may contain a wildcard (*)
	 * which will allow any valid character.
	 *
	 * Example:
	 *   if (hkm_url_is('admin*)) ...
	 *
	 * @param string $path
	 *
	 * @return boolean
	 */
	function hkm_url_is(string $path): bool
	{
		// Setup our regex to allow wildcards
		$path        = '/' . trim(str_replace('*', '(\S)*', $path), '/ ');
		$currentPath = '/' . trim(hkm_uri_string(true), '/ ');

		return (bool) preg_match("|^{$path}$|", $currentPath, $matches);
	}
}


/**
 * A wrapper for PHP's parse_url() function that handles consistency in the return
 * values across PHP versions.
 *
 * PHP 5.4.7 expanded parse_url()'s ability to handle non-absolute url's, including
 * schemeless and relative url's with :// in the path. This function works around
 * those limitations providing a standard output on PHP 5.2~5.4+.
 *
 * Secondly, across various PHP versions, schemeless URLs starting containing a ":"
 * in the query are being handled inconsistently. This function works around those
 * differences as well.
 *
 * @since 4.4.0
 * @since 4.7.0 The `$component` parameter was added for parity with PHP's `parse_url()`.
 *
 * @link https://www.php.net/manual/en/function.parse-url.php
 *
 * @param string $url       The URL to parse.
 * @param int    $component The specific component to retrieve. Use one of the PHP
 *                          predefined constants to specify which one.
 *                          Defaults to -1 (= return all parts as an array).
 * @return mixed False on parse failure; Array of URL components on success;
 *               When a specific component has been requested: null if the component
 *               doesn't exist in the given URL; a string or - in the case of
 *               PHP_URL_PORT - integer when it does. See parse_url()'s return values.
 */
function hkm_parse_url( $url, $component = -1 ) {
	$to_unset = array();
	$url      = (string) $url;

	if ( '//' === substr( $url, 0, 2 ) ) {
		$to_unset[] = 'scheme';
		$url        = 'placeholder:' . $url;
	} elseif ( '/' === substr( $url, 0, 1 ) ) {
		$to_unset[] = 'scheme';
		$to_unset[] = 'host';
		$url        = 'placeholder://placeholder' . $url;
	}

	$parts = parse_url( $url );

	if ( false === $parts ) {
		// Parsing failure.
		return $parts;
	}

	// Remove the placeholder values.
	foreach ( $to_unset as $key ) {
		unset( $parts[ $key ] );
	}

	return _get_component_from_parsed_url_array( $parts, $component );
}


/**
 * Retrieve a specific component from a parsed URL array.
 *
 * @internal
 *
 * @access private
 *
 * @link https://www.php.net/manual/en/function.parse-url.php
 *
 * @param array|false $url_parts The parsed URL. Can be false if the URL failed to parse.
 * @param int         $component The specific component to retrieve. Use one of the PHP
 *                               predefined constants to specify which one.
 *                               Defaults to -1 (= return all parts as an array).
 * @return mixed False on parse failure; Array of URL components on success;
 *               When a specific component has been requested: null if the component
 *               doesn't exist in the given URL; a string or - in the case of
 *               PHP_URL_PORT - integer when it does. See parse_url()'s return values.
 */
function _get_component_from_parsed_url_array( $url_parts, $component = -1 ) {
	if ( -1 === $component ) {
		return $url_parts;
	}

	$key = _hkm_translate_php_url_constant_to_key( $component );
	if ( false !== $key && is_array( $url_parts ) && isset( $url_parts[ $key ] ) ) {
		return $url_parts[ $key ];
	} else {
		return null;
	}
}

/**
 * Translate a PHP_URL_* constant to the named array keys PHP uses.
 *
 * @internal
 *
 * @access private
 *
 * @link https://www.php.net/manual/en/url.constants.php
 *
 * @param int $constant PHP_URL_* constant.
 * @return string|false The named key or false.
 */
function _hkm_translate_php_url_constant_to_key( $constant ) {
	$translation = array(
		PHP_URL_SCHEME   => 'scheme',
		PHP_URL_HOST     => 'host',
		PHP_URL_PORT     => 'port',
		PHP_URL_USER     => 'user',
		PHP_URL_PASS     => 'pass',
		PHP_URL_PATH     => 'path',
		PHP_URL_QUERY    => 'query',
		PHP_URL_FRAGMENT => 'fragment',
	);

	if ( isset( $translation[ $constant ] ) ) {
		return $translation[ $constant ];
	} else {
		return false;
	}
}

