<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@hakrichteam.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Exceptions;

use OutOfBoundsException;

class PageNotFoundException extends OutOfBoundsException implements ExceptionInterface
{
	use DebugTraceableTrait;

	/**
	 * Error code
	 *
	 * @var integer
	 */
	protected $code = 404;

	public static function FOR_PAGE_NOT_FOUND(string $message = null)
	{
		return new static($message ?? hkm_lang('HTTP.pageNotFound'));
	}
	public static function FOR_PAGE_NOT_FOUND_PRODUCTION(string $message = null)
	{
		return new static($message ?? hkm_lang('HTTP.pageNotFoundProduction'));
	}

	public static function FOR_EMPTY_CONTROLLER()
	{
		return new static(hkm_lang('HTTP.emptyController'));
	}

	public static function FOR_CONTROLLER_NOT_FOUND(string $controller, string $method)
	{
		return new static(hkm_lang('HTTP.controllerNotFound', [$controller, $method]));
	}

	public static function FOR_METHOD_NOT_FOUND(string $method)
	{
		return new static(hkm_lang('HTTP.methodNotFound', [$method]));
	}
}
