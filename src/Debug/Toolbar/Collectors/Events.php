<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@Hkm_code.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Debug\Toolbar\Collectors;

use Hkm_code\View\RendererInterface;
use Hkm_code\Vezirion\ServicesSystem;

/**
 * Views collector
 */
class Events extends BaseCollector
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
	 * Whether this collector has data that
	 * should be shown in the Vars tab.
	 *
	 * @var boolean
	 */
	protected static $hasVarData = false;

	/**
	 * The 'title' of this Collector.
	 * Used to name things in the toolbar HTML.
	 *
	 * @var string
	 */
	protected static $title = 'Events';

	/**
	 * Instance of the Renderer service
	 *
	 * @var RendererInterface
	 */
	protected static $viewer;

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 */
	public  function __construct()
	{
		self::$viewer = ServicesSystem::renderer();
	}

	//--------------------------------------------------------------------

	/**
	 * Child classes should implement this to return the timeline data
	 * formatted for correct usage.
	 *
	 * @return array
	 */
	protected static function FORMAT_TIMELINE_DATA(): array
	{
		$data = [];

		$rows = self::$viewer->getPerformanceData(); // @phpstan-ignore-line

		foreach ($rows as $info)
		{
			$data[] = [
				'name'      => 'View: ' . $info['view'],
				'component' => 'Views',
				'start'     => $info['start'],
				'duration'  => $info['end'] - $info['start'],
			];
		}

		return $data;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the data of this collector to be formatted in the toolbar
	 *
	 * @return array
	 */
	public static function DISPLAY(): array
	{
		$data = [
			'events' => [],
		];

		foreach (\Hkm_code\Events\Events::GET_PERFOMANCE_LOGS() as $row)
		{
			$key = $row['event'];

			if (! array_key_exists($key, $data['events']))
			{
				$data['events'][$key] = [
					'event'    => $key,
					'duration' => ($row['end'] - $row['start']) * 1000,
					'count'    => 1,
				];

				continue;
			}

			$data['events'][$key]['duration'] += ($row['end'] - $row['start']) * 1000;
			$data['events'][$key]['count']++;
		}

		foreach ($data['events'] as &$row)
		{
			$row['duration'] = number_format($row['duration'], 2);
		}

		return $data;
	}

	//--------------------------------------------------------------------

	/**
	 * Gets the "badge" value for the button.
	 *
	 * @return integer
	 */
	public static function GET_BADGE_VALUE(): int
	{
		return count(\Hkm_code\Events\Events::GET_PERFOMANCE_LOGS());
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
		return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAEASURBVEhL7ZXNDcIwDIVTsRBH1uDQDdquUA6IM1xgCA6MwJUN2hk6AQzAz0vl0ETUxC5VT3zSU5w81/mRMGZysixbFEVR0jSKNt8geQU9aRpFmp/keX6AbjZ5oB74vsaN5lSzA4tLSjpBFxsjeSuRy4d2mDdQTWU7YLbXTNN05mKyovj5KL6B7q3hoy3KwdZxBlT+Ipz+jPHrBqOIynZgcZonoukb/0ckiTHqNvDXtXEAaygRbaB9FvUTjRUHsIYS0QaSp+Dw6wT4hiTmYHOcYZsdLQ2CbXa4ftuuYR4x9vYZgdb4vsFYUdmABMYeukK9/SUme3KMFQ77+Yfzh8eYF8+orDuDWU5LAAAAAElFTkSuQmCC';
	}
}
