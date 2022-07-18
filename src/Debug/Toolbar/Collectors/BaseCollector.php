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

use Hkm_code\Debug\Exceptions;

/**
 * Base Toolbar collector
 */
class BaseCollector
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
	protected static $hasTabContent = false;

	/**
	 * Whether this collector needs to display
	 * a label or not.
	 *
	 * @var boolean
	 */
	protected static $hasLabel = false;

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
	protected static $title = '';

	//--------------------------------------------------------------------

	/**
	 * Gets the Collector's title.
	 *
	 * @param  boolean $safe
	 * @return string
	 */
	public static function GET_TITLE(bool $safe = false): string
	{
		if ($safe)
		{
			return str_replace(' ', '-', strtolower(self::$title));
		}

		return self::$title;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns any information that should be shown next to the title.
	 *
	 * @return string
	 */
	public static function GET_TITLE_DETAILS(): string
	{
		return '';
	}

	//--------------------------------------------------------------------

	/**
	 * Does this collector need it's own tab?
	 *
	 * @return boolean
	 */
	public static function HAS_TAB_CONTENT(): bool
	{
		return (bool) self::$hasTabContent;
	}

	//--------------------------------------------------------------------

	/**
	 * Does this collector have a label?
	 *
	 * @return boolean
	 */
	public static function HAS_LABEL(): bool
	{
		return (bool) self::$hasLabel;
	}

	//--------------------------------------------------------------------

	/**
	 * Does this collector have information for the timeline?
	 *
	 * @return boolean
	 */
	public static function HAS_TIMELINE_DATA(): bool
	{
		return (bool) self::$hasTimeline;
	}

	//--------------------------------------------------------------------

	/**
	 * Grabs the data for the timeline, properly formatted,
	 * or returns an empty array.
	 *
	 * @return array
	 */
	public static function TIMELINE_DATA(): array
	{
		if (! self::$hasTimeline)
		{
			return [];
		}

		return self::FORMAT_TIMELINE_DATA();
	}

	//--------------------------------------------------------------------

	/**
	 * Does this Collector have data that should be shown in the
	 * 'Vars' tab?
	 *
	 * @return boolean
	 */
	public static function HAS_VAR_DATA(): bool
	{
		return (bool) self::$hasVarData;
	}

	//--------------------------------------------------------------------

	/**
	 * Gets a collection of data that should be shown in the 'Vars' tab.
	 * The format is an array of sections, each with their own array
	 * of key/value pairs:
	 *
	 *  $data = [
	 *      'section 1' => [
	 *          'foo' => 'bar,
	 *          'bar' => 'baz'
	 *      ],
	 *      'section 2' => [
	 *          'foo' => 'bar,
	 *          'bar' => 'baz'
	 *      ],
	 *  ];
	 */
	public static function GET_VAR_DATA()
	{
		return null;
	}

	//--------------------------------------------------------------------

	/**
	 * Child classes should implement this to return the timeline data
	 * formatted for correct usage.
	 *
	 * Timeline data should be formatted into arrays that look like:
	 *
	 *  [
	 *      'name'      => 'Database::Query',
	 *      'component' => 'Database',
	 *      'start'     => 10       // milliseconds
	 *      'duration'  => 15       // milliseconds
	 *  ]
	 *
	 * @return array
	 */
	protected static function FORMAT_TIMELINE_DATA(): array
	{
		return [];
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the data of this collector to be formatted in the toolbar
	 *
	 * @return array|string
	 */
	public static function DISPLAY()
	{
		return [];
	}

	//--------------------------------------------------------------------

	/**
	 * Clean Path
	 *
	 * This makes nicer looking paths for the error output.
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	public static function CLEAN_PATH(string $file): string
	{
		return Exceptions::CLEAN_PATH($file);
	}

	/**
	 * Gets the "badge" value for the button.
	 */
	public static function GET_BADGE_VALUE()
	{
		return null;
	}

	/**
	 * Does this collector have any data collected?
	 *
	 * If not, then the toolbar button won't get shown.
	 *
	 * @return boolean
	 */
	public static function IS_EMPTY(): bool
	{
		return false;
	}

	/**
	 * Returns the HTML to display the icon. Should either
	 * be SVG, or a base-64 encoded.
	 *
	 * Recommended dimensions are 24px x 24px
	 *
	 * @return string
	 */
	public static function ICON(): string
	{
		return '';
	}

	/**
	 * Return settings as an array.
	 *
	 * @return array
	 */
	public static function GET_AS_ARRAY(): array
	{
		return [
			'title'           => self::GET_TITLE(),
			'titleSafe'       => self::GET_TITLE(true),
			'titleDetails'    => self::GET_TITLE_DETAILS(),
			'display'         => self::DISPLAY(),
			'badgeValue'      => self::GET_BADGE_VALUE(),
			'isEmpty'         => self::IS_EMPTY(),
			'hasTabContent'   => self::HAS_TAB_CONTENT(),
			'hasLabel'        => self::HAS_LABEL(),
			'icon'            => self::ICON(),
			'hasTimelineData' => self::HAS_TIMELINE_DATA(),
			'timelineData'    => self::TIMELINE_DATA(),
		];
	}
}
