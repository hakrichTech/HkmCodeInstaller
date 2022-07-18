<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@Hkm_code.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\View;

use Hkm_code\Vezirion\FileLocator;
use Hkm_code\Debug\Toolbar\Collectors\Views;
use Hkm_code\View\Exceptions\ViewException;
use Hkm_code\Vezirion\ServicesSystem;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Class View
 */
class View implements RendererInterface
{
	/**
	 * Data that is made available to the Views.
	 *
	 * @var array
	 */
	protected static $data = [];

	/**
	 * Merge savedData and userData
	 */
	protected static $tempData = null;

	/**
	 * The base directory to look in for our Views.
	 *
	 * @var string
	 */
	protected static $viewPath;

	/**
	 * The render variables
	 *
	 * @var array
	 */
	protected static $renderVars = [];

	/**
	 * Instance of FileLocator for when
	 * we need to attempt to find a view
	 * that's not in standard place.
	 *
	 * @var FileLocator
	 */
	protected static $loader;

	/**
	 * Logger instance.
	 *
	 * @var LoggerInterface
	 */
	protected static $logger;

	/**
	 * Should we store performance info?
	 *
	 * @var boolean
	 */
	protected static $debug = false;

	/**
	 * Cache stats about our performance here,
	 * when HKM_DEBUG = true
	 *
	 * @var array
	 */
	protected static $performanceData = [];

	/**
	 * @var ViewConfig
	 */
	protected static $config;

	/**
	 * Whether data should be saved between renders.
	 *
	 * @var boolean
	 */
	protected static $saveData;

	/**
	 * Number of loaded views
	 *
	 * @var integer
	 */
	protected static $viewsCount = 0;

	/**
	 * The name of the layout being used, if any.
	 * Set by the `extend` method used within views.
	 *
	 * @var string|null
	 */
	protected static $layout;


	/**
	 * Holds the sections and their data.
	 *
	 * @var array
	 */
	protected static $sections = [];

	/**
	 * The name of the current section being rendered,
	 * if any.
	 *
	 * @var string|null
	 * @deprecated
	 */
	protected static $currentSection;

	/**
	 * The name of the current section being rendered,
	 * if any.
	 *
	 * @var array<string>
	 */
	protected static $sectionStack = [];
	protected static $thiss;

	/**
	 * Constructor
	 *
	 * @param ViewConfig       $config
	 * @param string|null      $viewPath
	 * @param FileLocator|null $loader
	 * @param boolean|null     $debug
	 * @param LoggerInterface  $logger
	 */
	public function __construct( $config, string $viewPath = null,  $loader = null, bool $debug = null, LoggerInterface $logger = null)
	{
		self::$config   = $config;
		self::$viewPath = rtrim($viewPath, '\\/ ') . DIRECTORY_SEPARATOR;
		self::$loader   = $loader ?? ServicesSystem::LOCATOR();
		self::$logger   = $logger ?? ServicesSystem::LOGGER();
		self::$debug    = $debug ?? HKM_DEBUG;
		self::$saveData = (bool) $config::$saveData;
		self::$thiss = $this;
	}

