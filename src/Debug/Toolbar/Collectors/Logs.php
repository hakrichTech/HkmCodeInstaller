<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@hakrichteam.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Debug\Toolbar\Collectors;

use Hkm_code\Vezirion\ServicesSystem;

/**
 * Loags collector
 */
class Logs extends BaseCollector
{
	/**
	 * Whether this collector has data that can
	 * be displayed in the Timeline.
	 *
	 * @var boolean
	 */
	protected static $hasTimeline = false;

	/**
	 * Whether this collector needs to display
	 * content in a tab or not.
	 *
	 * @var boolean
	 */
	protected static $hasTabContent = true;

	/**
	 * The 'title' of this Collector.
	 * Used to name things in the toolbar HTML.
	 *
	 * @var string
	 */
	protected static $title = 'Logs';

	/**
	 * Our collected data.
	 *
	 * @var array
	 */
	protected static $data;

	//--------------------------------------------------------------------

	/**
	 * Returns the data of this collector to be formatted in the toolbar
	 *
	 * @return array
	 */
	public static function DISPLAY(): array
	{
		return [
			'logs' => self::COLLECT_LOGS(),
		];
	}

	//--------------------------------------------------------------------

	/**
	 * Does this collector actually have any data to display?
	 *
	 * @return boolean
	 */
	public static function IS_EMPTY(): bool
	{
		self::COLLECT_LOGS();

		return empty(self::$data);
	}

	//--------------------------------------------------------------------

	/**
	 * Display the icon.
	 *
	 * Icon from https://icons8.com - 1em package
	 *
	 * @return string
	 */
	public static function ICON(): string
	{
		return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAACYSURBVEhLYxgFJIHU1FSjtLS0i0D8AYj7gEKMEBkqAaAFF4D4ERCvAFrwH4gDoFIMKSkpFkB+OTEYqgUTACXfA/GqjIwMQyD9H2hRHlQKJFcBEiMGQ7VgAqCBvUgK32dmZspCpagGGNPT0/1BLqeF4bQHQJePpiIwhmrBBEADR1MRfgB0+WgqAmOoFkwANHA0FY0CUgEDAwCQ0PUpNB3kqwAAAABJRU5ErkJggg==';
	}

	//--------------------------------------------------------------------

	/**
	 * Ensures the data has been collected.
	 */
	protected static function COLLECT_LOGS()
	{
		if (! empty(self::$data))
		{
			return self::$data;
		}

		return self::$data = ServicesSystem::LOGGER(true)::$logCache ?? [];
	}

	//--------------------------------------------------------------------
}
