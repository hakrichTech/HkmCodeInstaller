<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@hakrichteam.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\I18n\Exceptions;

use Hkm_code\Exceptions\SystemException;

/**
 * I18nException
 */
class I18nException extends SystemException
{
	/**
	 * Thrown when createFromFormat fails to receive a valid
	 * DateTime back from DateTime::createFromFormat.
	 *
	 * @param string $format
	 *
	 * @return static
	 */
	public static function forInvalidFormat(string $format)
	{
		return new static(hkm_lang('Time.invalidFormat', [$format]));
	}

	/**
	 * Thrown when the numeric representation of the month falls
	 * outside the range of allowed months.
	 *
	 * @param string $month
	 *
	 * @return static
	 */
	public static function forInvalidMonth(string $month)
	{
		return new static(hkm_lang('Time.invalidMonth', [$month]));
	}

	/**
	 * Thrown when the supplied day falls outside the range
	 * of allowed days.
	 *
	 * @param string $day
	 *
	 * @return static
	 */
	public static function forInvalidDay(string $day)
	{
		return new static(hkm_lang('Time.invalidDay', [$day]));
	}

	/**
	 * Thrown when the day provided falls outside the allowed
	 * last day for the given month.
	 *
	 * @param string $lastDay
	 * @param string $day
	 *
	 * @return static
	 */
	public static function forInvalidOverDay(string $lastDay, string $day)
	{
		return new static(hkm_lang('Time.invalidOverDay', [$lastDay, $day]));
	}

	/**
	 * Thrown when the supplied hour falls outside the
	 * range of allowed hours.
	 *
	 * @param string $hour
	 *
	 * @return static
	 */
	public static function forInvalidHour(string $hour)
	{
		return new static(hkm_lang('Time.invalidHour', [$hour]));
	}

	/**
	 * Thrown when the supplied minutes falls outside the
	 * range of allowed minutes.
	 *
	 * @param string $minutes
	 *
	 * @return static
	 */
	public static function forInvalidMinutes(string $minutes)
	{
		return new static(hkm_lang('Time.invalidMinutes', [$minutes]));
	}

	/**
	 * Thrown when the supplied seconds falls outside the
	 * range of allowed seconds.
	 *
	 * @param string $seconds
	 *
	 * @return static
	 */
	public static function forInvalidSeconds(string $seconds)
	{
		return new static(hkm_lang('Time.invalidSeconds', [$seconds]));
	}
}
