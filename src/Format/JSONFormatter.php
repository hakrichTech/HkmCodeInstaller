<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@hakrichteam.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Format;

use Hkm_code\Format\Exceptions\FormatException;

/**
 * JSON data formatter
 */
class JSONFormatter implements FormatterInterface
{
	/**
	 * Takes the given data and formats it.
	 *
	 * @param mixed $data
	 *
	 * @return string|boolean (JSON string | false)
	 */
	public static function FORMAT($data)
	{
		$config = hkm_config('Format');

		$options = $config::$formatterOptions['application/json'] ?? JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
		$options = $options | JSON_PARTIAL_OUTPUT_ON_ERROR;

		$options = ENVIRONMENT === 'production' ? $options : $options | JSON_PRETTY_PRINT;

		$result = json_encode($data, $options, 512);

		if (! in_array(json_last_error(), [JSON_ERROR_NONE, JSON_ERROR_RECURSION], true))
		{
			throw FormatException::FOR_INVALID_JSON(json_last_error_msg());
		}

		return $result;
	}

	//--------------------------------------------------------------------
}
