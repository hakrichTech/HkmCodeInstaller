<?php

namespace Hkm_code\CommandsInstaller\AddOnCommands;

use Hkm_code\CLI\BaseCommand;
use Hkm_code\CLI\CLI;
use Hkm_code\Vezirion\ServicesSystem;

class CreatePobohet extends BaseCommand
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
	protected $name = 'make:route';

	/**
	 * The Command's Description
	 *
	 * @var string
	 */
	protected $description = '';

	/**
	 * The Command's Usage
	 *
	 * @var string
	 */ 
	protected $usage = 'make:route <name>';

	/**
	 * The Command's Arguments
	 *
	 * @var array
	 */
	protected $arguments = [
		'name' => 'route name'
	];

	/**
	 * The Command's Options
	 *
	 * @var array
	 */
	protected $options = [
		'--controller' => 'Route namespace by Default APP_NAMESPACE',
		'--method' => 'Route namespace by Default APP_NAMESPACE',
		'--namespace' => 'Route namespace by Default APP_NAMESPACE',
		'--suffix' => 'ff',
	];

	/**
	 * Actually execute a command.
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
		
	}

    public function FunctionName()
    {
        # code...
    }
}
