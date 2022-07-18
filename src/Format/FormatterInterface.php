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

/**
 * Formatter interface
 */
interface FormatterInterface
{
	/**
	 * Takes the given data and formats it.
	 *
	 * @param string|array $data
	 *
	 * @return mixed
	 */
	public static function FORMAT($data);
}
