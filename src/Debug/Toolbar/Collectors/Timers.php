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
 * Timers collector
 */
class Timers extends BaseCollector
{
	/**
	 * Whether this collector has data that can
	 * be displayed in the Timeline.
	 *
	 * @var boolean
	 */
	protected static $hasTimeline = true;

	/**
	 * Whether this collector needs to display
	 * content in a tab or not.
	 *
	 * @var boolean
	 */
	protected static $hasTabContent = false;

	/**
	 * The 'title' of this Collector.
	 * Used to name things in the toolbar HTML.
	 *
	 * @var string
	 */
	protected static $title = 'Timers';

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

		$benchmark = ServicesSystem::TIMER(true);
		$rows      = $benchmark::GET_TIMERS(6);

		foreach ($rows as $name => $info)
		{
			if ($name === 'total_execution')
			{
				continue;
			}

			$data[] = [
				'name'      => ucwords(str_replace('_', ' ', $name)),
				'component' => 'Timer',
				'start'     => $info['start'],
				'duration'  => $info['end'] - $info['start'],
			];
		}

		return $data;
	}
}
