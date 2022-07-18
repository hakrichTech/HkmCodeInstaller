<?php

namespace Hkm_code;

use Hkm_code\HTTP\Response;
use Hkm_code\Vezirion\ServicesSystem;
use Hkm_code\Application as App;
use Hkm_code\Exceptions\PageNotFoundException;
use Hkm_code\Exceptions\Pobohet\RedirectException;

/** 
 *
 */
class Setup extends App
{
	protected static $thisSetup;
	function __construct(\Hkm_code\Vezirion\BaseVezirion $config)
	{

		parent::__construct($config);
		self::$thisSetup = $this;

	}
	public static function USE_SAFE_OUTPUT(bool $safe = true)
	{
		self::$useSafeOutput = $safe;

		return self::$thisSetup;
	}
	public static function RUN($routes = null,  bool $returnResponse = false)
	{
		
			date_default_timezone_set('UTC');


		self::START_BENCHMARK();
		self::GET_REQUEST_OBJECT();
		self::GET_RESPONSE_OBJECT();

		self::FORCE_SECURE_ACCESS();

		self::SPOOF_REQUEST_METHOD();

		$cacheConfig = hkm_config('Cache');


		$response    = self::DISPLAY_CACHE($cacheConfig);

		if ($response instanceof Response) {
			if ($returnResponse) {

				return $response;
			}

			self::$response::PRETEND(self::$useSafeOutput)::SEND();
			self::CALL_EXIT(EXIT_SUCCESS);
			return;
		}

		try {

			return self::HANDLE_REQUEST($routes, $cacheConfig, $returnResponse);
			
		} catch (RedirectException $e) {
			$logger = ServicesSystem::LOGGER();
			$logger->info('REDIRECTED ROUTE at ' . $e->getMessage());

			// If the route is a 'redirect' route, it throws
			// the exception with the $to as the message
			self::$response::REDIRECT(hkm_base_url($e->getMessage()), 'auto', $e->getCode());
			self::SEND_RESPONSE();

			self::CALL_EXIT(EXIT_SUCCESS);
			return;
		} catch (PageNotFoundException $e) {

			if (ENVIRONMENT == "production") {
				self::DISPLAY_404_ERRORS($e::FOR_PAGE_NOT_FOUND_PRODUCTION());
			} else {
				self::DISPLAY_404_ERRORS($e);
			}
		}
		
		
	}
	protected static function START_BENCHMARK()
	{
		self::$startTime = microtime(true);

		self::$benchmark = ServicesSystem::TIMER();
		self::$benchmark::START('total_execution', self::$startTime);
		self::$benchmark::START('bootstrap');
	}

	public static function GET_PERFOMANCE_STATUS(): array
	{
		return [
			'startTime' => self::$startTime,
			'totalTime' => self::$totalTime,
		];
	}
}