	/**
	 * Builds the output based upon a file name and any
	 * data that has already been set.
	 *
	 * Valid $options:
	 *  - cache      Number of seconds to cache for
	 *  - cache_name Name to use for cache
	 *
	 * @param string       $view     File name of the view source
	 * @param array|null   $options  Reserved for 3rd-party uses since
	 *                               it might be needed to pass additional info
	 *                               to other template engines.
	 * @param boolean|null $saveData If true, saves data for subsequent calls,
	 *                               if false, cleans the data after displaying,
	 *                               if null, uses the config setting.
	 *
	 * @return string
	 */
	public static function RENDER(string $view, array $options = null, bool $saveData = null): string
	{
		self::$renderVars['start'] = microtime(true);

		// Store the results here so even if
		// multiple views are called in a view, it won't
		// clean it unless we mean it to.
		$saveData                    = $saveData ?? self::$saveData;
		$fileExt                     = pathinfo($view, PATHINFO_EXTENSION);
		$realPath                    = empty($fileExt) ? $view . '.php' : $view; // allow Views as .html, .tpl, etc (from CI3)
		self::$renderVars['view']    = $realPath;
		self::$renderVars['options'] = $options ?? [];

		// Was it cached?
		if (isset(self::$renderVars['options']['cache']))
		{
			$cacheName = self::$renderVars['options']['cache_name'] ?? str_replace('.php', '', self::$renderVars['view']);
			$cacheName = str_replace(['\\', '/'], '', $cacheName);

			self::$renderVars['cacheName'] = $cacheName;

			if ($output = hkm_cache(self::$renderVars['cacheName']))
			{
				self::LOG_PERFOMANCE(self::$renderVars['start'], microtime(true), self::$renderVars['view']);

				return $output;
			}
		}

		self::$renderVars['file'] = self::$viewPath.str_replace("\\","/", self::$renderVars['view']);

		
		if (! is_file(self::$renderVars['file']))
		{

			self::$renderVars['file'] = self::$loader::LOCATE_FILE(self::$renderVars['view'], 'Views', empty($fileExt) ? 'php' : $fileExt);
		}
	
		if (empty(self::$renderVars['file'])){
			self::$renderVars['file'] = hkm_apply_filters('on_extended_view_system',str_replace('.php','',self::$renderVars['view']));
		}



		// locateFile will return an empty string if the file cannot be found.
		if (empty(self::$renderVars['file']))
		{
			throw ViewException::FOR_INVALID_FILE(self::$renderVars['view']);
		}


		// Make our view data available to the view.
		self::$tempData = self::$tempData ?? self::$data;


		if ($saveData)
		{
			self::$data = self::$tempData;
		}

		// Save current vars
		$renderVars = self::$renderVars;


		$output = (function (): string {
			if(is_array(self::$renderVars['file'])){
				if (!is_file(self::$renderVars['file'][0])) {
					throw ViewException::FOR_INVALID_FILE(self::$renderVars['view']);
				}
			}else{
				if (!is_file(self::$renderVars['file'])) {
					throw ViewException::FOR_INVALID_FILE(self::$renderVars['view']);
				}	
			}
			extract(self::$tempData);
			ob_start();
			if(is_array(self::$renderVars['file'])) include self::$renderVars['file'][0];
			else include self::$renderVars['file'];
			return ob_get_clean() ?: '';
		})();

		// Get back current vars
		self::$renderVars = $renderVars;

		// When using layouts, the data has already been stored
		// in self::$sections, and no other valid output
		// is allowed in $output so we'll overwrite it.
		if (! is_null(self::$layout) && self::$sectionStack === [])
		{
			
			$layoutView   = self::$layout;
			self::$layout = null;
			// Save current vars
			$renderVars = self::$renderVars;
			$output     = self::RENDER($layoutView, $options, $saveData);
			// Get back current vars
			self::$renderVars = $renderVars;
		}

		self::LOG_PERFOMANCE(self::$renderVars['start'], microtime(true), self::$renderVars['view']);


		if ((self::$debug && (! isset($options['debug']) || $options['debug'] === true))
			&& in_array('Hkm_code\Filters\DebugToolbar',ServicesSystem::FILTERS()::GET_FILTERS_CLASS()['after'], true)
		)
		{

			$toolbarCollectors = hkm_config(Toolbar::class)::$collectors;
			if (in_array(Views::class, $toolbarCollectors, true))
			{
				
				// Clean up our path names to make them a little cleaner
				self::$renderVars['file'] = hkm_clean_path(self::$renderVars['file']);
				self::$renderVars['file'] = ++self::$viewsCount . ' ' . self::$renderVars['file'];

				$output = '<!-- DEBUG-VIEW START ' . self::$renderVars['file'] . ' -->' . PHP_EOL
					. $output . PHP_EOL
					. '<!-- DEBUG-VIEW ENDED ' . self::$renderVars['file'] . ' -->' . PHP_EOL;

					
			}
		}


		// Should we cache?
		if (isset(self::$renderVars['options']['cache']))
		{
			hkm_cache()::SAVE(self::$renderVars['cacheName'], $output, (int) self::$renderVars['options']['cache']);
		}

		self::$tempData = null;

		return $output;
	}

	/**
	 * Builds the output based upon a string and any
	 * data that has already been set.
	 * Cache does not apply, because there is no "key".
	 *
	 * @param string       $view     The view contents
	 * @param array|null   $options  Reserved for 3rd-party uses since
	 *                               it might be needed to pass additional info
	 *                               to other template engines.
	 * @param boolean|null $saveData If true, saves data for subsequent calls,
	 *                               if false, cleans the data after displaying,
	 *                               if null, uses the config setting.
	 *
	 * @return string
	 */
	public static function RENDER_STRING(string $view, array $options = null, bool $saveData = null): string
	{
		$start          = microtime(true);
		$saveData       = $saveData ?? self::$saveData;
		self::$tempData = self::$tempData ?? self::$data;

		if ($saveData)
		{
			self::$data = self::$tempData;
		}

		$output = (function (string $view): string {
			extract(self::$tempData);
			ob_start();
			eval('?>' . $view);
			return ob_get_clean() ?: '';
		})($view);

		self::LOG_PERFOMANCE($start, microtime(true), self::EXCEPT($view));
		self::$tempData = null;

		return $output;
	}

