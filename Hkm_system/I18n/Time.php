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

use Hkm_code\I18n\Exceptions\I18nException;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use IntlCalendar;
use IntlDateFormatter;
use Locale;

/**
 * Class Time
 *
 * A localized date/time package inspired
 * by Nesbot/Carbon and CakePHP/Chronos.
 *
 * Requires the intl PHP extension.
 *
 * @property string $date
 */
class Time extends DateTime
{
	/**
	 * @var DateTimeZone
	 */
	protected static $timezone;

	/**
	 * @var string
	 */
	protected static $locale;

	/**
	 * Format to use when displaying datetime through __toString
	 *
	 * @var string
	 */
	protected static $toStringFormat = 'yyyy-MM-dd HH:mm:ss';

	/**
	 * Used to check time string to determine if it is relative time or not....
	 *
	 * @var string
	 */
	protected  static $relativePattern = '/this|next|last|tomorrow|yesterday|midnight|today|[+-]|first|last|ago/i';

	/**
	 * @var static|DateTimeInterface|null
	 */
	protected  static $testNow;
	protected  static $thiss;

	//--------------------------------------------------------------------
	// Constructors
	//--------------------------------------------------------------------

	/**
	 * Time constructor.
	 *
	 * @param string|null              $time
	 * @param DateTimeZone|string|null $timezone
	 * @param string|null              $locale
	 *
	 * @throws Exception
	 */
	public function __construct(string $time = null, $timezone = null, string $locale = null)
	{
		// If no locale was provided, grab it from Locale (set by IncomingRequest for web requests)
		self::$locale = ! empty($locale) ? $locale : Locale::getDefault();

		self::$thiss = $this;
		// If a test instance has been provided, use it instead.
		if (is_null($time) && static::$testNow instanceof Time)
		{
			if (empty($timezone))
			{
				$timezone = static::$testNow->getTimezone();
			}

			$time = static::$testNow::TO_DATE_TIME_STRING();
		}

		$timezone       = ! empty($timezone) ? $timezone : date_default_timezone_get();
		self::$timezone = $timezone instanceof DateTimeZone ? $timezone : new DateTimeZone($timezone);

		// If the time string was a relative string (i.e. 'next Tuesday')
		// then we need to adjust the time going in so that we have a current
		// timezone to work with.
		if (! empty($time) && (is_string($time) && static::HAS_RELATIVE_KEYWORDS($time)))
		{
			$instance = new DateTime('now', self::$timezone);
			$instance->modify($time);
			$time = $instance->format('Y-m-d H:i:s');
		}

		parent::__construct($time, self::$timezone);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a new Time instance with the timezone set.
	 *
	 * @param string|DateTimeZone|null $timezone
	 * @param string|null              $locale
	 *
	 * @return Time
	 * @throws Exception
	 */
	public  static function NOW($timezone = null, string $locale = null)
	{
		return new Time(null, $timezone, $locale);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a new Time instance while parsing a datetime string.
	 *
	 * Example:
	 *  $time = Time::parse('first day of December 2008');
	 *
	 * @param string                   $datetime
	 * @param DateTimeZone|string|null $timezone
	 * @param string|null              $locale
	 *
	 * @return Time
	 * @throws Exception
	 */
	public  static function PARSE(string $datetime, $timezone = null, string $locale = null)
	{
		return new Time($datetime, $timezone, $locale);
	}

	//--------------------------------------------------------------------

	/**
	 * Return a new time with the time set to midnight.
	 *
	 * @param DateTimeZone|string|null $timezone
	 * @param string|null              $locale
	 *
	 * @return Time
	 * @throws Exception
	 */
	public static  function TODAY($timezone = null, string $locale = null)
	{
		return new Time(date('Y-m-d 00:00:00'), $timezone, $locale);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an instance set to midnight yesterday morning.
	 *
	 * @param DateTimeZone|string|null $timezone
	 * @param string|null              $locale
	 *
	 * @return Time
	 * @throws Exception
	 */
	public static  function YESTERDAY($timezone = null, string $locale = null)
	{
		return new Time(date('Y-m-d 00:00:00', strtotime('-1 day')), $timezone, $locale);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an instance set to midnight tomorrow morning.
	 *
	 * @param DateTimeZone|string|null $timezone
	 * @param string|null              $locale
	 *
	 * @return Time
	 * @throws Exception
	 */
	public static  function TOMORROW($timezone = null, string $locale = null)
	{
		return new Time(date('Y-m-d 00:00:00', strtotime('+1 day')), $timezone, $locale);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a new instance based on the year, month and day. If any of those three
	 * are left empty, will default to the current value.
	 *
	 * @param integer|null             $year
	 * @param integer|null             $month
	 * @param integer|null             $day
	 * @param DateTimeZone|string|null $timezone
	 * @param string|null              $locale
	 *
	 * @return Time
	 * @throws Exception
	 */
	public static function CREATE_FROM_DATE(int $year = null, int $month = null, int $day = null, $timezone = null, string $locale = null)
	{
		return static::create($year, $month, $day, null, null, null, $timezone, $locale);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a new instance with the date set to today, and the time set to the values passed in.
	 *
	 * @param integer|null             $hour
	 * @param integer|null             $minutes
	 * @param integer|null             $seconds
	 * @param DateTimeZone|string|null $timezone
	 * @param string|null              $locale
	 *
	 * @return Time
	 * @throws Exception
	 */
	public static function CREATE_FROM_TIME(int $hour = null, int $minutes = null, int $seconds = null, $timezone = null, string $locale = null)
	{
		return static::CREATE(null, null, null, $hour, $minutes, $seconds, $timezone, $locale);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a new instance with the date time values individually set.
	 *
	 * @param integer|null             $year
	 * @param integer|null             $month
	 * @param integer|null             $day
	 * @param integer|null             $hour
	 * @param integer|null             $minutes
	 * @param integer|null             $seconds
	 * @param DateTimeZone|string|null $timezone
	 * @param string|null              $locale
	 *
	 * @return Time
	 * @throws Exception
	 */
	public static  function CREATE(int $year = null, int $month = null, int $day = null, int $hour = null, int $minutes = null, int $seconds = null, $timezone = null, string $locale = null)
	{
		$year    = is_null($year) ? date('Y') : $year;
		$month   = is_null($month) ? date('m') : $month;
		$day     = is_null($day) ? date('d') : $day;
		$hour    = empty($hour) ? 0 : $hour;
		$minutes = empty($minutes) ? 0 : $minutes;
		$seconds = empty($seconds) ? 0 : $seconds;

		return new Time(date('Y-m-d H:i:s', strtotime("{$year}-{$month}-{$day} {$hour}:{$minutes}:{$seconds}")), $timezone, $locale);
	}

	//--------------------------------------------------------------------

	/**
	 * Provides a replacement for DateTime's own createFromFormat function, that provides
	 * more flexible timeZone handling
	 *
	 * @param string                   $format
	 * @param string                   $datetime
	 * @param DateTimeZone|string|null $timeZone
	 *
	 * @return Time
	 * @throws Exception
	 */
	public static function CREATE_FROM_FORMAT($format, $datetime, $timeZone = null)
	{
		if (! $date = parent::createFromFormat($format, $datetime))
		{
			throw I18nException::forInvalidFormat($format);
		}

		return new Time($date->format('Y-m-d H:i:s'), $timeZone);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a new instance with the datetime set based on the provided UNIX timestamp.
	 *
	 * @param integer                  $timestamp
	 * @param DateTimeZone|string|null $timezone
	 * @param string|null              $locale
	 *
	 * @return Time
	 * @throws Exception
	 */
	public static function CREATE_FROM_TIMESTAMP(int $timestamp, $timezone = null, string $locale = null)
	{
		return new Time(gmdate('Y-m-d H:i:s', $timestamp), $timezone ?? 'UTC', $locale);
	}

	//--------------------------------------------------------------------

	/**
	 * Takes an instance of DateTimeInterface and returns an instance of Time with it's same values.
	 *
	 * @param DateTimeInterface $dateTime
	 * @param string|null       $locale
	 *
	 * @return Time
	 * @throws Exception
	 */
	public static  function CREATE_FROM_INSTANCE(DateTimeInterface $dateTime, string $locale = null)
	{
		$date     = $dateTime->format('Y-m-d H:i:s');
		$timezone = $dateTime->getTimezone();

		return new Time($date, $timezone, $locale);
	}

	//--------------------------------------------------------------------

	/**
	 * Takes an instance of DateTime and returns an instance of Time with it's same values.
	 *
	 * @param DateTime    $dateTime
	 * @param string|null $locale
	 *
	 * @return Time
	 * @throws Exception
	 *
	 * @deprecated         Use createFromInstance() instead
	 * @codeCoverageIgnore
	 */
	public static function INSTANCE(DateTime $dateTime, string $locale = null)
	{
		return self::CREATE_FROM_INSTANCE($dateTime, $locale);
	}

	//--------------------------------------------------------------------

	/**
	 * Converts the current instance to a mutable DateTime object.
	 *
	 * @return DateTime
	 * @throws Exception
	 */
	public static function TO_DATE_TIME()
	{
		$dateTime = new DateTime('', self::$thiss->getTimezone());
		$dateTime->setTimestamp(self::$thiss->getTimestamp());

		return $dateTime;
	}

	//--------------------------------------------------------------------
	// For Testing
	//--------------------------------------------------------------------

	/**
	 * Creates an instance of Time that will be returned during testing
	 * when calling 'Time::now' instead of the current time.
	 *
	 * @param Time|DateTimeInterface|string|null $datetime
	 * @param DateTimeZone|string|null           $timezone
	 * @param string|null                        $locale
	 *
	 * @throws Exception
	 */
	public static  function SET_TEST_NOW($datetime = null, $timezone = null, string $locale = null)
	{
		// Reset the test instance
		if (is_null($datetime))
		{
			static::$testNow = null;
			return;
		}

		// Convert to a Time instance
		if (is_string($datetime))
		{
			$datetime = new Time($datetime, $timezone, $locale);
		}
		elseif ($datetime instanceof DateTimeInterface && ! $datetime instanceof Time)
		{
			$datetime = new Time($datetime->format('Y-m-d H:i:s'), $timezone);
		}

		static::$testNow = $datetime;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns whether we have a testNow instance saved.
	 *
	 * @return boolean
	 */
	public static function HAS_TEST_NOW(): bool
	{
		return ! is_null(static::$testNow);
	}

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// Getters
	//--------------------------------------------------------------------

	/**
	 * Returns the localized Year
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function GET_YEAR(): string
	{
		return self::TO_LOCALIZED_STRING('y');
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the localized Month
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function GET_MONTH(): string
	{
		return self::TO_LOCALIZED_STRING('M');
	}

	//--------------------------------------------------------------------

	/**
	 * Return the localized day of the month.
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function GET_DAY(): string
	{
		return self::TO_LOCALIZED_STRING('l');
	}

	//--------------------------------------------------------------------

	/**
	 * Return the localized hour (in 24-hour format).
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function GET_HOUR(): string
	{
		return self::TO_LOCALIZED_STRING('H');
	}

	//--------------------------------------------------------------------

	/**
	 * Return the localized minutes in the hour.
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function GET_MINUTE(): string
	{
		return self::TO_LOCALIZED_STRING('m');
	}

	//--------------------------------------------------------------------

	/**
	 * Return the localized seconds
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function GET_SECOND(): string
	{
		return self::TO_LOCALIZED_STRING('s');
	}

	//--------------------------------------------------------------------

	/**
	 * Return the index of the day of the week
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function GET_DAY_OF_WEEK(): string
	{
		return self::TO_LOCALIZED_STRING('d');
	}

	//--------------------------------------------------------------------

	/**
	 * Return the index of the day of the year
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function GET_DAY_OF_YEAR(): string
	{
		return self::TO_LOCALIZED_STRING('z');
	}

	//--------------------------------------------------------------------

	/**
	 * Return the index of the week in the month
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function GET_WEEK_OF_MONTH(): string
	{
		return self::TO_LOCALIZED_STRING('j');
	}

	//--------------------------------------------------------------------

	/**
	 * Return the index of the week in the year
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function GET_WEEK_OF_YEAR(): string
	{
		return self::TO_LOCALIZED_STRING('w');
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the age in years from the "current" date and 'now'
	 *
	 * @return integer
	 * @throws Exception
	 */
	public static function GET_AGE()
	{
		$now  = Time::NOW()->getTimestamp();
		$time = self::$thiss->getTimestamp();

		// future dates have no age
		return max(0, date('Y', $now) - date('Y', $time));
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the number of the current quarter for the year.
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function GET_QUARTER(): string
	{
		return self::TO_LOCALIZED_STRING('Q');
	}

	//--------------------------------------------------------------------

	/**
	 * Are we in daylight savings time currently?
	 *
	 * @return boolean
	 */
	public static function GET_DST(): bool
	{
		// grab the transactions that would affect today
		$start       = strtotime('-1 year', self::$thiss->getTimestamp());
		$end         = strtotime('+2 year', $start);
		$transitions = self::$timezone->getTransitions($start, $end);

		$daylightSaving = false;
		foreach ($transitions as $transition)
		{
			if ($transition['time'] > self::$thiss->format('U'))
			{
				$daylightSaving = (bool) $transition['isdst'] ?? $daylightSaving;
				break;
			}
		}
		return $daylightSaving;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns boolean whether the passed timezone is the same as
	 * the local timezone.
	 *
	 * @return boolean
	 */
	public static function GET_LOCAL(): bool
	{
		$local = date_default_timezone_get();

		return $local === self::$timezone->getName();
	}

	//--------------------------------------------------------------------

	/**
	 * Returns boolean whether object is in UTC.
	 *
	 * @return boolean
	 */
	public static function GET_UTC(): bool
	{
		return self::$thiss->getOffset() === 0;
	}

	/**
	 * Returns the name of the current timezone.
	 *
	 * @return string
	 */
	public static function GET_TIMEZONE_NAME(): string
	{
		return self::$timezone->getName();
	}

	//--------------------------------------------------------------------
	// Setters
	//--------------------------------------------------------------------

	/**
	 * Sets the current year for this instance.
	 *
	 * @param integer|string $value
	 *
	 * @return Time
	 * @throws Exception
	 */
	public static function SET_YEAR($value)
	{
		return self::SET_VALUE('year', $value);
	}

	/**
	 * Sets the month of the year.
	 *
	 * @param integer|string $value
	 *
	 * @return Time
	 * @throws Exception
	 */
	public static function SET_MONTH($value)
	{
		if (is_numeric($value) && ($value < 1 || $value > 12))
		{
			throw I18nException::forInvalidMonth($value);
		}

		if (is_string($value) && ! is_numeric($value))
		{
			$value = date('m', strtotime("{$value} 1 2017"));
		}

		return self::SET_VALUE('month', $value);
	}

	/**
	 * Sets the day of the month.
	 *
	 * @param integer|string $value
	 *
	 * @return Time
	 * @throws Exception
	 */
	public static function SET_DAY($value)
	{
		if ($value < 1 || $value > 31)
		{
			throw I18nException::forInvalidDay($value);
		}

		$date    = self::GET_YEAR() . '-' . self::GET_MONTH();
		$lastDay = date('t', strtotime($date));
		if ($value > $lastDay)
		{
			throw I18nException::forInvalidOverDay($lastDay, $value);
		}

		return self::SET_VALUE('day', $value);
	}

	/**
	 * Sets the hour of the day (24 hour cycle)
	 *
	 * @param integer|string $value
	 *
	 * @return Time
	 * @throws Exception
	 */
	public static function SET_HOUR($value)
	{
		if ($value < 0 || $value > 23)
		{
			throw I18nException::forInvalidHour($value);
		}

		return self::SET_VALUE('hour', $value);
	}

	/**
	 * Sets the minute of the hour
	 *
	 * @param integer|string $value
	 *
	 * @return Time
	 * @throws Exception
	 */
	public static function SET_MINUTE($value)
	{
		if ($value < 0 || $value > 59)
		{
			throw I18nException::forInvalidMinutes($value);
		}

		return self::SET_VALUE('minute', $value);
	}

	/**
	 * Sets the second of the minute.
	 *
	 * @param integer|string $value
	 *
	 * @return Time
	 * @throws Exception
	 */
	public static function SET_SECOND($value)
	{
		if ($value < 0 || $value > 59)
		{
			throw I18nException::forInvalidSeconds($value);
		}

		return self::SET_VALUE('second', $value);
	}

	/**
	 * Helper method to do the heavy lifting of the 'setX' methods.
	 *
	 * @param string  $name
	 * @param integer $value
	 *
	 * @return Time
	 * @throws Exception
	 */
	protected static function SET_VALUE(string $name, $value)
	{
		[$year, $month, $day, $hour, $minute, $second] = explode('-', self::$thiss->format('Y-n-j-G-i-s'));
		$$name                                             = $value;

		return Time::create(
			(int) $year,
			(int) $month,
			(int) $day,
			(int) $hour,
			(int) $minute,
			(int) $second,
			self::GET_TIMEZONE_NAME(),
			self::$locale
		);
	}

	/**
	 * Returns a new instance with the revised timezone.
	 *
	 * @param string|DateTimeZone $timezone
	 *
	 * @return Time
	 * @throws Exception
	 */
	public  function setTimezone($timezone)
	{
		$timezone = $timezone instanceof DateTimeZone ? $timezone : new DateTimeZone($timezone);
		return Time::CREATE_FROM_INSTANCE(self::TO_DATE_TIME()->setTimezone($timezone), self::$locale);
	}

	/**
	 * Returns a new instance with the date set to the new timestamp.
	 *
	 * @param integer $timestamp
	 *
	 * @return Time
	 * @throws Exception
	 */
	public  function setTimestamp($timestamp)
	{
		$time = date('Y-m-d H:i:s', $timestamp);

		return Time::PARSE($time, self::$timezone, self::$locale);
	}

	//--------------------------------------------------------------------
	// Add/Subtract
	//--------------------------------------------------------------------

	/**
	 * Returns a new Time instance with $seconds added to the time.
	 *
	 * @param integer $seconds
	 *
	 * @return static
	 */
	public static function ADD_SECONDS(int $seconds)
	{

		return TIME::CREATE_FROM_INSTANCE(self::$thiss)->add(DateInterval::createFromDateString("{$seconds} seconds"));
	}

	/**
	 * Returns a new Time instance with $minutes added to the time.
	 *
	 * @param integer $minutes
	 *
	 * @return static
	 */
	public static function ADD_MINUTES(int $minutes)
	{

		return TIME::CREATE_FROM_INSTANCE(self::$thiss)->add(DateInterval::createFromDateString("{$minutes} minutes"));
	}

	/**
	 * Returns a new Time instance with $hours added to the time.
	 *
	 * @param integer $hours
	 *
	 * @return static
	 */
	public static function ADD_HOURS(int $hours)
	{

		return TIME::CREATE_FROM_INSTANCE(self::$thiss)->add(DateInterval::createFromDateString("{$hours} hours"));
	}

	/**
	 * Returns a new Time instance with $days added to the time.
	 *
	 * @param integer $days
	 *
	 * @return static
	 */
	public static function ADD_DAYS(int $days)
	{
        
		return TIME::CREATE_FROM_INSTANCE(self::$thiss)->add(DateInterval::createFromDateString("{$days} days"));
	}

	/**
	 * Returns a new Time instance with $months added to the time.
	 *
	 * @param integer $months
	 *
	 * @return static
	 */
	public static function ADD_MONTHS(int $months)
	{

		return TIME::CREATE_FROM_INSTANCE(self::$thiss)->add(DateInterval::createFromDateString("{$months} months"));
	}

	/**
	 * Returns a new Time instance with $years added to the time.
	 *
	 * @param integer $years
	 *
	 * @return static
	 */
	public static function ADD_YEARS(int $years)
	{

		return TIME::CREATE_FROM_INSTANCE(self::$thiss)->add(DateInterval::createFromDateString("{$years} years"));
	}

	/**
	 * Returns a new Time instance with $seconds subtracted from the time.
	 *
	 * @param integer $seconds
	 *
	 * @return static
	 */
	public static function SUB_SECONDS(int $seconds)
	{
		return TIME::CREATE_FROM_INSTANCE(self::$thiss)->sub(DateInterval::createFromDateString("{$seconds} seconds"));
	}

	/**
	 * Returns a new Time instance with $minutes subtracted from the time.
	 *
	 * @param integer $minutes
	 *
	 * @return static
	 */
	public static function SUB_MINUTES(int $minutes)
	{

		return TIME::CREATE_FROM_INSTANCE(self::$thiss)->sub(DateInterval::createFromDateString("{$minutes} minutes"));
	}

	/**
	 * Returns a new Time instance with $hours subtracted from the time.
	 *
	 * @param integer $hours
	 *
	 * @return static
	 */
	public static function SUB_HOURS(int $hours)
	{

		return TIME::CREATE_FROM_INSTANCE(self::$thiss)->sub(DateInterval::createFromDateString("{$hours} hours"));
	}

	/**
	 * Returns a new Time instance with $days subtracted from the time.
	 *
	 * @param integer $days
	 *
	 * @return static
	 */
	public static function SUB_DAYS(int $days)
	{
		

		return TIME::CREATE_FROM_INSTANCE(self::$thiss)->sub(DateInterval::createFromDateString("{$days} days"));
	}

	/**
	 * Returns a new Time instance with $months subtracted from the time.
	 *
	 * @param integer $months
	 *
	 * @return static
	 */
	public static function SUB_MONTHS(int $months)
	{

		return TIME::CREATE_FROM_INSTANCE(self::$thiss)->sub(DateInterval::createFromDateString("{$months} months"));
	}

	/**
	 * Returns a new Time instance with $hours subtracted from the time.
	 *
	 * @param integer $years
	 *
	 * @return static
	 */
	public static function SUB_YEARS(int $years)
	{

		return TIME::CREATE_FROM_INSTANCE(self::$thiss)->sub(DateInterval::createFromDateString("{$years} years"));
	}

	//--------------------------------------------------------------------
	// Formatters
	//--------------------------------------------------------------------

	/**
	 * Returns the localized value of the date in the format 'Y-m-d H:i:s'
	 *
	 * @throws Exception
	 */
	public static function TO_DATE_TIME_STRING()
	{
		return self::TO_LOCALIZED_STRING('y-M-d H:m:s');
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a localized version of the date in Y-m-d format.
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function TO_DATE_STRING()
	{
		return self::TO_LOCALIZED_STRING('y-M-d');
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a localized version of the date in nicer date format:
	 *
	 *  i.e. Apr 1, 2017
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function TO_FORMATED_DATE_STRING()
	{
		return self::TO_LOCALIZED_STRING('M d, y');
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a localized version of the time in nicer date format:
	 *
	 *  i.e. 13:20:33
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function TO_TIME_STRING()
	{
		return self::TO_LOCALIZED_STRING('H:m:s');
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the localized value of this instance in $format.
	 *
	 * @param string|null $format
	 *
	 * @return string|boolean
	 * @throws Exception
	 */
	public static function TO_LOCALIZED_STRING(string $format = null)
	{
		$format = $format ?? self::$toStringFormat;
		return self::TO_DATE_TIME()->format($format);
	}

	public static function TO_DATE_HUMANIZE()
	{
		return self::TO_DATE_TIME()->format('F j\, Y H:i T');

	}

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// Comparison
	//--------------------------------------------------------------------

	/**
	 * Determines if the datetime passed in is equal to the current instance.
	 * Equal in this case means that they represent the same moment in time,
	 * and are not required to be in the same timezone, as both times are
	 * converted to UTC and compared that way.
	 *
	 * @param Time|DateTimeInterface|string $testTime
	 * @param string|null                   $timezone
	 *
	 * @return boolean
	 * @throws Exception
	 */
	public static function EQUALS($testTime, string $timezone = null): bool
	{
		$testTime = self::GET_UTC_OBJECT($testTime, $timezone);

		$ourTime = self::TO_DATE_TIME()
				->setTimezone(new DateTimeZone('UTC'))
				->format('Y-m-d H:i:s');

		return $testTime->format('Y-m-d H:i:s') === $ourTime;
	}

	//--------------------------------------------------------------------

	/**
	 * Ensures that the times are identical, taking timezone into account.
	 *
	 * @param Time|DateTimeInterface|string $testTime
	 * @param string|null                   $timezone
	 *
	 * @return boolean
	 * @throws Exception
	 */
	public static function SAME_AS($testTime, string $timezone = null): bool
	{
		if ($testTime instanceof DateTimeInterface)
		{
			$testTime = $testTime->format('Y-m-d H:i:s');
		}
		elseif (is_string($testTime))
		{
			$timezone = $timezone ?: self::$timezone;
			$timezone = $timezone instanceof DateTimeZone ? $timezone : new DateTimeZone($timezone);
			$testTime = new DateTime($testTime, $timezone);
			$testTime = $testTime->format('Y-m-d H:i:s');
		}

		$ourTime = self::TO_DATE_TIME_STRING();

		return $testTime === $ourTime;
	}

	//--------------------------------------------------------------------

	/**
	 * Determines if the current instance's time is before $testTime,
	 * after converting to UTC.
	 *
	 * @param mixed       $testTime
	 * @param string|null $timezone
	 *
	 * @return boolean
	 * @throws Exception
	 */
	public static function IS_BEFORE($testTime, string $timezone = null): bool
	{
		$testTime = self::GET_UTC_OBJECT($testTime, $timezone)->getTimestamp();
		$ourTime  = self::$thiss->getTimestamp();

		return $ourTime < $testTime;
	}

	//--------------------------------------------------------------------

	/**
	 * Determines if the current instance's time is after $testTime,
	 * after converting in UTC.
	 *
	 * @param mixed       $testTime
	 * @param string|null $timezone
	 *
	 * @return boolean
	 * @throws Exception
	 */
	public static function IS_AFTER($testTime, string $timezone = null): bool
	{
		$testTime = self::GET_UTC_OBJECT($testTime, $timezone)->getTimestamp();
		$ourTime  = self::$thiss->getTimestamp();

		return $ourTime > $testTime;
	}

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// Differences
	//--------------------------------------------------------------------

	/**
	 * Returns a text string that is easily readable that describes
	 * how long ago, or how long from now, a date is, like:
	 *
	 *  - 3 weeks ago
	 *  - in 4 days
	 *  - 6 hours ago
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public static function HUMANIZE()
	{
		$now  = IntlCalendar::fromDateTime(Time::NOW(self::$timezone)::TO_DATE_TIME_STRING());
		$time = self::GET_CALENDAR()->getTime();

		$years   = $now->fieldDifference($time, IntlCalendar::FIELD_YEAR);
		$months  = $now->fieldDifference($time, IntlCalendar::FIELD_MONTH);
		$days    = $now->fieldDifference($time, IntlCalendar::FIELD_DAY_OF_YEAR);
		$hours   = $now->fieldDifference($time, IntlCalendar::FIELD_HOUR_OF_DAY);
		$minutes = $now->fieldDifference($time, IntlCalendar::FIELD_MINUTE);

		$phrase = null;

		if ($years !== 0)
		{
			$phrase = hkm_lang('Time.years', [abs($years)]);
			$before = $years < 0;
		}
		elseif ($months !== 0)
		{
			$phrase = hkm_lang('Time.months', [abs($months)]);
			$before = $months < 0;
		}
		elseif ($days !== 0 && (abs($days) >= 7))
		{
			$weeks  = ceil($days / 7);
			$phrase = hkm_lang('Time.weeks', [abs($weeks)]);
			$before = $days < 0;
		}
		elseif ($days !== 0)
		{
			$before = $days < 0;

			// Yesterday/Tomorrow special cases
			if (abs($days) === 1)
			{
				return $before ? hkm_lang('Time.yesterday') : hkm_lang('Time.tomorrow');
			}

			$phrase = hkm_lang('Time.days', [abs($days)]);
		}
		elseif ($hours !== 0)
		{
			$phrase = hkm_lang('Time.hours', [abs($hours)]);
			$before = $hours < 0;
		}
		elseif ($minutes !== 0)
		{
			$phrase = hkm_lang('Time.minutes', [abs($minutes)]);
			$before = $minutes < 0;
		}
		else
		{
			return hkm_lang('Time.now');
		}

		return $before ? hkm_lang('Time.ago', [$phrase]) : hkm_lang('Time.inFuture', [$phrase]);
	}

	/**
	 * @param mixed       $testTime
	 * @param string|null $timezone
	 *
	 * @return TimeDifference
	 * @throws Exception
	 */
	public static function DIFFERENCE($testTime="now", string $timezone = null)
	{
		$testTime = self::GET_UTC_OBJECT($testTime, $timezone);
		$ourTime  = self::GET_UTC_OBJECT(self::$thiss);


		return new TimeDifference($ourTime, $testTime);
	}

	//--------------------------------------------------------------------
	// Utilities
	//--------------------------------------------------------------------

	/**
	 * Returns a Time instance with the timezone converted to UTC.
	 *
	 * @param mixed       $time
	 * @param string|null $timezone
	 *
	 * @return DateTime|static
	 * @throws Exception
	 */
	public static function GET_UTC_OBJECT($time, string $timezone = null)
	{
		if ($time instanceof Time)
		{
			$time = $time::TO_DATE_TIME();
		}
		elseif (is_string($time))
		{
			$timezone = $timezone ?: self::$timezone;
			$timezone = $timezone instanceof DateTimeZone ? $timezone : new DateTimeZone($timezone);
			$time     = new DateTime($time, $timezone);
		}

		if ($time instanceof DateTime || $time instanceof DateTimeImmutable)
		{
			$time = $time->setTimezone(new DateTimeZone('UTC'));
		}

		return $time;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the IntlCalendar object used for this object,
	 * taking into account the locale, date, etc.
	 *
	 * Primarily used internally to provide the difference and comparison functions,
	 * but available for public static consumption if they need it.
	 *
	 * @return IntlCalendar
	 * @throws Exception
	 */
	public static function GET_CALENDAR()
	{
		return IntlCalendar::fromDateTime(self::TO_DATE_TIME_STRING());
	}

	//--------------------------------------------------------------------

	/**
	 * Check a time string to see if it includes a relative date (like 'next Tuesday').
	 *
	 * @param string $time
	 *
	 * @return boolean
	 */
	protected static  function HAS_RELATIVE_KEYWORDS(string $time): bool
	{
		// skip common format with a '-' in it
		if (preg_match('/[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}/', $time) !== 1)
		{
			return preg_match(static::$relativePattern, $time) > 0;
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Outputs a short format version of the datetime.
	 *
	 * @return string
	 * @throws Exception
	 */
	public  function __toString(): string
	{
		return IntlDateFormatter::formatObject(self::TO_DATE_TIME(), self::$toStringFormat, self::$locale);
	}

	//--------------------------------------------------------------------

	/**
	 * Allow for property-type access to any getX method...
	 *
	 * Note that we cannot use this for any of our setX methods,
	 * as they return new Time objects, but the __set ignores
	 * return values.
	 * See http://php.net/manual/en/language.oop5.overloading.php
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public  function __get($name)
	{
		$method = 'get' . ucfirst($name);

		if (method_exists($this, $method))
		{
			return $this::$$method();
		}

		return null;
	}

	//--------------------------------------------------------------------

	/**
	 * Allow for property-type checking to any getX method...
	 *
	 * @param string $name
	 *
	 * @return boolean
	 */
	public  function __isset($name): bool
	{
		$method = 'GET_' . ucfirst($name);

		return method_exists($this, $method);
	}

	//--------------------------------------------------------------------

	/**
	 * This is called when we unserialize the Time object.
	 */
	public  function __wakeup()
	{
		/**
		 * Prior to unserialization, this is a string.
		 *
		 * @var string $timezone
		 */
		$timezone = $this::$timezone;

		self::$timezone = new DateTimeZone($timezone);
		parent::__construct($this::$date, $this::$timezone);
	}
}
