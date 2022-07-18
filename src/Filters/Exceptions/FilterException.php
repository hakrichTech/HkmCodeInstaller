<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@Hkm_code.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Filters\Exceptions;

use Hkm_code\Exceptions\ConfigException;
use Hkm_code\Exceptions\ExceptionInterface;

/**
 * FilterException
 */
class FilterException extends ConfigException implements ExceptionInterface
{
	/**
	 * Thrown when the provided alias is not within
	 * the list of configured filter aliases.
	 *
	 * @param string $alias
	 *
	 * @return static
	 */
	public static function FOR_NO_ALIAS(string $alias)
	{
		return new static(hkm_lang('Filters.noFilter', [$alias]));
	}

	/**
	 * Thrown when the filter class does not implement FilterInterface.
	 *
	 * @param string $class
	 *
	 * @return static
	 */
	public static function FOR_INCORRECT_INTERFACE(string $class)
	{
		return new static(hkm_lang('Filters.incorrectInterface', [$class]));
	}
}
