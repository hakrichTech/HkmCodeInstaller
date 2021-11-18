<?php


namespace Hkm_code\Debug;

use RuntimeException;

class Timer
{
	/**
	 * List of all timers.
	 *
	 * @var array
	 */
	protected static $timers = [];

    protected static $thiss;

	 public function __construct()
	 {
		 self::$thiss = $this;
	 }
	public static function START(string $name, float $time = null)
	{
		self::$timers[strtolower($name)] = [
			'start' => ! empty($time) ? $time : microtime(true),
			'end'   => null,
		];

		return self::$thiss;
	}

	
	public static function STOP(string $name)
	{
		$name = strtolower($name);

		if (empty(self::$timers[$name]))
		{
			throw new RuntimeException('Cannot stop timer: invalid name given.');
		}

		self::$timers[$name]['end'] = microtime(true);

		return self::$thiss;
	}

	
	public static function GET_ELAPSED_TIME(string $name, int $decimals = 4)
	{
		$name = strtolower($name);

		if (empty(self::$timers[$name]))
		{
			return null;
		}

		$timer = self::$timers[$name];

		if (empty($timer['end']))
		{
			$timer['end'] = microtime(true);
		}

		return (float) number_format($timer['end'] - $timer['start'], $decimals);
	}

	
	public static function GET_TIMERS(int $decimals = 4): array
	{
		$timers = self::$timers;

		foreach ($timers as &$timer)
		{
			if (empty($timer['end']))
			{
				$timer['end'] = microtime(true);
			}

			$timer['duration'] = (float) number_format($timer['end'] - $timer['start'], $decimals);
		}

		return $timers;
	}

	
	public static function HAS(string $name): bool
	{
		return array_key_exists(strtolower($name), self::$timers);
	}

	
}
