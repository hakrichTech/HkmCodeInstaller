<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@Hkm_code.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\Debug;

use Hkm_code\Application;
use Hkm_code\Debug\Toolbar\Collectors\BaseCollector;
use Hkm_code\Debug\Toolbar\Collectors\Config;
use Hkm_code\Debug\Toolbar\Collectors\History;
use Hkm_code\Format\JSONFormatter;
use Hkm_code\Format\XMLFormatter;
use Hkm_code\HTTP\DownloadResponse;
use Hkm_code\HTTP\IncomingRequest;
use Hkm_code\HTTP\Request;
use Hkm_code\HTTP\Response;
use Hkm_code\Vezirion\ServicesSystem;
use GuzzleHttp\RequestOptions;
use Kint\Kint;

/**
 * Debug Toolbar
 *
 * Displays a toolbar with bits of stats to aid a developer in debugging.
 *
 * Inspiration: http://prophiler.fabfuel.de
 */
class Toolbar
{
	/**
	 * Toolbar configuration settings.
	 *
	 * @var ToolbarConfig
	 */
	protected static $config;

	/**
	 * Collectors to be used and displayed.
	 *
	 * @var BaseCollector[]
	 */
	protected static $collectors = [];

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @param ToolbarConfig $config
	 */
	public function __construct( $config)
	{

		self::$config = $config;

		foreach ($config::$collectors as $collector)
		{
			if (! class_exists($collector))
			{
				hkm_log_message('critical', 'Toolbar collector does not exists(' . $collector . ').' .
						'please check $collectors in the '.'\Toolbar.php file.');
				continue;
			}

			self::$collectors[] = new $collector();
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Returns all the data required by Debug Bar
	 *
	 * @param float             $startTime App start time
	 * @param float             $totalTime
	 * @param Request  $request
	 * @param Response $response
	 *
	 * @return string JSON encoded data
	 */
	public static function RUN(float $startTime, float $totalTime, Request $request, Response $response): string
	{

		/**
		 * @var IncomingRequest $request
		 * @var Response $response
		 */
		hkm_helper('url');
		// Data items used within the view.
		$data['url']             = hkm_current_url();
		$data['method']          = $request::GET_METHOD(true);
		$data['isAJAX']          = $request::IS_AJAX();
		$data['startTime']       = $startTime;
		$data['totalTime']       = $totalTime * 1000;
		$data['totalMemory']     = number_format((memory_get_peak_usage()) / 1024 / 1024, 3);
		$data['segmentDuration'] = self::ROUND_TO($data['totalTime'] / 7);
		$data['segmentCount']    = (int) ceil($data['totalTime'] / $data['segmentDuration']);
		$data['HKM_VERSION']      = Application::HKM_VERSION;
		$data['collectors']      = [];

		foreach (self::$collectors as $collector)
		{
			$data['collectors'][] = $collector::GET_AS_ARRAY();
		}

		foreach (self::COLLECT_VAR_DATA() as $heading => $items)
		{
			$varData = [];

			if (is_array($items))
			{
				foreach ($items as $key => $value)
				{
					if (is_string($value))
					{
						$varData[hkm_esc($key)] = hkm_esc($value);
					}
					else
					{
						$oldKintMode       = Kint::$mode_default;
						$oldKintCalledFrom = Kint::$display_called_from;

						Kint::$mode_default        = Kint::MODE_RICH;
						Kint::$display_called_from = false;

						$kint = @Kint::dump($value);
						$kint = substr($kint, strpos($kint, '</style>') + 8 );

						Kint::$mode_default        = $oldKintMode;
						Kint::$display_called_from = $oldKintCalledFrom;

						$varData[hkm_esc($key)] = $kint;
					}
				}
			}

			$data['vars']['varData'][hkm_esc($heading)] = $varData;
		}

		if (! empty($_SESSION))
		{
			foreach ($_SESSION as $key => $value)
			{
				// Replace the binary data with string to avoid json_encode failure.
				if (is_string($value) && preg_match('~[^\x20-\x7E\t\r\n]~', $value))
				{
					$value = 'binary data';
				}

				$data['vars']['session'][hkm_esc($key)] = is_string($value) ? hkm_esc($value) : '<pre>' . hkm_esc(print_r($value, true)) . '</pre>';
			}
		}

		foreach ($request::GET_GET() as $name => $value)
		{
			$data['vars']['get'][hkm_esc($name)] = is_array($value) ? '<pre>' . hkm_esc(print_r($value, true)) . '</pre>' : hkm_esc($value);
		}

		foreach ($request::GET_POST() as $name => $value)
		{
			$data['vars']['post'][hkm_esc($name)] = is_array($value) ? '<pre>' . hkm_esc(print_r($value, true)) . '</pre>' : hkm_esc($value);
		}

        $options = [];
		foreach ($request::HEADERS() as $key => $value)
		{
			if (is_string($key) && ! is_array($value))
			{
				$options[] = $key . '=' . $value;
			}
			elseif (is_array($value))
			{
				$key       = key($value);
				$options[] = $key . '=' . $value[$key];
			}
			elseif (is_numeric($key))
			{
				$options[] = $value;
			}
		}

		$data['vars']['headers'][hkm_esc($request::HEADERS()['Host'])] = hkm_esc(implode(', ', $options));


		foreach ($request::GET_COOKIE() as $name => $value)
		{
			$data['vars']['cookies'][hkm_esc($name)] = hkm_esc($value);
		}

		$data['vars']['request'] = ($request::IS_SECURE() ? 'HTTPS' : 'HTTP') . '/' . $request::GET_PROTOCOL_VERSION();

		$data['vars']['response'] = [
			'statusCode'  => $response::GET_STATUS_CODE(),
			'reason'      => hkm_esc($response::GET_REASON()),
			'contentType' => hkm_esc($response::GET_HEADER_LINE('content-type')),
		];

		$data['config'] = Config::display();

		if ($response::$CSP !== null)
		{
			$response::$CSP::ADD_IMAGE_SRC('data:');
		}

		return json_encode($data);
	}

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------

	/**
	 * Called within the view to display the timeline itself.
	 *
	 * @param array   $collectors
	 * @param float   $startTime
	 * @param integer $segmentCount
	 * @param integer $segmentDuration
	 * @param array   $styles
	 *
	 * @return string
	 */
	protected static function RNDER_TIMELINE(array $collectors, float $startTime, int $segmentCount, int $segmentDuration, array &$styles): string
	{
		$displayTime = $segmentCount * $segmentDuration;
		$rows        = self::COLLECTION_TIMELINE_DATA($collectors);
		$output      = '';
		$styleCount  = 0;

		foreach ($rows as $row)
		{
			$output .= '<tr>';
			$output .= "<td>{$row['name']}</td>";
			$output .= "<td>{$row['component']}</td>";
			$output .= "<td class='debug-bar-alignRight'>" . number_format($row['duration'] * 1000, 2) . ' ms</td>';
			$output .= "<td class='debug-bar-noverflow' colspan='{$segmentCount}'>";

			$offset = ((((float) $row['start'] - $startTime) * 1000) / $displayTime) * 100;
			$length = (((float) $row['duration'] * 1000) / $displayTime) * 100;

			$styles['debug-bar-timeline-' . $styleCount] = "left: {$offset}%; width: {$length}%;";

			$output .= "<span class='timer debug-bar-timeline-{$styleCount}' title='" . number_format($length, 2) . "%'></span>";
			$output .= '</td>';
			$output .= '</tr>';

			$styleCount++;
		}

		return $output;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a sorted array of timeline data arrays from the collectors.
	 *
	 * @param array $collectors
	 *
	 * @return array
	 */
	protected static function COLLECTION_TIMELINE_DATA($collectors): array
	{
		$data = [];

		// Collect it
		foreach ($collectors as $collector)
		{
			if (! $collector['hasTimelineData'])
			{
				continue;
			}

			$data = array_merge($data, $collector['timelineData']);
		}

		// Sort it

		return $data;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an array of data from all of the modules
	 * that should be displayed in the 'Vars' tab.
	 *
	 * @return array
	 */
	protected static function COLLECT_VAR_DATA(): array
	{
		$data = [];

		foreach (self::$collectors as $collector)
		{
			if (! $collector::HAS_VAR_DATA())
			{
				continue;
			}

			$data = array_merge($data, $collector::GET_VAR_DATA()());
		}

		return $data;
	}

	//--------------------------------------------------------------------

	/**
	 * Rounds a number to the nearest incremental value.
	 *
	 * @param float   $number
	 * @param integer $increments
	 *
	 * @return float
	 */
	protected static function ROUND_TO(float $number, int $increments = 5): float
	{
		$increments = 1 / $increments;

		return (ceil($number * $increments) / $increments);
	}

	//--------------------------------------------------------------------

	/**
	 * PREPARE for debugging..
	 *
	 * @param  RequestOptions  $request
	 * @param  ResponseRequestOptions $response
	 * @global \Hkm_code\Hkm_code $app
	 * @return void
	 */
	public static function PREPARE(Request $request = null, Response $response = null)
	{
		/**
		 * @var IncomingRequest $request
		 * @var Response $response
		 */

		if (HKM_DEBUG && ! hkm_is_cli())
		{
			global $app;

			$request  = $request ?? ServicesSystem::REQUEST();
			$response = $response ?? ServicesSystem::RESPONSE();

			// Disable the toolbar for downloads
			if ($response instanceof DownloadResponse)
			{
				return;
			}

			$toolbar = ServicesSystem::TOOLBAR(hkm_config('Toolbar'));
			$stats   = $app::GET_PERFOMANCE_STATUS();
			$data    = $toolbar::RUN(
					$stats['startTime'],
					$stats['totalTime'],
					$request,
					$response
			);

			hkm_helper('filesystem');

			// Updated to time() so we can get history
			$time = time();

			if (! is_dir(WRITEPATH . 'debugbar'))
			{
				mkdir(WRITEPATH . 'debugbar', 0777);
			}

			hkm_write_file(WRITEPATH . 'debugbar/' . 'debugbar_' . $time . '.json', $data, 'w+');

			$format = $response::GET_HEADER_LINE('content-type');

			// Non-HTML formats should not include the debugbar
			// then we send headers saying where to find the debug data
			// for this response
			if ($request::IS_AJAX() || strpos($format, 'html') === false)
			{
				$response::SET_HEADER('Debugbar-Time', "$time")
						::SET_HEADER('Debugbar-Link', hkm_site_url("?debugbar_time={$time}"))
						::GET_BODY();

				return;
			}

			$oldKintMode        = Kint::$mode_default;
			Kint::$mode_default = Kint::MODE_RICH;
			$kintScript         = @Kint::dump('');
			Kint::$mode_default = $oldKintMode;
			$kintScript         = substr($kintScript, 0, strpos($kintScript, '</style>') + 8 );

			$script = PHP_EOL
					. '<script type="text/javascript" {csp-script-nonce} id="debugbar_loader" '
					. 'data-time="' . $time . '" '
					. 'src="' . hkm_site_url() . '?debugbar"></script>'
					. '<script type="text/javascript" {csp-script-nonce} id="debugbar_dynamic_script"></script>'
					. '<style type="text/css" {csp-style-nonce} id="debugbar_dynamic_style"></style>'
					. $kintScript
					. PHP_EOL;

			if (strpos($response::GET_BODY(), '<head>') !== false)
			{
				$response::SET_BODY(preg_replace('/<head>/', '<head>' . $script, $response::GET_BODY(), 1));
				return;
			}

			$response::APPEND_BODY($script);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Inject debug toolbar into the response.
	 */
	public static function RESPOND()
	{
		if (ENVIRONMENT === 'testing')
		{
			return;
		}

		// @codeCoverageIgnoreStart
		$request = ServicesSystem::REQUEST();

		// If the request contains '?debugbar then we're
		// simply returning the loading script
		if ($request::GET_GET('debugbar') !== null)
		{
			
			// Let the browser know that we are sending javascript
			header('Content-Type: application/javascript');

			ob_start();
			include(self::$config::$viewsPath . 'toolbarloader.js.php');
			$output = ob_get_clean();

			exit($output);
		}
		

		// Otherwise, if it includes ?debugbar_time, then
		// we should return the entire debugbar.
		if ($request::GET_GET('debugbar_time'))
		{
			hkm_helper('security');

			// Negotiate the content-type to format the output
			$format = $request::NEGOTIATE('media', [
				'text/html',
				'application/json',
				'application/xml',
			]);
			$format = explode('/', $format)[1];

			$file     = sanitize_filename('debugbar_' . $request::GET_GET('debugbar_time'));
			$filename = WRITEPATH . 'debugbar/' . $file . '.json';

			// Show the toolbar
			if (is_file($filename))
			{
				$contents = self::FORMAT(file_get_contents($filename), $format);
				exit($contents);
			}

			// File was not written or do not exists
			http_response_code(404);
			exit; // Exit here is needed to avoid load the index page
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Format output
	 *
	 * @param string $data   JSON encoded Toolbar data
	 * @param string $format html, json, xml
	 *
	 * @return string
	 */
	protected static function FORMAT(string $data, string $format = 'html'): string
	{
		$data = json_decode($data, true);

		if (self::$config::$maxHistory !== 0)
		{
			$history = new History();
			$history::SET_FILES(
				(int) ServicesSystem::REQUEST()::GET_GET('debugbar_time'),
				self::$config::$maxHistory
			);

			$data['collectors'][] = $history::GET_AS_ARRAY();
		}

		$output = '';

		switch ($format)
		{
			case 'html':
				$data['styles'] = [];
				extract($data);
				$parser = ServicesSystem::PARSE(self::$config::$viewsPath, null, false);
				ob_start();
				include(self::$config::$viewsPath . 'toolbar.tpl.php');
				$output = ob_get_clean();
				break;
			case 'json':
				$formatter = new JSONFormatter();
				$output    = $formatter::FORMAT($data);
				break;
			case 'xml':
				$formatter = new XMLFormatter;
				$output    = $formatter::FORMAT($data);
				break;
		}

		return $output;
	}
}
