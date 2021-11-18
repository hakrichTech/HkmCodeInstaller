<?php
namespace Hkm_code\CLI;

use Hkm_code\Controller;
use Hkm_code\Vezirion\Services;
use ReflectionException;
use Hkm_code\Exceptions\SystemException;

/**
 * Command runner
 */ 
class CommandRunner extends Controller
{
	/**
	 * Instance of class managing the collection of commands
	 *
	 * @var Commands
	 */
	protected static $commands;
	public static $thiss;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		self::$commands = Services::COMMANDS();
		self::$thiss = $this;
	}

	/**
	 * We map all un-routed CLI methods through this function
	 * so we have the chance to look for a Command first.
	 *
	 * @param string $method
	 * @param array  ...$params
	 *
	 * @return mixed
	 * @throws ReflectionException
	 */
	public static function _REMAP($method, ...$params)
	{
		// The first param is usually empty, so scrap it.
		if (empty($params[0]))
		{
			array_shift($params);
		}
		if (method_exists(self::$thiss, strtoupper($method))) {
			return self::$method($params);
		}else{
			SystemException::FOR_METHOD_NOT_DEFINED($method);
		}
		
	}

	/**
	 * Default command.
	 *
	 * @param array $params
	 *
	 * @return mixed
	 * @throws ReflectionException
	 */
	public static function INDEX(array $params)
	{
		
		$command = array_shift($params) ?? 'list';
		return self::$commands::RUN($command, $params);
	}

	/**
	 * Allows access to the current commands that have been found.
	 *
	 * @return array
	 */
	public static function GET_COMMANDS(): array
	{
		return self::$commands::GET_COMMANDS();
	}
}
