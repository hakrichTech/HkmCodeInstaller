<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@Hkm_code.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Cookie\Exceptions;

use Hkm_code\Exceptions\SystemException;

/**
 * CookieException is thrown for invalid cookies initialization and management.
 */
class CookieException extends SystemException
{
	/**
	 * Thrown for invalid type given for the "Expires" attribute.
	 *
	 * @param string $type
	 *
	 * @return static
	 */
	public static function forInvalidExpiresTime(string $type)
	{
		return new static(hkm_lang('Cookie.invalidExpiresTime', [$type]));
	}

	/**
	 * Thrown when the value provided for "Expires" is invalid.
	 *
	 * @return static
	 */
	public static function forInvalidExpiresValue()
	{
		return new static(hkm_lang('Cookie.invalidExpiresValue'));
	}

	/**
	 * Thrown when the cookie name contains invalid characters per RFC 2616.
	 *
	 * @param string $name
	 *
	 * @return static
	 */
	public static function forInvalidCookieName(string $name)
	{
		return new static(hkm_lang('Cookie.invalidCookieName', [$name]));
	}

	/**
	 * Thrown when the cookie name is empty.
	 *
	 * @return static
	 */
	public static function forEmptyCookieName()
	{
		return new static(hkm_lang('Cookie.emptyCookieName'));
	}

	/**
	 * Thrown when using the `__Secure-` prefix but the `Secure` attribute
	 * is not set to true.
	 *
	 * @return static
	 */
	public static function forInvalidSecurePrefix()
	{
		return new static(hkm_lang('Cookie.invalidSecurePrefix'));
	}

	/**
	 * Thrown when using the `__Host-` prefix but the `Secure` flag is not
	 * set, the `Domain` is set, and the `Path` is not `/`.
	 *
	 * @return static
	 */
	public static function forInvalidHostPrefix()
	{
		return new static(hkm_lang('Cookie.invalidHostPrefix'));
	}

	/**
	 * Thrown when the `SameSite` attribute given is not of the valid types.
	 *
	 * @param string $sameSite
	 *
	 * @return static
	 */
	public static function forInvalidSameSite(string $sameSite)
	{
		return new static(hkm_lang('Cookie.invalidSameSite', [$sameSite]));
	}

	/**
	 * Thrown when the `SameSite` attribute is set to `None` but the `Secure`
	 * attribute is not set.
	 *
	 * @return static
	 */
	public static function forInvalidSameSiteNone()
	{
		return new static(hkm_lang('Cookie.invalidSameSiteNone'));
	}

	/**
	 * Thrown when the `CookieStore` class is filled with invalid Cookie objects.
	 *
	 * @param array<string|integer> $data
	 *
	 * @return static
	 */
	public static function forInvalidCookieInstance(array $data)
	{
		return new static(hkm_lang('Cookie.invalidCookieInstance', $data));
	}

	/**
	 * Thrown when the queried Cookie object does not exist in the cookie collection.
	 *
	 * @param string[] $data
	 *
	 * @return static
	 */
	public static function forUnknownCookieInstance(array $data)
	{
		return new static(hkm_lang('Cookie.unknownCookieInstance', $data));
	}
}