	/**
	 * Extract first bit of a long string and add ellipsis
	 *
	 * @param  string  $string
	 * @param  integer $length
	 * @return string
	 */
	public static function EXCEPT(string $string, int $length = 20): string
	{
		return (strlen($string) > $length) ? substr($string, 0, $length - 3) . '...' : $string;
	}

	/**
	 * Sets several pieces of view data at once.
	 *
	 * @param array  $data
	 * @param string $context The context to escape it for: html, css, js, url
	 *                        If null, no escaping will happen
	 *
	 * @return RendererInterface
	 */
	public static function SET_DATA(array $data = [], string $context = null): RendererInterface
	{
		if ($context)
		{
			$data = hkm_esc($data, $context);
		}

		self::$tempData = self::$tempData ?? self::$data;
		self::$tempData = array_merge(self::$tempData, $data);

		return self::$thiss;
	}

	/**
	 * Sets a single piece of view data.
	 *
	 * @param string $name
	 * @param mixed  $value
	 * @param string $context The context to escape it for: html, css, js, url
	 *                        If null, no escaping will happen
	 *
	 * @return RendererInterface
	 */
	public static function SET_VAR(string $name, $value = null, string $context = null): RendererInterface
	{
		if ($context)
		{
			$value = hkm_esc($value, $context);
		}

		self::$tempData        = self::$tempData ?? self::$data;
		self::$tempData[$name] = $value;

		return self::$thiss;
	}

	/**
	 * Removes all of the view data from the system.
	 *
	 * @return RendererInterface
	 */
	public static function RESET_DATA(): RendererInterface
	{
		self::$data = [];

		return self::$thiss;
	}

	/**
	 * Returns the current data that will be displayed in the view.
	 *
	 * @return array
	 */
	public static function GET_DATA(): array
	{
		return self::$tempData ?? self::$data;
	}

	/**
	 * Specifies that the current view should extend an existing layout.
	 *
	 * @param string $layout
	 *
	 * @return void
	 */
	public static function EXTEND(string $layout)
	{
		self::$layout = $layout;
	}

	/**
	 * Starts holds content for a section within the layout.
	 *
	 * @param string $name Section name
	 *
	 * @return void
	 *
	 */
	public static function SECTION(string $name)
	{
		//Saved to prevent BC.
		self::$currentSection = $name;
		self::$sectionStack[] = $name;

		ob_start();
	}

	/**
	 * Captures the last section
	 *
	 * @return void
	 * @throws RuntimeException
	 */
	public static function END_SECTION()
	{
		$contents = ob_get_clean();

		if (self::$sectionStack === [])
		{
			throw new RuntimeException('View themes, no current section.');
		}

		$section = array_pop(self::$sectionStack);

		// Ensure an array exists so we can store multiple entries for this.
		if (! array_key_exists($section, self::$sections))
		{
			self::$sections[$section] = [];
		}

		self::$sections[$section][] = $contents;
	}

	/**
	 * Renders a section's contents.
	 *
	 * @param string $sectionName
	 */
	public static function RENDER_SECTION(string $sectionName)
	{
		if (! isset(self::$sections[$sectionName]))
		{
			echo '';

			return;
		}

		foreach (self::$sections[$sectionName] as $key => $contents)
		{
			echo $contents;
			unset(self::$sections[$sectionName][$key]);
		}
	}

	/**
	 * Used within layout views to include additional views.
	 *
	 * @param string     $view
	 * @param array|null $options
	 * @param boolean    $saveData
	 *
	 * @return string
	 */
	public static function INCLUDE(string $view, array $options = null, $saveData = true): string
	{
		return self::RENDER($view, $options, $saveData);
	}

	/**
	 * Returns the performance data that might have been collected
	 * during the execution. Used primarily in the Debug Toolbar.
	 *
	 * @return array
	 */
	public static function GET_PERFOMANCE_DATA(): array
	{
		return self::$performanceData;
	}

	/**
	 * Logs performance data for rendering a view.
	 *
	 * @param float  $start
	 * @param float  $end
	 * @param string $view
	 *
	 * @return void
	 */
	protected static function LOG_PERFOMANCE(float $start, float $end, string $view)
	{
		if (self::$debug)
		{
			self::$performanceData[] = [
				'start' => $start,
				'end'   => $end,
				'view'  => $view,
			];
		}
	}
}
