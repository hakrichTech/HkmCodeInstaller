<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@Hkm_code.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Format;

use Hkm_code\Format\Exceptions\FormatException;
use Hkm_code\Vezirion\BaseVezirion;

/**
 * The Format class is a convenient place to create Formatters.
 */
class Format
{
	/**
	 * Configuration instance
	 *
	 * @var FormatConfig
	 */
	protected static $config;

	/**
	 * Constructor.
	 *
	 * @param FormatConfig $config
	 */
	public function __construct(BaseVezirion $config)
	{
		self::$config = $config;
	}

	/**
	 * Returns the current configuration instance.
	 *
	 * @return FormatConfig
	 */
	public static function GET_CONFIG()
	{
		return self::$config;
	}

	/**
	 * A Factory method to return the appropriate formatter for the given mime type.
	 *
	 * @param string $mime
	 *
	 * @throws FormatException
	 *
	 * @return FormatterInterface
	 */
	public static function GET_FORMATTER(string $mime): FormatterInterface
	{
		if (! array_key_exists($mime, self::$config::$formatters))
		{
			throw FormatException::FOR_INVALID_MIME($mime);
		}

		$className = self::$config::$formatters[$mime];

		if (! class_exists($className))
		{
			throw FormatException::FOR_INVALID_FORMATTER($className);
		}

		$class = new $className();

		if (! $class instanceof FormatterInterface)
		{
			throw FormatException::FOR_INVALID_FORMATTER($className);
		}

		return $class;
	}
}
