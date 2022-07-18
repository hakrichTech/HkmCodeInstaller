<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@Hkm_code.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\CommandsInstaller\Command;

use Hkm_code\CLI\CLI;
use Hkm_traits\AddOn\ListCommandsTrait;
use Hkm_code\CLI\BaseCommand;
use Hkm_code\CLI\CommandsInstaller;
use Psr\Log\LoggerInterface;

/**
 * CI Help command for the spark script.
 *
 * Lists the basic usage information fo r the spark script,
 * and provides a way to list help for other commands.
 */
class ListCommands extends BaseCommand
{
	/**
	 * The group the command is lumped under
	 * when listing commands.
	 *
	 * @var string
	 */
	protected $group = 'Commands';

	/**
	 * The Command's name
	 *
	 * @var string
	 */
	protected $name = 'info:system';

	/**
	 * the Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Lists the available commands';

	/**
	 * the Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'list';

	/**
	 * the Command's Arguments
	 *
	 * @var array
	 */
	protected $arguments = [];

	/**
	 * the Command's Options
	 *
	 * @var array
	 */
	protected $options = [
		'--simple' => 'Prints a list of the commands with no other info',
	];

	public function __construct(LoggerInterface $logger, CommandsInstaller $commands)
	{
		parent::__construct($logger,$commands);
	}
	//--------------------------------------------------------------------

	/**
	 * Displays the help for the spark cli script itself.
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
		$commands = $this->commands::GET_COMMANDS();
		ksort($commands);

		// Check for 'simple' format
		return array_key_exists('simple', $params) || CLI::getOption('simple')
			? $this->listSimple($commands)
			: $this->listFull($commands);
	}
	use ListCommandsTrait;

	
}
