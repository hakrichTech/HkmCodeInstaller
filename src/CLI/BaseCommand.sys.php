<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@hakrichteam.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\CLI;

use Psr\Log\LoggerInterface;
use ReflectionException;
use Throwable;

/**
 * BaseCommand is the base class used in creating CLI commands.
 *
 * @property string          $group
 * @property string          $name
 * @property string          $usage
 * @property string          $description
 * @property array           $options
 * @property array           $arguments
 * @property LoggerInterface $logger
 * @property Commands        $commands
 */
abstract class BaseCommand
{
	/**
	 * The group the command is lumped under
	 * when listing commands.
	 *
	 * @var string
	 */
	protected $group;

	/**
	 * The Command's name
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * the Command's usage description
	 *
	 * @var string
	 */
	protected $usage;

	/**
	 * the Command's short description
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * the Command's options description
	 *
	 * @var array
	 */
	protected $options = [];

	/**
	 * the Command's Arguments description
	 *
	 * @var array
	 */
	protected $arguments = [];

	/**
	 * The Logger to use for a command
	 *
	 * @var LoggerInterface
	 */
	protected $logger;

	/**
	 * Instance of Commands so
	 * commands can call other commands.
	 *
	 * @var Commands
	 */
	protected $commands;

	/**
	 * BaseCommand constructor.
	 *
	 * @param LoggerInterface $logger
	 */
	public function __construct(LoggerInterface $logger, $commands)
	{
		$this->logger   = $logger;
		$this->commands = $commands;

	}

	/**
	 * Actually execute a command.
	 * This has to be over-ridden in any concrete implementation.
	 *
	 * @param array $params
	 */
	abstract public function run(array $params);

	/**
	 * Can be used by a command to run other commands.
	 *
	 * @param string $command
	 * @param array  $params
	 *
	 * @return mixed
	 * @throws ReflectionException
	 */
	protected function call(string $command, array $params = [])
	{

		return $this->commands::RUN($command, $params);
	}

	/**
	 * A simple method to display an error with line/file, in child commands.
	 *
	 * @param Throwable $e
	 */
	protected function showError(Throwable $e)
	{
		$exception = $e;
		$message   = $e->getMessage();

		require SYSTEMROOTPATH . 'errors/cli/error_exception.php';
	}

	/**
	 * Show Help includes (Usage, Arguments, Description, Options).
	 */
	public function showHelp()
	{
		CLI::write(hkm_lang('CLI.helpUsage'), 'yellow');

		if (! empty($this->usage))
		{
			$usage = $this->usage;
		}
		else
		{
			$usage = $this->name;

			if (! empty($this->arguments))
			{
				$usage .= ' [arguments]';
			}
		}

		CLI::write(hkm_setPad($usage, 0, 0, 2));

		if (! empty($this->description))
		{
			CLI::newLine();
			CLI::write(hkm_lang('CLI.helpDescription'), 'yellow');
			CLI::write(hkm_setPad($this->description, 0, 0, 2));
		}

		if (! empty($this->arguments))
		{
			CLI::newLine();
			CLI::write(hkm_lang('CLI.helpArguments'), 'yellow');
			$length = max(array_map('strlen', array_keys($this->arguments)));

			foreach ($this->arguments as $argument => $description)
			{
				CLI::write(CLI::color(hkm_setPad($argument, $length, 2, 2), 'green') . $description);
			}
		}

		if (! empty($this->options))
		{
			CLI::newLine();
			CLI::write(hkm_lang('CLI.helpOptions'), 'yellow');
			$length = max(array_map('strlen', array_keys($this->options)));

			foreach ($this->options as $option => $description)
			{
				CLI::write(CLI::color(hkm_setPad($option, $length, 2, 2), 'green') . $description);
			}
		}
	}

	/**
	 * Pads our string out so that all titles are the same length to nicely line up descriptions.
	 *
	 * @param string  $item
	 * @param integer $max
	 * @param integer $extra  How many extra spaces to add at the end
	 * @param integer $indent
	 *
	 * @return string
	 */
	public function hkm_setPad(string $item, int $max, int $extra = 2, int $indent = 0): string
	{
		$max += $extra + $indent;

		return str_pad(str_repeat(' ', $indent) . $item, $max);
	}


	public function AppName()
	{

		$f=false;
		$pat = '';
		$app = trim(CLI::prompt('App name', null, 'required'));

		CLI::newLine(0);

		
        if ($app == APP_NAME)$f = true;
		elseif ($app."HakrichApp" ==APP_NAME) $f=true; 
		else {
			if ($app == "System") {
				CLI::error(hkm_lang('Security.disallowedAction'), 'light_gray', 'red');
			    CLI::newLine();
				exit;
			}else{
				$app2 = $app."HakrichApp";
				$pat = SYSTEMROOTPATH."../".$app."/App";
				$pat1 = SYSTEMROOTPATH."../".$app;
				$pat2 =SYSTEMROOTPATH."../".$app2."/App";
				$pat3 = SYSTEMROOTPATH."../".$app2;


				
				if (is_dir($pat)) {
					$pat = $pat1;
					$f = true;
				}else {
					if (is_dir($pat2)) {
						$app = $app2;
						$pat = $pat3;
						$f = true;
					}else {
						CLI::error('Directory Not found: '.$pat, 'light_gray', 'red');
						CLI::newLine();
						exit;
					}
					
				}
			}
		}

		
		return [$f,$pat,$app];
	}

	/**
	 * Get pad for $key => $value array output
	 *
	 * @param array   $array
	 * @param integer $pad
	 *
	 * @return integer
	 *
	 * @deprecated Use hkm_setPad() instead.
	 *
	 * @codeCoverageIgnore
	 */
	public function getPad(array $array, int $pad): int
	{
		$max = 0;

		foreach (array_keys($array) as $key)
		{
			$max = max($max, strlen($key));
		}

		return $max + $pad;
	}

	/**
	 * Makes it simple to access our protected properties.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function __get(string $key)
	{
		if (isset($this->$key))
		{
			return $this->$key;
		}

		return null;
	}

	/**
	 * Makes it simple to check our protected properties.
	 *
	 * @param string $key
	 *
	 * @return boolean
	 */
	public function __isset(string $key): bool
	{
		return isset($this->$key);
	}
}
