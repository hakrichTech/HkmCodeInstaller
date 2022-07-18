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

use Hkm_code\Cookie\CookieStore;
use Hkm_code\Exceptions\HTTP\HTTPException;
use Hkm_code\Vezirion\ServicesSystem;

/**
 * Handle a redirect response
 */
class RedirectResponse extends Response
{
	/**
	 * Sets the URI to redirect to and, optionally, the HTTP status code to use.
	 * If no code is provided it will be automatically determined.
	 *
	 * @param string       $uri    The URI to redirect to
	 * @param integer|null $code   HTTP status code
	 * @param string       $method
	 *
	 * @return $this
	 */
	public static function TO(string $uri, int $code = null, string $method = 'auto')
	{
		// If it appears to be a relative URL, then convert to full URL
		// for better security.
		hkm_helper('url');
		if (strpos($uri, 'http') !== 0)
		{
			
			$uri = hkm_site_url($uri);
		}

		return self::REDIRECT($uri, $method, $code);
	}

	/**
	 * Sets the URI to redirect to but as a reverse-routed or named route
	 * instead of a raw URI.
	 *
	 * @param string  $route
	 * @param array   $params
	 * @param integer $code
	 * @param string  $method
	 *
	 * @throws HTTPException
	 *
	 * @return $this
	 */
	public static function ROUTE(string $route, array $params = [], int $code = 302, string $method = 'auto')
	{
		$route = ServicesSystem::ROUTES()::REVERSE_ROUTE($route, ...$params);

		if (! $route)
		{
			throw HTTPException::FOR_INVALID_REDIRECT_ROUTE($route);
		}

		return self::REDIRECT(hkm_site_url($route), $method, $code);
	}

	/**
	 * Helper function to return to previous page.
	 *
	 * Example:
	 *  return REDIRECT()->BACK();
	 *
	 * @param integer|null $code
	 * @param string       $method
	 *
	 * @return $this
	 */
	public static function BACK(int $code = null, string $method = 'auto')
	{

		return self::REDIRECT(hkm_previous_url(), $method, $code);
	}

	/**
	 * Spehkmfies that the current $_GET and $_POST arrays should be
	 * packaged up with the response.
	 *
	 * It will then be available via the 'old()' helper function.
	 *
	 * @return $this
	 */
	public static function WITH_INPUT()
	{
		$session = ServicesSystem::SESSION();

		$session->flash('_hkm_old_input', [
			'get'  => $_GET ?? [],
			'post' => $_POST ?? [],
		]);

		// If the validation has any errors, transmit those back
		// so they can be displayed when the validation is handled
		// within a method different than displaying the form.
		$validation = ServicesSystem::VALIDATION();

		if ($validation->getErrors())
		{
			$session->flash('_hkm_validation_errors', serialize($validation->getErrors()));
		}

		return self::$thiss;
	}

	/**
	 * Adds a key and message to the session as Flashdata.
	 *
	 * @param string       $key
	 * @param string|array $message
	 *
	 * @return $this
	 */
	public static function WITH(string $key, $message)
	{
		ServicesSystem::SESSION()->flash($key, $message);

		return self::$thiss;
	}

	/**
	 * Copies any cookies from the global Response instance
	 * into this RedirectResponse. Useful when you've just
	 * set a cookie but need ensure that's actually sent
	 * with the response instead of lost.
	 *
	 * @return $this|RedirectResponse
	 */
	public static function WITH_COOKIES()
	{
		self::$cookieStore = new CookieStore(ServicesSystem::RESPONSE()::GET_COOKIES());

		return self::$thiss;
	}

	/**
	 * Copies any headers from the global Response instance
	 * into this RedirectResponse. Useful when you've just
	 * set a header be need to ensure its actually sent
	 * with the redirect response.
	 *
	 * @return $this|RedirectResponse
	 */
	public static function WITH_HEADERS()
	{
		foreach (ServicesSystem::RESPONSE()::HEADERS() as $name => $header)
		{
			self::SET_HEADER($name, $header);
		}

		return self::$thiss;
	}
}
