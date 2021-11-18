<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@Hkm_code.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\I18n;

use DateTime;
use IntlCalendar;

/**
 * Class TimeDifference
 */
class TimeDifference
{
	/**
	 * The timestamp of the "current" time.
	 *
	 * @var IntlCalendar
	 */
	protected static $currentTime;

	/**
	 * The timestamp to compare the current time to.
	 *
	 * @var float
	 */
	protected static $testTime;

	/**
	 * Eras.
	 *
	 * @var float
	 */
	protected static $eras = 0;

	/**
	 * Years.
	 *
	 * @var float
	 */
	protected static $years = 0;

	/**
	 * Months.
	 *
	 * @var float
	 */
	protected static $months = 0;

	/**
	 * Weeks.
	 *
	 * @var integer
	 */
	protected static $weeks = 0;

	/**
	 * Days.
	 *
	 * @var integer
	 */
	protected static $days = 0;

	/**
	 * Hours.
	 *
	 * @var integer
	 */
	protected static $hours = 0;

	/**
	 * Minutes.
	 *
	 * @var integer
	 */
	protected static $minutes = 0;

	/**
	 * Seconds.
	 *
	 * @var integer
	 */
	protected static $seconds = 0;

	/**
	 * Difference in seconds.
	 *
	 * @var integer
	 */
	protected static $difference;
	protected static $thiss;

	/**
	 * Note: both parameters are required to be in the same timezone. No timezone
	 * shifting is done internally.
	 *
	 * @param DateTime $currentTime
	 * @param DateTime $testTime
	 */
	public  function __construct(DateTime $currentTime, DateTime $testTime)
	{
		self::$difference = $currentTime->getTimestamp() - $testTime->getTimestamp();
        self::$thiss = $this;
		$current = IntlCalendar::fromDateTime($currentTime);
		$time    = IntlCalendar::fromDateTime($testTime)->getTime();

		self::$currentTime = $current;
		self::$testTime    = $time;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the number of years of difference between the two.
	 *
	 * @param boolean $raw
	 *
	 * @return float|integer
	 */
	public static function GET_YEARS(bool $raw = false)
	{
		if ($raw)
		{
			return self::$difference / YEAR;
		}

		$time = clone(self::$currentTime);
		return $time->fieldDifference(self::$testTime, IntlCalendar::FIELD_YEAR);
	}

	/**
	 * Returns the number of months difference between the two dates.
	 *
	 * @param boolean $raw
	 *
	 * @return float|integer
	 */
	public static function GET_MONTHS(bool $raw = false)
	{
		if ($raw)
		{
			return self::$difference / MONTH;
		}

		$time = clone(self::$currentTime);
		return $time->fieldDifference(self::$testTime, IntlCalendar::FIELD_MONTH);
	}

	/**
	 * Returns the number of weeks difference between the two dates.
	 *
	 * @param boolean $raw
	 *
	 * @return float|integer
	 */
	public static function GET_WEEKS(bool $raw = false)
	{
		if ($raw)
		{
			return self::$difference / WEEK;
		}

		$time = clone(self::$currentTime);
		return (int) ($time->fieldDifference(self::$testTime, IntlCalendar::FIELD_DAY_OF_YEAR) / 7);
	}

	/**
	 * Returns the number of days difference between the two dates.
	 *
	 * @param boolean $raw
	 *
	 * @return float|integer
	 */
	public static function GET_DAYS(bool $raw = false)
	{
		if ($raw)
		{
			return self::$difference / DAY;
		}

		$time = clone(self::$currentTime);
		return $time->fieldDifference(self::$testTime, IntlCalendar::FIELD_DAY_OF_YEAR);
	}

	/**
	 * Returns the number of hours difference between the two dates.
	 *
	 * @param boolean $raw
	 *
	 * @return float|integer
	 */
	public static function GET_HOURS(bool $raw = false)
	{
		if ($raw)
		{
			return self::$difference / HOUR;
		}

		$time = clone(self::$currentTime);
		return $time->fieldDifference(self::$testTime, IntlCalendar::FIELD_HOUR_OF_DAY);
	}

	/**
	 * Returns the number of minutes difference between the two dates.
	 *
	 * @param boolean $raw
	 *
	 * @return float|integer
	 */
	public static function GET_MINUTES(bool $raw = false)
	{
		if ($raw)
		{
			return self::$difference / MINUTE;
		}

		$time = clone(self::$currentTime);
		return $time->fieldDifference(self::$testTime, IntlCalendar::FIELD_MINUTE);
	}

	/**
	 * Returns the number of seconds difference between the two dates.
	 *
	 * @param boolean $raw
	 *
	 * @return integer
	 */
	public static function GET_SECONDS(bool $raw = false)
	{
		if ($raw)
		{
			return self::$difference;
		}

		$time = clone(self::$currentTime);
		return $time->fieldDifference(self::$testTime, IntlCalendar::FIELD_SECOND);
	}

	/**
	 * Convert the time to human readable format
	 *
	 * @param string|null $locale
	 *
	 * @return string
	 */
	public static function HUMANIZE(string $locale = null): string
	{
		$current = clone(self::$currentTime);

		$years   = $current->fieldDifference(self::$testTime, IntlCalendar::FIELD_YEAR);
		$months  = $current->fieldDifference(self::$testTime, IntlCalendar::FIELD_MONTH);
		$days    = $current->fieldDifference(self::$testTime, IntlCalendar::FIELD_DAY_OF_YEAR);
		$hours   = $current->fieldDifference(self::$testTime, IntlCalendar::FIELD_HOUR_OF_DAY);
		$minutes = $current->fieldDifference(self::$testTime, IntlCalendar::FIELD_MINUTE);

		$phrase = null;

		if ($years !== 0)
		{
			$phrase = hkm_lang('Time.years', [abs($years)], $locale);
			$before = $years < 0;
		}
		elseif ($months !== 0)
		{
			$phrase = hkm_lang('Time.months', [abs($months)], $locale);
			$before = $months < 0;
		}
		elseif ($days !== 0 && (abs($days) >= 7))
		{
			$weeks  = ceil($days / 7);
			$phrase = hkm_lang('Time.weeks', [abs($weeks)], $locale);
			$before = $days < 0;
		}
		elseif ($days !== 0)
		{
			$phrase = hkm_lang('Time.days', [abs($days)], $locale);
			$before = $days < 0;
		}
		elseif ($hours !== 0)
		{
			$phrase = hkm_lang('Time.hours', [abs($hours)], $locale);
			$before = $hours < 0;
		}
		elseif ($minutes !== 0)
		{
			$phrase = hkm_lang('Time.minutes', [abs($minutes)], $locale);
			$before = $minutes < 0;
		}
		else
		{
			return hkm_lang('Time.now', [], $locale);
		}

		return $before
			? hkm_lang('Time.ago', [$phrase], $locale)
			: hkm_lang('Time.inFuture', [$phrase], $locale);
	}

	/**
	 * Allow property-like access to our calculated values.
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public  function __get($name)
	{
		$name   = ucfirst(strtolower($name));
		$method = "get{$name}";

		if (method_exists($this, $method))
		{
			return self::$$$method();
		}

		return null;
	}

	/**
	 * Allow property-like checking for our calculated values.
	 *
	 * @param string $name
	 *
	 * @return boolean
	 */
	public  function __isset($name)
	{
		$name   = ucfirst(strtolower($name));
		$method = "get{$name}";

		return method_exists($this, $method);
	}
}
