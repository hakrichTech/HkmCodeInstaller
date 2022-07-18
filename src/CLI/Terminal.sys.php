<?php
namespace Hkm_code\CLI;
use Hkm_code\Application;
use Exception;
use Hkm_code\Setup;

class Terminal
{

    /**
	 * Main Hkm_code instance.
	 *
	 * @var Setup
	 */
	protected static $app;

	
	public function __construct(Setup $app)
	{
		CLI::init();
		self::$app = $app;
	}

	//--------------------------------------------------------------------

	/**
	 * Runs the current command discovered on the CLI.
	 *
	 * @param boolean $useSafeOutput
	 *
	 * @return Request|Response|Response|mixed
	 * @throws Exception
	 */
	public static function RUN(bool $useSafeOutput = false)
	{

		$path = CLI::getURI() ?: 'list';

		
		self::$app::SET_PATH("ci{$path}");


		return self::$app::USE_SAFE_OUTPUT($useSafeOutput)::RUN();
	}
 

	/**
	 * Runs the current command discovered on the CLI.
	 *
	 * @param boolean $useSafeOutput
	 *
	 * @return Request|Response|Response|mixed
	 * @throws Exception
	 */
	public static function RUN_BIN(bool $useSafeOutput = false)
	{
		$path = CLI::getURI() ?: 'info:system';


		self::$app::SET_PATH("hkm{$path}");


		return self::$app::USE_SAFE_OUTPUT($useSafeOutput)::RUN();
	}
  
	 
	public static function SHOW_HEADER(bool $suppress = false)
	{
		if ($suppress)
		{
			return;
		}
		CLI::write(sprintf('Hakrich System v%s Command Line Tool - Server Time: %s UTC%s', Application::HKM_VERSION, date('Y-m-d H:i:s'), date('P')), 'green');
		CLI::newLine();
	}
}
