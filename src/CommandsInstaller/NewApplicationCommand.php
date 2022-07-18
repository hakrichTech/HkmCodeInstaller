<?php

namespace Hkm_code\CommandsInstaller;

use Hkm_code\CLI\BaseCommand;
use Hkm_code\CLI\GeneratorTrait;

class NewApplicationCommand extends BaseCommand
{
	use GeneratorTrait;

	/**
	 * The Command's Group
	 *
	 * @var string
	 */
	protected $group = 'Commands';

	/**
	 * The Command's Name
	 *
	 * @var string
	 */
	protected $name = 'new:app';

	/**
	 * The Command's Description
	 *
	 * @var string
	 */
	protected $description = 'Create new application project';

	/**
	 * The Command's Usage
	 *
	 * @var string
	 */
	protected $usage = 'new:app <name> [options]';

	/**
	 * The Command's Arguments
	 *
	 * @var array
	 */
	protected $arguments = [
		'name' => 'The command class name.'
	];

	/**
	 * The Command's Options
	 *
	 * @var array
	 */
	protected $options = [
		'--auth'       => 'The command name. Default: "command:name"',
		'--type'      => 'The command type. Options [Commands, generator]. Default: "basic".',
		'--group'     => 'The command group. Default: [Commands -> "Hkm_code", generator -> "Generators"].',
		'--namespace' => 'Set root namespace. Default: "SYSTEM_COMMANDS_NAMESPACE".',
		'--suffix'    => 'Append the component title to the class name (e.g. User => UserCommand).',
		'--force'     => 'Force overwrite existing file.',
	];

	/**
	 * Actually execute a command.
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
		$this->component = 'HakrichApp';
		$this->template  = 'command.tpl.php';
         
		$this->executeApp($params);
	}
}
 