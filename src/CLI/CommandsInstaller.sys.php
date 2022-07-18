<?php

namespace Hkm_code\CLI;

use Hkm_code\Log\Logger;
use Hkm_code\Vezirion\ServicesSystem;
use ReflectionClass;
use ReflectionException;

/**
 * Core functionality for running, listing, etc commands.
 */
class CommandsInstaller
{
	/**
	 * The found commands.
	 *
	 * @var array
	 */
	protected static $commands = [];

	/**
	 * Logger instance.
	 *
	 * @var Logger
	 */
	protected static $logger;
	protected static $thiss;

	/**
	 * Constructor
	 *
	 * @param Logger|null $logger
	 */
	public function __construct($logger = null)
	{

		self::$logger = $logger ?? ServicesSystem::LOGGER();

		self::$thiss = $this;
		self::DISCOVER_COMMANDS();


	}

	/**
	 * Runs a command given
	 *
	 * @param string $command
	 * @param array  $params
	 */
	public static function RUN(string $command, array $params)
	{
		if (! self::VERIFY_COMMAND($command, self::$commands))
		{

			return;
		}

		// The file would have already been loaded during the
		// createCommandList function...
		$className = self::$commands[$command]['class'];

		$class     = new $className(self::$logger, self::$thiss);

		return $class->run($params);
	}

	/**
	 * Provide access to the list of commands.
	 *
	 * @return array
	 */
	public static function GET_COMMANDS()
	{
		return self::$commands;
	}

	/**
	 * Discovers all commands in the framework and within user code,
	 * and collects instances of them to work with.
	 *
	 * @return void
	 */
	public static function DISCOVER_COMMANDS()
	{
		if (self::$commands !== [])
		{
			return;
		} 

		/** @var FileLocator $locator */
		$files   = hkm_findQualifiedNameFromPath('CommandsInstaller');

		
 
		// If no matching command files were found, bail
		// This should never happen in unit testing.
		if ($files === [])
		{
			return; // @codeCoverageIgnore
		}

		// Loop over each file checking to see if a command with that
		// alias exists in the class.
		foreach ($files as $file)
		{
			$className = $file;

			if (empty($className) || ! class_exists($className))
			{

				continue;
			}

			try
			{
				$class = new ReflectionClass($className);
				if (! $class->isInstantiable() || ! $class->isSubclassOf(BaseCommand::class))
				{
					continue;
				}

				/** @var BaseCommand $class */
				$class = new $className(self::$logger, self::$thiss);

				if (isset($class->group))
				{
					self::$commands[$class->name] = [
						'class'       => $className,
						'file'        => $file,
						'group'       => $class->group,
						'description' => $class->description,
					];
				}

				unset($class);
			}
			catch (ReflectionException $e)
			{
				self::$logger->error($e->getMessage());
			}
		}

		asort(self::$commands);
	}

	/**
	 * Verifies if the command being sought is found
	 * in the commands list.
	 *
	 * @param string $command
	 * @param array  $commands
	 *
	 * @return boolean
	 */
	public static function VERIFY_COMMAND(string $command, array $commands): bool
	{
		if (isset($commands[$command]))
		{
			return true;
		}

		$message = hkm_lang('CLI.commandNotFound', [$command]);

		if ($alternatives = self::GET_COMMAND_ALTERNATIVES($command, $commands))
		
		{
			if (count($alternatives) === 1)
			{
				$message .= "\n\n" . hkm_lang('CLI.altCommandSingular') . "\n    ";
			}
			else
			{
				$message .= "\n\n" . hkm_lang('CLI.altCommandPlural') . "\n    ";
			}

			$message .= implode("\n    ", $alternatives);
		}

		CLI::error($message);
		CLI::newLine();

		return false;
	}

	/**
	 * Finds alternative of `$name` among collection
	 * of commands.
	 *
	 * @param string $name
	 * @param array  $collection
	 *
	 * @return array
	 */
	protected static function GET_COMMAND_ALTERNATIVES(string $name, array $collection): array
	{
		$alternatives = [];

		foreach (array_keys($collection) as $commandName)
		{
			$lev = levenshtein($name, $commandName);

			if ($lev <= strlen($commandName) / 3 || strpos($commandName, $name) !== false)
			{
				$alternatives[$commandName] = $lev;
			}
		}

		ksort($alternatives, SORT_NATURAL | SORT_FLAG_CASE);

		return array_keys($alternatives);
	}
}
