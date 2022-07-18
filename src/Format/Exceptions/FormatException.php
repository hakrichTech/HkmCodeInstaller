<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@hakrichteam.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Format\Exceptions;

use Hkm_code\Exceptions\DebugTraceableTrait;
use Hkm_code\Exceptions\ExceptionInterface;
use RuntimeException;

/**
 * FormatException
 */
class FormatException extends RuntimeException implements ExceptionInterface
{
	use DebugTraceableTrait;

	/**
	 * Thrown when the instantiated class does not exist.
	 *
	 * @param string $class
	 *
	 * @return FormatException
	 */
	public static function FOR_INVALID_FORMATTER(string $class)
	{
		return new static(hkm_lang('Format.invalidFormatter', [$class]));
	}

	/**
	 * Thrown in JSONFormatter when the json_encode produces
	 * an error code other than JSON_ERROR_NONE and JSON_ERROR_RECURSION.
	 *
	 * @param string $error
	 *
	 * @return FormatException
	 */
	public static function FOR_INVALID_JSON(string $error = null)
	{
		return new static(hkm_lang('Format.invalidJSON', [$error]));
	}

	/**
	 * Thrown when the supplied MIME type has no
	 * defined Formatter class.
	 *
	 * @param string $mime
	 *
	 * @return FormatException
	 */
	public static function FOR_INVALID_MIME(string $mime)
	{
		return new static(hkm_lang('Format.invalidMime', [$mime]));
	}

	/**
	 * Thrown on XMLFormatter when the `simplexml` extension
	 * is not installed.
	 *
	 * @return FormatException
	 *
	 * @codeCoverageIgnore
	 */
	public static function FOR_MISSING_EXTENSION()
	{
		return new static(hkm_lang('Format.missingExtension'));
	}
}
