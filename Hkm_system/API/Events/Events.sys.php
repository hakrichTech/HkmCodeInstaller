<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Events;

use Hkm_code\Vezirion\Modules;
use Hkm_code\Vezirion\Services;

define('EVENT_PRIORITY_LOW', 200);
define('EVENT_PRIORITY_NORMAL', 100);
define('EVENT_PRIORITY_HIGH', 10);

/**
 * Events
 */
class Events
{
	/**
	 * The list of listeners.
	 *
	 * @var array
	 */
	protected static $listeners = [];

	/**
	 * Flag to let us know if we've read from the Config file(s)
	 * and have all of the defined events.
	 *
	 * @var boolean
	 */
	protected static $initialized = false;

	/**
	 * If true, events will not actually be fired.
	 * Useful during testing.
	 *
	 * @var boolean
	 */
	protected static $simulate = false;

	/**
	 * Stores information about the events
	 * for display in the debug toolbar.
	 *
	 * @var array<array<string, float|string>>
	 */
	protected static $performanceLog = [];

	/**
	 * A list of found files.
	 *
	 * @var string[]
	 */
	protected static $files = [];

	/**
	 * Ensures that we have a events file ready.
	 *
	 * @return void
	 */
	public static function INITIALIZE()
	{
		// Don't overwrite anything....
		if (static::$initialized)
		{
			return;
		}

		/**
		 * @var Modules
		 */
		$config = hkm_config('Modules');
		$events = VEZIRIONPATH . 'Events.php';
		$files  = [];

		if ($config::SHOULD_DISCOVER('events'))
		{
			$files = Services::LOCATOR()::SEARCH(APP_CONFIG_NAMESPACE.'\Events');
		}

		$files = array_filter(array_map(static function (string $file) {
			if (is_file($file))
			{
				return realpath($file) ?: $file;
			}

			return false; // @codeCoverageIgnore
		}, $files));

		static::$files = array_unique(array_merge($files, [$events]));

		foreach (static::$files as $file)
		{
			include $file;
		}

		static::$initialized = true;
	}

	/**
	 * Registers an action to happen on an event. The action can be any sort
	 * of callable:
	 *
	 *  Events::on('create', 'myFunction');               // procedural function
	 *  Events::on('create', ['myClass', 'myMethod']);    // Class::method
	 *  Events::on('create', [$myInstance, 'myMethod']);  // Method on an existing instance
	 *  Events::on('create', function() {});              // Closure
	 *
	 * @param string   $eventName
	 * @param callable $callback
	 * @param integer  $priority
	 *
	 * @return void
	 */
	public static function ON($eventName, $callback, $priority = EVENT_PRIORITY_NORMAL)
	{
		if (! isset(static::$listeners[$eventName]))
		{
			static::$listeners[$eventName] = [
				true, // If there's only 1 item, it's sorted.
				[$priority],
				[$callback],
			];
		}
		else
		{
			static::$listeners[$eventName][0]   = false; // Not sorted
			static::$listeners[$eventName][1][] = $priority;
			static::$listeners[$eventName][2][] = $callback;
		}
	}

	/**
	 * Runs through all subscribed methods running them one at a time,
	 * until either:
	 *  a) All subscribers have finished or
	 *  b) a method returns false, at which point execution of subscribers stops.
	 *
	 * @param string $eventName
	 * @param mixed  $arguments
	 *
	 * @return boolean
	 */
	public static function TRIGGER($eventName, ...$arguments): bool
	{
		// Read in our Config/Events file so that we have them all!
		if (! static::$initialized)
		{
			static::INITIALIZE();
		}

		$listeners = static::LISTENERS($eventName);

		foreach ($listeners as $listener)
		{
			$start = microtime(true);

			$result = static::$simulate === false ? call_user_func($listener, ...$arguments) : true;

			if (HKM_DEBUG)
			{
				static::$performanceLog[] = [
					'start' => $start,
					'end'   => microtime(true),
					'event' => strtolower($eventName),
				];
			}

			if ($result === false)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns an array of listeners for a single event. They are
	 * sorted by priority.
	 *
	 * @param string $eventName
	 *
	 * @return array
	 */
	public static function LISTENERS($eventName): array
	{
		
		if (! isset(static::$listeners[$eventName]))
		{
			return [];
		}

		// The list is not sorted
		if (! static::$listeners[$eventName][0])
		{
			// Sort it!
			array_multisort(static::$listeners[$eventName][1], SORT_NUMERIC, static::$listeners[$eventName][2]);

			// Mark it as sorted already!
			static::$listeners[$eventName][0] = true;
		}

		return static::$listeners[$eventName][2];
	}

	/**
	 * Removes a single listener from an event.
	 *
	 * If the listener couldn't be found, returns FALSE, else TRUE if
	 * it was removed.
	 *
	 * @param string   $eventName
	 * @param callable $listener
	 *
	 * @return boolean
	 */
	public static function REMOVE_LISTENNER($eventName, callable $listener): bool
	{
		if (! isset(static::$listeners[$eventName]))
		{
			return false;
		}

		foreach (static::$listeners[$eventName][2] as $index => $check)
		{
			if ($check === $listener)
			{
				unset(static::$listeners[$eventName][1][$index]);
				unset(static::$listeners[$eventName][2][$index]);

				return true;
			}
		}

		return false;
	}

	/**
	 * Removes all listeners.
	 *
	 * If the event_name is specified, only listeners for that event will be
	 * removed, otherwise all listeners for all events are removed.
	 *
	 * @param string|null $eventName
	 *
	 * @return void
	 */
	public static function REMOVE_ALL_LISTENERS($eventName = null)
	{
		if (! is_null($eventName))
		{
			unset(static::$listeners[$eventName]);
		}
		else
		{
			static::$listeners = [];
		}
	}

	/**
	 * Sets the path to the file that routes are read from.
	 *
	 * @param array $files
	 *
	 * @return void
	 */
	public static function SET_FILES(array $files)
	{
		static::$files = $files;
	}

	/**
	 * Returns the files that were found/loaded during this request.
	 *
	 * @return string[]
	 */
	public function GET_FILES()
	{
		return static::$files;
	}

	/**
	 * Turns simulation on or off. When on, events will not be triggered,
	 * simply logged. Useful during testing when you don't actually want
	 * the tests to run.
	 *
	 * @param boolean $choice
	 *
	 * @return void
	 */
	public static function SIMULATE(bool $choice = true)
	{
		static::$simulate = $choice;
	}

	/**
	 * Getter for the performance log records.
	 *
	 * @return array<array<string, float|string>>
	 */
	public static function GET_PERFOMANCE_LOGS()
	{
		return static::$performanceLog;
	}
}
