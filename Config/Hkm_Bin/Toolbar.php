<?php

namespace Hkm_Config\Hkm_Bin;

use Hkm_code\Vezirion\BaseVezirion;
use Hkm_code\Debug\Toolbar\Collectors\Database;
use Hkm_code\Debug\Toolbar\Collectors\Events;
use Hkm_code\Debug\Toolbar\Collectors\Files;
use Hkm_code\Debug\Toolbar\Collectors\Logs;
use Hkm_code\Debug\Toolbar\Collectors\Routes;
use Hkm_code\Debug\Toolbar\Collectors\Timers;
use Hkm_code\Debug\Toolbar\Collectors\Views;

/**
 * --------------------------------------------------------------------------
 * Debug Toolbar
 * --------------------------------------------------------------------------
 *
 * The Debug Toolbar provides a way to see information about the performance
 * and state of your application during that page display. By default it will
 * NOT be displayed under production environments, and will only display if
 * `HKM_DEBUG` is true, since if it's not, there's not much to display anyway.
 */
class Toolbar extends BaseVezirion
{
	/**
	 * --------------------------------------------------------------------------
	 * Toolbar Collectors
	 * --------------------------------------------------------------------------
	 *
	 * List of toolbar collectors that will be called when Debug Toolbar
	 * fires up and collects data from.
	 *
	 * @var string[]
	 */
	public static $collectors = [
		Timers::class,
		Database::class,
		Logs::class,
		Views::class,
		// \hakrichteam\Debug\Toolbar\Collectors\Cache::class,
		Files::class,
		Routes::class,
		Events::class,
	];

	/**
	 * --------------------------------------------------------------------------
	 * Max History
	 * --------------------------------------------------------------------------
	 *
	 * `$maxHistory` sets a limit on the number of past requests that are stored,
	 * helping to conserve file space used to store them. You can set it to
	 * 0 (zero) to not have any history stored, or -1 for unlimited history.
	 *
	 * @var integer
	 */
	public static $maxHistory = 20;

	/**
	 * --------------------------------------------------------------------------
	 * Toolbar Views Path
	 * --------------------------------------------------------------------------
	 *
	 * The full path to the the views that are used by the toolbar.
	 * This MUST have a trailing slash.
	 *
	 * @var string
	 */
	public static $viewsPath = SYSTEMPATH . 'Debug/Toolbar/Views/';

	/**
	 * --------------------------------------------------------------------------
	 * Max Queries
	 * --------------------------------------------------------------------------
	 *
	 * If the Database Collector is enabled, it will log every query that the
	 * the system generates so they can be displayed on the toolbar's timeline
	 * and in the query log. This can lead to memory issues in some instances
	 * with hundreds of queries.
	 *
	 * `$maxQueries` defines the maximum amount of queries that will be stored.
	 *
	 * @var integer
	 */
	public static $maxQueries = 100;
}
