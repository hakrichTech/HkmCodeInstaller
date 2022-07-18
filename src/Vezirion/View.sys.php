<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@Hkm_code.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Vezirion;

/**
 * View configuration
 */
class View extends BaseVezirion
{
	/**
	 * When false, the view method will clear the data between each
	 * call.
	 *
	 * @var boolean
	 */
	public static $saveData = true;

	/**
	 * Parser Filters map a filter name with any PHP callable. When the
	 * Parser prepares a variable for display, it will chain it
	 * through the filters in the order defined, inserting any parameters.
	 *
	 * To prevent potential abuse, all filters MUST be defined here
	 * in order for them to be available for use within the Parser.
	 */
	public static $filters = [];

	/**
	 * Parser Plugins provide a way to extend the functionality provided
	 * by the core Parser by creating aliases that will be replaced with
	 * any callable. Can be single or tag pair.
	 */
	public static $plugins = [];

	/**
	 * Built-in View filters.
	 *
	 * @var array
	 */
	protected static $coreFilters = [
		'abs'            => '\abs',
		'capitalize'     => '\Hkm_code\View\Filters::capitalize',
		'date'           => '\Hkm_code\View\Filters::date',
		'date_modify'    => '\Hkm_code\View\Filters::date_modify',
		'default'        => '\Hkm_code\View\Filters::default',
		'esc'            => '\Hkm_code\View\Filters::esc',
		'excerpt'        => '\Hkm_code\View\Filters::excerpt',
		'highlight'      => '\Hkm_code\View\Filters::highlight',
		'highlight_code' => '\Hkm_code\View\Filters::highlight_code',
		'limit_words'    => '\Hkm_code\View\Filters::limit_words',
		'limit_chars'    => '\Hkm_code\View\Filters::limit_chars',
		'local_currency' => '\Hkm_code\View\Filters::local_currency',
		'local_number'   => '\Hkm_code\View\Filters::local_number',
		'lower'          => '\strtolower',
		'nl2br'          => '\Hkm_code\View\Filters::nl2br',
		'number_format'  => '\number_format',
		'prose'          => '\Hkm_code\View\Filters::prose',
		'round'          => '\Hkm_code\View\Filters::round',
		'strip_tags'     => '\strip_tags',
		'title'          => '\Hkm_code\View\Filters::title',
		'upper'          => '\strtoupper',
	];

	/**
	 * Built-in View plugins.
	 *
	 * @var array
	 */
	protected static $corePlugins = [
		'current_url'       => '\Hkm_code\View\Plugins::currentURL',
		'previous_url'      => '\Hkm_code\View\Plugins::previousURL',
		'mailto'            => '\Hkm_code\View\Plugins::mailto',
		'safe_mailto'       => '\Hkm_code\View\Plugins::safeMailto',
		'lang'              => '\Hkm_code\View\Plugins::lang',
		'validation_errors' => '\Hkm_code\View\Plugins::validationErrors',
		'route'             => '\Hkm_code\View\Plugins::route',
		'siteURL'           => '\Hkm_code\View\Plugins::siteURL',
	];

	/**
	 * Constructor.
	 *
	 * Merge the built-in and developer-configured filters and plugins,
	 * with preference to the developer ones.
	 */
	public function __construct()
	{
		$this::$filters = array_merge($this::$coreFilters, $this::$filters);
		$this::$plugins = array_merge($this::$corePlugins, $this::$plugins);

		parent::__construct();
	}
}
