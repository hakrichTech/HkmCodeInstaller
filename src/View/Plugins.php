<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@hakrichteam.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\View;

use Hkm_code\HTTP\URI;
use Hkm_code\Vezirion\ServicesSystem;

/**
 * View plugins
 */
class Plugins
{
	/**
	 * Wrap helper function to use as view plugin.
	 *
	 * @return string|URI
	 */
	public static function currentURL()
	{
		return hkm_current_url();
	}

	//--------------------------------------------------------------------

	/**
	 * Wrap helper function to use as view plugin.
	 *
	 * @return URI|mixed|string
	 */
	public static function previousURL()
	{
		return previous_url();
	}

	//--------------------------------------------------------------------

	/**
	 * Wrap helper function to use as view plugin.
	 *
	 * @param array $params
	 *
	 * @return string
	 */
	public static function mailto(array $params = []): string
	{
		$email = $params['email'] ?? '';
		$title = $params['title'] ?? '';
		$attrs = $params['attributes'] ?? '';

		return mailto($email, $title, $attrs);
	}

	//--------------------------------------------------------------------

	/**
	 * Wrap helper function to use as view plugin.
	 *
	 * @param array $params
	 *
	 * @return string
	 */
	public static function safeMailto(array $params = []): string
	{
		$email = $params['email'] ?? '';
		$title = $params['title'] ?? '';
		$attrs = $params['attributes'] ?? '';

		return safe_mailto($email, $title, $attrs);
	}

	//--------------------------------------------------------------------

	/**
	 * Wrap helper function to use as view plugin.
	 *
	 * @param array $params
	 *
	 * @return string
	 */
	public static function hkm_lang(array $params = []): string
	{
		$line = array_shift($params);

		return hkm_lang($line, $params);
	}

	//--------------------------------------------------------------------

	/**
	 * Wrap helper function to use as view plugin.
	 *
	 * @param array $params
	 *
	 * @return string
	 */
	public static function ValidationErrors(array $params = []): string
	{
		$validator = ServicesSystem::VALIDATION();
		if (empty($params))
		{
			return $validator::LIST_ERRORS();
		}

		return $validator::SHOW_ERROR($params['field']);
	}

	//--------------------------------------------------------------------

	/**
	 * Wrap helper function to use as view plugin.
	 *
	 * @param array $params
	 *
	 * @return string|false
	 */
	public static function route(array $params = [])
	{
		return hkm_route_to(...$params);
	}

	//--------------------------------------------------------------------

	/**
	 * Wrap helper function to use as view plugin.
	 *
	 * @param array $params
	 *
	 * @return string
	 */
	public static function siteURL(array $params = []): string
	{
		return site_url(...$params);
	}
}
