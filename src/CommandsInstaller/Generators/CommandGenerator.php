<?php

/**
 * This file is part of the Hkm_code 4 framework.
 *
 * (c) Hkm_code Foundation <admin@Hkm_code.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hkm_code\CommandsInstaller\Generators;

use Hkm_code\CLI\BaseCommand;
use Hkm_code\CLI\CLI;
use Hkm_code\CLI\GeneratorTrait;

/** 
 * Generates a skeleton command file.
 */
class CommandGenerator extends BaseCommand
{
	use GeneratorTrait;

	/**
	 * The Command's Group
	 *
	 * @var string
	 */
	protected $group = 'Generators';

	/**
	 * The Command's Name
	 *
	 * @var string
	 */
	protected $name = 'make:command';

	/**
	 * The Command's Description
	 *
	 * @var string
	 */
	protected $description = 'Generates a new spark command.';

	/**
	 * The Command's Usage
	 *
	 * @var string
	 */
	protected $usage = 'make:command <name> [options]';

	/**
	 * The Command's Arguments
	 *
	 * @var array
	 */
	protected $arguments = [
		'name' => 'The command class name.',
	];

	/**
	 * The Command's Options
	 *
	 * @var array
	 */
	protected $options = [
		'--command'   => 'The command name. Default: "command:name"',
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
		$this->component = 'Command';
		$this->directory = 'Commands\AddOnCommands';
		$this->template  = 'command.tpl.php';

		$this->classNameLang = 'CLI.generator.className.command';
		$this->execute($params);
	} 

	/**
	 * Prepare options and do the necessary replacements.
	 *
	 * @param string $class
	 *
	 * @return string
	 */
	protected function prepare(string $class): string
	{
		$command = $this->getOption('command');
		$group   = $this->getOption('group');
		$type    = $this->getOption('type');

		$command = strtolower(is_string($command) ? $command : CLI::prompt(hkm_lang('CLI.generator.commandName'), null, 'required'));
		$type    = is_string($type) ? $type : 'Commands';

		if (! in_array($type, ['Commands', 'Generator'], true))
		{
			// @codeCoverageIgnoreStart
			$type = CLI::prompt(hkm_lang('CLI.generator.commandType'), ['Commands', 'generator'], 'required');
			CLI::newLine();
			// @codeCoverageIgnoreEnd
		}

		if (! is_string($group))
		{
			$group = $type === 'generator' ? 'Generators' : $type;
		}

		return $this->parseTemplate(
			$class,
			['{group}', '{command}'],
			[$group, $command],
			['type' => $type]
		);
	}
}
