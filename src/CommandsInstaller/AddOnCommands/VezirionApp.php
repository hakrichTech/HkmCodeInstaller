<?php

namespace Hkm_code\CommandsInstaller\AddOnCommands;

use Hkm_code\CLI\BaseCommand;
use Hkm_code\CLI\CLI;
use Hkm_code\CLI\VezirionTrait;

class VezirionApp extends BaseCommand
{
	
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
	protected $name = 'config:app';

	/**
	 * The Command's Description
	 *
	 * @var string
	 */
	protected $description = 'To config your application settings.';

	/**
	 * The Command's Usage
	 *
	 * @var string
	 */
	protected $usage = 'config:app';

	/**
	 * The Command's Arguments
	 *
	 * @var array
	 */
	protected $arguments = [];

	protected $configType = [];

	/**
	 * The Command's Options
	 *
	 * @var array
	 */
	protected $options = [
		'-list' => 'Prints a list of the config type in your application',
		'-t' => 'Set config type',
		'-d' => 'Show config list with description',
		'-v' => 'Show config list with values',
		'-nv' => 'Show config list with no values',
		'-namespace' => 'Show config list with no values',
		'-app' => 'Show config list with no values default is [APP_NAME]',
	];

	private $properties = [];
	private $namespace;
	private $appName;


	/**
	 * Actually execute a command.
	 *
	 * @param array $params
	 */
	use VezirionTrait;
	public function run(array $params)
	{
		$type = $this->getOption('t')??CLI::prompt('Set your config type', null, 'required');
		$this->vezirionType = ucfirst(strtolower($type));
		$this->namespace = $this->getOption('namespace')??'vezirion';
		$this->appName = $this->getOption('app')??APP_NAME;

		$this->execute($this->vezirionType, $this->namespace,$this->appName);			
	}

}
